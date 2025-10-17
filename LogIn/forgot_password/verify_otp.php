<?php
session_start();
require '../connectdb/connect.php';

if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_otp'])) {
    $entered_otp = trim($_POST['otp']);
    $stored_otp = $_SESSION['otp'] ?? null;
    $otp_expiry = $_SESSION['otp_expires'] ?? 0;
    $current_time = time();

    if ($current_time > $otp_expiry) {
        $_SESSION['error'] = "OTP expired. Please request a new one.";
    } elseif ($entered_otp == $stored_otp) {
        $_SESSION['otp_verified'] = true; // ✅ mark OTP as verified
        header("Location: reset_password.php"); // ✅ go to password reset page
        exit();
    } else {
        $_SESSION['error'] = "Invalid OTP!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Verify OTP</title>
  <style>
    body { background-image: url('../Images/BG.jpg'); background-size: cover; font-family: 'Montserrat', sans-serif; }
    .otp-container { max-width: 400px; margin: 100px auto; background: rgba(255,255,255,0.9); padding: 30px; border-radius: 10px; text-align: center; }
    input { width: 100%; padding: 10px; margin-top: 10px; border-radius: 5px; border: 1px solid #ccc; }
    button { margin-top: 15px; padding: 10px 20px; background: #7b4f2e; color: #fff; border: none; cursor: pointer; width: 100%; }
    button:hover { background: #5a3820; }
    .error { color: red; margin-top: 10px; }
  </style>
</head>
<body>

<div class="otp-container">
  <h2>Verify OTP</h2>
  <?php if (isset($_SESSION['error'])): ?>
      <div class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
  <?php endif; ?>
  <form action="" method="POST">
    <input type="text" name="otp" placeholder="Enter OTP" required>
    <button type="submit" name="verify_otp">Verify</button>
  </form>
</div>

</body>
</html>
