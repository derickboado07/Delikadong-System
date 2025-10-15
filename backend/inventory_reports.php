<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
require_once 'db_connect.php';

// Get the action from the request
$action = $_GET['action'] ?? '';

// Handle different report requests
switch ($action) {
    case 'summary':
        getSummaryData();
        break;
    case 'charts':
        getChartData();
        break;
    case 'lowStock':
        getLowStockItems();
        break;
    case 'recentUpdates':
        getRecentUpdates();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

// Get summary statistics
function getSummaryData() {
    global $conn;
    
    try {
        // Total items
        $totalItemsQuery = "SELECT COUNT(*) as total FROM menu_inventory";
        $totalResult = $conn->query($totalItemsQuery);
        $totalItems = $totalResult->fetch_assoc()['total'];
        
        // Low stock items (1-5 units)
        $lowStockQuery = "SELECT COUNT(*) as total FROM menu_inventory WHERE stock_quantity > 0 AND stock_quantity <= 5";
        $lowStockResult = $conn->query($lowStockQuery);
        $lowStockItems = $lowStockResult->fetch_assoc()['total'];
        
        // Out of stock items
        $outOfStockQuery = "SELECT COUNT(*) as total FROM menu_inventory WHERE stock_quantity = 0";
        $outOfStockResult = $conn->query($outOfStockQuery);
        $outOfStockItems = $outOfStockResult->fetch_assoc()['total'];
        
        // Total inventory value
        $valueQuery = "SELECT SUM(mi.stock_quantity * m.price) as total_value 
                       FROM menu_inventory mi 
                       JOIN menu m ON mi.menu_id = m.id";
        $valueResult = $conn->query($valueQuery);
        $totalValue = $valueResult->fetch_assoc()['total_value'] ?? 0;
        
        $data = [
            'total_items' => $totalItems,
            'low_stock_items' => $lowStockItems,
            'out_of_stock_items' => $outOfStockItems,
            'total_value' => $totalValue
        ];
        
        echo json_encode(['success' => true, 'data' => $data]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

// Get chart data
function getChartData() {
    global $conn;
    
    try {
        // Category distribution data
        $categoryQuery = "SELECT m.category, SUM(mi.stock_quantity) as total_stock 
                         FROM menu_inventory mi 
                         JOIN menu m ON mi.menu_id = m.id 
                         GROUP BY m.category 
                         ORDER BY total_stock DESC";
        $categoryResult = $conn->query($categoryQuery);
        
        $categoryData = [];
        while ($row = $categoryResult->fetch_assoc()) {
            $categoryData[] = $row;
        }
        
        // Status distribution data
        $statusQuery = "SELECT 
                          SUM(CASE WHEN stock_quantity > 5 THEN 1 ELSE 0 END) as in_stock,
                          SUM(CASE WHEN stock_quantity > 0 AND stock_quantity <= 5 THEN 1 ELSE 0 END) as low_stock,
                          SUM(CASE WHEN stock_quantity = 0 THEN 1 ELSE 0 END) as out_of_stock
                        FROM menu_inventory";
        $statusResult = $conn->query($statusQuery);
        $statusData = $statusResult->fetch_assoc();
        
        $data = [
            'category_data' => $categoryData,
            'status_data' => $statusData
        ];
        
        echo json_encode(['success' => true, 'data' => $data]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

// Get low stock items
function getLowStockItems() {
    global $conn;
    
    try {
        $query = "SELECT mi.id, mi.stock_quantity, m.name as menu_name, m.price as menu_price, m.category as menu_category
                  FROM menu_inventory mi 
                  JOIN menu m ON mi.menu_id = m.id 
                  WHERE mi.stock_quantity <= 5
                  ORDER BY mi.stock_quantity ASC, m.name ASC";
        
        $result = $conn->query($query);
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $data]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

// Get recent updates
function getRecentUpdates() {
    global $conn;
    
    try {
        $query = "SELECT mi.stock_quantity, mi.last_updated, m.name as menu_name, m.category as menu_category
                  FROM menu_inventory mi 
                  JOIN menu m ON mi.menu_id = m.id 
                  ORDER BY mi.last_updated DESC 
                  LIMIT 10";
        
        $result = $conn->query($query);
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $data]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

// Close database connection
$conn->close();
?>