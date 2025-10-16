<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Ingredients Manager</title>
  <link rel="stylesheet" href="../HomePage/MainHome.css?v=1.0">
  <link rel="stylesheet" href="IngredientsManager.css?v=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body>
  
<?php include '../include/navbar.php'; ?>
  <div class="right-Menu">
    <div class="content-wrapper">
      <div class="content-header">
        <h1>Ingredients</h1>
        <div class="header-actions">
          <button class="btn btn-primary" id="btnAdd">Add Ingredient</button>
        </div>
      </div>

      <div class="table-card">
        <div class="table-container">
          <table id="ingredientsTable">
            <thead><tr><th>Name</th><th>Unit</th><th>Stock</th><th>Actions</th></tr></thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
      
      <!-- Ingredient Modal -->
      <div id="ingredientModal" class="modal" aria-hidden="true">
        <div class="modal-dialog">
          <form id="ingredientForm" class="modal-form">
            <h2 id="modalTitle">Add Ingredient</h2>
            <input type="hidden" id="ingredientId" />
            <label>Name
              <input id="ingredientName" name="name" type="text" required />
            </label>
            <label>Unit
              <select id="ingredientUnit" name="unit" required>
                <option value="ml">ml</option>
                <option value="pcs">pcs</option>
                <option value="g">g</option>
              </select>
            </label>
            <label>Stock Quantity
              <input id="ingredientStock" name="stock_quantity" type="number" step="0.01" min="0" required />
            </label>
            <div class="modal-actions">
              <button type="submit" class="btn btn-primary" id="saveIngredient">Save</button>
              <button type="button" class="btn btn-secondary" id="cancelIngredient">Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script src="IngredientsManager.js?v=1.0"></script>
</body>
</html>
