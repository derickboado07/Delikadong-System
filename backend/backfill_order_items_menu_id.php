<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

try {
    // Update menu_id where null by matching menu name case-insensitive
    $sql = "UPDATE order_items oi 
            JOIN menu m ON LOWER(m.name) = LOWER(oi.item_name)
            SET oi.menu_id = m.id
            WHERE oi.menu_id IS NULL OR oi.menu_id = 0";
    $res = $conn->query($sql);
    if ($res === false) {
        echo json_encode(['success' => false, 'message' => $conn->error]);
        exit;
    }
    $affected = $conn->affected_rows;

    // Count remaining unmatched rows
    $remainingRes = $conn->query("SELECT COUNT(*) AS cnt FROM order_items WHERE menu_id IS NULL OR menu_id = 0");
    $remaining = $remainingRes->fetch_assoc()['cnt'];

    echo json_encode(['success' => true, 'updated_rows' => $affected, 'remaining_unmatched' => intval($remaining)]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();

?>
