<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
require_once 'db_connect.php';

// Get the action from the request
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Handle different CRUD operations
switch ($action) {
    case 'create':
        createInventoryItem();
        break;
    case 'read':
        readInventoryItems();
        break;
    case 'update':
        updateInventoryItem();
        break;
    case 'delete':
        deleteInventoryItem();
        break;
    case 'quickUpdate':
        quickUpdateStock();
        break;
    case 'getMenuItems':
        getMenuItems();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

// Create new inventory item
function createInventoryItem() {
    global $conn;
    
    try {
        $menu_id = $_POST['menu_id'] ?? '';
        $stock_quantity = $_POST['stock_quantity'] ?? 0;
        
        // Validate input
        if (empty($menu_id) || !is_numeric($stock_quantity)) {
            echo json_encode(['success' => false, 'message' => 'Invalid input data']);
            return;
        }
        
        // Check if inventory item already exists for this menu item
        $checkStmt = $conn->prepare("SELECT id FROM menu_inventory WHERE menu_id = ?");
        $checkStmt->bind_param("i", $menu_id);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Inventory item already exists for this menu item']);
            return;
        }
        
        // Insert new inventory item
        $stmt = $conn->prepare("INSERT INTO menu_inventory (menu_id, stock_quantity) VALUES (?, ?)");
        $stmt->bind_param("ii", $menu_id, $stock_quantity);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Inventory item created successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create inventory item: ' . $conn->error]);
        }
        
        $stmt->close();
        $checkStmt->close();
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

// Read inventory items
function readInventoryItems() {
    global $conn;
    
    try {
        $id = $_GET['id'] ?? null;
        
        if ($id) {
            // Get specific inventory item
            $sql = "SELECT mi.*, m.name as menu_name, m.price as menu_price, m.category as menu_category 
                    FROM menu_inventory mi 
                    JOIN menu m ON mi.menu_id = m.id 
                    WHERE mi.id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
        } else {
            // Get all inventory items but only pastries category
            $sql = "SELECT mi.*, m.name as menu_name, m.price as menu_price, m.category as menu_category 
                FROM menu_inventory mi 
                JOIN menu m ON mi.menu_id = m.id 
                WHERE m.category = 'pastries' 
                ORDER BY m.name";
            $stmt = $conn->prepare($sql);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $data]);
        $stmt->close();
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

// Update inventory item
function updateInventoryItem() {
    global $conn;
    
    try {
        $id = $_POST['id'] ?? '';
        $menu_id = $_POST['menu_id'] ?? '';
        $stock_quantity = $_POST['stock_quantity'] ?? 0;
        
        // Validate input
        if (empty($id) || empty($menu_id) || !is_numeric($stock_quantity)) {
            echo json_encode(['success' => false, 'message' => 'Invalid input data']);
            return;
        }
        
        // Ensure the referenced menu item belongs to pastries category
        $menuCheck = $conn->prepare("SELECT category FROM menu WHERE id = ? LIMIT 1");
        $menuCheck->bind_param('i', $menu_id);
        $menuCheck->execute();
        $menuRes = $menuCheck->get_result();
        if ($menuRes->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Referenced menu item not found']);
            return;
        }
        $menuRow = $menuRes->fetch_assoc();
        if (strtolower($menuRow['category']) !== 'pastries') {
            echo json_encode(['success' => false, 'message' => 'Can only create/update inventory for pastries category']);
            return;
        }

        // Check if another inventory item exists for this menu item (excluding current item)
        $checkStmt = $conn->prepare("SELECT id FROM menu_inventory WHERE menu_id = ? AND id != ?");
        $checkStmt->bind_param("ii", $menu_id, $id);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Another inventory item already exists for this menu item']);
            return;
        }
        
        // Update inventory item
        $stmt = $conn->prepare("UPDATE menu_inventory SET menu_id = ?, stock_quantity = ? WHERE id = ?");
        $stmt->bind_param("iii", $menu_id, $stock_quantity, $id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Inventory item updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No changes made or item not found']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update inventory item: ' . $conn->error]);
        }
        
        $stmt->close();
        $checkStmt->close();
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

// Delete inventory item
function deleteInventoryItem() {
    global $conn;
    
    try {
        $id = $_POST['id'] ?? '';
        
        // Validate input
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'Invalid input data']);
            return;
        }
        
        // Check if item exists
        $checkStmt = $conn->prepare("SELECT id FROM menu_inventory WHERE id = ?");
        $checkStmt->bind_param("i", $id);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Inventory item not found']);
            return;
        }
        
        // Delete inventory item
        $stmt = $conn->prepare("DELETE FROM menu_inventory WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Inventory item deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete inventory item: ' . $conn->error]);
        }
        
        $stmt->close();
        $checkStmt->close();
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

// Quick update stock (add or remove)
function quickUpdateStock() {
    global $conn;
    
    try {
        $id = $_POST['id'] ?? '';
        $operation = $_POST['operation'] ?? ''; // 'add' or 'remove'
        $amount = $_POST['amount'] ?? 0;
        
        // Validate input
        if (empty($id) || empty($operation) || !is_numeric($amount) || $amount <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid input data']);
            return;
        }
        
        // Get current stock
        $stmt = $conn->prepare("SELECT stock_quantity, menu_id FROM menu_inventory WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Inventory item not found']);
            return;
        }
        
        $row = $result->fetch_assoc();
        $currentStock = $row['stock_quantity'];
        $menuId = $row['menu_id'];
        
        // Calculate new stock
        if ($operation === 'add') {
            $newStock = $currentStock + $amount;
        } elseif ($operation === 'remove') {
            $newStock = $currentStock - $amount;
            if ($newStock < 0) {
                echo json_encode(['success' => false, 'message' => 'Cannot remove more stock than available']);
                return;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid operation']);
            return;
        }
        
        // Update stock
        $updateStmt = $conn->prepare("UPDATE menu_inventory SET stock_quantity = ? WHERE id = ?");
        $updateStmt->bind_param("ii", $newStock, $id);
        
        if ($updateStmt->execute()) {
            $operationText = $operation === 'add' ? 'added' : 'removed';
            $preposition = $operation === 'add' ? 'to' : 'from';
            echo json_encode([
                'success' => true, 
                'message' => "Successfully {$operationText} {$amount} units {$preposition} inventory",
                'new_stock' => $newStock
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update stock: ' . $conn->error]);
        }
        
        $stmt->close();
        $updateStmt->close();
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

// Get menu items for dropdown
function getMenuItems() {
    global $conn;
    
    try {
        $sql = "SELECT id, name, price, category FROM menu ORDER BY category, name";
        $result = $conn->query($sql);
        
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