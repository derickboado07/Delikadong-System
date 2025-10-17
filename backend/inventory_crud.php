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
    case 'checkAvailability':
        checkAvailability();
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
            // Get all inventory items
            $sql = "SELECT mi.*, m.name as menu_name, m.price as menu_price, m.category as menu_category
                FROM menu_inventory mi
                JOIN menu m ON mi.menu_id = m.id
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
        
        // Allow all menu categories for inventory

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
        $sql = "SELECT id, name, price, category FROM menu WHERE status = 'active' ORDER BY category, name";
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

// Check availability of menu items and their ingredients
function checkAvailability() {
    global $conn;

    try {
        $menu_items = json_decode($_POST['menu_items'] ?? '[]', true);

        if (empty($menu_items)) {
            echo json_encode(['success' => false, 'message' => 'No menu items provided']);
            return;
        }

        $unavailable_items = [];
        $insufficient_ingredients = [];

        foreach ($menu_items as $item) {
            $menu_id = $item['menu_id'] ?? null;
            $quantity = $item['quantity'] ?? 1;

            if (!$menu_id) continue;

            // Check if menu item has inventory
            $invStmt = $conn->prepare("SELECT stock_quantity FROM menu_inventory WHERE menu_id = ?");
            $invStmt->bind_param("i", $menu_id);
            $invStmt->execute();
            $invResult = $invStmt->get_result();

            if ($invResult->num_rows === 0) {
                // No inventory record - item is unavailable
                $menuStmt = $conn->prepare("SELECT name FROM menu WHERE id = ?");
                $menuStmt->bind_param("i", $menu_id);
                $menuStmt->execute();
                $menuRow = $menuStmt->get_result()->fetch_assoc();
                $unavailable_items[] = $menuRow['name'] ?? 'Unknown Item';
                $menuStmt->close();
                $invStmt->close();
                continue;
            }

            $invRow = $invResult->fetch_assoc();
            $available_stock = $invRow['stock_quantity'];

            if ($available_stock < $quantity) {
                // Insufficient stock
                $menuStmt = $conn->prepare("SELECT name FROM menu WHERE id = ?");
                $menuStmt->bind_param("i", $menu_id);
                $menuStmt->execute();
                $menuRow = $menuStmt->get_result()->fetch_assoc();
                $unavailable_items[] = $menuRow['name'] ?? 'Unknown Item';
                $menuStmt->close();
                $invStmt->close();
                continue;
            }

            $invStmt->close();

            // Check ingredients availability
            $recipeStmt = $conn->prepare("SELECT i.name as ingredient_name, mr.quantity_required, i.stock_quantity
                                        FROM menu_recipes mr
                                        JOIN ingredients i ON mr.ingredient_id = i.id
                                        WHERE mr.menu_id = ?");
            $recipeStmt->bind_param("i", $menu_id);
            $recipeStmt->execute();
            $recipeResult = $recipeStmt->get_result();

            while ($recipeRow = $recipeResult->fetch_assoc()) {
                $required = $recipeRow['quantity_required'] * $quantity;
                $available = $recipeRow['stock_quantity'];

                if ($available < $required) {
                    $insufficient_ingredients[] = $recipeRow['ingredient_name'];
                }
            }

            $recipeStmt->close();
        }

        $response = ['success' => true];

        if (!empty($unavailable_items)) {
            $response['product_unavailable'] = true;
            $response['unavailable_products'] = $unavailable_items;
        }

        if (!empty($insufficient_ingredients)) {
            $response['ingredients_unavailable'] = true;
            $response['insufficient_ingredients'] = array_unique($insufficient_ingredients);
        }

        echo json_encode($response);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

// Close database connection
$conn->close();
?>
