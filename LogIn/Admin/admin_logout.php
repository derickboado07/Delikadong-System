<?php
session_start();

// Only remove admin session variables
if (isset($_SESSION['admin_id'])) {
    unset($_SESSION['admin_id'], $_SESSION['admin_username']);
}

// Do NOT destroy the entire session; users stay logged in

header("Location: ../Users/User.php");
exit();
?>
