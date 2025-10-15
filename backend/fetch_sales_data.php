<?php
header('Content-Type: application/json');
include 'db_connect.php';

$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;

// If no dates provided, show today's data
if (!$start_date && !$end_date) {
    $query = "SELECT
        CURDATE() as date,
        COUNT(DISTINCT CASE WHEN DATE(o.created_at) = CURDATE() THEN o.id END) as orders_today,
        SUM(CASE WHEN DATE(o.created_at) = CURDATE() THEN o.total_amount ELSE 0 END) as gross_sales,
        SUM(CASE WHEN DATE(o.created_at) = CURDATE() THEN o.total_amount ELSE 0 END) as net_income,
        (SELECT oi.item_name FROM order_items oi
         JOIN orders ord ON oi.order_id = ord.id
         WHERE DATE(ord.created_at) = CURDATE()
         AND ord.payment_status = 'paid'
         GROUP BY oi.item_name
         ORDER BY SUM(oi.quantity) DESC
         LIMIT 1) as top_product
    FROM orders o
    WHERE o.payment_status = 'paid'";
} else {
    // If dates provided, show data for the period
    $query = "SELECT
        DATE(o.created_at) as date,
        COUNT(DISTINCT o.id) as orders_today,
        SUM(o.total_amount) as gross_sales,
        SUM(o.total_amount) as net_income,
        (SELECT oi.item_name FROM order_items oi
         JOIN orders ord ON oi.order_id = ord.id
         WHERE DATE(ord.created_at) BETWEEN '$start_date' AND '$end_date'
         AND ord.payment_status = 'paid'
         GROUP BY oi.item_name
         ORDER BY SUM(oi.quantity) DESC
         LIMIT 1) as top_product
    FROM orders o
    WHERE o.payment_status = 'paid'
    AND DATE(o.created_at) BETWEEN '$start_date' AND '$end_date'
    GROUP BY DATE(o.created_at)
    ORDER BY date DESC";
}

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
