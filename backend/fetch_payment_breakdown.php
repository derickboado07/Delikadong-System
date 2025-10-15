<?php
header('Content-Type: application/json');
include 'db_connect.php';

$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;

$query = "SELECT
    CASE
        WHEN payment_method LIKE 'GCash%' THEN 'GCash'
        WHEN payment_method = 'qrph' THEN 'QRPH'
        WHEN payment_method = 'Cash' THEN 'Cash'
        ELSE 'Other'
    END as payment_method,
    COUNT(*) * 100.0 / SUM(COUNT(*)) OVER() as percentage
FROM orders
WHERE payment_status = 'paid'
AND payment_method IS NOT NULL
AND payment_method != ''";

if ($start_date && $end_date) {
    $query .= " AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'";
} elseif ($end_date) {
    $query .= " AND DATE(created_at) = '$end_date'";
}

$query .= " GROUP BY
    CASE
        WHEN payment_method LIKE 'GCash%' THEN 'GCash'
        WHEN payment_method = 'qrph' THEN 'QRPH'
        WHEN payment_method = 'Cash' THEN 'Cash'
        ELSE 'Other'
    END
ORDER BY percentage DESC";

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
