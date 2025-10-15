<?php
header('Content-Type: application/json');
include 'db_connect.php';

$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;

// Get all days of the week with sales data
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

// Create a map of existing data
$salesData = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $salesData[$row['day_of_week']] = floatval($row['sales']);
    }
}

// Define all days of the week
$daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

// Create complete dataset with 0 for missing days
$data = [];
foreach ($daysOfWeek as $day) {
    $data[] = [
        'day_of_week' => $day,
        'sales' => $salesData[$day] ?? 0
    ];
}

echo json_encode(["status" => "success", "data" => $data]);

$conn->close();
?>
