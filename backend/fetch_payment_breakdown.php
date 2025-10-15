<?php
header('Content-Type: application/json');
include 'db_sales_connect.php';

$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;

$query = "SELECT payment_method, percentage FROM payment_breakdown WHERE 1=1";

if ($start_date && $end_date) {
    $query .= " AND date BETWEEN '$start_date' AND '$end_date'";
} elseif ($end_date) {
    $query .= " AND date = '$end_date'";
}

$query .= " ORDER BY percentage DESC";

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
