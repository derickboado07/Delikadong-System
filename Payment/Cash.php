<?php
session_start();

// Get order data from session - UPDATED FOR NEW DATABASE STRUCTURE
$order_id = $_SESSION['pending_order_id'] ?? null;
$order_total = $_SESSION['order_total'] ?? 0;

// If no pending order, redirect back to menu
if (empty($order_id)) {
    header("Location: ../CoffeeMenu/HomeMenu.php");
    exit;
}

// Convert to array for compatibility with existing payment.js
$order_ids = [$order_id];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="cashdesign.css" />
  <title>Cash Payment</title>
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
      <h3>Cash</h3>
      <div class="wallet-option">
        <input type="radio" name="wallet" checked>
        <span>Cash Payment</span>
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

      <!-- Rest of your cash payment form remains the same -->
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

      <div class="cash-section">
        <h4>Pay with Cash</h4>
        <p><b>Total Pay: <span id="finalPayCash">₱<?php echo number_format($order_total, 2); ?></span></b></p>

        <label for="cashInput"><b>Cash:</b></label>
        <input type="text" id="cashInput" placeholder="Enter cash amount" />

        <button id="enterCashBtn">Enter</button>

        <p><b>Discounted:</b> <span id="discountedAmount">₱0.00</span></p>
        <p><b>Change:</b> <span id="changeAmount">₱0.00</span></p>

        <button id="confirmPaymentBtn" disabled>Confirm Payment</button>
        <a id="listOrdersLink" class="list-orders" href="../List-Orders/Orderlist.php" style="display:none;">List Orders</a>
      </div>
    </div>
  </div>

  <script src="payment.js"></script>
</body>
</html>