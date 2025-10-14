<?php
include 'db_connect.php'; // Assuming this is in your backend folder

// 1. Get data from the client (e.g., via a POST request)
// NOTE: This is an example; your actual input data structure will vary.
$cart = json_decode(file_get_contents("php://input"), true)['items'];
$transaction_data = json_decode(file_get_contents("php://input"), true)['transaction'];

// 2. Generate a unique order group identifier
$new_order_group = 'ORD_' . time() . rand(100, 999);

// 3. Define the main transaction details (used for every item in the group)
$final_total = $transaction_data['final_total'] ?? 0.00;
$discount_amount = $transaction_data['discount_amount'] ?? 0.00;
$payment_method = $transaction_data['payment_method'] ?? 'Cash';
$cash_amount = $transaction_data['cash_amount'] ?? 0.00;
$change_amount = $transaction_data['change_amount'] ?? 0.00;
$payment_status = 'paid'; // Set status upon successful payment

// 4. Prepare the SQL statement for insertion
$stmt = $conn->prepare("INSERT INTO orders 
    (order_group, item_name, quantity, price, category, size, sugar_level, addons, extras, payment_method, final_total, discount_amount, cash_amount, change_amount, payment_status) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

// 5. Loop through cart items and execute the insertion for each one
foreach ($cart as $item) {
    // Note: Only the first item needs to carry the transaction totals for your load_orders.php to work
    // For simplicity, we'll send it for all items, but this can be optimized.
    $stmt->bind_param("ssidsissssdidds",
        $new_order_group,
        $item['name'],
        $item['quantity'],
        $item['price'],
        $item['category'],
        $item['size'],
        $item['sugar_level'],
        $item['addons'],
        $item['extras'],
        $payment_method,
        $final_total,
        $discount_amount,
        $cash_amount,
        $change_amount,
        $payment_status
    );
    $stmt->execute();
}

// 6. Respond to the client
if ($conn->error) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
} else {
    echo json_encode(["status" => "success", "order_group" => $new_order_group, "total_items" => count($cart)]);
}

$stmt->close();
$conn->close();
?>