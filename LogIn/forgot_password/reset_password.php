<?php
session_start();
require '../connectdb/connect.php';

// if OTP not verified, block access
if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true) {
    header("Location: forgot.php");
    exit();
}

$email = $_SESSION['reset_email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'])) {
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match!";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // check if email belongs to user or admin
        $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $update = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        } else {
            $update = $conn->prepare("UPDATE admins SET password = ? WHERE email = ?");
        }
        $update->bind_param("ss", $hashed_password, $email);
        $update->execute();

        // clear sessions
        unset($_SESSION['otp'], $_SESSION['otp_expires'], $_SESSION['reset_email'], $_SESSION['otp_verified'], $_SESSION['otp_resend_count']);

        $_SESSION['error'] = "✅ Password successfully changed. Please log in.";
        header("Location: ../Users/User.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password</title>
  <style>
    body { background-image: url('../Images/BG.jpg'); background-size: cover; font-family: 'Montserrat', sans-serif; }
    .reset-container { max-width: 400px; margin: 100px auto; background: rgba(255,255,255,0.9); padding: 30px; border-radius: 10px; text-align: center; }
    input { width: 100%; padding: 10px; margin-top: 10px; border-radius: 5px; border: 1px solid #ccc; }
    button { margin-top: 15px; padding: 10px 20px; background: #7b4f2e; color: #fff; border: none; cursor: pointer; width: 100%; }
    button:hover { background: #5a3820; }
    .error { color: red; margin-top: 10px; }
  </style>
</head>
<body>

<div class="reset-container">
  <h2>Set New Password</h2>
  <?php if (isset($_SESSION['error'])): ?>
      <div class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
  <?php endif; ?>

  <form action="" method="POST">
    <input type="password" name="new_password" placeholder="New Password" required>
    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
    <button type="submit">Change Password</button>
  </form>
</div>

</body>
</html>
