<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

try {
    if ($action === 'list') {
        // pagination, search, category filter
        $q = $_GET['q'] ?? '';
        $category = $_GET['category'] ?? '';
        $page = max(1, intval($_GET['page'] ?? 1));
        $perPage = max(5, intval($_GET['per_page'] ?? 10));
        $offset = ($page - 1) * $perPage;

        $params = [];
        $where = [];
        if ($q !== '') { $where[] = "(LOWER(name) LIKE ?)"; $params[] = '%'.strtolower($q).'%'; }
        if ($category !== '') { $where[] = "category = ?"; $params[] = $category; }

        $whereSql = $where ? 'WHERE '.implode(' AND ', $where) : '';

        $countSql = "SELECT COUNT(*) AS cnt FROM menu $whereSql";
        $stmt = $conn->prepare($countSql);
        if ($params) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $total = $stmt->get_result()->fetch_assoc()['cnt'] ?? 0;
        $stmt->close();

    $sql = "SELECT id, name, price, category, image FROM menu $whereSql ORDER BY category, name LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);
        // bind params + perPage + offset
        $bindParams = $params;
        $bindTypes = $params ? str_repeat('s', count($params)) : '';
        $bindTypes .= 'ii';
        $bindParams[] = $perPage; $bindParams[] = $offset;
        if ($bindParams) {
            $stmt->bind_param($bindTypes, ...$bindParams);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($r = $res->fetch_assoc()) $rows[] = $r;
        echo json_encode(['success'=>true,'data'=>$rows,'pagination'=>['page'=>$page,'per_page'=>$perPage,'total'=>$total]]);
        exit;
    }

    if ($action === 'get') {
        $id = intval($_GET['id'] ?? 0);
        $stmt = $conn->prepare("SELECT id, name, price, category FROM menu WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        echo json_encode(['success'=>true,'data'=>$row]); exit;
    }

    if ($action === 'create') {
        // support JSON body or multipart/form-data with file upload
        $name = $price = $category = $imageFilename = null;
        if (!empty($_FILES) || strpos($_SERVER['CONTENT_TYPE'] ?? '', 'multipart/form-data') !== false) {
            // ensure Images directory exists
            $uploadDir = __DIR__ . '/../Images/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $name = $_POST['name'] ?? '';
            $price = floatval($_POST['price'] ?? 0);
            $category = $_POST['category'] ?? '';
            if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $imageFilename = uniqid('menu_') . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageFilename);
            }
        } else {
            $input = json_decode(file_get_contents('php://input'), true);
            $name = $input['name'] ?? '';
            $price = floatval($input['price'] ?? 0);
            $category = $input['category'] ?? '';
        }
        // ensure image column exists
        $colCheck = $conn->prepare("SELECT COUNT(*) AS cnt FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'menu' AND COLUMN_NAME = 'image'");
        $colCheck->execute();
        $colCnt = $colCheck->get_result()->fetch_assoc()['cnt'] ?? 0;
        $colCheck->close();
        if (intval($colCnt) === 0) {
            $conn->query("ALTER TABLE menu ADD COLUMN image VARCHAR(255) DEFAULT NULL");
        }
        $stmt = $conn->prepare("INSERT INTO menu (name, price, category, image) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('sdss', $name, $price, $category, $imageFilename);
        $stmt->execute();
        echo json_encode(['success'=>true,'id'=>$conn->insert_id]); exit;
    }

    if ($action === 'update') {
        // support file upload as well
        $id = 0; $name = ''; $price = 0; $category = ''; $imageFilename = null;
        if (!empty($_FILES) || strpos($_SERVER['CONTENT_TYPE'] ?? '', 'multipart/form-data') !== false) {
            $id = intval($_POST['id'] ?? 0);
            $name = $_POST['name'] ?? '';
            $price = floatval($_POST['price'] ?? 0);
            $category = $_POST['category'] ?? '';
            $uploadDir = __DIR__ . '/../Images/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $imageFilename = uniqid('menu_') . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageFilename);
            }
        } else {
            $input = json_decode(file_get_contents('php://input'), true);
            $id = intval($input['id'] ?? 0);
            $name = $input['name'] ?? '';
            $price = floatval($input['price'] ?? 0);
            $category = $input['category'] ?? '';
        }
        if ($imageFilename) {
            $stmt = $conn->prepare("UPDATE menu SET name = ?, price = ?, category = ?, image = ? WHERE id = ?");
            $stmt->bind_param('sdssi', $name, $price, $category, $imageFilename, $id);
        } else {
            $stmt = $conn->prepare("UPDATE menu SET name = ?, price = ?, category = ? WHERE id = ?");
            $stmt->bind_param('sdsi', $name, $price, $category, $id);
        }
        $stmt->execute();
        echo json_encode(['success'=>true]); exit;
    }

    if ($action === 'delete') {
        $id = intval($_GET['id'] ?? $_POST['id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM menu WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        echo json_encode(['success'=>true]); exit;
    }

    if ($action === 'categories') {
        $res = $conn->query("SELECT DISTINCT category FROM menu ORDER BY category");
        $cats = [];
        while ($r = $res->fetch_assoc()) $cats[] = $r['category'];
        echo json_encode(['success'=>true,'data'=>$cats]); exit;
    }

    echo json_encode(['success'=>false,'message'=>'Unknown action']);

} catch (Exception $e) {
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}

$conn->close();

?>
