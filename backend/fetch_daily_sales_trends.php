<?php
header('Content-Type: application/json');
include 'db_connect.php';

$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;

$query = "SELECT
    DATE(o.created_at) as date,
    DAYNAME(o.created_at) as day_of_week,
    SUM(o.total_amount) as sales
FROM orders o
WHERE o.payment_status = 'paid'";

if ($start_date && $end_date) {
    $query .= " AND DATE(o.created_at) BETWEEN '$start_date' AND '$end_date'";
} elseif ($end_date) {
    $query .= " AND DATE(o.created_at) = '$end_date'";
}

$query .= " GROUP BY DATE(o.created_at), DAYNAME(o.created_at)
ORDER BY DATE(o.created_at) ASC";

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
