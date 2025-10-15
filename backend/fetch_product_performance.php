<?php
header('Content-Type: application/json');
include 'db_sales_connect.php';

$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;

$query = "SELECT product_name, SUM(quantity_sold) as total_quantity, SUM(sales) as total_sales FROM product_performance WHERE 1=1";

if ($start_date && $end_date) {
    $query .= " AND date BETWEEN '$start_date' AND '$end_date'";
} elseif ($end_date) {
    $query .= " AND date = '$end_date'";
}

$query .= " GROUP BY product_name ORDER BY total_sales DESC LIMIT 10";

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
