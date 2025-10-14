<?php
session_start();
// In your Payments.php file, add this at the top:
$order_id = $_GET['order_id'] ?? ($_SESSION['pending_order_id'] ?? null);
$order_total = $_SESSION['order_total'] ?? 0;

error_log("💰 PAYMENT PAGE - Order ID: " . $order_id . ", Total: " . $order_total);

if (!$order_id) {
    error_log("❌ Payment page accessed without order_id");
    // You might want to redirect back or show an error
}

// Convert to array for compatibility with existing payment.js
$order_ids = [$order_id];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Paydesign.css" />
    <title>Online Payment</title>
</head>
<body>
 <!-- Header -->
  <div class="header">
    <img src="../Images/Icon.png" alt="Logo">
    <h1>AratCoffee</h1>
  </div>

  <!-- Payment Container -->
  <div class="payment-container">

    <!-- Left: Payment Method -->
    <div class="payment-method">
      <h3>Payment Method</h3>
      <a href="Cash.php" class="method-btn">💵 Cash Payment</a>
      <a href="Payments.php" class="method-btn">💳 Online Payment</a>
    </div>

    <!-- Middle: E-Wallet -->
    <div class="e-wallet">
      <h3>E-Wallet</h3>
      <div class="wallet-option">
        <input type="radio" name="wallet" checked>
        <img src="../Images/GCash-Logo.jpg" alt="GCash">
        <span>GCash Payment</span>
      </div>
    </div>

    <!-- Right: Payment Info -->
    <div class="payment-info">
      <h4>Payment Information:</h4>
      <p><strong>Date:</strong> <span id="paymentDate"></span></p>
      <p>Total Pay: <span id="totalPay">₱<?php echo number_format($order_total, 2); ?></span></p>
      <p>Discount: <span id="discountInfo">None</span></p>
      <p><b>Final Total: <span id="finalPay">₱<?php echo number_format($order_total, 2); ?></span></b></p>

      <!-- Hidden fields for order data -->
      <input type="hidden" id="orderIds" value="<?php echo htmlspecialchars(json_encode([$order_id])); ?>">
      <input type="hidden" id="orderTotal" value="<?php echo $order_total; ?>">

      <!-- Rest of your code remains the same -->
      <div class="discount-box">
        <table>
          <tr>
            <td><label><input type="radio" name="discount" value="senior"> Senior Citizen</label></td>
            <td>20%</td>
          </tr>
          <tr>
            <td><label><input type="radio" name="discount" value="student"> Student</label></td>
            <td>10%</td>
          </tr>
          <tr>
            <td><label><input type="radio" name="discount" value="none" checked> None</label></td>
          </tr>
        </table>
      </div>

      <div class="qr-section">
        <h4>Pay with <img src="../Images/GCash-Logo.jpg" alt="gcash" class="gcash-small"></h4>
        <p id="gcashName">Na***** F**</p>
        <img src="../Images/GCASH.jpg" alt="GCash QR Code" class="qr-code">
        
        <div class="reference-section" style="margin: 15px 0;">
          <label for="referenceNumber" style="display: block; margin-bottom: 8px; font-weight: bold;">
            Enter GCash Reference Number:
          </label>
          <input type="text" 
                 id="referenceNumber" 
                 placeholder="e.g., GC1234567890" 
                 style="padding: 8px; width: 200px; border: 1px solid #ccc; border-radius: 4px;"
                 maxlength="20">
          <p style="font-size: 12px; color: #666; margin: 5px 0;">
            ⓘ After scanning QR, enter the reference number from GCash
          </p>
        </div>
        
        <p><a href="#" id="successfulPaymentLink">Confirm Payment</a></p>
      </div>
    </div>
  </div>

  <script src="payment.js"></script>


  <script>
// Pass the order_id to your payment JavaScript
const orderId = <?php echo json_encode($order_id); ?>;
const orderTotal = <?php echo json_encode($order_total); ?>;

console.log("Payment page loaded - Order ID:", orderId, "Total:", orderTotal);

// Use these variables in your payment processing
</script>
</body>
</html>