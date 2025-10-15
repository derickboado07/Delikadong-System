<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
session_start();

include 'db_connect.php';

// Get the raw POST data
$input = file_get_contents("php://input");
error_log("=== SAVE ORDER DEBUG ===");
error_log("Raw input: " . $input);

if (empty($input)) {
    echo json_encode(["status" => "error", "message" => "No data received"]);
    exit;
}

$data = json_decode($input, true);

if (!$data || !isset($data['orders'])) {
    echo json_encode(["status" => "error", "message" => "Missing orders data"]);
    exit;
}

$orders = $data['orders'];
$total = floatval($data['total'] ?? 0);

error_log("Processing " . count($orders) . " items, Total: " . $total);

// Start transaction
$conn->begin_transaction();

try {
    // Ensure order_items has a menu_id column (nullable)
    $dbNameResult = $conn->query("SELECT DATABASE() AS dbname");
    $dbName = $dbNameResult->fetch_assoc()['dbname'];
    $colCheckSql = "SELECT COUNT(*) AS cnt FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'order_items' AND COLUMN_NAME = 'menu_id'";
    $colCheckStmt = $conn->prepare($colCheckSql);
    $colCheckStmt->bind_param('s', $dbName);
    $colCheckStmt->execute();
    $colCnt = $colCheckStmt->get_result()->fetch_assoc();
    $colCheckStmt->close();
    if (intval($colCnt['cnt']) === 0) {
        $conn->query("ALTER TABLE order_items ADD COLUMN menu_id INT NULL AFTER order_id");
        error_log('Added menu_id column to order_items');
    }
    // 1. Create main order record
    $order_stmt = $conn->prepare("INSERT INTO orders (staff_name, subtotal, total_amount, payment_status) VALUES (?, ?, ?, 'unpaid')");
    $staff_name = "Cashier";
    
    $order_stmt->bind_param("sdd", $staff_name, $total, $total);
    
    if (!$order_stmt->execute()) {
        throw new Exception("Failed to create order: " . $order_stmt->error);
    }
    
    $order_id = $conn->insert_id;
    $order_stmt->close();
    
    error_log("✅ Created order with ID: " . $order_id);
    
    // 2. Insert order items - include menu_id if found by name
    // We'll use a prepared statement with a subquery to find menu_id by name (case-insensitive). If not found, menu_id will be NULL.
    $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, menu_id, item_name, category, size, sugar_level, addons, extras, quantity, price, total) VALUES (?, (SELECT id FROM menu WHERE LOWER(name) = LOWER(?) LIMIT 1), ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if (!$item_stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $successCount = 0;

    foreach ($orders as $index => $item) {
        $item_name = $item['name'] ?? 'Unknown Item';
        $category = $item['category'] ?? 'unknown';
        $size = $item['size'] ?? '';
        $sugar_level = $item['sugarLevel'] ?? '';
        $addons = !empty($item['addons']) ? json_encode($item['addons']) : '[]';
        $extras = !empty($item['extras']) ? json_encode($item['extras']) : '[]';
        $quantity = intval($item['quantity'] ?? 1);
        $price = floatval($item['basePrice'] ?? 0);
        $item_total = floatval($item['getTotal'] ?? $price * $quantity);
        
        error_log("📦 Inserting item $index: order_id=$order_id, name=$item_name, category=$category");
        
        $item_stmt->bind_param("issssssiddd", 
            $order_id, $item_name, $item_name, $category, $size, $sugar_level, $addons, $extras, $quantity, $price, $item_total);
        
        if ($item_stmt->execute()) {
            $successCount++;
            error_log("✅ Successfully inserted item: " . $item_name);
            // No inventory deduction here; inventory will be deducted when payment is processed
        } else {
            error_log("❌ Failed to insert item: " . $item_stmt->error);
            throw new Exception("Failed to insert item $item_name: " . $item_stmt->error);
        }
    }
    
    $item_stmt->close();
    
    // Commit transaction
    $conn->commit();
    
    error_log("🎉 Transaction committed. Successfully inserted $successCount items for order $order_id");
    
    // Store in session for payment page
    $_SESSION['pending_order_id'] = $order_id;
    $_SESSION['order_total'] = $total;
    
    echo json_encode([
        "status" => "success", 
        "message" => "Order created successfully",
        "order_id" => $order_id,
        "items_count" => $successCount,
        "total" => $total
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    error_log("💥 Transaction failed: " . $e->getMessage());
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

$conn->close();
?>