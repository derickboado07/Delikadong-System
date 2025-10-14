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
            // First delete order_items (child records)
            $delete_items = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
            $delete_items->bind_param("i", $order_id);
            
            if (!$delete_items->execute()) {
                throw new Exception("Failed to delete order items: " . $delete_items->error);
            }
            $delete_items->close();
            
            // Then delete the order
            $delete_order = $conn->prepare("DELETE FROM orders WHERE id = ?");
            $delete_order->bind_param("i", $order_id);
            
            if ($delete_order->execute()) {
                $conn->commit();
                echo json_encode(["status" => "success", "message" => "Order completed and removed successfully"]);
            } else {
                throw new Exception("Failed to delete order: " . $delete_order->error);
            }
            
            $delete_order->close();
            
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