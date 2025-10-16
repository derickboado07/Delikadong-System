<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);
include 'db_connect.php';

// Ensure tables exist
$conn->query("CREATE TABLE IF NOT EXISTS ingredients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    unit VARCHAR(64) DEFAULT 'unit',
    stock_quantity DOUBLE NOT NULL DEFAULT 0,
    price DECIMAL(10,2) DEFAULT 0.00,
    last_updated TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS menu_recipes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    menu_id INT NOT NULL,
    ingredient_id INT NOT NULL,
    quantity_required DOUBLE NOT NULL DEFAULT 0,
    UNIQUE KEY unique_menu_ingredient (menu_id, ingredient_id)
)");

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

try {
    if ($action === 'list') {
        $res = $conn->query("SELECT * FROM ingredients ORDER BY name ASC");
        $rows = [];
        while ($r = $res->fetch_assoc()) $rows[] = $r;
        echo json_encode(['status'=>'ok','data'=>$rows]);
        exit;
    }

    if ($action === 'create') {
        $input = json_decode(file_get_contents('php://input'), true);
        $name = $input['name'] ?? '';
        $unit = $input['unit'] ?? 'unit';
        $stock = floatval($input['stock_quantity'] ?? 0);
        $price = floatval($input['price'] ?? 0);
        $stmt = $conn->prepare("INSERT INTO ingredients (name, unit, stock_quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssdd', $name, $unit, $stock, $price);
        $stmt->execute();
        echo json_encode(['status'=>'ok','id'=>$conn->insert_id]);
        exit;
    }

    if ($action === 'update') {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = intval($input['id'] ?? 0);
        $name = $input['name'] ?? '';
        $unit = $input['unit'] ?? 'unit';
        $stock = floatval($input['stock_quantity'] ?? 0);
        $price = floatval($input['price'] ?? 0);
        // properly bind parameters: name (s), unit (s), stock (d), price (d), id (i)
        $stmt = $conn->prepare("UPDATE ingredients SET name = ?, unit = ?, stock_quantity = ?, price = ? WHERE id = ?");
        $stmt->bind_param('ssddi', $name, $unit, $stock, $price, $id);
        $stmt->execute();
        echo json_encode(['status'=>'ok']);
        exit;
    }

    if ($action === 'delete') {
        // support id via GET, form POST or JSON body
        $id = 0;
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
        } else {
            $raw = file_get_contents('php://input');
            $json = $raw ? json_decode($raw, true) : null;
            if (is_array($json) && isset($json['id'])) {
                $id = intval($json['id']);
            } else {
                $id = intval($_POST['id'] ?? 0);
            }
        }
        if ($id <= 0) {
            echo json_encode(['status'=>'error','message'=>'Invalid id']);
            exit;
        }
        $stmt = $conn->prepare("DELETE FROM ingredients WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        echo json_encode(['status'=>'ok']);
        exit;
    }

    // Recipes management
    if ($action === 'getRecipes') {
        $menu_id = intval($_GET['menu_id'] ?? 0);
        $stmt = $conn->prepare("SELECT mr.id, mr.menu_id, mr.ingredient_id, mr.quantity_required, i.name, i.unit FROM menu_recipes mr LEFT JOIN ingredients i ON i.id = mr.ingredient_id WHERE mr.menu_id = ?");
        $stmt->bind_param('i', $menu_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($r = $res->fetch_assoc()) $rows[] = $r;
        echo json_encode(['status'=>'ok','data'=>$rows]);
        exit;
    }

    if ($action === 'saveRecipe') {
        $input = json_decode(file_get_contents('php://input'), true);
        $menu_id = intval($input['menu_id'] ?? 0);
        // If client provided an array of items, replace existing recipes for this menu
        if (!empty($input['items']) && is_array($input['items'])) {
            $items = $input['items'];
            // start transaction
            $conn->begin_transaction();
            try {
                // delete existing recipes for menu
                $del = $conn->prepare("DELETE FROM menu_recipes WHERE menu_id = ?");
                $del->bind_param('i', $menu_id);
                $del->execute();

                $ins = $conn->prepare("INSERT INTO menu_recipes (menu_id, ingredient_id, quantity_required) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity_required = VALUES(quantity_required)");
                foreach ($items as $it) {
                    $ingredient_id = intval($it['ingredient_id'] ?? 0);
                    $qty = floatval($it['quantity_required'] ?? 0);
                    if ($ingredient_id <= 0) continue;
                    $ins->bind_param('iid', $menu_id, $ingredient_id, $qty);
                    $ins->execute();
                }
                $conn->commit();
                echo json_encode(['status'=>'ok']);
                exit;
            } catch (Exception $e) {
                $conn->rollback();
                throw $e;
            }
        }

        // fallback: single item (legacy)
        $ingredient_id = intval($input['ingredient_id'] ?? 0);
        $qty = floatval($input['quantity_required'] ?? 0);
        $stmt = $conn->prepare("INSERT INTO menu_recipes (menu_id, ingredient_id, quantity_required) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity_required = VALUES(quantity_required)");
        $stmt->bind_param('iid', $menu_id, $ingredient_id, $qty);
        $stmt->execute();
        echo json_encode(['status'=>'ok']);
        exit;
    }

    if ($action === 'deleteRecipe') {
        $id = intval($_GET['id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM menu_recipes WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        echo json_encode(['status'=>'ok']);
        exit;
    }

    echo json_encode(['status'=>'error','message'=>'Unknown action']);
} catch (Exception $e) {
    error_log('ingredients_crud error: '.$e->getMessage());
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}

?>
