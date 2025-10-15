<?php
header('Content-Type: application/json');
include 'db_sales_connect.php';

$limit = $_GET['limit'] ?? 15;

$query = "SELECT order_number, amount, DATE_FORMAT(date_time, '%m-%d-%y, %H:%i') as formatted_date_time FROM recent_orders ORDER BY date_time DESC LIMIT $limit";

$result = $conn_sales->query($query);

if ($result) {
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $data]);
} else {
    echo json_encode(["status" => "error", "message" => "Query failed: " . $conn_sales->error]);
}

$conn_sales->close();
?>
