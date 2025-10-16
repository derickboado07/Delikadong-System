<?php
?>
<!doctype html>
<html>
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Products</title>
    <link rel="stylesheet" href="../HomePage/MainHome.css">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="Products.css">
  </head>
  <body>
    <?php include '../include/navbar.php'; ?>

    <div class="right-Menu">
      <div class="content-wrapper">
        <div class="content-header">
          <h1>Products</h1>
          <div class="header-actions">
            <input id="searchInput" placeholder="Search products..." />
            <select id="categoryFilter"><option value="">All Categories</option></select>
            <button id="btnAddProduct" class="btn btn-primary">Add Product</button>
          </div>
        </div>

        <div class="table-card">
          <div class="table-container">
            <table id="productsTable">
              <thead><tr><th>Image</th><th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Actions</th></tr></thead>
              <tbody></tbody>
            </table>
          </div>
          <div class="table-footer">
            <button id="prevPage" class="btn">Prev</button>
            <span id="pageInfo"></span>
            <button id="nextPage" class="btn">Next</button>
          </div>
        </div>

        <!-- Product Modal -->
        <div id="productModal" class="modal" aria-hidden="true">
          <div class="modal-dialog">
            <form id="productForm" class="modal-form">
              <h2 id="modalTitle">Add Product</h2>
              <input type="hidden" id="productId" />
              <label>Name <input id="productName" required /></label>
              <label>Category
                <select id="productCategory" required>
                  <option value="pastries">pastries</option>
                  <option value="meals">meals</option>
                  <option value="espresso">espresso</option>
                  <option value="signature">signature</option>
                </select>
              </label>
              <label>Price <input id="productPrice" type="number" step="0.01" required /></label>
              <label>Image <input id="productImage" type="file" accept="image/*" /></label>
              <div class="modal-actions">
                <button type="button" class="btn btn-secondary" id="cancelProduct">Cancel</button>
                <button type="submit" class="btn btn-primary" id="saveProduct">Save</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <script src="Products.js"></script>
  </body>
</html>
