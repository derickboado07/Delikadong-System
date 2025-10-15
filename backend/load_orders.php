<?php
include 'db_connect.php';
header('Content-Type: application/json');

// DEBUG: Log what we're doing
error_log("=== LOAD ORDERS START ===");

// UPDATED: Remove image from query
$result = $conn->query("
    SELECT
        o.id as order_id,
        o.staff_name,
        o.order_status,
        o.subtotal,
        o.discount_type,
        o.discount_amount,
        o.total_amount,
        o.payment_method,
        o.payment_status,
        o.cash_amount,
        o.change_amount,
        o.created_at,
        oi.id as item_id,
        oi.item_name,
        oi.category,
        oi.size,
        oi.sugar_level,
        oi.addons,
        oi.extras,
        oi.quantity,
        oi.price as item_price,
        oi.total as item_total
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.payment_status = 'paid'
    AND (o.order_status IS NULL OR o.order_status != 'completed')
    ORDER BY o.created_at DESC, oi.id ASC
");

if (!$result) {
    error_log("Query failed: " . $conn->error);
    echo json_encode([
        "status" => "error", 
        "message" => "Database query failed: " . $conn->error,
        "orders" => []
    ]);
    exit;
}

error_log("Query returned " . $result->num_rows . " rows");

$orders = [];
$order_count = 0;
$item_count = 0;

while ($row = $result->fetch_assoc()) {
    $order_id = $row['order_id'];
    
    if (!isset($orders[$order_id])) {
        $orders[$order_id] = [
            "id" => $order_id,
            "staff_name" => $row['staff_name'],
            "order_status" => $row['order_status'],
            "subtotal" => floatval($row['subtotal'] ?? 0),
            "discount_type" => $row['discount_type'] ?? 'none',
            "discount_amount" => floatval($row['discount_amount'] ?? 0),
            "total_amount" => floatval($row['total_amount'] ?? 0),
            "payment_method" => $row['payment_method'] ?? 'cash',
            "payment_status" => $row['payment_status'] ?? 'paid',
            "cash_amount" => floatval($row['cash_amount'] ?? 0),
            "change_amount" => floatval($row['change_amount'] ?? 0),
            "created_at" => $row['created_at'],
            "items" => []
        ];
        $order_count++;
    }
    
    // Add item if it exists
    if ($row['item_id'] !== null && !empty($row['item_name'])) {
        // FIX: Properly decode extras - don't encode again
        $extras_data = [];
        if (!empty($row['extras'])) {
            // Check if it's already a JSON string or needs decoding
            if (is_string($row['extras']) && $row['extras'][0] === '[') {
                $extras_data = json_decode($row['extras'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    error_log("JSON decode error for extras: " . json_last_error_msg());
                    $extras_data = [];
                }
            } else {
                // If it's not JSON, try to handle it as a string
                $extras_data = [$row['extras']];
            }
        }
        
        // FIX: Also properly decode addons
        $addons_data = [];
        if (!empty($row['addons'])) {
            if (is_string($row['addons']) && $row['addons'][0] === '[') {
                $addons_data = json_decode($row['addons'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    error_log("JSON decode error for addons: " . json_last_error_msg());
                    $addons_data = [];
                }
            } else {
                $addons_data = [$row['addons']];
            }
        }
        
        $orders[$order_id]["items"][] = [
            "id" => $row['item_id'],
            "name" => $row['item_name'],
            "category" => $row['category'] ?? '',
            "size" => $row['size'] ?? '',
            "sugar_level" => $row['sugar_level'] ?? '',
            "addons" => $addons_data,
            "extras" => $extras_data, // FIXED: Now properly decoded
            "quantity" => intval($row['quantity'] ?? 1),
            "price" => floatval($row['item_price'] ?? 0),
            "total" => floatval($row['item_total'] ?? 0)
        ];
        $item_count++;
        
        error_log("✅ Added item: " . $row['item_name'] . " | Extras: " . print_r($extras_data, true));
    }
}

error_log("Processed $order_count orders with $item_count items total");

echo json_encode([
    "status" => "success",
    "orders" => array_values($orders),
    "debug" => [
        "total_orders" => $order_count,
        "total_items" => $item_count,
        "query_rows" => $result->num_rows
    ]
]);

$conn->close();
?>