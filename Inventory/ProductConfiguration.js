// Product Configuration JavaScript
document.addEventListener('DOMContentLoaded', function(){
  // Mode selector
  const modeSelect = document.getElementById('modeSelect');
  const productsSection = document.getElementById('productsSection');
  const recipesSection = document.getElementById('recipesSection');

  modeSelect.addEventListener('change', function() {
    if (this.value === 'products') {
      productsSection.style.display = 'block';
      recipesSection.style.display = 'none';
      initProducts();
    } else {
      productsSection.style.display = 'none';
      recipesSection.style.display = 'block';
      initRecipes();
    }
  });

  // Initialize products by default
  initProducts();
});

// Products functionality (copied from Products.js)
function showToast(message, type='success'){
  let toast = document.getElementById('products-toast');
  if (!toast){
    toast = document.createElement('div');
    toast.id = 'products-toast';
    toast.style.position = 'fixed';
    toast.style.right = '20px';
    toast.style.bottom = '20px';
    toast.style.padding = '10px 14px';
    toast.style.borderRadius = '6px';
    toast.style.boxShadow = '0 2px 6px rgba(0,0,0,0.2)';
    toast.style.color = '#fff';
    toast.style.zIndex = 9999;
    document.body.appendChild(toast);
  }
  toast.textContent = message;
  toast.style.background = type === 'error' ? '#d9534f' : (type === 'warning' ? '#f0ad4e' : '#5cb85c');
  toast.style.display = 'block';
  clearTimeout(toast._timeout);
  toast._timeout = setTimeout(()=>{ toast.style.display = 'none'; }, 3000);
}

function clearProductForm(){
  document.getElementById('productId').value = '';
  document.getElementById('productName').value = '';
  document.getElementById('productCategory').value = '';
  document.getElementById('productPrice').value = '0';
  const fileInput = document.getElementById('productImage');
  if (fileInput) fileInput.value = '';
}

async function loadProducts(){
  try{
    const res = await fetch('../backend/inventory_crud.php?action=getMenuItems');
    const json = await res.json();
    const tbody = document.querySelector('#productsTable tbody');
    tbody.innerHTML = '';
    if (json.success && Array.isArray(json.data)){
      json.data.forEach(p=>{
        const imageSrc = p.image ? `../Images/${p.image}` : '../Images/Icon.png';
        const tr = document.createElement('tr');
        tr.innerHTML = `<td><img src="${imageSrc}" alt="${p.name}" class="product-thumbnail" onerror="this.src='../Images/Icon.png'"></td><td>${p.id}</td><td>${p.name}</td><td>${p.category}</td><td>₱${parseFloat(p.price).toFixed(2)}</td><td><button class="btn btn-sm btn-primary btn-edit" data-id="${p.id}">Edit</button> <button class="btn btn-sm btn-danger btn-del" data-id="${p.id}">Delete</button></td>`;
        tbody.appendChild(tr);
      });
      if (json.data.length === 0) tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#666">No products found</td></tr>';
    } else {
      tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:#c00">Error loading products</td></tr>';
    }
  }catch(e){
    console.error(e);
  }
}

let currentPage = 1, perPage = 10, currentQuery = '', currentCategory = '';

function initProducts(){
  populateCategoryFilter();
  bindProductEvents();
  fetchAndRender();
}

function bindProductEvents(){
  document.getElementById('searchInput').addEventListener('input', (e)=>{ currentQuery = e.target.value; currentPage = 1; fetchAndRender(); });
  document.getElementById('categoryFilter').addEventListener('change', (e)=>{ currentCategory = e.target.value; currentPage = 1; fetchAndRender(); });
  document.getElementById('prevPage').addEventListener('click', ()=>{ if (currentPage>1){ currentPage--; fetchAndRender(); } });
  document.getElementById('nextPage').addEventListener('click', ()=>{ currentPage++; fetchAndRender(); });
  document.getElementById('btnAddProduct').addEventListener('click', ()=> openProductModal('create'));
  document.getElementById('cancelProduct').addEventListener('click', ()=> closeProductModal());
  document.getElementById('productForm').addEventListener('submit', saveProduct);

  document.querySelector('#productsTable tbody').addEventListener('click', async (e)=>{
    const id = e.target.dataset.id;
    if (!id) return;
    if (e.target.classList.contains('btn-del')){
      if (!confirm('Delete product?')) return;
      const res = await fetch('../backend/menu_crud.php?action=delete&id='+encodeURIComponent(id));
      const json = await res.json();
      if (json && json.success) { showToast('Product deleted','success'); fetchAndRender(); }
      else showToast('Failed to delete product','error');
    }
    if (e.target.classList.contains('btn-edit')){
      const res = await fetch('../backend/menu_crud.php?action=get&id='+encodeURIComponent(id));
      const json = await res.json();
      openProductModal('edit', json.data);
    }
  });
}

function populateCategoryFilter(){
  const sel = document.getElementById('categoryFilter');
  ['','pastries','meals','espresso','signature'].forEach(c=>{ const opt = document.createElement('option'); opt.value=c; opt.textContent = c || 'All Categories'; sel.appendChild(opt); });
}

async function fetchAndRender(){
  const url = `../backend/menu_crud.php?action=list&page=${currentPage}&per_page=${perPage}&q=${encodeURIComponent(currentQuery)}&category=${encodeURIComponent(currentCategory)}`;
  const res = await fetch(url);
  const json = await res.json();
  // page info
  document.getElementById('pageInfo').textContent = `Page ${json.pagination.page} / ${Math.ceil(json.pagination.total/json.pagination.per_page)}`;
  // render rows
  const tbody = document.querySelector('#productsTable tbody'); tbody.innerHTML='';
  if (json.success && Array.isArray(json.data)){
    json.data.forEach(p=>{
      const imageSrc = p.image ? `../Images/${p.image}` : '../Images/Icon.png';
      const tr=document.createElement('tr');
      tr.innerHTML = `<td><img src="${imageSrc}" alt="${p.name}" class="product-thumbnail" onerror="this.src='../Images/Icon.png'"></td><td>${p.id}</td><td>${p.name}</td><td>${p.category}</td><td>₱${parseFloat(p.price).toFixed(2)}</td><td><button class="btn btn-sm btn-primary btn-edit" data-id="${p.id}">Edit</button> <button class="btn btn-sm btn-danger btn-del" data-id="${p.id}">Delete</button></td>`;
      tbody.appendChild(tr);
    });
  } else { tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#c00">Error loading products</td></tr>' }
}

function openProductModal(mode='create', data=null){
  document.getElementById('productModal').setAttribute('aria-hidden','false');
  if (mode==='create'){ document.getElementById('modalTitle').textContent='Add Product'; document.getElementById('productId').value=''; document.getElementById('productName').value=''; document.getElementById('productCategory').value=''; document.getElementById('productPrice').value='0'; }
  else { document.getElementById('modalTitle').textContent='Edit Product'; document.getElementById('productId').value=data.id; document.getElementById('productName').value=data.name; document.getElementById('productCategory').value=data.category; document.getElementById('productPrice').value=data.price; }
}

function closeProductModal(){ document.getElementById('productModal').setAttribute('aria-hidden','true'); }

async function saveProduct(e){
  e.preventDefault();
  const id = document.getElementById('productId').value;
  const name = document.getElementById('productName').value.trim();
  const category = document.getElementById('productCategory').value.trim();
  const price = parseFloat(document.getElementById('productPrice').value)||0;
  const fileInput = document.getElementById('productImage');
  const url = '../backend/menu_crud.php?action=' + (id ? 'update' : 'create');

  let res, json;
  if (fileInput && fileInput.files && fileInput.files.length > 0) {
    const fd = new FormData();
    if (id) fd.append('id', id);
    fd.append('name', name);
    fd.append('category', category);
    fd.append('price', price);
    fd.append('image', fileInput.files[0]);
    res = await fetch(url, { method: 'POST', body: fd });
    json = await res.json();
  } else {
    const payload = { name, category, price };
    if (id) payload.id = id;
    res = await fetch(url, { method:'POST', body: JSON.stringify(payload), headers:{'Content-Type':'application/json'} });
    res = await fetch(url, { method:'POST', body: JSON.stringify(payload), headers:{'Content-Type':'application/json'} });
    json = await res.json();
  }
  if (json && json.success) {
    showToast('Product saved', 'success');
    clearProductForm();
    closeProductModal();
    fetchAndRender();
  } else {
    showToast('Failed to save product', 'error');
  }
}

// Recipes functionality (copied from RecipeEditor.js)
function showRecipeToast(message, type = 'success') {
  let toast = document.getElementById('recipe-toast');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'recipe-toast';
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
  toast._timeout = setTimeout(() => { toast.style.display = 'none'; }, 3000);
}

const apiBase = '../backend/ingredients_crud.php';
const menuApi = '../backend/inventory_crud.php';

function initRecipes() {
  const menuSelect = document.getElementById('menuSelect');
  const loadBtn = document.getElementById('loadRecipeBtn');
  const newBtn = document.getElementById('newRecipeBtn');
  const addRowBtn = document.getElementById('addRowBtn');
  const saveBtn = document.getElementById('saveRecipeBtn');
  const clearBtn = document.getElementById('clearRecipeBtn');
  const tbody = document.querySelector('#recipeTable tbody');

  let currentMenuId = null;

  async function fetchMenus() {
    try {
      const res = await fetch(menuApi + '?action=getMenuItems');
      const data = await res.json();
      if (!data || !Array.isArray(data.data)) return;
      menuSelect.innerHTML = '<option value="">-- Select Menu --</option>' + data.data.map(m => `<option value="${m.id}">${m.name}</option>`).join('');
    } catch (e) { console.error(e); }
  }

  async function fetchIngredients() {
    const res = await fetch(apiBase + '?action=list');
    const json = await res.json();
    return Array.isArray(json.data) ? json.data : [];
  }

  function makeIngredientOptions(ingredients) {
    return '<option value="">-- Select Ingredient --</option>' + ingredients.map(i => `<option value="${i.id}" data-unit="${i.unit}">${i.name}</option>`).join('');
  }

  function addRow(recipeItem = {}) {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>
        <select class="ingredient-select">
        </select>
      </td>
      <td><input class="qty-input" type="number" min="0" step="0.01" value="${recipeItem.quantity_required || ''}" /></td>
      <td class="unit-cell">${recipeItem.unit || ''}</td>
      <td><button class="btn remove-row">Remove</button></td>
    `;
    tbody.appendChild(tr);

    fetchIngredients().then(ings => {
      const sel = tr.querySelector('.ingredient-select');
      sel.innerHTML = makeIngredientOptions(ings);
      if (recipeItem && recipeItem.ingredient_id) {
        // set value explicitly as string to avoid type mismatches
        sel.value = String(recipeItem.ingredient_id);
        // dispatch change so unit cell updates
        sel.dispatchEvent(new Event('change'));
      }
      sel.addEventListener('change', (e) => {
        const opt = e.target.selectedOptions[0];
        tr.querySelector('.unit-cell').textContent = opt ? (opt.dataset.unit || '') : '';
      });
    });

    tr.querySelector('.remove-row').addEventListener('click', () => tr.remove());
  }

  async function loadRecipe(menuId) {
    tbody.innerHTML = '';
    if (!menuId) return;
    currentMenuId = menuId;
    const res = await fetch(apiBase + '?action=getRecipes&menu_id=' + encodeURIComponent(menuId));
    const json = await res.json();
    if (!json || !Array.isArray(json.data)) return;
    if (json.data.length === 0) addRow();
    json.data.forEach(r => addRow({ ingredient_id: r.ingredient_id, quantity_required: r.quantity_required, unit: r.unit }));
  }

  async function saveRecipe() {
    if (!currentMenuId) {
      showRecipeToast('Please select a menu item first', 'warning');
      return;
    }

    // gather rows
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const payload = rows.map(r => {
      const ingSel = r.querySelector('.ingredient-select');
      const qty = r.querySelector('.qty-input').value || 0;
      return { ingredient_id: ingSel.value, quantity_required: qty };
    }).filter(r => r.ingredient_id);

    if (payload.length === 0) {
      showRecipeToast('Please add at least one ingredient to the recipe', 'warning');
      return;
    }

    // send saveRecipe action
    try {
      const resp = await fetch(apiBase + '?action=saveRecipe', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ menu_id: currentMenuId, items: payload })
      });
      const json = await resp.json();
      if (json.status === 'ok') {
        showRecipeToast('Recipe saved successfully!', 'success');
        loadRecipe(currentMenuId);
      } else {
        showRecipeToast('Error saving recipe: ' + (json.message || 'Unknown error'), 'error');
      }
    } catch (e) {
      console.error(e);
      showRecipeToast('Network error occurred', 'error');
    }
  }

  // bind events
  loadBtn.addEventListener('click', () => loadRecipe(menuSelect.value));
  newBtn.addEventListener('click', () => { tbody.innerHTML = ''; addRow(); currentMenuId = menuSelect.value || null; });
  addRowBtn.addEventListener('click', () => addRow());
  saveBtn.addEventListener('click', saveRecipe);
  clearBtn.addEventListener('click', () => { tbody.innerHTML = ''; });

  // init
  fetchMenus();

  // Scroll to top button functionality
  const scrollToTopBtn = document.getElementById('scrollToTopBtn');
  const recipeArea = document.querySelector('.recipe-area');

  function toggleScrollToTopBtn() {
    if (recipeArea.scrollTop > 200) {
      scrollToTopBtn.classList.add('show');
    } else {
      scrollToTopBtn.classList.remove('show');
    }
  }

  recipeArea.addEventListener('scroll', toggleScrollToTopBtn);

  scrollToTopBtn.addEventListener('click', () => {
    recipeArea.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  });

  // Scroll to bottom button functionality
  const scrollToBottomBtn = document.getElementById('scrollToBottomBtn');

  function toggleScrollToBottomBtn() {
    if (recipeArea.scrollTop < recipeArea.scrollHeight - recipeArea.clientHeight - 200) {
      scrollToBottomBtn.classList.add('show');
    } else {
      scrollToBottomBtn.classList.remove('show');
    }
  }

  recipeArea.addEventListener('scroll', toggleScrollToBottomBtn);

  scrollToBottomBtn.addEventListener('click', () => {
    recipeArea.scrollTo({
      top: recipeArea.scrollHeight,
      behavior: 'smooth'
    });
  });
}
