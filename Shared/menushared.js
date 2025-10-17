// This ensures all code executes only after the HTML is fully loaded.
document.addEventListener("DOMContentLoaded", () => {
  // Burger menu for shortcut to specific option of  food
  //  START
  const menuToggle = document.getElementById('menu-toggle');
  const dropdownMenu = document.getElementById('dropdown');

  if (menuToggle && dropdownMenu) {
    menuToggle.addEventListener('click', function(e) {
      e.stopPropagation();
      dropdownMenu.style.display = dropdownMenu.style.display === 'flex' ? 'none' : 'flex';
    });

    // Close dropdown when clicking elsewhere
    document.addEventListener('click', function(e) {
      if (!dropdownMenu.contains(e.target) && e.target !== menuToggle) {
        dropdownMenu.style.display = 'none';
      }
    });

    // Prevent closing when clicking inside dropdown
    dropdownMenu.addEventListener('click', function(e) {
      e.stopPropagation();
    });

    // Close dropdown when clicking on a link
    dropdownMenu.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', () => {
        dropdownMenu.style.display = 'none';
      });
    });
  }
  // END

  // Fade animations in left navbar
  // START
  const icons = document.querySelectorAll(".icon, .icons");
  icons.forEach((icon, index) => {
    setTimeout(() => icon.classList.add("fade-in-icon"), 100 + index * 100);
  });

  const options = document.querySelectorAll(".Option");
  options.forEach((option, index) => {
    setTimeout(() => option.classList.add("fade-in-Menu"), 300 + index * 100);
    //END

    //This function will read the data attribute
    option.addEventListener("click", function () {
      const itemName = this.getAttribute("data-name");
      const itemPrice = this.getAttribute("data-price");
      const itemImage = this.getAttribute("data-image");

      // Detect category from body class (Coffee / Meals / Pastries)
      const category = document.body.classList.contains("coffee-page")
        ? "coffee"
        : document.body.classList.contains("meals-page")
        ? "meals"
        : "pastries";

      openOrderPanel(itemName, itemPrice, itemImage, category);
    });
  });

  // Order panel elements
  orderPanel = document.getElementById("orderPanel"); //To show the order panel when ordering
  overlay = document.getElementById("overlay");
  closeOrderBtn = document.getElementById("closeOrder");  //Closing order
  orderItemsContainer = document.getElementById("orderItemsContainer");
  emptyOrderMessage = document.getElementById("emptyOrderMessage"); //This will display if no orders
  orderTotalEl = document.getElementById("orderTotal"); //For order total
  confirmBtn = document.querySelector(".confirm-btn");  //For confirm function
  // Condition for order Panel
  if (closeOrderBtn) closeOrderBtn.addEventListener("click", closeOrderPanel);
  if (overlay) overlay.addEventListener("click", closeOrderPanel);

  // ---------- NEW: Confirm handler sends to backend/save_order.php ----------
// In the confirm button event listener, replace with this:
// In the confirm button event listener, replace with this:
if (confirmBtn) {
  confirmBtn.addEventListener("click", async () => {
    if (orderItems.length > 0) {
      try {
        // First, get menu_ids for each item by name
        const menuIds = {};
        for (const item of orderItems) {
          try {
            const menuRes = await fetch(`../backend/menu_crud.php?action=read&name=${encodeURIComponent(item.name)}`);
            const menuData = await menuRes.json();
            if (menuData.success && menuData.data.length > 0) {
              menuIds[item.name] = menuData.data[0].id;
              console.log(`✅ Found menu_id for ${item.name}: ${menuData.data[0].id}`);
            } else {
              console.warn(`❌ Menu item not found: ${item.name}`);
            }
          } catch (menuErr) {
            console.error(`❌ Error fetching menu_id for ${item.name}:`, menuErr);
          }
        }

        // Now check availability of all items in the order
        const availabilityData = {
          menu_items: orderItems.map(item => ({
            menu_id: menuIds[item.name] || null,
            quantity: item.quantity
          })).filter(item => item.menu_id !== null) // Only check items with valid menu_id
        };

        if (availabilityData.menu_items.length === 0) {
          alert("❌ Unable to verify product availability. Please try again.");
          return;
        }

        console.log('Checking availability:', JSON.stringify(availabilityData, null, 2));

        const availRes = await fetch("../backend/inventory_crud.php?action=checkAvailability", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: new URLSearchParams({ menu_items: JSON.stringify(availabilityData.menu_items) })
        });

        const availData = await availRes.json();
        console.log("Availability response:", availData);

        // Check for unavailable products
        if (availData.product_unavailable) {
          const unavailableProducts = availData.unavailable_products.join(', ');
          alert(`❌ Product is not available: ${unavailableProducts}`);
          return;
        }

        // Check for insufficient ingredients
        if (availData.ingredients_unavailable) {
          const insufficientIngredients = availData.insufficient_ingredients.join(', ');
          alert(`❌ Ingredients are not available: ${insufficientIngredients}`);
          return;
        }

        // If all checks pass, proceed with order
        // Prepare order data
        const orderData = {
          orders: orderItems.map(item => {
            const orderItem = {
              name: item.name || 'Unknown',
              quantity: item.quantity || 1,
              basePrice: item.basePrice || 0,
              category: item.category || 'unknown',
              size: item.size || '',
              sugarLevel: item.sugarLevel || '',
              addons: item.addons || [],
              extras: item.extras || []
            };

            if (typeof item.getTotal === 'function') {
              orderItem.getTotal = item.getTotal();
            } else {
              orderItem.getTotal = (item.basePrice || 0) * (item.quantity || 1);
            }

            return orderItem;
          }),
          total: orderTotalEl.textContent || '0.00'
        };

        console.log('Sending order data:', JSON.stringify(orderData, null, 2));

        const res = await fetch("../backend/save_order.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(orderData)
        });

        const data = await res.json();
        console.log("Server response:", data);

        if (data.status === "success") {
          alert("✅ Order saved successfully!");
          localStorage.removeItem("currentCart");
          orderItems = [];
          renderOrderItems();
          updateOrderTotal();
          closeOrderPanel();

          // FIXED: Properly redirect with order_id
          const orderId = data.order_id;
          if (orderId) {
            console.log("Redirecting to payment with order_id:", orderId);
            window.location.href = `../Payment/Payments.php?order_id=${orderId}`;
          } else {
            console.error("No order_id received from server");
            window.location.href = "../Payment/Payments.php";
          }
        } else {
          alert("⚠️ Error saving order: " + data.message);
        }
      } catch (err) {
        console.error("Fetch error:", err);
        alert("⚠️ Unable to connect to server. Check console for details.");
      }
    } else {
      alert("Please add items to your order first.");
    }
  });
}

  // ✅ Load cart on page load
  loadCart();
});

// ----------------- CART LOGIC -----------------
let orderPanel,
  overlay,
  closeOrderBtn,
  orderItemsContainer,
  emptyOrderMessage,
  orderTotalEl,
  confirmBtn;

let orderItems = [];
// FIX: Use a more robust ID generation to prevent conflicts
let orderCounter = localStorage.getItem('orderCounter') ? parseInt(localStorage.getItem('orderCounter')) : 0;

function openOrderPanel(itemName, itemPrice, itemImage, category) {
  addOrderItem(itemName, itemPrice, itemImage, category);

  orderPanel.classList.add("active");
  overlay.classList.add("active");
  document.body.style.overflow = "hidden";

  if (orderItems.length > 0) {
    emptyOrderMessage.style.display = "none";
  }
}

function closeOrderPanel() {
  orderPanel.classList.remove("active");
  overlay.classList.remove("active");
  document.body.style.overflow = "auto";
}

function addOrderItem(name, basePrice, image, category) {
  // FIX: Generate a more unique ID using timestamp and counter
  const id = `item-${Date.now()}-${orderCounter++}`;
  // Save the updated counter to localStorage
  localStorage.setItem('orderCounter', orderCounter.toString());
  
  const item = {
    id,
    name,
    basePrice: parseInt(basePrice),
    image,
    quantity: 1,
    size: "Sapat",
    sizePrice: 0,
    sugarLevel: "Normal", // Add sugar level property
    addons: [],
    addonsPrice: 0,
    extras: [],
    extrasPrice: 0,
    category,
    getTotal() {
      let total = this.basePrice * this.quantity;
      if (this.category === "coffee") {
        total = (this.basePrice + this.sizePrice + this.addonsPrice) * this.quantity;
      } else if (this.category === "meals") {
        total = (this.basePrice + this.extrasPrice) * this.quantity;
      }
      return total;
    },
  };

  orderItems.push(item);
  renderOrderItems();
  updateOrderTotal();
  saveCart(); // ✅ save
}

// Add sugar level update function
function updateSugarLevel(id, newSugarLevel) {
  // FIX: Use findIndex to ensure we're updating the correct item
  const itemIndex = orderItems.findIndex((item) => item.id === id);
  if (itemIndex !== -1) {
    const item = orderItems[itemIndex];
    item.sugarLevel = newSugarLevel;
    updateOrderTotal();
    updateItemTotal(id);
    saveCart(); // ✅ save
  }
}
function removeOrderItem(id) {
  // FIX: Make sure we're removing the correct item by using strict comparison
  orderItems = orderItems.filter((item) => item.id !== id);
  renderOrderItems();
  updateOrderTotal();
  if (orderItems.length === 0) emptyOrderMessage.style.display = "block";
  saveCart(); // ✅ save
}

function updateQuantity(id, change) {
  // FIX: Use findIndex to ensure we're updating the correct item
  const itemIndex = orderItems.findIndex((item) => item.id === id);
  if (itemIndex !== -1) {
    const item = orderItems[itemIndex];
    const newQuantity = item.quantity + change;
    if (newQuantity >= 1 && newQuantity <= 20) {
      item.quantity = newQuantity;
      updateOrderTotal();
      updateItemTotal(id);
      document.getElementById(`quantity-${id}`).textContent = newQuantity;
      saveCart(); // ✅ save
    }
  }
}

function updateSize(id, newSize) {
  // FIX: Use findIndex to ensure we're updating the correct item
  const itemIndex = orderItems.findIndex((item) => item.id === id);
  if (itemIndex !== -1) {
    const item = orderItems[itemIndex];
    item.size = newSize;
    item.sizePrice =
      newSize === "Sobra" ? 30 : newSize === "Sakto" ? 15 : 0;
    updateOrderTotal();
    updateItemTotal(id);
    saveCart(); // ✅ save
  }
}

function toggleAddon(itemId, addonName, addonPrice, isChecked) {
  // FIX: Use findIndex to ensure we're updating the correct item
  const itemIndex = orderItems.findIndex((item) => item.id === itemId);
  if (itemIndex !== -1) {
    const item = orderItems[itemIndex];
    if (isChecked) {
      item.addons.push({ name: addonName, price: addonPrice });
    } else {
      item.addons = item.addons.filter((addon) => addon.name !== addonName);
    }
    item.addonsPrice = item.addons.reduce((sum, a) => sum + a.price, 0);
    updateOrderTotal();
    updateItemTotal(itemId);
    saveCart(); // ✅ save
  }
}

function toggleExtra(itemId, extraName, extraPrice, isChecked) {
  // FIX: Use findIndex to ensure we're updating the correct item
  const itemIndex = orderItems.findIndex((item) => item.id === itemId);
  if (itemIndex !== -1) {
    const item = orderItems[itemIndex];
    if (isChecked) {
      item.extras.push({ name: extraName, price: extraPrice });
    } else {
      item.extras = item.extras.filter((extra) => extra.name !== extraName);
    }
    item.extrasPrice = item.extras.reduce((sum, e) => sum + e.price, 0);
    updateOrderTotal();
    updateItemTotal(itemId);
    saveCart(); // ✅ save
  }
}

function updateItemTotal(id) {
  // FIX: Use findIndex to ensure we're updating the correct item
  const itemIndex = orderItems.findIndex((item) => item.id === id);
  if (itemIndex !== -1) {
    const item = orderItems[itemIndex];
    const el = document.getElementById(`total-${id}`);
    if (el) el.textContent = `TOTAL = ₱${item.getTotal().toFixed(2)}`;
  }
}

function updateOrderTotal() {
  const total = orderItems.reduce((sum, item) => sum + item.getTotal(), 0);
  orderTotalEl.textContent = total.toFixed(2);
}

function toggleAddonsDropdown(itemId) {
  const content = document.getElementById(`addons-content-${itemId}`);
  if (content) {
    content.classList.toggle('active');
  }
}

function toggleExtrasDropdown(itemId) {
  const content = document.getElementById(`extras-content-${itemId}`);
  if (content) {
    content.classList.toggle('active');
  }
}

function renderOrderItems() {
  if (!orderItemsContainer) return;
  orderItemsContainer.innerHTML = "";
  if (orderItems.length === 0) {
    if (emptyOrderMessage) emptyOrderMessage.style.display = "block";
    return;
  }

  if (emptyOrderMessage) emptyOrderMessage.style.display = "none";

  orderItems.forEach((item) => {
    const orderItemEl = document.createElement("div");
    orderItemEl.className = "order-item";
    orderItemEl.dataset.itemId = item.id;

    let extrasSection = "";
    if (item.category === "coffee") {
      extrasSection = `
        <div class="dropdowns">
          <select onchange="updateSize('${item.id}', this.value)">
            <option value="Sapat" ${item.size === "Sapat" ? "selected" : ""}>Sapat</option>
            <option value="Sakto" ${item.size === "Sakto" ? "selected" : ""}>Sakto (+₱15)</option>
            <option value="Sobra" ${item.size === "Sobra" ? "selected" : ""}>Sobra (+₱30)</option>
          </select>
        </div>
        <div class="dropdowns">
          <select onchange="updateSugarLevel('${item.id}', this.value)">
            <option value="Normal" ${item.sugarLevel === "Normal" ? "selected" : ""}>Normal</option>
            <option value="25%" ${item.sugarLevel === "25%" ? "selected" : ""}>25%</option>
            <option value="50%" ${item.sugarLevel === "50%" ? "selected" : ""}>50%</option>
            <option value="75%" ${item.sugarLevel === "75%" ? "selected" : ""}>75%</option>
            <option value="No Sugar" ${item.sugarLevel === "No Sugar" ? "selected" : ""}>No Sugar</option>
          </select>
        </div>
        <div class="addons-section">
          <div class="addons-toggle" onclick="toggleAddonsDropdown('${item.id}')">Add-ons <span>▼</span></div>
          <div class="addons-content" id="addons-content-${item.id}">
            <label><input type="checkbox" onchange="toggleAddon('${item.id}', 'Syrup', 10, this.checked)" ${item.addons.some(a => a.name === 'Syrup') ? 'checked' : ''}> Syrup (+₱10)</label>
            <label><input type="checkbox" onchange="toggleAddon('${item.id}', 'Milk', 15, this.checked)" ${item.addons.some(a => a.name === 'Milk') ? 'checked' : ''}> Milk (+₱15)</label>
            <label><input type="checkbox" onchange="toggleAddon('${item.id}', 'Espresso', 20, this.checked)" ${item.addons.some(a => a.name === 'Espresso') ? 'checked' : ''}> Extra Espresso (+₱20)</label>
          </div>
        </div>
      `;
    } else if (item.category === "meals") {
      // Check if it's a pizza item by name
      const pizzaItems = ["Hawaiian", "Arat Signature", "Garden Veggie", "Sausage Pizza"];
      const isPizza = pizzaItems.includes(item.name);

      if (!isPizza) {
        // Only show extras for non-pizza meals
        extrasSection = `
          <div class="extras-section">
            <div class="extras-toggle" onclick="toggleExtrasDropdown('${item.id}')">Extras <span>▼</span></div>
            <div class="extras-content" id="extras-content-${item.id}">
              <label><input type="checkbox" onchange="toggleExtra('${item.id}', 'White Rice', 15, this.checked)" ${item.extras.some(e => e.name === 'White Rice') ? 'checked' : ''}> White Rice (+₱15)</label>
              <label><input type="checkbox" onchange="toggleExtra('${item.id}', 'Fried Egg', 10, this.checked)" ${item.extras.some(e => e.name === 'Fried Egg') ? 'checked' : ''}> Fried Egg (+₱10)</label>
              <label><input type="checkbox" onchange="toggleExtra('${item.id}', 'Cheese Sauce', 20, this.checked)" ${item.extras.some(e => e.name === 'Cheese Sauce') ? 'checked' : ''}> Cheese Sauce (+₱20)</label>
            </div>
          </div>
        `;
      }
      // If it's a pizza, extrasSection remains empty
    }
    // For pastries, extrasSection remains empty

    orderItemEl.innerHTML = `
      <img src="${item.image}" class="order-item-img">
      <div class="order-details">
        <h3>${item.name}</h3>
        ${extrasSection}
        <div class="price-info" id="price-info-${item.id}"></div>
        <div class="item-total" id="total-${item.id}">TOTAL = ₱${item.getTotal().toFixed(2)}</div>
        <div class="quantity-controls">
          <button onclick="updateQuantity('${item.id}', -1)">-</button>
          <span id="quantity-${item.id}">${item.quantity}</span>
          <button onclick="updateQuantity('${item.id}', 1)">+</button>
        </div>
        <button class="remove-btn" onclick="removeOrderItem('${item.id}')">x</button>
      </div>
    `;

    orderItemsContainer.appendChild(orderItemEl);
  });
}


// Save cart to localStorage
function saveCart() {
  // Keep saving currentCart to localStorage as a fallback / recovery
  localStorage.setItem("currentCart", JSON.stringify(orderItems));
}

// Load cart from localStorage
function loadCart() {
  //  BACKEND DEV: Replace this with PHP fetch in the future if you want server-side cart persistence
  const saved = localStorage.getItem("currentCart");
  if (saved) {
    orderItems = JSON.parse(saved);

    // restore methods like getTotal() and ensure sugarLevel exists ONLY for coffee
    orderItems = orderItems.map(item => ({
      ...item,
      sugarLevel: item.category === "coffee" ? (item.sugarLevel || "Normal") : "",
      getTotal() {
        let total = this.basePrice * this.quantity;
        if (this.category === "coffee") {
          total = (this.basePrice + this.sizePrice + this.addonsPrice) * this.quantity;
        } else if (this.category === "meals") {
          total = (this.basePrice + this.extrasPrice) * this.quantity;
        }
        return total;
      }
    }));

    renderOrderItems();
    updateOrderTotal();
  }
}

// Add global cart saving when switching categories
// This will automatically save when user navigates away
window.addEventListener('beforeunload', function() {
  saveCart();
});

// Also save when visibility changes (tab switch, etc.)
document.addEventListener('visibilitychange', function() {
  if (document.hidden) {
    saveCart();
  }
});
