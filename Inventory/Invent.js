document.addEventListener('DOMContentLoaded', () => {

  // --- Global State ---
  let inventoryState = {};

  // --- Initial Animation Logic ---
  const icons = document.querySelectorAll('.icon, .icons');
  icons.forEach((icon, index) => {
    setTimeout(() => {
      icon.classList.add('fade-in-icon');
    }, 100 + index * 100);
  });

  // --- Custom Message Box (Replaces Alert) ---
  const messageBox = document.getElementById('message-box');

  function showMessageBox(message, isSuccess = false) {
    messageBox.textContent = message;
    messageBox.classList.remove('show', 'success');

    if (isSuccess) {
      messageBox.classList.add('show', 'success');
    } else {
      messageBox.classList.add('show');
    }

    setTimeout(() => {
      messageBox.classList.remove('show', 'success');
    }, 3000);
  }

  // --- Category Switching Logic ---
  const categoryToggle = document.getElementById('categoryToggle');
  const categoryList = document.querySelector('.category-list');
  const categoryItems = document.querySelectorAll('.category-item');
  const sections = document.querySelectorAll('.section');

  categoryToggle.addEventListener('click', () => {
    const isExpanded = categoryToggle.getAttribute('aria-expanded') === 'true';
    categoryToggle.setAttribute('aria-expanded', !isExpanded);
  });

  document.body.addEventListener('click', (event) => {
    if (!categoryToggle.contains(event.target) && !categoryList.contains(event.target)) {
      categoryToggle.setAttribute('aria-expanded', 'false');
    }
  });

  categoryItems.forEach(item => {
    item.addEventListener('click', () => {
      const targetId = item.getAttribute('data-target');
      sections.forEach(section => section.classList.remove('active'));

      const targetSection = document.getElementById(targetId);
      if (targetSection) targetSection.classList.add('active');

      categoryItems.forEach(btn => btn.classList.remove('active'));
      item.classList.add('active');

      categoryToggle.setAttribute('aria-expanded', 'false');
    });
  });

  // --- Core Inventory Functions ---

  /**
   * Updates the visual representation of an item's stock level.
   */
  function updateStockUI(key, itemElement) {
    const item = inventoryState[key];
    const bar = itemElement.querySelector(".bar");
    const stockText = itemElement.querySelector(".stock-text");

    // Calculate stock percentage safely
    const percent = item.max > 0 ? (item.current / item.max) * 100 : 0;
    const roundedPercent = Math.round(percent * 10) / 10; // avoids float overflow

    // --- Fixed Liters display logic ---
    const unitLower = item.unit ? item.unit.toLowerCase() : "";
    const isLitre = unitLower.includes("litre") || unitLower.includes("liter");
    
    // Format numbers for display - always show 1 decimal for liters, integers for others
    let currentStockDisplay, maxStockDisplay;
    
    if (isLitre) {
      // Use parseFloat to remove trailing zeros (e.g., 0.40 -> 0.4) while maintaining the 1 decimal place if needed
      currentStockDisplay = parseFloat(item.current.toFixed(1));
      maxStockDisplay = parseFloat(item.max.toFixed(1));
    } else {
      currentStockDisplay = Math.round(item.current);
      maxStockDisplay = Math.round(item.max);
    }

    stockText.textContent = `${currentStockDisplay}/${maxStockDisplay} ${item.unit}`;

    // --- Bar fill fix ---
    bar.style.width = Math.min(roundedPercent, 100) + "%";

    // --- Bar color logic ---
    if (item.current >= item.max) {
      // Full capacity
      bar.style.backgroundColor = '#4dd0e1';
    } else if (percent < 20) {
      // Low stock
      bar.style.backgroundColor = '#ff9800';
    } else {
      // Normal range
      bar.style.backgroundColor = '#4CAF50';
    }
  }

  /**
   * Deducts stock for an item.
   */
  function handleManualDeduction(key, itemElement) {
    const item = inventoryState[key];
    const adjustment = item.adjustment;

    if (item.current >= adjustment) {
      item.current -= adjustment;
      updateStockUI(key, itemElement);
      showMessageBox(`Manually deducted ${adjustment} ${item.unit} of ${key}.`, true);
    } else if (item.current > 0) {
      const remaining = item.current;
      item.current = 0;
      updateStockUI(key, itemElement);
      
      const unitLower = item.unit ? item.unit.toLowerCase() : "";
      const isLitre = unitLower.includes("litre") || unitLower.includes("liter");
      const remainingDisplay = isLitre ? remaining.toFixed(1) : Math.round(remaining);
      
      showMessageBox(`Manually deducted remaining ${remainingDisplay} ${item.unit} of ${key}.`, true);
    } else {
      showMessageBox(`${key} is already at zero stock!`);
    }
  }

  /**
   * Adds stock for an item.
   */
  function handleManualAddition(key, itemElement) {
    const item = inventoryState[key];
    const adjustment = item.adjustment;

    if (item.current + adjustment <= item.max) {
      item.current += adjustment;
      updateStockUI(key, itemElement);
      showMessageBox(`Manually added ${adjustment} ${item.unit} of ${key}.`, true);
    } else if (item.current < item.max) {
      const added = item.max - item.current;
      item.current = item.max;
      updateStockUI(key, itemElement);
      
      // Fixed message formatting for liters
      const unitLower = item.unit ? item.unit.toLowerCase() : "";
      const isLitre = unitLower.includes("litre") || unitLower.includes("liter");
      const addedDisplay = isLitre ? added.toFixed(1) : Math.round(added);
      
      showMessageBox(`Manually added ${addedDisplay} ${item.unit} of ${key} (Filled to max).`, true);
    } else {
      showMessageBox(`${key} is already at maximum stock!`);
    }
  }

  // --- Initialization ---
  document.querySelectorAll(".item").forEach(itemElement => {
    const key = itemElement.getAttribute('data-key');
    // **CRITICAL FIX**: Use parseFloat() to correctly read decimal values (LITRES)
    const max = parseFloat(itemElement.getAttribute('data-max'));
    const initial = parseFloat(itemElement.getAttribute('data-initial'));
    const unit = itemElement.getAttribute('data-unit');
    const adjustment = parseFloat(itemElement.getAttribute('data-manual-adjustment')) || 1;

    inventoryState[key] = {
      current: initial,
      max: max,
      unit: unit,
      adjustment: adjustment
    };

    updateStockUI(key, itemElement);

    const addButton = itemElement.querySelector(".add-btn");
    const deductButton = itemElement.querySelector(".deduct-btn");

    addButton.addEventListener("click", () => handleManualAddition(key, itemElement));
    deductButton.addEventListener("click", () => handleManualDeduction(key, itemElement));
  });

});