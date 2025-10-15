<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Reports</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../HomePage/MainHome.css" />
    <link rel="stylesheet" href="InventoryReports.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include '../include/navbar.php'; ?>

    <div class="right-Menu">
       <div class="content-wrapper" style="background: transparent; box-shadow: none;">
            <div class="content-header">
                <h1>Inventory Reports & Analytics</h1>
                <div class="header-actions">
                    <button class="btn btn-primary" onclick="exportReport()">
                        <i class="fas fa-download"></i> Export Report
                    </button>
                    <button class="btn btn-secondary" onclick="refreshReports()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="summary-cards">
                <div class="card">
                    <div class="card-header">
                        <h3>Total Items</h3>
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div class="card-body">
                        <div class="number" id="totalItems">0</div>
                        <div class="label">Menu Items in Stock</div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3>Low Stock Items</h3>
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="card-body">
                        <div class="number warning" id="lowStockItems">0</div>
                        <div class="label">Items Below 5 Units</div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3>Out of Stock</h3>
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="card-body">
                        <div class="number danger" id="outOfStockItems">0</div>
                        <div class="label">Items Need Restock</div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3>Total Stock Value</h3>
                        <i class="fas fa-peso-sign"></i>
                    </div>
                    <div class="card-body">
                        <div class="number success" id="totalValue">₱0.00</div>
                        <div class="label">Current Inventory Value</div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="charts-row">
                <div class="chart-container">
                    <div class="chart-header">
                        <h3>Stock Distribution by Category</h3>
                    </div>
                    <canvas id="categoryChart"></canvas>
                </div>

                <div class="chart-container">
                    <div class="chart-header">
                        <h3>Stock Status Overview</h3>
                    </div>
                    <canvas id="statusChart"></canvas>
                </div>
            </div>

            <!-- Data Tables -->
            <div class="tables-section">
                <!-- Low Stock Alert -->
                <div class="table-card">
                    <div class="table-header">
                        <h3><i class="fas fa-exclamation-triangle"></i> Low Stock Alert</h3>
                    </div>
                    <div class="table-container">
                        <table id="lowStockTable">
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th>Category</th>
                                    <th>Current Stock</th>
                                    <th>Price</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="lowStockTableBody">
                                <!-- Data will be populated here -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Stock Updates -->
                <div class="table-card">
                    <div class="table-header">
                        <h3><i class="fas fa-clock"></i> Recent Stock Updates</h3>
                    </div>
                    <div class="table-container">
                        <table id="recentUpdatesTable">
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th>Category</th>
                                    <th>Stock</th>
                                    <th>Last Updated</th>
                                </tr>
                            </thead>
                            <tbody id="recentUpdatesTableBody">
                                <!-- Data will be populated here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Spinner -->
    <div id="loadingSpinner" class="loading-spinner" style="display: none;">
        <i class="fas fa-spinner fa-spin"></i>
        <p>Loading reports...</p>
    </div>

    <script src="InventoryReports.js"></script>
</body>
</html>