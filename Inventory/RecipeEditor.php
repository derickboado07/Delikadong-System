<?php
// Simple Recipe Editor page — uses existing include/navbar.php for layout
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Recipe Editor</title>
    <link rel="stylesheet" href="../Inventory/RecipeEditor.css?v=1.0" />
    <link rel="stylesheet" href="../HomePage/MainHome.css?v=1.0" />
    <script defer src="../Inventory/RecipeEditor.js?v=1.0"></script>
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  </head>
  <body>
    <?php include_once __DIR__ . '/../include/navbar.php'; ?>

    <div class="right-Menu">
      <div class="content-wrapper recipe-editor">
        <h2>Recipe Editor</h2>

        <div class="controls">
          <div class="control-group">
            <label for="menuSelect">Select Menu Item</label>
            <select id="menuSelect">
              <option value="">-- Select Menu --</option>
            </select>
          </div>
          <div class="control-buttons">
            <button id="loadRecipeBtn" class="btn secondary">Load Recipe</button>
            <button id="newRecipeBtn" class="btn">New Recipe</button>
          </div>
        </div>

        <div class="recipe-area">
          <div class="table-container">
            <table id="recipeTable">
              <thead>
                <tr>
                  <th>Ingredient</th>
                  <th>Quantity Required</th>
                  <th>Unit</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <!-- rows inserted dynamically -->
              </tbody>
            </table>
          </div>

          <div class="recipe-actions">
            <button id="addRowBtn" class="btn">Add Ingredient</button>
            <button id="saveRecipeBtn" class="btn primary">Save Recipe</button>
            <button id="clearRecipeBtn" class="btn secondary">Clear All</button>
          </div>
        </div>

      </div>
    </div>
  </body>
</html>
