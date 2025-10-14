<?php
session_start();
include '../backend/db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>List of Orders</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="Orders.css" />
</head>

<body class="pastries-page">

<?php include '../include/navbar.php'; ?>

<div class="right-Menu" style="background-color: #ebe4e0 ;">
    <div class="orders-header">
      <img src="../Images/Main-icon-black.png" alt="Logo" class="orders-logo" />
      <h1>Order List</h1>
    </div>

<div id="ordersContainer"></div>
<script src="Orders.js"></script>
</body>
</html>
