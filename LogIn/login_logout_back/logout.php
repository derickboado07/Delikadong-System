<?php
session_start();
include __DIR__ . '/../connectdb/connect.php';

// Only log out the user
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    $check = $conn->query("SHOW COLUMNS FROM users LIKE 'is_online'");
    if ($check && $check->num_rows > 0) {
        $update = $conn->prepare("UPDATE users SET is_online = 0, time_out = NOW() WHERE user_id = ?");
        $update->bind_param("i", $user_id);
        $update->execute();
        $update->close();
    }

    // Only remove user session variables
    unset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['full_name']);
}

// Do NOT destroy the entire session; admin stays logged in

header("Location: ../Users/User.php");
exit();
?>
