<?php
header('Content-Type: application/json');
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);
    
    $order_id = $data['order_id'] ?? null;
    
    if ($order_id) {
        // Start transaction for safety
        $conn->begin_transaction();
        
        try {
            // Update order status to completed (keep for dashboard)
            $update_order = $conn->prepare("UPDATE orders SET order_status = 'completed' WHERE id = ?");
            $update_order->bind_param("i", $order_id);

            if ($update_order->execute()) {
                $conn->commit();
                echo json_encode(["status" => "success", "message" => "Order marked as completed successfully"]);
            } else {
                throw new Exception("Failed to update order: " . $update_order->error);
            }

            $update_order->close();
            
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "No order ID provided"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}

$conn->close();
?>