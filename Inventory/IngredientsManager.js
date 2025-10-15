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

// small toast helper for this page
function showToast(message, type='success'){
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
}

async function loadIngredients(){
  const res = await api('list');
  const tbody = document.querySelector('#ingredientsTable tbody');
  tbody.innerHTML='';
  if (res.status === 'ok' && res.data) {
    res.data.forEach(it=>{
      const tr = document.createElement('tr');
      tr.innerHTML = `<td>${it.name}</td><td>${it.unit}</td><td>${it.stock_quantity}</td><td><button data-id="${it.id}" class="btn btn-sm btn-secondary btn-edit">Edit</button> <button data-id="${it.id}" class="btn btn-sm btn-danger btn-del">Delete</button></td>`;
      tbody.appendChild(tr);
    });
    if (res.data.length === 0) {
      tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:#666;">No ingredients yet. Click Add Ingredient to create one.</td></tr>';
    }
  }
  else {
    tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:#c00;">Error loading ingredients (see console).</td></tr>';
  }
}

document.addEventListener('DOMContentLoaded', ()=>{
  // modal elements
  const modal = document.getElementById('ingredientModal');
  const frm = document.getElementById('ingredientForm');
  const modalTitle = document.getElementById('modalTitle');
  const inputId = document.getElementById('ingredientId');
  const inputName = document.getElementById('ingredientName');
  const inputUnit = document.getElementById('ingredientUnit');
  const inputStock = document.getElementById('ingredientStock');

  function openModal(mode='create', data=null){
    modal.setAttribute('aria-hidden','false');
    if (mode === 'create'){
      modalTitle.textContent = 'Add Ingredient';
      inputId.value=''; inputName.value=''; inputUnit.value='pcs'; inputStock.value='0';
    } else {
      modalTitle.textContent = 'Edit Ingredient';
      inputId.value = data.id; inputName.value = data.name; inputUnit.value = data.unit; inputStock.value = data.stock_quantity;
    }
    inputName.focus();
  }

  function closeModal(){ modal.setAttribute('aria-hidden','true'); }

  document.getElementById('btnAdd').addEventListener('click', ()=> openModal('create'));

  document.getElementById('cancelIngredient').addEventListener('click', (e)=>{ e.preventDefault(); closeModal(); });

  frm.addEventListener('submit', async (e)=>{
    e.preventDefault();
    const id = inputId.value;
    const payload = { name: inputName.value.trim(), unit: inputUnit.value.trim(), stock_quantity: parseFloat(inputStock.value) || 0 };
    try {
      if (id) {
        payload.id = id;
        const res = await api('update', payload);
        if (res.status === 'ok') {
          showToast('Ingredient updated successfully', 'success');
        } else {
          showToast('Failed to update ingredient', 'error');
        }
      } else {
        const res = await api('create', payload);
        if (res.status === 'ok') {
          showToast('Ingredient added successfully', 'success');
        } else {
          showToast('Failed to add ingredient', 'error');
        }
      }
      closeModal();
      clearIngredientForm();
      loadIngredients();
    } catch (err) {
      showToast('An error occurred', 'error');
    }
  });

  loadIngredients();

  document.querySelector('#ingredientsTable').addEventListener('click', async (e)=>{
    const id = e.target.dataset.id;
    if (!id) return;
    if (e.target.classList.contains('btn-del')){
      if (!confirm('Delete ingredient?')) return;
      try {
        const res = await api('delete',{id});
        if (res.status === 'ok') {
          showToast('Ingredient deleted successfully', 'success');
        } else {
          showToast('Failed to delete ingredient', 'error');
        }
        loadIngredients();
      } catch (err) {
        showToast('An error occurred', 'error');
      }
    }
    if (e.target.classList.contains('btn-edit')){
      // fetch single row data from table row
      const tr = e.target.closest('tr');
      const name = tr.children[0].textContent.trim();
      const unit = tr.children[1].textContent.trim();
      const stock = tr.children[2].textContent.trim();
      openModal('edit',{ id, name, unit, stock_quantity: stock });
    }
  });
});
