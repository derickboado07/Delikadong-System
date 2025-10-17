<?php
// seed_admin.php
// Run this once to insert the built-in admin (or re-run to update it).
// IMPORTANT: delete this file after use or protect it — it contains plaintext password.
session_start();
include '../connectdb/connect.php';

// The $real password is here in PHP (you asked for this).


// Hash the password using PHP's password_hash (bcrypt by default).
$hashed_password = password_hash($real_password, PASSWORD_DEFAULT);

// Admin details
$name = '';
$username = '';
$email = '';

// Use prepared statements to avoid duplicates or injection
$stmt = $conn->prepare("INSERT INTO admins (name, username, email, password) VALUES (?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE name = VALUES(name), email = VALUES(email), password = VALUES(password)");
$stmt->bind_param('ssss', $name, $username, $email, $hashed_password);

if ($stmt->execute()) {
    echo "Admin inserted/updated successfully.\n";
} else {
    echo "Error: " . $stmt->error . "\n";
}

$stmt->close();
$conn->close();
?>
