document.addEventListener('DOMContentLoaded', function(){
  initProducts();
});

// small toast helper for this page
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

// Products UI
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

async function loadCategories(){
  // deprecated: categories are now a fixed set in the UI
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
