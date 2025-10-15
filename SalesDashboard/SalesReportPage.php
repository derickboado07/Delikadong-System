<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sales Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="Sales Report.css" />
</head>
<body>
<?php include '../include/navbar.php'; ?>

  <!-- Main Content -->
  <div class="main-content">
    <!-- White Background Header -->
    <div class="dashboard-header">
      <h2>ARAT COFFEE SALES DASHBOARD</h2>
      <div class="filter">
        <input type="date" id="startDate" />
        <input type="date" id="endDate" />
        <button id="applyFilter">Apply Filter</button>
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="cards">
      <div class="card">
        <h4>Gross Sales</h4>
        <p class="value" id="grossSales">₱0</p>
      </div>
      <div class="card">
        <h4>Order Today</h4>
        <p class="value" id="ordersToday">0 Orders</p>
      </div>
      <div class="card">
        <h4>Net Income</h4>
        <p class="value" id="netIncome">₱0</p>
      </div>
      <div class="card top-product">
        <h4>Top Product</h4>
        <p class="value" id="topProduct"><i class="fa-solid fa-star"></i> N/A</p>
      </div>
    </div>

    <!-- Charts -->
    <div class="charts">
      <div class="chart-box">
        <h3>Sales Trends <span>Daily Sales</span></h3>
        <canvas id="dailyChart"></canvas>
      </div>
      <div class="chart-box">
        <h3>Monthly Sales <span id="monthlyStatus">up by 0% Compare last month</span></h3>
        <canvas id="monthlyChart"></canvas>
      </div>
    </div>

      <!-- Extra Sales Dashboard Section -->
  <div class="extra-dashboard">
    <div class="left-side">
      <!-- Product Performance -->
      <div class="extra-box performance-box">
        <h3>Product Performance</h3>
        <table class="performance-table">
          <tr>
            <th>Product Name</th>
            <th>Quantity Sold</th>
            <th>Sales</th>
          </tr>
          <tr><td>Iced Latte</td><td>159</td><td>₱15,600</td></tr>
          <tr><td>Cappuccino</td><td>127</td><td>₱13,860</td></tr>
          <tr><td>Caramel Macchiato</td><td>121</td><td>₱13,740</td></tr>
          <tr><td>Salted Caramel</td><td>104</td><td>₱12,550</td></tr>
        </table>
      </div>

      <!-- Payment Breakdown -->
      <div class="extra-box payment-box rounded-container">
        <h3 class="payment-title">Payment Breakdown</h3>
        <div class="chart-container">
          <canvas id="paymentChart"></canvas>
          <div class="legend">
            <span><span class="dot cash"></span> Cash</span>
            <span><span class="dot gcash"></span> Gcash</span>
            <span><span class="dot qrph"></span> QRPH</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Orders -->
    <div class="extra-box orders-box">
      <h3>Recent Orders</h3>
      <table class="orders-table">
        <tr><th>Order #</th><th>Amount</th><th>Date & Time</th></tr>
        <tr><td>3211</td><td>₱160</td><td>06-15-25, 21:22</td></tr>
        <tr><td>3210</td><td>₱170</td><td>06-15-25, 21:15</td></tr>
        <tr><td>3209</td><td>₱200</td><td>06-15-25, 21:01</td></tr>
        <tr><td>3208</td><td>₱380</td><td>06-15-25, 20:52</td></tr>
        <tr><td>3207</td><td>₱175</td><td>06-15-25, 20:32</td></tr>
        <tr><td>3206</td><td>₱175</td><td>06-15-25, 19:26</td></tr>
        <tr><td>3205</td><td>₱240</td><td>06-15-25, 19:42</td></tr>
        <tr><td>3204</td><td>₱560</td><td>06-15-25, 18:43</td></tr>
        <tr><td>3203</td><td>₱690</td><td>06-15-25, 18:41</td></tr>
        <tr><td>3202</td><td>₱170</td><td>06-15-25, 18:35</td></tr>
        <tr><td>3201</td><td>₱170</td><td>06-15-25, 17:12</td></tr>
        <tr><td>3200</td><td>₱170</td><td>06-15-25, 17:10</td></tr>
        <tr><td>3199</td><td>₱165</td><td>06-15-25, 16:21</td></tr>
        <tr><td>3198</td><td>₱340</td><td>06-15-25, 15:54</td></tr>
      </table>
    </div>
  </div>

    </div>

  </div>

  <!-- Chart.js for graphs -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <!-- Script for interactivity & backend integration -->
  <script>
    // Function to fetch sales data from database
    async function fetchSalesData(startDate = null, endDate = null) {
      try {
        let url = '../backend/fetch_sales_data.php';
        const params = new URLSearchParams();
        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);
        if (params.toString()) url += '?' + params.toString();

        const response = await fetch(url);
        const result = await response.json();

        if (result.status === 'success') {
          return result.data;
        } else {
          console.error('Error fetching data:', result.message);
          return [];
        }
      } catch (error) {
        console.error('Fetch error:', error);
        return [];
      }
    }

    // Function to update dashboard with data
    function updateDashboard(data) {
      if (data.length > 0) {
        // Use the most recent data (first in array since ordered by date DESC)
        const latestData = data[0];
        document.getElementById("grossSales").innerText = "₱" + parseFloat(latestData.gross_sales).toLocaleString();
        document.getElementById("ordersToday").innerText = latestData.orders_today + " Orders";
        document.getElementById("netIncome").innerText = "₱" + parseFloat(latestData.net_income).toLocaleString();
        document.getElementById("topProduct").innerHTML = '<i class="fa-solid fa-star"></i> ' + latestData.top_product;
      } else {
        // Reset to default values if no data
        document.getElementById("grossSales").innerText = "₱0";
        document.getElementById("ordersToday").innerText = "0 Orders";
        document.getElementById("netIncome").innerText = "₱0";
        document.getElementById("topProduct").innerHTML = '<i class="fa-solid fa-star"></i> N/A';
      }
    }

    // Apply filter button
    document.getElementById("applyFilter").addEventListener("click", async () => {
      const start = document.getElementById("startDate").value;
      const end = document.getElementById("endDate").value;

      if (!end) {
        alert("Please select an end date");
        return;
      }

      const data = await fetchSalesData(start, end);
      updateDashboard(data);

      if (data.length === 0) {
        alert("No data found for the selected date range");
      }
    });

    // Load initial data on page load
    document.addEventListener('DOMContentLoaded', async () => {
      const data = await fetchSalesData();
      updateDashboard(data);
    });

    // Charts Example (dummy)
    const dailyCtx = document.getElementById("dailyChart").getContext("2d");
    new Chart(dailyCtx, {
      type: "bar",
      data: {
        labels: ["Mon", "Tue", "Wed", "Thu", "Fri"],
        datasets: [{
          label: "Daily Sales",
          data: [1200, 1900, 3000, 2500, 3200],
          borderColor: "#6ce5e8",
          backgroundColor: "#6ce5e8",
          fill: true
        }]
      }
    });

    const monthlyCtx = document.getElementById("monthlyChart").getContext("2d");
    new Chart(monthlyCtx, {
      type: "bar",
      data: {
        labels: ["May", "Jun", "Jul", "Aug", "Sep"],
        datasets: [{
          label: "Monthly Sales",
          data: [15000, 20000, 22000, 25000, 28000],
          backgroundColor: "#6ce5e8"
        }]
      }
    });

    // Payment Breakdown Pie Chart
    const paymentCtx = document.getElementById("paymentChart").getContext("2d");
    new Chart(paymentCtx, {
      type: "pie",
      data: {
        labels: ["Cash", "Gcash", "QRPH"],
        datasets: [{
          data: [50, 30, 20], // Dummy data percentages
          backgroundColor: ["green", "blue", "red"],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            display: false // Hide default legend, use custom
          }
        }
      }
    });
  </script>
</body>
</html>
