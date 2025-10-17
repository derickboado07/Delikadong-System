<?php
require_once 'backend/db_connect.php';

try {
    $conn->query("ALTER TABLE menu ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active'");
    echo "Status column added to menu table successfully.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

$conn->close();
?>
