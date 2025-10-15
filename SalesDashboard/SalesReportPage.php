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
        <p class="no-data" id="dailyNoData" style="display:none;">No data available</p>
      </div>
      <div class="chart-box">
        <h3>Monthly Sales <span id="monthlyStatus">up by 0% Compare last month</span></h3>
        <canvas id="monthlyChart"></canvas>
        <p class="no-data" id="monthlyNoData" style="display:none;">No data available</p>
      </div>
    </div>

      <!-- Extra Sales Dashboard Section -->
  <div class="extra-dashboard">
    <div class="left-side">
      <!-- Product Performance -->
      <div class="extra-box performance-box">
        <h3>Product Performance</h3>
        <table class="performance-table">
          <thead>
            <tr>
              <th>Product Name</th>
              <th>Quantity Sold</th>
              <th>Sales</th>
            </tr>
          </thead>
          <tbody>
            <!-- Data will be populated dynamically -->
          </tbody>
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
        <thead>
          <tr><th>Order #</th><th>Amount</th><th>Date & Time</th></tr>
        </thead>
        <tbody>
          <!-- Data will be populated dynamically -->
        </tbody>
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

    // Function to fetch product performance data
    async function fetchProductPerformance(startDate = null, endDate = null) {
      try {
        let url = '../backend/fetch_product_performance.php';
        const params = new URLSearchParams();
        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);
        if (params.toString()) url += '?' + params.toString();

        const response = await fetch(url);
        const result = await response.json();

        if (result.status === 'success') {
          return result.data;
        } else {
          console.error('Error fetching product performance:', result.message);
          return [];
        }
      } catch (error) {
        console.error('Fetch error:', error);
        return [];
      }
    }

    // Function to fetch payment breakdown data
    async function fetchPaymentBreakdown(startDate = null, endDate = null) {
      try {
        let url = '../backend/fetch_payment_breakdown.php';
        const params = new URLSearchParams();
        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);
        if (params.toString()) url += '?' + params.toString();

        const response = await fetch(url);
        const result = await response.json();

        if (result.status === 'success') {
          return result.data;
        } else {
          console.error('Error fetching payment breakdown:', result.message);
          return [];
        }
      } catch (error) {
        console.error('Fetch error:', error);
        return [];
      }
    }

    // Function to fetch recent orders data
    async function fetchRecentOrders() {
      try {
        console.log('Fetching recent orders...');
        const response = await fetch('../backend/fetch_recent_orders.php?limit=12');
        const result = await response.json();
        console.log('Recent orders response:', result);

        if (result.status === 'success') {
          console.log('Recent orders data:', result.data);
          return result.data;
        } else {
          console.error('Error fetching recent orders:', result.message);
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
        document.getElementById("grossSales").innerText = "₱" + parseFloat(latestData.gross_sales || 0).toLocaleString();
        document.getElementById("ordersToday").innerText = (latestData.orders_today || 0) + " Orders";
        document.getElementById("netIncome").innerText = "₱" + parseFloat(latestData.net_income || 0).toLocaleString();
        document.getElementById("topProduct").innerHTML = '<i class="fa-solid fa-star"></i> ' + (latestData.top_product || 'N/A');
      } else {
        // Reset to default values if no data
        document.getElementById("grossSales").innerText = "₱0";
        document.getElementById("ordersToday").innerText = "0 Orders";
        document.getElementById("netIncome").innerText = "₱0";
        document.getElementById("topProduct").innerHTML = '<i class="fa-solid fa-star"></i> N/A';
      }
    }

    // Function to update product performance table
    function updateProductPerformance(data) {
      const tableBody = document.querySelector('.performance-table tbody');
      if (!tableBody) return;

      tableBody.innerHTML = '';

      if (data.length > 0) {
        data.forEach(product => {
          const row = document.createElement('tr');
          row.innerHTML = `
            <td>${product.product_name}</td>
            <td>${product.total_quantity}</td>
            <td>₱${parseFloat(product.total_sales).toLocaleString()}</td>
          `;
          tableBody.appendChild(row);
        });
      } else {
        const row = document.createElement('tr');
        row.innerHTML = '<td colspan="3">No data available</td>';
        tableBody.appendChild(row);
      }
    }

    // Function to update payment breakdown chart
    function updatePaymentBreakdown(data) {
      if (window.paymentChart) {
        window.paymentChart.destroy();
      }

      const ctx = document.getElementById("paymentChart").getContext("2d");

      if (data.length > 0) {
        const labels = data.map(item => item.payment_method);
        const percentages = data.map(item => parseFloat(item.percentage));

        window.paymentChart = new Chart(ctx, {
          type: "pie",
          data: {
            labels: labels,
            datasets: [{
              data: percentages,
              backgroundColor: ["green", "blue", "red"],
              borderWidth: 1
            }]
          },
          options: {
            responsive: true,
            plugins: {
              legend: {
                display: false
              }
            }
          }
        });
      }
    }

    // Function to update recent orders table
    function updateRecentOrders(data) {
      console.log('updateRecentOrders called with data:', data);
      const tableBody = document.querySelector('.orders-table tbody');
      console.log('Table body found:', tableBody);
      if (!tableBody) {
        console.error('Could not find .orders-table tbody element');
        return;
      }

      tableBody.innerHTML = '';

      if (data.length > 0) {
        console.log('Processing', data.length, 'orders');
        data.forEach(order => {
          const row = document.createElement('tr');
          row.innerHTML = `
            <td>${order.order_number}</td>
            <td>₱${parseFloat(order.amount).toLocaleString()}</td>
            <td>${order.formatted_date_time}</td>
          `;
          tableBody.appendChild(row);
        });
        console.log('Orders added to table');
      } else {
        console.log('No orders data, showing empty message');
        const row = document.createElement('tr');
        row.innerHTML = '<td colspan="3">No orders available</td>';
        tableBody.appendChild(row);
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

      // Fetch all data with filters
      const salesData = await fetchSalesData(start, end);
      const productData = await fetchProductPerformance(start, end);
      const paymentData = await fetchPaymentBreakdown(start, end);
      const dailyTrendsData = await fetchDailySalesTrends(start, end);

      // Update all sections
      updateDashboard(salesData);
      updateProductPerformance(productData);
      updatePaymentBreakdown(paymentData);
      updateDailySalesChart(dailyTrendsData);

      if (salesData.length === 0) {
        alert("No data found for the selected date range");
      }
    });

    // Function to refresh all dashboard data
    async function refreshDashboardData() {
      try {
        // Fetch all data without filters
        const salesData = await fetchSalesData();
        const productData = await fetchProductPerformance();
        const paymentData = await fetchPaymentBreakdown();
        const ordersData = await fetchRecentOrders();
        const dailyTrendsData = await fetchDailySalesTrends();
        const monthlyTrendsData = await fetchMonthlySalesTrends();

        // Update all sections
        updateDashboard(salesData);
        updateProductPerformance(productData);
        updatePaymentBreakdown(paymentData);
        updateRecentOrders(ordersData);
        updateDailySalesChart(dailyTrendsData);
        updateMonthlySalesChart(monthlyTrendsData);
      } catch (error) {
        console.error('Error refreshing dashboard data:', error);
      }
    }

    // Load initial data on page load
    document.addEventListener('DOMContentLoaded', async () => {
      await refreshDashboardData();

      // Set up real-time updates every 30 seconds
      setInterval(refreshDashboardData, 30000);

      // Listen for real-time updates from order completion (cross-tab)
      window.addEventListener('storage', function(e) {
        if (e.key === 'dashboardRefresh') {
          console.log('Dashboard refresh triggered by order completion');
          refreshDashboardData();
        }
      });

      // Also refresh when page becomes visible (same-tab navigation)
      document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
          console.log('Dashboard tab became visible, refreshing data');
          refreshDashboardData();
        }
      });
    });

    // Function to fetch daily sales trends data
    async function fetchDailySalesTrends(startDate = null, endDate = null) {
      try {
        let url = '../backend/fetch_daily_sales_trends.php';
        const params = new URLSearchParams();
        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);
        if (params.toString()) url += '?' + params.toString();

        const response = await fetch(url);
        const result = await response.json();

        if (result.status === 'success') {
          return result.data;
        } else {
          console.error('Error fetching daily sales trends:', result.message);
          return [];
        }
      } catch (error) {
        console.error('Fetch error:', error);
        return [];
      }
    }

    // Function to fetch monthly sales trends data
    async function fetchMonthlySalesTrends() {
      try {
        const response = await fetch('../backend/fetch_monthly_sales_trends.php');
        const result = await response.json();

        if (result.status === 'success') {
          return result.data;
        } else {
          console.error('Error fetching monthly sales trends:', result.message);
          return [];
        }
      } catch (error) {
        console.error('Fetch error:', error);
        return [];
      }
    }

    // Function to update daily sales chart
    function updateDailySalesChart(data) {
      if (window.dailyChart) {
        window.dailyChart.destroy();
      }

      const ctx = document.getElementById("dailyChart").getContext("2d");

      if (data.length > 0) {
        const labels = data.map(item => item.day_of_week);
        const sales = data.map(item => parseFloat(item.sales));

        window.dailyChart = new Chart(ctx, {
          type: "bar",
          data: {
            labels: labels,
            datasets: [{
              label: "Daily Sales",
              data: sales,
              borderColor: "#6ce5e8",
              backgroundColor: "#6ce5e8",
              fill: true
            }]
          }
        });
      }
    }

    // Function to update monthly sales chart
    function updateMonthlySalesChart(data) {
      if (window.monthlyChart) {
        window.monthlyChart.destroy();
      }

      const ctx = document.getElementById("monthlyChart").getContext("2d");

      if (data.length > 0) {
        const labels = data.map(item => item.month);
        const sales = data.map(item => parseFloat(item.sales));

        window.monthlyChart = new Chart(ctx, {
          type: "bar",
          data: {
            labels: labels,
            datasets: [{
              label: "Monthly Sales",
              data: sales,
              backgroundColor: "#6ce5e8"
            }]
          }
        });
      }
    }

  </script>
</body>
</html>
