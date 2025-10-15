<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "db_sales";

$conn_sales = new mysqli($servername, $username, $password, $database);

if ($conn_sales->connect_error) {
  die(json_encode(["status" => "error", "message" => "Database connection failed."]));
}
?>
