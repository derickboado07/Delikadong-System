<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Forgot Password</title>
  <link rel="stylesheet" href="../Users/design.css">
  <style>
    html, body {
      height: 100%;              /* make sure it fills the whole page */
      margin: 0;
      padding: 0;
    }

    body {
      background-image: url('../Images/BG.jpg');
      background-repeat: no-repeat;
      background-size: cover;     /* makes the image cover the entire screen */
      background-position: center;
      background-attachment: fixed; /* optional: makes it stay fixed when scrolling */
      font-family: 'Montserrat', sans-serif;
      min-height: 100vh;          /* ensures it always fills viewport height */
    }

    .forgot-container {
      max-width: 400px;
      margin: 100px auto;
      background: rgba(255, 255, 255, 0.9);
      padding: 30px;
      border-radius: 10px;
      text-align: center;
    }
    input {
      width: 100%;
      padding: 10px;
      margin-top: 10px;
      border-radius: 5px;
      border: 1px solid #ccc;
    }
    button {
      margin-top: 15px;
      padding: 10px 20px;
      background: #7b4f2e;
      color: #fff;
      border: none;
      cursor: pointer;
      width: 100%;
    }
    button:hover {
      background: #5a3820;
    }
    .error {
      color: red;
      margin-top: 10px;
    }
  </style>
</head>
<body>

<div class="forgot-container">
  <h2>Forgot Password</h2>
  <?php if (isset($_SESSION['error'])): ?>
      <div class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
  <?php endif; ?>

  <form action="send_otp.php" method="POST">
    <input type="email" name="email" placeholder="Enter your Gmail" required>
    <input type="password" name="new_password" placeholder="Enter new password" required>
    <button type="submit">Send OTP</button>
  </form>

    <!-- Return button -->
  <form action="../Users/User.php" method="GET">
    <button type="submit" class="return-btn">Return to Login</button>
  </form>
</div>

</body>
</html>
