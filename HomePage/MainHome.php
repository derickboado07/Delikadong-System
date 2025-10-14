<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Main Menu</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="MainHome.css" />
</head>
<body>
<?php include '../include/navbar.php'; ?>
<!-- In the right side of the page has 4 types of menu which is coffee, pastries, drinks, meals-->
 <!-- The alt purpose is when the user has low internet and can't load the image the alt title will be displayed instead -->
  <div class="right-Menu">
    <a href="../CoffeeMenu/HomeMenu.php" class="Option">
      <img src="../Images/coffee_1.png" alt="Coffee" />
      <h1>Coffee</h1>
    </a>
    <a href="../PastriesMenu/PastriesMenu.php" class="Option">
      <img src="../Images/cookie.jpeg" alt="Pastries" />
      <h1>Pastries</h1>
    </a>
    <a href="../MealsMenu/Meals.php" class="Option">
      <img src="../Images/OIP.png" alt="Meals" />
      <h1>Meals</h1>
    </a>

  </div>

  <!-- Logout Confirmation Modal -->
  <div class="logout-modal" id="logoutModal">
    <div class="logout-modal-content">
      <div class="logout-modal-header">
        <h3>Confirm Logout</h3>
        <button class="close-logout-modal">&times;</button>
      </div>
      <div class="logout-modal-body">
        <p>Are you sure you want to logout?</p>
      </div>
      <div class="logout-modal-footer">
        <button class="logout-btn confirm-logout">Yes, Logout</button>
        <button class="logout-btn cancel-logout">Cancel</button>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // Fade in logo
      const logo = document.querySelector('.left-navbar img');
      
      // Fade in navigation icons with delay
      const icons = document.querySelectorAll('.icon, .icons');
      icons.forEach((icon, index) => {
          setTimeout(() => {
              icon.classList.add('fade-in-icon');
          }, 100 + index * 100);
      });
      
      // Fade in menu options with delay
      const options = document.querySelectorAll('.Option');
      options.forEach((option, index) => {
          setTimeout(() => {
              option.classList.add('fade-in-option');
          }, 300 + index * 150);
      });

      // Logout confirmation modal functionality
      const logoutModal = document.getElementById('logoutModal');
      const signOutBtn = document.getElementById('signOutBtn');
      const closeLogoutModal = document.querySelector('.close-logout-modal');
      const cancelLogout = document.querySelector('.cancel-logout');
      const confirmLogout = document.querySelector('.confirm-logout');

      // Open modal when sign-out is clicked
      if (signOutBtn) {
          signOutBtn.addEventListener('click', function(e) {
              e.preventDefault();
              logoutModal.style.display = 'flex';
          });
      }

      // Close modal when X is clicked
      if (closeLogoutModal) {
          closeLogoutModal.addEventListener('click', function() {
              logoutModal.style.display = 'none';
          });
      }

      // Close modal when cancel is clicked
      if (cancelLogout) {
          cancelLogout.addEventListener('click', function() {
              logoutModal.style.display = 'none';
          });
      }

      // Redirect to logout page when confirm is clicked
      if (confirmLogout) {
          confirmLogout.addEventListener('click', function() {
              window.location.href = '../MainPage/Mainpage.html';
          });
      }

      // Close modal if user clicks outside the modal content
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