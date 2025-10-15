<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

include 'db_connect.php';

// Get the raw POST data
$input = file_get_contents("php://input");
error_log("=== PAYMENT DEBUG ===");
error_log("Raw input received: " . $input);

// Log session data for debugging
error_log("Session data at payment: " . print_r($_SESSION, true));

$data = json_decode($input, true);

if (!$data) {
    error_log("JSON decode failed");
    echo json_encode(["status" => "error", "message" => "Invalid data received"]);
    exit;
}

error_log("Decoded payment data: " . print_r($data, true));

// FIXED: Try multiple sources for order_id
$order_id = $data['order_id'] ?? $_SESSION['pending_order_id'] ?? null;
$payment_method = $data['payment_method'] ?? 'cash';
$discount_type = $data['discount_type'] ?? 'none';
$discount_amount = floatval($data['discount_amount'] ?? 0);
$final_total = floatval($data['final_total'] ?? 0);
$cash_amount = floatval($data['cash_amount'] ?? 0);
$change_amount = floatval($data['change_amount'] ?? 0);
$reference_number = $data['reference_number'] ?? null;

error_log("Order ID from various sources:");
error_log(" - From POST data: " . ($data['order_id'] ?? 'NOT SET'));
error_log(" - From Session: " . ($_SESSION['pending_order_id'] ?? 'NOT SET'));
error_log(" - Final Order ID: " . $order_id);

if (!$order_id) {
    error_log("❌ No order ID found in any source");
    echo json_encode([
        "status" => "error", 
        "message" => "No order ID provided. Please start over.",
        "debug" => [
            "post_data" => $data,
            "session_data" => $_SESSION
        ]
    ]);
    exit;
}

// Validate reference number for GCash
if ($payment_method === 'gcash' && empty($reference_number)) {
    error_log("GCash payment missing reference number");
    echo json_encode(["status" => "error", "message" => "GCash reference number is required"]);
    exit;
}

try {
    // Check if order exists
    $check_sql = "SELECT id, staff_name, payment_status FROM orders WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    
    if (!$check_stmt) {
        throw new Exception("Check prepare failed: " . $conn->error);
    }
    
    $check_stmt->bind_param("i", $order_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $order = $result->fetch_assoc();
    $check_stmt->close();
    
    if (!$order) {
        throw new Exception("Order ID $order_id not found in database");
    }
    
    error_log("Found order in DB: " . print_r($order, true));

    // Store reference number in payment_method field
    $payment_method_display = $payment_method;
    if ($payment_method === 'gcash' && $reference_number) {
        $payment_method_display = 'GCash (' . $reference_number . ')';
    }

    // Update order with payment information
    $update_sql = "UPDATE orders SET
        payment_method = ?,
        discount_type = ?,
        discount_amount = ?,
        total_amount = ?,
        cash_amount = ?,
        change_amount = ?,
        payment_status = 'paid'
        WHERE id = ?";
    
    $update_stmt = $conn->prepare($update_sql);
    
    if (!$update_stmt) {
        throw new Exception("Update prepare failed: " . $conn->error);
    }
    
    $update_stmt->bind_param("ssdddii", 
        $payment_method_display,
        $discount_type, 
        $discount_amount, 
        $final_total, 
        $cash_amount, 
        $change_amount, 
        $order_id
    );
    
    if ($update_stmt->execute()) {
        error_log("✅ Successfully updated order ID: " . $order_id);
        
        // Clear the session after successful payment
        unset($_SESSION['pending_order_id']);
        unset($_SESSION['order_total']);
        
        echo json_encode([
            "status" => "success", 
            "message" => "Payment processed successfully",
            "order_id" => $order_id,
            "reference_number" => $reference_number
        ]);
    } else {
        throw new Exception("Failed to update order: " . $update_stmt->error);
    }
    
    $update_stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    error_log("Exception in payment processing: " . $e->getMessage());
    echo json_encode([
        "status" => "error", 
        "message" => "Database error: " . $e->getMessage(),
        "debug_info" => [
            "order_id" => $order_id,
            "input_data" => $data
        ]
    ]);
}
?>