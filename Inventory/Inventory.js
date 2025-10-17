// Global variables
let currentEditingId = null;
let menuItems = [];

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    setupInventorySelector();
    loadInventoryData();
    loadMenuItems();
    setupEventListeners();
});

// Setup inventory type selector
function setupInventorySelector() {
    const selector = document.getElementById('inventoryType');
    selector.addEventListener('change', function() {
        const externalSection = document.getElementById('externalInventory');
        const internalSection = document.getElementById('internalInventory');

        if (this.value === 'external') {
            externalSection.style.display = 'block';
            internalSection.style.display = 'none';
            loadInventoryData();
        } else {
            externalSection.style.display = 'none';
            internalSection.style.display = 'block';
            loadIngredients();
        }
    });
}

// Auto-refresh inventory data every 30 seconds when active
setInterval(() => {
    const selector = document.getElementById('inventoryType');
    if (selector) {
        if (selector.value === 'external') {
            loadInventoryData();
        } else if (selector.value === 'internal') {
            loadIngredients();
        }
    }
}, 30000);

// Setup event listeners
function setupEventListeners() {
    // Form submission
    document.getElementById('inventoryForm').addEventListener('submit', handleFormSubmit);

    // Modal close on outside click
    window.addEventListener('click', function(event) {
        const inventoryModal = document.getElementById('inventoryModal');
        const quickUpdateModal = document.getElementById('quickUpdateModal');
        const confirmModal = document.getElementById('confirmModal');
        const ingredientModal = document.getElementById('ingredientModal');

        if (event.target === inventoryModal) {
            closeModal();
        }
        if (event.target === quickUpdateModal) {
            closeQuickUpdateModal();
        }
        if (event.target === confirmModal) {
            closeConfirmModal();
        }
        if (event.target === ingredientModal) {
            closeIngredientModal();
        }
    });

    // Enter key handling for stock inputs
    document.getElementById('addStockAmount').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            updateStock('add');
        }
    });

    document.getElementById('removeStockAmount').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            updateStock('remove');
        }
    });

    // Ingredient form
    document.getElementById('ingredientForm').addEventListener('submit', handleIngredientFormSubmit);
    const btnAddIngredient = document.getElementById('btnAddIngredient');
    if (btnAddIngredient) {
        btnAddIngredient.addEventListener('click', () => openIngredientModal('create'));
    }
}

// Load inventory data from server
async function loadInventoryData() {
    showLoadingSpinner(true);

    try {
        const response = await fetch('../backend/inventory_crud.php?action=read');
        const data = await response.json();

        if (data.success) {
            displayInventoryData(data.data);
        } else {
            showToast('Error loading inventory data: ' + data.message, 'error');
        }
    } catch (error) {
        showToast('Error connecting to server: ' + error.message, 'error');
    } finally {
        showLoadingSpinner(false);
    }
}

// Load menu items for dropdown
async function loadMenuItems() {
    try {
        const response = await fetch('../backend/inventory_crud.php?action=getMenuItems');
        const data = await response.json();

        if (data.success) {
            menuItems = data.data;
            populateMenuSelect();
        }
    } catch (error) {
        console.error('Error loading menu items:', error);
    }
}

// Populate menu select dropdown
function populateMenuSelect() {
    const dropdown = document.getElementById('menuSelectDropdown');
    dropdown.innerHTML = '<option value="">Choose a menu item...</option>';

    menuItems.forEach(item => {
        const option = document.createElement('option');
        option.value = item.id;
        option.textContent = `${item.name} (${item.category}) - ₱${parseFloat(item.price).toFixed(2)}`;
        dropdown.appendChild(option);
    });
}

// Display inventory data in table
function displayInventoryData(data) {
    const tbody = document.getElementById('inventoryTableBody');
    tbody.innerHTML = '';

    if (data.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" style="text-align: center; padding: 40px; color: #666;">
                    <i class="fas fa-box-open" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                    No inventory items found. Click "Add New Item" to get started.
                </td>
            </tr>
        `;
        return;
    }

    data.forEach(item => {
        const row = document.createElement('tr');
        row.className = 'table-row-enter';

        let statusClass = 'status-in-stock';
        let statusText = 'In Stock';

        if (item.stock_quantity === 0) {
            statusClass = 'status-out-of-stock';
            statusText = 'Out of Stock';
        } else if (item.stock_quantity <= 5) {
            statusClass = 'status-low-stock';
            statusText = 'Low Stock';
        }

        row.innerHTML = `
            <td>${item.id}</td>
            <td><strong>${item.menu_name}</strong></td>
            <td><span style="text-transform: capitalize; padding: 4px 8px; background: #f0f0f0; border-radius: 4px;">${item.menu_category}</span></td>
            <td>₱${parseFloat(item.menu_price).toFixed(2)}</td>
            <td><strong>${item.stock_quantity}</strong></td>
            <td><span class="status-badge ${statusClass}">${statusText}</span></td>
            <td>${formatDateTime(item.last_updated)}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn btn-info btn-sm" onclick="openQuickUpdate(${item.id}, '${item.menu_name}', ${item.stock_quantity})" title="Quick Update">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    <button class="btn btn-primary btn-sm" onclick="editItem(${item.id})" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="deleteItem(${item.id}, '${item.menu_name}')" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        `;

        tbody.appendChild(row);
    });
}

// Format date time for display
function formatDateTime(dateTime) {
    const date = new Date(dateTime);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
}

// Filter table based on search input
function filterTable() {
    const input = document.getElementById('searchInput');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('inventoryTable');
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) {
        const row = rows[i];
        const cells = row.getElementsByTagName('td');
        let found = false;

        for (let j = 0; j < cells.length - 1; j++) {
            if (cells[j].textContent.toLowerCase().indexOf(filter) > -1) {
                found = true;
                break;
            }
        }

        row.style.display = found ? '' : 'none';
    }
}

// Modal functions
function openAddModal() {
    currentEditingId = null;
    document.getElementById('modalTitle').textContent = 'Add New Inventory Item';
    document.getElementById('submitText').textContent = 'Add Item';
    document.getElementById('inventoryForm').reset();
    document.getElementById('itemId').value = '';
    const dropdown = document.getElementById('menuSelectDropdown');
    const readonly = document.getElementById('menuSelectReadonly');
    const hidden = document.getElementById('menuId');
    if (dropdown) dropdown.style.display = '';
    if (readonly) { readonly.style.display = 'none'; readonly.value = ''; }
    if (hidden) hidden.value = '';
    document.getElementById('inventoryModal').style.display = 'block';
}

function openEditModal(id, menuId, stockQuantity) {
    currentEditingId = id;
    document.getElementById('modalTitle').textContent = 'Edit Inventory Item';
    document.getElementById('submitText').textContent = 'Update Item';
    document.getElementById('itemId').value = id;
    const dropdown = document.getElementById('menuSelectDropdown');
    const readonly = document.getElementById('menuSelectReadonly');
    const hidden = document.getElementById('menuId');
    if (dropdown) dropdown.style.display = 'none';
    if (readonly) readonly.style.display = '';
    hidden.value = menuId;
    const menuObj = menuItems.find(m => parseInt(m.id) === parseInt(menuId));
    if (menuObj) {
        readonly.value = `${menuObj.name} (${menuObj.category}) - ₱${parseFloat(menuObj.price).toFixed(2)}`;
    } else {
        readonly.value = '';
    }
    document.getElementById('stockQuantity').value = stockQuantity;
    document.getElementById('inventoryModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('inventoryModal').style.display = 'none';
    currentEditingId = null;
}

// Quick update modal functions
function openQuickUpdate(id, name, currentStock) {
    document.getElementById('quickUpdateItemName').textContent = name;
    document.getElementById('currentStock').textContent = currentStock;
    document.getElementById('addStockAmount').value = 1;
    document.getElementById('removeStockAmount').value = 1;
    document.getElementById('quickUpdateModal').style.display = 'block';
    document.getElementById('quickUpdateModal').dataset.itemId = id;
}

function closeQuickUpdateModal() {
    document.getElementById('quickUpdateModal').style.display = 'none';
}

// Handle form submission
async function handleFormSubmit(e) {
    e.preventDefault();

    const hidden = document.getElementById('menuId');
    if (!hidden.value) {
        const dropdown = document.getElementById('menuSelectDropdown');
        if (dropdown && dropdown.value) {
            hidden.value = dropdown.value;
        }
    }

    if (!hidden.value) {
        showToast('Please select a valid menu item from the list.', 'warning');
        return;
    }

    const formData = new FormData(e.target);
    const action = currentEditingId ? 'update' : 'create';
    formData.append('action', action);

    try {
        const response = await fetch('../backend/inventory_crud.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            showToast(data.message, 'success');
            closeModal();
            loadInventoryData();
        } else {
            showToast('Error: ' + data.message, 'error');
        }
    } catch (error) {
        showToast('Error: ' + error.message, 'error');
    }
}

// Edit item
async function editItem(id) {
    try {
        const response = await fetch(`../backend/inventory_crud.php?action=read&id=${id}`);
        const data = await response.json();

        if (data.success && data.data.length > 0) {
            const item = data.data[0];
            openEditModal(item.id, item.menu_id, item.stock_quantity);
        } else {
            showToast('Error loading item data', 'error');
        }
    } catch (error) {
        showToast('Error: ' + error.message, 'error');
    }
}

// Delete item
function deleteItem(id, name) {
    document.getElementById('confirmMessage').textContent = `Are you sure you want to delete "${name}" from inventory?`;
    document.getElementById('confirmModal').style.display = 'block';

    document.getElementById('confirmBtn').onclick = async function() {
        closeConfirmModal();

        try {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);

            const response = await fetch('../backend/inventory_crud.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                showToast(data.message, 'success');
                loadInventoryData();
            } else {
                showToast('Error: ' + data.message, 'error');
            }
        } catch (error) {
            showToast('Error: ' + error.message, 'error');
        }
    };
}

// Quick stock update
async function updateStock(operation) {
    const modal = document.getElementById('quickUpdateModal');
    const itemId = modal.dataset.itemId;
    const amount = operation === 'add'
        ? parseInt(document.getElementById('addStockAmount').value)
        : parseInt(document.getElementById('removeStockAmount').value);

    if (!amount || amount <= 0) {
        showToast('Please enter a valid amount', 'warning');
        return;
    }

    try {
        const formData = new FormData();
        formData.append('action', 'quickUpdate');
        formData.append('id', itemId);
        formData.append('operation', operation);
        formData.append('amount', amount);

        const response = await fetch('../backend/inventory_crud.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            showToast(data.message, 'success');
            closeQuickUpdateModal();
            loadInventoryData();
        } else {
            showToast('Error: ' + data.message, 'error');
        }
    } catch (error) {
        showToast('Error: ' + error.message, 'error');
    }
}

// Confirm modal functions
function closeConfirmModal() {
    document.getElementById('confirmModal').style.display = 'none';
}

// Ingredients functions
async function api(action, data) {
    const opts = { method: data ? 'POST' : 'GET', headers: {} };
    if (data) {
        opts.headers['Content-Type'] = 'application/json';
        opts.body = JSON.stringify(data);
    }
    const url = '../backend/ingredients_crud.php?action=' + encodeURIComponent(action);
    try {
        const res = await fetch(url, opts);
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return await res.json();
    } catch (err) {
        console.error('API error', action, err);
        return { status: 'error', message: err.message };
    }
}

function showToastIngredients(message, type='success'){
    let toast = document.getElementById('ingredients-toast');
    if (!toast){
        toast = document.createElement('div');
        toast.id = 'ingredients-toast';
        toast.style.position = 'fixed';
        toast.style.right = '20px';
        toast.style.bottom = '20px';
        toast.style.padding = '16px 20px';
        toast.style.borderRadius = '12px';
        toast.style.boxShadow = '0 8px 25px rgba(0,0,0,0.15)';
        toast.style.color = '#fff';
        toast.style.zIndex = 9999;
        toast.style.fontWeight = '500';
        toast.style.fontSize = '14px';
        toast.style.backdropFilter = 'blur(10px)';
        toast.style.border = '1px solid rgba(255,255,255,0.2)';
        toast.style.animation = 'slideInRight 0.3s ease';
        document.body.appendChild(toast);
    }
    toast.textContent = message;
    toast.style.background = type === 'error' ? '#d9534f' : (type === 'warning' ? '#f0ad4e' : '#5cb85c');
    toast.style.display = 'block';
    clearTimeout(toast._timeout);
    toast._timeout = setTimeout(()=>{ toast.style.display = 'none'; }, 3000);
}

function clearIngredientForm(){
    document.getElementById('ingredientId').value = '';
    document.getElementById('ingredientName').value = '';
    document.getElementById('ingredientUnit').value = 'pcs';
    document.getElementById('ingredientStock').value = '0';
    document.getElementById('ingredientPrice').value = '0.00';
}

async function loadIngredients(){
    const res = await api('list');
    const tbody = document.querySelector('#ingredientsTable tbody');
    tbody.innerHTML='';
    if (res.status === 'ok' && res.data) {
        res.data.forEach(it=>{
            const price = parseFloat(it.price || 0).toFixed(2);
            const stock = parseFloat(it.stock_quantity || 0);
            let statusClass = 'status-in-stock';
            let statusText = 'In Stock';
            if (stock === 0) {
                statusClass = 'status-out-of-stock';
                statusText = 'Out of Stock';
            } else if (stock <= 5) {
                statusClass = 'status-low-stock';
                statusText = 'Low Stock';
            }
            const lastUpdate = it.last_updated ? formatDateTime(it.last_updated) : 'Never';
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${it.id}</td><td>${it.name}</td><td>${it.unit}</td><td>₱${price}</td><td><strong>${stock}</strong></td><td><span class="status-badge ${statusClass}">${statusText}</span></td><td>${lastUpdate}</td><td><div class="action-buttons"><button data-id="${it.id}" class="btn btn-primary btn-sm btn-edit" title="Edit"><i class="fas fa-edit"></i></button> <button data-id="${it.id}" class="btn btn-danger btn-sm btn-del" title="Delete"><i class="fas fa-trash"></i></button></div></td>`;
            tbody.appendChild(tr);
        });
        if (res.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:#666;">No ingredients yet. Click Add Ingredient to create one.</td></tr>';
        }
    }
    else {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:#c00;">Error loading ingredients (see console).</td></tr>';
    }
}

function openIngredientModal(mode='create', data=null){
    const modal = document.getElementById('ingredientModal');
    const modalTitle = document.getElementById('modalTitleIngredient');
    const inputId = document.getElementById('ingredientId');
    const inputName = document.getElementById('ingredientName');
    const inputUnit = document.getElementById('ingredientUnit');
    const inputStock = document.getElementById('ingredientStock');
    const inputPrice = document.getElementById('ingredientPrice');

    modal.setAttribute('aria-hidden','false');
    if (mode === 'create'){
        modalTitle.textContent = 'Add Ingredient';
        inputId.value=''; inputName.value=''; inputUnit.value='pcs'; inputStock.value='0'; inputPrice.value='0.00';
    } else {
        modalTitle.textContent = 'Edit Ingredient';
        inputId.value = data.id; inputName.value = data.name; inputUnit.value = data.unit; inputStock.value = data.stock_quantity; inputPrice.value = parseFloat(data.price || 0).toFixed(2);
    }
    inputName.focus();
}

function closeIngredientModal(){
    document.getElementById('ingredientModal').setAttribute('aria-hidden','true');
}

async function handleIngredientFormSubmit(e){
    e.preventDefault();
    const id = document.getElementById('ingredientId').value;
    const payload = {
        name: document.getElementById('ingredientName').value.trim(),
        unit: document.getElementById('ingredientUnit').value.trim(),
        stock_quantity: parseFloat(document.getElementById('ingredientStock').value) || 0,
        price: parseFloat(document.getElementById('ingredientPrice').value) || 0
    };
    try {
        if (id) {
            payload.id = id;
            const res = await api('update', payload);
            if (res.status === 'ok') {
                showToastIngredients('Ingredient updated successfully', 'success');
            } else {
                showToastIngredients('Failed to update ingredient', 'error');
            }
        } else {
            const res = await api('create', payload);
            if (res.status === 'ok') {
                showToastIngredients('Ingredient added successfully', 'success');
            } else {
                showToastIngredients('Failed to add ingredient', 'error');
            }
        }
        closeIngredientModal();
        clearIngredientForm();
        loadIngredients();
    } catch (err) {
        showToastIngredients('An error occurred', 'error');
    }
}

// Handle ingredient table actions
document.addEventListener('click', async (e) => {
    const id = e.target.dataset.id;
    if (!id) return;
    if (e.target.classList.contains('btn-del')) {
        if (!confirm('Delete ingredient?')) return;
        try {
            const res = await api('delete',{id});
            if (res.status === 'ok') {
                showToastIngredients('Ingredient deleted successfully', 'success');
            } else {
                showToastIngredients('Failed to delete ingredient', 'error');
            }
            loadIngredients();
        } catch (err) {
            showToastIngredients('An error occurred', 'error');
        }
    }
    if (e.target.classList.contains('btn-edit')) {
        const tr = e.target.closest('tr');
        const id = tr.children[0].textContent.trim();
        const name = tr.children[1].textContent.trim();
        const unit = tr.children[2].textContent.trim();
        const priceText = tr.children[3].textContent.trim();
        const price = parseFloat(priceText.replace('₱', '')) || 0;
        const stock = tr.children[4].textContent.trim();
        openIngredientModal('edit',{ id, name, unit, stock_quantity: stock, price });
    }
});

// Toast notification
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    const icon = toast.querySelector('.toast-icon');
    const messageElement = toast.querySelector('.toast-message');

    let iconClass = 'fas fa-check-circle';
    if (type === 'error') {
        iconClass = 'fas fa-exclamation-circle';
    } else if (type === 'warning') {
        iconClass = 'fas fa-exclamation-triangle';
    }

    icon.className = 'toast-icon ' + iconClass;
    messageElement.textContent = message;

    toast.className = 'toast ' + type;
    toast.style.display = 'block';

    setTimeout(() => {
        closeToast();
    }, 5000);
}

function closeToast() {
    const toast = document.getElementById('toast');
    toast.style.display = 'none';
}

// Loading spinner
function showLoadingSpinner(show) {
    const spinner = document.getElementById('loadingSpinner');
    const table = document.querySelector('.table-container');

    if (show) {
        spinner.style.display = 'block';
        table.style.display = 'none';
    } else {
        spinner.style.display = 'none';
        table.style.display = 'block';
    }
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
        e.preventDefault();
        openAddModal();
    }

    if (e.key === 'Escape') {
        closeModal();
        closeQuickUpdateModal();
        closeConfirmModal();
        closeIngredientModal();
        closeToast();
    }

    if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
        e.preventDefault();
        loadInventoryData();
    }
});

// Export functions for global access
window.openAddModal = openAddModal;
window.closeModal = closeModal;
window.editItem = editItem;
window.deleteItem = deleteItem;
window.openQuickUpdate = openQuickUpdate;
window.closeQuickUpdateModal = closeQuickUpdateModal;
window.updateStock = updateStock;
window.closeConfirmModal = closeConfirmModal;
window.closeToast = closeToast;
window.filterTable = filterTable;
