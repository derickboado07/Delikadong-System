<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "arat_coffee_db";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
  die(json_encode(["status" => "error", "message" => "Database connection failed."]));
}
?>
