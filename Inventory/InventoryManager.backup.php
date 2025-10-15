<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management - CRUD</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="InventoryManager.css">
</head>
<body>
    <?php include '../include/navbar.php'; ?>

    <div class="content-wrapper">
        <div class="content-header">
            <h1>Menu Inventory Management</h1>
            <img src="../Images/Icon.png" alt="Logo" class="top-logo" />
        </div>

        <!-- Controls Section -->
        <div class="controls-section">
            <button class="btn btn-primary" onclick="openAddModal()">
                <i class="fas fa-plus"></i> Add New Item
            </button>
            <div class="search-container">
                <input type="text" id="searchInput" placeholder="Search items..." onkeyup="filterTable()">
                <i class="fas fa-search"></i>
            </div>
        </div>

        <!-- Inventory Table -->
        <div class="table-container">
            <table id="inventoryTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Menu Item</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Current Stock</th>
                        <th>Status</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="inventoryTableBody">
                    <!-- Data will be loaded here -->
                </tbody>
            </table>
        </div>

        <!-- Loading Spinner -->
        <div id="loadingSpinner" class="loading-spinner" style="display: none;">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Loading...</p>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div id="inventoryModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Add New Inventory Item</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form id="inventoryForm">
                <input type="hidden" id="itemId" name="id">
                
                <div class="form-group">
                    <label for="menuSelect">Select Menu Item:</label>
                    <select id="menuSelect" name="menu_id" required>
                        <option value="">Choose a menu item...</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="stockQuantity">Stock Quantity:</label>
                    <input type="number" id="stockQuantity" name="stock_quantity" min="0" required>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span id="submitText">Add Item</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Stock Update Modal -->
    <div id="quickUpdateModal" class="modal">
        <div class="modal-content small">
            <div class="modal-header">
                <h2>Quick Stock Update</h2>
                <span class="close" onclick="closeQuickUpdateModal()">&times;</span>
            </div>
            <div class="quick-update-content">
                <p id="quickUpdateItemName"></p>
                <p>Current Stock: <span id="currentStock"></span></p>
                
                <div class="stock-actions">
                    <div class="stock-input-group">
                        <label>Add Stock:</label>
                        <div class="input-with-btn">
                            <input type="number" id="addStockAmount" min="1" value="1">
                            <button class="btn btn-success" onclick="updateStock('add')">
                                <i class="fas fa-plus"></i> Add
                            </button>
                        </div>
                    </div>
                    
                    <div class="stock-input-group">
                        <label>Remove Stock:</label>
                        <div class="input-with-btn">
                            <input type="number" id="removeStockAmount" min="1" value="1">
                            <button class="btn btn-warning" onclick="updateStock('remove')">
                                <i class="fas fa-minus"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Toast -->
    <div id="toast" class="toast">
        <div class="toast-content">
            <i class="toast-icon"></i>
            <span class="toast-message"></span>
        </div>
        <button class="toast-close" onclick="closeToast()">&times;</button>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmModal" class="modal">
        <div class="modal-content small">
            <div class="modal-header">
                <h2>Confirm Action</h2>
            </div>
            <div class="confirm-content">
                <p id="confirmMessage"></p>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeConfirmModal()">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmBtn">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <script src="InventoryManager.js"></script>
</body>
</html>