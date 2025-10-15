<?php
header('Content-Type: application/json');
include 'db_connect.php';

$limit = $_GET['limit'] ?? 15;

$query = "SELECT
    o.id as order_number,
    o.total_amount as amount,
    DATE_FORMAT(o.created_at, '%m-%d-%y, %H:%i') as formatted_date_time
FROM orders o
WHERE o.payment_status = 'paid'
AND (o.order_status = 'completed' OR o.order_status IS NULL)
ORDER BY o.created_at DESC
LIMIT " . intval($limit);

$result = $conn->query($query);

if ($result) {
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $data]);
} else {
    echo json_encode(["status" => "error", "message" => "Query failed: " . $conn->error]);
}

$conn->close();
?>
