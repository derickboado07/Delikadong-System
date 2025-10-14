<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Inventory Management</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="Inven.css">
</head>
<body>
<?php include '../include/navbar.php'; ?>

  <div class="content-wrapper">
    <div class="content-header">
      <h1>Inventory</h1>
      <img src="../Images/Icon.png" alt="Logo" class="top-logo" />
    </div>

    <div class="category-container">
      <button class="category-btn" aria-expanded="false" id="categoryToggle">
        Category <span class="arrow">▸</span>
      </button>

      <div class="category-list" role="list">
        <button class="category-item active" data-target="coffee-section">Coffee</button>
        <button class="category-item" data-target="pastries-section">Pastries</button>
        <button class="category-item" data-target="meals-section">Meals</button>
      </div>
    </div>

    <section id="coffee-section" class="section active">
      <h2 class="section-title">Coffee-ingredients</h2>
      <div class="item" data-key="Espresso" data-initial="80" data-max="100" data-unit=" grams SINGLE SHOT" data-manual-adjustment="20"><span class="item-name">Espresso</span><div class="bar-container"><div class="bar"></div></div><span class="stock-text"></span><div class="btn-group"><button class="stock-btn deduct-btn">-</button><button class="stock-btn add-btn">+</button></div></div>
      <div class="item" data-key="Water Bottles" data-initial="50" data-max="50" data-unit="LITRES" data-manual-adjustment="10"><span class="item-name">Water Bottles</span><div class="bar-container"><div class="bar"></div></div><span class="stock-text"></span><div class="btn-group"><button class="stock-btn deduct-btn">-</button><button class="stock-btn add-btn">+</button></div></div>

      <h3 class="section-subtitle" style="margin-top: 40px;">Extras/Sides:</h3>
      <div class="item" data-key="Sugar 15 (grams) " data-initial="50" data-max="100" data-unit="grams" data-manual-adjustment="10"><span class="item-name">Sugar</span><div class="bar-container"><div class="bar"></div></div><span class="stock-text"></span><div class="btn-group"><button class="stock-btn deduct-btn">-</button><button class="stock-btn add-btn">+</button></div></div>
      <div class="item" data-key="Syrup 10 (grams)" data-initial="20" data-max="40" data-unit="grams" data-manual-adjustment="5"><span class="item-name">Syrup</span><div class="bar-container"><div class="bar"></div></div><span class="stock-text"></span><div class="btn-group"><button class="stock-btn deduct-btn">-</button><button class="stock-btn add-btn">+</button></div></div>
      <div class="item" data-key="Espresso 15 (grams)" data-initial="0.8" data-max="1.5" data-unit="grams" data-manual-adjustment="0.3"><span class="item-name">Espresso</span><div class="bar-container"><div class="bar"></div></div><span class="stock-text"></span><div class="btn-group"><button class="stock-btn deduct-btn">-</button><button class="stock-btn add-btn">+</button></div></div>
    </section>

    <section id="pastries-section" class="section">
      <h2 class="section-title">Pastries-ingredients</h2>
      
      <h3 class="section-subtitle">Sandwich Ingredients:</h3>
      <div class="item" data-key="Bread Slices" data-initial="100" data-max="150" data-unit="SLICES" data-manual-adjustment="20"><span class="item-name">Bread Slices</span><div class="bar-container"><div class="bar"></div></div><span class="stock-text"></span><div class="btn-group"><button class="stock-btn deduct-btn">-</button><button class="stock-btn add-btn">+</button></div></div>
      <div class="item" data-key="Mayonnaise" data-initial="500" data-max="1000" data-unit="G" data-manual-adjustment="250"><span class="item-name">Mayonnaise</span><div class="bar-container"><div class="bar"></div></div><span class="stock-text"></span><div class="btn-group"><button class="stock-btn deduct-btn">-</button><button class="stock-btn add-btn">+</button></div></div>
      <div class="item" data-key="Salt/Pepper" data-initial="200" data-max="500" data-unit="G" data-manual-adjustment="100"><span class="item-name">Salt/Pepper Mix</span><div class="bar-container"><div class="bar"></div></div><span class="stock-text"></span><div class="btn-group"><button class="stock-btn deduct-btn">-</button><button class="stock-btn add-btn">+</button></div></div>

      <div class="item" data-key="Chicken Breast" data-initial="1500" data-max="2000" data-unit="G" data-manual-adjustment="500"><span class="item-name">Chicken Breast</span><div class="bar-container"><div class="bar"></div></div><span class="stock-text"></span><div class="btn-group"><button class="stock-btn deduct-btn">-</button><button class="stock-btn add-btn">+</button></div></div>
      <div class="item" data-key="Lettuce" data-initial="10" data-max="15" data-unit="HEADS" data-manual-adjustment="3"><span class="item-name">Lettuce (Heads)</span><div class="bar-container"><div class="bar"></div></div><span class="stock-text"></span><div class="btn-group"><button class="stock-btn deduct-btn">-</button><button class="stock-btn add-btn">+</button></div></div>
      <div class="item" data-key="Tomato Slices" data-initial="50" data-max="75" data-unit="SLICES" data-manual-adjustment="15"><span class="item-name">Tomato Slices</span><div class="bar-container"><div class="bar"></div></div><span class="stock-text"></span><div class="btn-group"><button class="stock-btn deduct-btn">-</button><button class="stock-btn add-btn">+</button></div></div>
      <div class="item" data-key="Sandwich Spread" data-initial="400" data-max="800" data-unit="ML" data-manual-adjustment="200"><span class="item-name">Sandwich Spread</span><div class="bar-container"><div class="bar"></div></div><span class="stock-text"></span><div class="btn-group"><button class="stock-btn deduct-btn">-</button><button class="stock-btn add-btn">+</button></div></div>
      
      <h3 class="section-subtitle" style="margin-top: 20px;">Outside Purchased Food (PCS):</h3>
      <div class="item" data-key="Cookie Crinkles" data-initial="30" data-max="50" data-unit="PCS" data-manual-adjustment="10"><span class="item-name">Cookie Crinkles (PCS)</span><div class="bar-container"><div class="bar"></div></div><span class="stock-text"></span><div class="btn-group"><button class="stock-btn deduct-btn">-</button><button class="stock-btn add-btn">+</button></div></div>
      <div class="item" data-key="Flavored Croissant" data-initial="20" data-max="30" data-unit="PCS" data-manual-adjustment="5"><span class="item-name">Flavored Croissant</span><div class="bar-container"><div class="bar"></div></div><span class="stock-text"></span><div class="btn-group"><button class="stock-btn deduct-btn">-</button><button class="stock-btn add-btn">+</button></div></div>
      <div class="item" data-key="Macho Nacho" data-initial="15" data-max="25" data-unit="PACKS" data-manual-adjustment="5"><span class="item-name">Macho Nacho</span><div class="bar-container"><div class="bar"></div></div><span class="stock-text"></span><div class="btn-group"><button class="stock-btn deduct-btn">-</button><button class="stock-btn add-btn">+</button></div></div>
    </section>

    <section id="meals-section" class="section">
      <h2 class="section-title">Meals-inventory</h2>
      
      <h3 class="section-subtitle">Ingredients:</h3>
      <div class="item" data-key="Eggs (PCS)" data-initial="36" data-max="60" data-unit="PCS" data-manual-adjustment="12"><span class="item-name">Eggs</span><div class="bar-container"><div class="bar"></div></div><span class="stock-text"></span><div class="btn-group"><button class="stock-btn deduct-btn">-</button><button class="stock-btn add-btn">+</button></div></div>
      <div class="item" data-key="Rice (Bulk)" data-initial="5000" data-max="10000" data-unit="G" data-manual-adjustment="1000"><span class="item-name">Rice</span><div class="bar-container"><div class="bar"></div></div><span class="stock-text"></span><div class="btn-group"><button class="stock-btn deduct-btn">-</button><button class="stock-btn add-btn">+</button></div></div>
      <div class="item" data-key="Ham (Bulk)" data-initial="1000" data-max="1500" data-unit="G" data-manual-adjustment="250"><span class="item-name">Ham</span><div class="bar-container"><div class="bar"></div></div><span class="stock-text"></span><div class="btn-group"><button class="stock-btn deduct-btn">-</button><button class="stock-btn add-btn">+</button></div></div>
      
      <h3 class="section-subtitle" style="margin-top: 20px;">Condiments and Cooking Essentials:</h3>
      <div class="item" data-key="Cooking Oil" data-initial="5.0" data-max="10.0" data-unit="LITRES" data-manual-adjustment="2.0"><span class="item-name">Cooking Oil</span><div class="bar-container"><div class="bar"></div></div><span class="stock-text"></span><div class="btn-group"><button class="stock-btn deduct-btn">-</button><button class="stock-btn add-btn">+</button></div></div>
      <div class="item" data-key="Salt" data-initial="500" data-max="1000" data-unit="G" data-manual-adjustment="200"><span class="item-name">Salt</span><div class="bar-container"><div class="bar"></div></div><span class="stock-text"></span><div class="btn-group"><button class="stock-btn deduct-btn">-</button><button class="stock-btn add-btn">+</button></div></div>
      <div class="item" data-key="Black Pepper" data-initial="100" data-max="200" data-unit="G" data-manual-adjustment="50"><span class="item-name">Black Pepper</span><div class="bar-container"><div class="bar"></div></div><span class="stock-text"></span><div class="btn-group"><button class="stock-btn deduct-btn">-</button><button class="stock-btn add-btn">+</button></div></div>
      <div class="item" data-key="Soy Sauce" data-initial="1.0" data-max="2.0" data-unit="LITRES" data-manual-adjustment="0.5"><span class="item-name">Soy Sauce</span><div class="bar-container"><div class="bar"></div></div><span class="stock-text"></span><div class="btn-group"><button class="stock-btn deduct-btn">-</button><button class="stock-btn add-btn">+</button></div></div>
      <div class="item" data-key="Vinegar" data-initial="1.0" data-max="2.0" data-unit="LITRES" data-manual-adjustment="0.5"><span class="item-name">Vinegar</span><div class="bar-container"><div class="bar"></div></div><span class="stock-text"></span><div class="btn-group"><button class="stock-btn deduct-btn">-</button><button class="stock-btn add-btn">+</button></div></div>
      
      <h3 class="section-subtitle" style="margin-top: 20px;">Meal Components:</h3>
      <div class="item" data-key="Omelette Spices" data-initial="100" data-max="200" data-unit="G" data-manual-adjustment="50"><span class="item-name">Omelette Spices/Seasoning</span><div class="bar-container"><div class="bar"></div></div><span class="stock-text"></span><div class="btn-group"><button class="stock-btn deduct-btn">-</button><button class="stock-btn add-btn">+</button></div></div>
      <div class="item" data-key="Hungarian Sausage" data-initial="20" data-max="30" data-unit="PCS" data-manual-adjustment="5"><span class="item-name">Hungarian Sausage</span><div class="bar-container"><div class="bar"></div></div><span class="stock-text"></span><div class="btn-group"><button class="stock-btn deduct-btn">-</button><button class="stock-btn add-btn">+</button></div></div>
      <div class="item" data-key="Pasta Noodles" data-initial="2000" data-max="3000" data-unit="G" data-manual-adjustment="500"><span class="item-name">Pasta Noodles</span><div class="bar-container"><div class="bar"></div></div><span class="stock-text"></span><div class="btn-group"><button class="stock-btn deduct-btn">-</button><button class="stock-btn add-btn">+</button></div></div>
      <div class="item" data-key="Carbonara Sauce Base" data-initial="1.0" data-max="2.0" data-unit="LITRES" data-manual-adjustment="0.5"><span class="item-name">Carbonara Sauce Base</span><div class="bar-container"><div class="bar"></div></div><span class="stock-text"></span><div class="btn-group"><button class="stock-btn deduct-btn">-</button><button class="stock-btn add-btn">+</button></div></div>
      <div class="item" data-key="Pork Sisig Meat" data-initial="1500" data-max="2500" data-unit="G" data-manual-adjustment="500"><span class="item-name">Pork Sisig Meat</span><div class="bar-container"><div class="bar"></div></div><span class="stock-text"></span><div class="btn-group"><button class="stock-btn deduct-btn">-</button><button class="stock-btn add-btn">+</button></div></div>

      <h3 class="section-subtitle" style="margin-top: 40px;">Extras/Sides:</h3>
      <div class="item" data-key="White Rice (Portions)" data-initial="50" data-max="100" data-unit="PORTIONS" data-manual-adjustment="10"><span class="item-name">White Rice</span><div class="bar-container"><div class="bar"></div></div><span class="stock-text"></span><div class="btn-group"><button class="stock-btn deduct-btn">-</button><button class="stock-btn add-btn">+</button></div></div>
      <div class="item" data-key="Fried Egg (PCS)" data-initial="20" data-max="40" data-unit="PCS" data-manual-adjustment="5"><span class="item-name">Fried Egg</span><div class="bar-container"><div class="bar"></div></div><span class="stock-text"></span><div class="btn-group"><button class="stock-btn deduct-btn">-</button><button class="stock-btn add-btn">+</button></div></div>
      <div class="item" data-key="Cheese Sauce" data-initial="0.8" data-max="1.5" data-unit="LITRES" data-manual-adjustment="0.3"><span class="item-name">Cheese Sauce</span><div class="bar-container"><div class="bar"></div></div><span class="stock-text"></span><div class="btn-group"><button class="stock-btn deduct-btn">-</button><button class="stock-btn add-btn">+</button></div></div>
    </section>
  </div>

  <div id="message-box"></div>


  <script src="Invent.js"></script>
</body>
</html>