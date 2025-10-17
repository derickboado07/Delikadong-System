<?php
session_start();
include '../connectdb/connect.php';

// Only allow logged-in users
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Users/User.php");
    exit();
}

// Get user info from session
$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];
$username = $_SESSION['username'];

// Optional: Fetch latest user info from DB if needed
$stmt = $conn->prepare("SELECT time_in, time_out, is_online FROM users WHERE user_id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($time_in, $time_out, $is_online);
$stmt->fetch();
$stmt->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Main Menu</title>

  <!-- Font Awesome for Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

  <!-- External CSS file for styling -->
  <link rel="stylesheet" href="MainHome.css" />
</head>
<body>

  <!-- ============================================================
       LEFT SIDEBAR NAVIGATION
       ============================================================
       This section acts as the navigation panel for the application.
       It includes links to Home, Menu, Orders, Sales, Inventory, and Logout.
  -->
  <div class="left-navbar">
    <!-- Logo of the application -->
    <img src="../Images/Icon.png" alt="Logo" />

    <!-- Navigation Links with Icons -->
    <a href="../HomePage/MainHome.php" class="icon">
      <i class="fa-solid fa-house"></i>
      <h6>Home</h6>
    </a>

    <a href="../CoffeeMenu/HomeMenu.php" class="icon">
      <i class="fa-solid fa-mug-saucer"></i>
      <h6>Menu</h6>
    </a>

    <a href="../List-Orders/Orderlist.php" class="icon">
      <i class="fa-solid fa-list"></i>
      <h6>Orders</h6>
    </a>

    <a href="../Total_sales/Sales.php" class="icon">
      <i class="fa-solid fa-wallet"></i>
      <h6>Sales</h6>
    </a>

    <a href="inventory.php" class="icon">
      <i class="fa-solid fa-boxes-stacked"></i>
      <h6>Inventory</h6>
    </a>

    <!-- Sign-out button that will trigger the logout modal -->
    <a href="#" class="icons" id="signOutBtn">
      <i class="fa-solid fa-arrow-right-from-bracket"></i>
      <h6>Sign-Out</h6>
    </a>
  </div>

  <!-- ============================================================
       RIGHT SIDE MENU
       ============================================================
       This section displays different categories that the user can select.
       It includes Coffee, Pastries, and Meals options.
  -->
  <div class="right-Menu">
    <!-- Coffee Option -->
    <a href="../CoffeeMenu/HomeMenu.php" class="Option">
      <img src="../Images/coffee_1.png" alt="Coffee" />
      <h1>Coffee</h1>
    </a>

    <!-- Pastries Option -->
    <a href="../PastriesMenu/PastriesMenu.php" class="Option">
      <img src="../Images/cookie.jpeg" alt="Pastries" />
      <h1>Pastries</h1>
    </a>

    <!-- Meals Option -->
    <a href="../MealsMenu/Meals.php" class="Option">
      <img src="../Images/OIP.png" alt="Meals" />
      <h1>Meals</h1>
    </a>
  </div>

  <!-- ============================================================
       LOGOUT CONFIRMATION MODAL
       ============================================================
       This modal appears when the user clicks the "Sign-Out" button.
       It allows the user to confirm or cancel the logout action.
  -->
  <div class="logout-modal" id="logoutModal">
    <div class="logout-modal-content">
      <!-- Header Section of Modal -->
      <div class="logout-modal-header">
        <h3>Confirm Logout</h3>
        <!-- Close button (X) -->
        <button class="close-logout-modal">&times;</button>
      </div>

      <!-- Modal Body -->
      <div class="logout-modal-body">
        <p>Are you sure you want to logout?</p>
      </div>

      <!-- Footer with Confirm and Cancel buttons -->
      <div class="logout-modal-footer">
        <button class="logout-btn confirm-logout">Yes, Logout</button>
        <button class="logout-btn cancel-logout">Cancel</button>
      </div>
    </div>
  </div>

  <!-- ============================================================
       JAVASCRIPT SECTION
       ============================================================
       This script:
       1. Handles fade-in animations for menu icons and options
       2. Controls the opening and closing of the logout modal
       3. Redirects the user to logout.php upon confirmation
  -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {

      // ============================================================
      // ANIMATIONS FOR ICONS AND MENU OPTIONS
      // ============================================================
      // Select all icons in the sidebar
      const icons = document.querySelectorAll('.icon, .icons');
      // Loop through each icon and add fade-in effect with delay
      icons.forEach((icon, index) => {
        setTimeout(() => {
          icon.classList.add('fade-in-icon');
        }, 100 + index * 100);
      });

      // Select all menu options (Coffee, Pastries, Meals)
      const options = document.querySelectorAll('.Option');
      // Loop through each option and add fade-in effect with delay
      options.forEach((option, index) => {
        setTimeout(() => {
          option.classList.add('fade-in-option');
        }, 300 + index * 150);
      });

      // ============================================================
      // LOGOUT MODAL FUNCTIONALITY
      // ============================================================

      // Get references to modal elements
      const logoutModal = document.getElementById('logoutModal');
      const signOutBtn = document.getElementById('signOutBtn');
      const closeLogoutModal = document.querySelector('.close-logout-modal');
      const cancelLogout = document.querySelector('.cancel-logout');
      const confirmLogout = document.querySelector('.confirm-logout');

      // 1. When "Sign-Out" is clicked → show the modal
      if (signOutBtn) {
        signOutBtn.addEventListener('click', function(e) {
          e.preventDefault(); // Prevent page refresh
          logoutModal.style.display = 'flex'; // Show modal
        });
      }

      // 2. When "X" button is clicked → close modal
      if (closeLogoutModal) {
        closeLogoutModal.addEventListener('click', function() {
          logoutModal.style.display = 'none';
        });
      }

      // 3. When "Cancel" button is clicked → close modal
      if (cancelLogout) {
        cancelLogout.addEventListener('click', function() {
          logoutModal.style.display = 'none';
        });
      }

      // 4. When "Yes, Logout" is clicked → redirect to logout.php
      if (confirmLogout) {
        confirmLogout.addEventListener('click', function() {
          // This will trigger the PHP logout process
          window.location.href = '../login_logout_back/logout.php';
        });
      }

      // 5. Close modal if user clicks outside modal content
      if (logoutModal) {
        logoutModal.addEventListener('click', function(e) {
          if (e.target === logoutModal) {
            logoutModal.style.display = 'none';
          }
        });
      }
    });
  </script>
</body>
</html>
