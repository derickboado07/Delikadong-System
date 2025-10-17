<?php
session_start();
include '../connectdb/connect.php';

if (!isset($_SESSION['admin_username'])) {
    header("Location: ../Users/user.php");
    exit();
}

if (isset($_GET['id']) && isset($_GET['action'])) {
    $id = (int) $_GET['id'];
    $action = $_GET['action'];

    if ($action === 'approve') {
        $status = 'approved';
    } elseif ($action === 'reject') {
        $status = 'rejected';
    } else {
        header("Location: Admin.php");
        exit();
    }

    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE user_id = ?");
    $stmt->bind_param('si', $status, $id);
    $stmt->execute();
    $stmt->close();
}

header("Location: Admin.php");
exit();
?>
