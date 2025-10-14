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

<div class="left-navbar">
    <img src="../Images/Icon.png" alt="Logo" />
    <a href="../HomePage/MainHome.php" class="icon"><i class="fa-solid fa-house"></i><h6>Home</h6></a>
    <a href="../PastriesMenu/PastriesMenu.php" class="icon"><i class="fa-solid fa-mug-saucer"></i><h6>Menu</h6></a>
    <a href="../List-Orders/Orderlist.php" class="icon"><i class="fa-solid fa-list"></i><h6>Orders</h6></a>
    <a href="../Inventory/Inventory.php" class="icon"><i class="fa-solid fa-boxes-stacked"></i><h6>Inventory</h6></a>
    <a href="../HomePage/MainHome.php" class="icons" id="signOutBtn"><i class="fa-solid fa-arrow-right-from-bracket"></i><h6>Sign-Out</h6></a>
</div>

<div class="right-Menu" style="background-color: #ebe4e0 ;">
    <div class="orders-header">
      <img src="../Images/Main-icon-black.png" alt="Logo" class="orders-logo" />
      <h1>Order List</h1>
    </div>
    
<div id="ordersContainer"></div>
<script src="Orders.js"></script>
</body>
</html>