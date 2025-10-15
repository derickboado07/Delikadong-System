<?php
header('Content-Type: application/json');
include 'db_connect.php';

$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;

$query = "SELECT
    oi.item_name as product_name,
    SUM(oi.quantity) as total_quantity,
    SUM(oi.total) as total_sales
FROM order_items oi
JOIN orders o ON oi.order_id = o.id
WHERE o.payment_status = 'paid'";

if ($start_date && $end_date) {
    $query .= " AND DATE(o.created_at) BETWEEN '$start_date' AND '$end_date'";
} elseif ($end_date) {
    $query .= " AND DATE(o.created_at) = '$end_date'";
}

$query .= " GROUP BY oi.item_name ORDER BY total_sales DESC LIMIT 10";

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
