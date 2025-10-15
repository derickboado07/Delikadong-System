// Toast notification helper
function showToast(message, type = 'success') {
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

document.addEventListener('DOMContentLoaded', () => {
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
      showToast('Please select a menu item first', 'warning');
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
      showToast('Please add at least one ingredient to the recipe', 'warning');
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
        showToast('Recipe saved successfully!', 'success');
        loadRecipe(currentMenuId);
      } else {
        showToast('Error saving recipe: ' + (json.message || 'Unknown error'), 'error');
      }
    } catch (e) {
      console.error(e);
      showToast('Network error occurred', 'error');
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
});
