<?php
header('Content-Type: application/json');
include 'db_sales_connect.php';

$query = "SELECT month, year, sales FROM monthly_sales_trends ORDER BY year ASC, FIELD(month, 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec') ASC";

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
