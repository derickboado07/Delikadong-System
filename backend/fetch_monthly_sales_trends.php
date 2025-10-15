<?php
header('Content-Type: application/json');
include 'db_connect.php';

$query = "SELECT
    DATE_FORMAT(o.created_at, '%b') as month,
    YEAR(o.created_at) as year,
    SUM(o.total_amount) as sales
FROM orders o
WHERE o.payment_status = 'paid'
GROUP BY YEAR(o.created_at), MONTH(o.created_at), DATE_FORMAT(o.created_at, '%b')
ORDER BY YEAR(o.created_at) ASC, MONTH(o.created_at) ASC";

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
