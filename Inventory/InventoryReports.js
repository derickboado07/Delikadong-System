// Global variables
let categoryChart = null;
let statusChart = null;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    loadReportsData();
});

// Load all reports data
async function loadReportsData() {
    showLoadingSpinner(true);
    
    try {
        await Promise.all([
            loadSummaryData(),
            loadChartData(),
            loadLowStockItems(),
            loadRecentUpdates()
        ]);
    } catch (error) {
        console.error('Error loading reports data:', error);
    } finally {
        showLoadingSpinner(false);
    }
}

// Load summary statistics
async function loadSummaryData() {
    try {
        const response = await fetch('../backend/inventory_reports.php?action=summary');
        const data = await response.json();
        
        if (data.success) {
            updateSummaryCards(data.data);
        }
    } catch (error) {
        console.error('Error loading summary data:', error);
    }
}

// Update summary cards
function updateSummaryCards(data) {
    document.getElementById('totalItems').textContent = data.total_items || 0;
    document.getElementById('lowStockItems').textContent = data.low_stock_items || 0;
    document.getElementById('outOfStockItems').textContent = data.out_of_stock_items || 0;
    document.getElementById('totalValue').textContent = `₱${parseFloat(data.total_value || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}`;
    
    // Add animation classes
    document.querySelectorAll('.card').forEach((card, index) => {
        setTimeout(() => {
            card.classList.add('fade-in-up');
        }, index * 100);
    });
}

// Load chart data
async function loadChartData() {
    try {
        const response = await fetch('../backend/inventory_reports.php?action=charts');
        const data = await response.json();
        
        if (data.success) {
            createCategoryChart(data.data.category_data);
            createStatusChart(data.data.status_data);
        }
    } catch (error) {
        console.error('Error loading chart data:', error);
    }
}

// Create category distribution chart
function createCategoryChart(data) {
    const ctx = document.getElementById('categoryChart').getContext('2d');
    
    // Destroy existing chart if it exists
    if (categoryChart) {
        categoryChart.destroy();
    }
    
    const labels = data.map(item => item.category.charAt(0).toUpperCase() + item.category.slice(1));
    const values = data.map(item => item.total_stock);
    const colors = [
        '#667eea',
        '#764ba2',
        '#f093fb',
        '#f5576c',
        '#4facfe',
        '#00f2fe'
    ];
    
    categoryChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: colors.slice(0, labels.length),
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return `${label}: ${value} units (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

// Create status overview chart
function createStatusChart(data) {
    const ctx = document.getElementById('statusChart').getContext('2d');
    
    // Destroy existing chart if it exists
    if (statusChart) {
        statusChart.destroy();
    }
    
    statusChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['In Stock', 'Low Stock', 'Out of Stock'],
            datasets: [{
                label: 'Number of Items',
                data: [
                    data.in_stock || 0,
                    data.low_stock || 0,
                    data.out_of_stock || 0
                ],
                backgroundColor: [
                    '#28a745',
                    '#ffc107',
                    '#dc3545'
                ],
                borderColor: [
                    '#1e7e34',
                    '#e0a800',
                    '#c82333'
                ],
                borderWidth: 2,
                borderRadius: 5,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
}

// Load low stock items
async function loadLowStockItems() {
    try {
        const response = await fetch('../backend/inventory_reports.php?action=lowStock');
        const data = await response.json();
        
        if (data.success) {
            displayLowStockItems(data.data);
        }
    } catch (error) {
        console.error('Error loading low stock items:', error);
    }
}

// Display low stock items in table
function displayLowStockItems(items) {
    const tbody = document.getElementById('lowStockTableBody');
    tbody.innerHTML = '';
    
    if (items.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="empty-state">
                    <i class="fas fa-check-circle"></i>
                    <p>No low stock items found!</p>
                </td>
            </tr>
        `;
        return;
    }
    
    items.forEach(item => {
        const row = document.createElement('tr');
        
        const statusClass = item.stock_quantity === 0 ? 'status-out' : 'status-low';
        const statusText = item.stock_quantity === 0 ? 'Out of Stock' : 'Low Stock';
        
        row.innerHTML = `
            <td><strong>${item.menu_name}</strong></td>
            <td><span style="text-transform: capitalize; padding: 4px 8px; background: #f0f0f0; border-radius: 4px;">${item.menu_category}</span></td>
            <td><span class="status-badge ${statusClass}">${item.stock_quantity} units</span></td>
            <td>₱${parseFloat(item.menu_price).toFixed(2)}</td>
            <td>
                <button class="btn btn-success" onclick="quickRestockItem(${item.id}, '${item.menu_name}')">
                    <i class="fas fa-plus"></i> Restock
                </button>
            </td>
        `;
        
        tbody.appendChild(row);
    });
}

// Load recent updates
async function loadRecentUpdates() {
    try {
        const response = await fetch('../backend/inventory_reports.php?action=recentUpdates');
        const data = await response.json();
        
        if (data.success) {
            displayRecentUpdates(data.data);
        }
    } catch (error) {
        console.error('Error loading recent updates:', error);
    }
}

// Display recent updates in table
function displayRecentUpdates(items) {
    const tbody = document.getElementById('recentUpdatesTableBody');
    tbody.innerHTML = '';
    
    if (items.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="empty-state">
                    <i class="fas fa-clock"></i>
                    <p>No recent updates found</p>
                </td>
            </tr>
        `;
        return;
    }
    
    items.forEach(item => {
        const row = document.createElement('tr');
        
        let statusClass = 'status-in-stock';
        if (item.stock_quantity === 0) {
            statusClass = 'status-out-of-stock';
        } else if (item.stock_quantity <= 5) {
            statusClass = 'status-low-stock';
        }
        
        row.innerHTML = `
            <td><strong>${item.menu_name}</strong></td>
            <td><span style="text-transform: capitalize; padding: 4px 8px; background: #f0f0f0; border-radius: 4px;">${item.menu_category}</span></td>
            <td><strong>${item.stock_quantity}</strong></td>
            <td>${formatDateTime(item.last_updated)}</td>
        `;
        
        tbody.appendChild(row);
    });
}

// Quick restock item
async function quickRestockItem(id, name) {
    const amount = prompt(`How many units of "${name}" would you like to add to stock?`, '10');
    
    if (amount === null || amount === '' || isNaN(amount) || parseInt(amount) <= 0) {
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('action', 'quickUpdate');
        formData.append('id', id);
        formData.append('operation', 'add');
        formData.append('amount', parseInt(amount));
        
        const response = await fetch('../backend/inventory_crud.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(`Successfully added ${amount} units to ${name}`);
            loadReportsData(); // Refresh all data
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
}

// Format date time for display
function formatDateTime(dateTime) {
    const date = new Date(dateTime);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
}

// Show/hide loading spinner
function showLoadingSpinner(show) {
    const spinner = document.getElementById('loadingSpinner');
    spinner.style.display = show ? 'block' : 'none';
}

// Refresh reports
function refreshReports() {
    loadReportsData();
}

// Export report (simple implementation)
function exportReport() {
    // Create a simple CSV export
    const summaryData = {
        totalItems: document.getElementById('totalItems').textContent,
        lowStockItems: document.getElementById('lowStockItems').textContent,
        outOfStockItems: document.getElementById('outOfStockItems').textContent,
        totalValue: document.getElementById('totalValue').textContent
    };
    
    let csvContent = "Inventory Report\n\n";
    csvContent += "Summary:\n";
    csvContent += `Total Items,${summaryData.totalItems}\n`;
    csvContent += `Low Stock Items,${summaryData.lowStockItems}\n`;
    csvContent += `Out of Stock Items,${summaryData.outOfStockItems}\n`;
    csvContent += `Total Value,${summaryData.totalValue}\n\n`;
    
    // Add low stock items
    csvContent += "Low Stock Items:\n";
    csvContent += "Item Name,Category,Stock,Price\n";
    
    const lowStockRows = document.querySelectorAll('#lowStockTableBody tr');
    lowStockRows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length >= 4) {
            const name = cells[0].textContent.trim();
            const category = cells[1].textContent.trim();
            const stock = cells[2].textContent.trim();
            const price = cells[3].textContent.trim();
            csvContent += `"${name}","${category}","${stock}","${price}"\n`;
        }
    });
    
    // Create and download file
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', `inventory_report_${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Expose functions to global scope
window.refreshReports = refreshReports;
window.exportReport = exportReport;
window.quickRestockItem = quickRestockItem;