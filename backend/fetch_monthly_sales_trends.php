<?php
header('Content-Type: application/json');
include 'db_connect.php';

// Get current year
$currentYear = date('Y');

// Get all months with sales data for current year
$query = "SELECT
    DATE_FORMAT(o.created_at, '%b') as month,
    MONTH(o.created_at) as month_num,
    YEAR(o.created_at) as year,
    SUM(o.total_amount) as sales
FROM orders o
WHERE o.payment_status = 'paid' AND YEAR(o.created_at) = $currentYear
GROUP BY YEAR(o.created_at), MONTH(o.created_at), DATE_FORMAT(o.created_at, '%b')
ORDER BY MONTH(o.created_at) ASC";

$result = $conn->query($query);

// Create a map of existing data
$salesData = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $salesData[$row['month']] = floatval($row['sales']);
    }
}

// Define all months
$months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

// Create complete dataset with 0 for missing months
$data = [];
foreach ($months as $month) {
    $data[] = [
        'month' => $month,
        'sales' => $salesData[$month] ?? 0
    ];
}

echo json_encode(["status" => "success", "data" => $data]);

$conn->close();
?>
