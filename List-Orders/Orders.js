document.addEventListener('DOMContentLoaded', () => {
  setTimeout(() => {
    const icons = document.querySelectorAll(".left-navbar .icon, .left-navbar .icons");
    icons.forEach((icon, index) => {
      setTimeout(() => {
        icon.style.opacity = "1";
        icon.style.animation = "fadeIn 0.5s ease-out forwards";
      }, 100 + index * 100);
    });
  }, 500);

  const container = document.getElementById('ordersContainer');
  container.innerHTML = '<div class="loading-message">Loading orders...</div>';

  fetch('../backend/load_orders.php')
    .then(res => {
      if (!res.ok) {
        throw new Error('Network response was not ok');
      }
      return res.json();
    })
    .then(data => {
      console.log('Orders data received:', data);
      
      if (data.status === 'error') {
        container.innerHTML = '<div class="error-message">Error: ' + data.message + '</div>';
        return;
      }
      
      if (!data.orders || data.orders.length === 0) {
        container.innerHTML = '<div class="no-orders">No orders found.</div>';
        return;
      }

      renderOrders(data.orders, container);
    })
    .catch(err => {
      console.error('Error loading orders:', err);
      container.innerHTML = '<div class="error-message">Error loading orders: ' + err.message + '</div>';
    });
});

function renderOrders(orders, container) {
    console.log("Rendering orders...", orders);
    
    container.innerHTML = "";
    
    orders.forEach(order => {
        console.log("Creating card for order:", order);
        const card = document.createElement("div");
        card.className = "order-card";

        // Calculate item count
        const itemCount = order.items ? order.items.length : 0;
        
        card.innerHTML = `
            <div class="order-header">
                <div>
                    <strong>Order #${order.id}</strong><br>
                    <small>${formatDate(order.created_at)}</small>
                </div>
                <div class="order-total">
                    <span>Total: ₱${parseFloat(order.total_amount || 0).toFixed(2)}</span><br>
                    <small class="payment-method">${order.payment_method || 'Cash'}</small>
                </div>
            </div>
            
            <div class="order-items">
                ${order.items && order.items.length > 0 ? order.items.map(item => `
                    <div class="order-item">
                        <div class="item-details">
                            <div class="item-name-quantity">
                                <span class="item-quantity">${item.quantity}x</span>
                                <span class="item-name">${item.name}</span>
                            </div>
                            <div class="item-price">₱${parseFloat(item.total || 0).toFixed(2)}</div>
                            
                            ${renderItemOptions(item)}
                        </div>
                    </div>
                `).join('') : '<div class="no-items">No items in this order</div>'}
            </div>
            
            ${order.payment_status === 'paid' ? `
                <div class="order-footer">
                    <button class="done-btn" data-id="${order.id}">
                        ✅ Mark as Completed
                    </button>
                </div>
            ` : ''}
        `;

        container.appendChild(card);
    });

    // Add event listener for done buttons
    container.addEventListener('click', function(e) {
        if (e.target.classList.contains('done-btn')) {
            const orderId = e.target.getAttribute('data-id');
            markOrderDone(orderId);
        }
    });
}

function renderItemOptions(item) {
    const category = item.category?.toLowerCase() || '';
    let optionsHTML = '';
    
    // Espresso & Signature Drinks (coffee category)
    if (category === 'espresso' || category === 'signature' || category === 'coffee') {
        if (item.size) {
            optionsHTML += `<div class="item-option"><strong>Size:</strong> ${item.size}</div>`;
        }
        if (item.sugar_level) {
            optionsHTML += `<div class="item-option"><strong>Sugar Level:</strong> ${item.sugar_level}</div>`;
        }
        if (item.addons && Array.isArray(item.addons) && item.addons.length > 0) {
            optionsHTML += `
                <div class="item-option">
                    <strong>Add-ons:</strong> ${formatAddonsExtras(item.addons)}
                </div>
            `;
        }
    }
    
    // Meals - FIXED: Properly handle extras
    else if (category === 'meals' || category === 'meal') {
        console.log("Meal item extras:", item.extras); // Debug log
        
        if (item.extras && Array.isArray(item.extras) && item.extras.length > 0) {
            // Filter out empty extras
            const validExtras = item.extras.filter(extra => 
                extra && (typeof extra === 'string' || extra.name || extra.price)
            );
            
            if (validExtras.length > 0) {
                optionsHTML += `
                    <div class="item-option">
                        <strong>Extras:</strong> ${formatAddonsExtras(validExtras)}
                    </div>
                `;
            }
        }
    }
    
    return optionsHTML;
}

function formatAddonsExtras(data) {
    if (!data || !Array.isArray(data) || data.length === 0) {
        return 'None';
    }
    
    try {
        return data.map(item => {
            if (!item) return 'Unknown';
            
            if (typeof item === 'string') {
                return item.trim() || 'Unknown';
            }
            
            if (item.name && item.price) {
                return `${item.name} (+₱${parseFloat(item.price).toFixed(2)})`;
            }
            
            if (item.name) {
                return item.name;
            }
            
            return 'Unknown';
        }).filter(item => item !== 'Unknown').join(', ');
    } catch (e) {
        console.error('Error formatting addons/extras:', e, data);
        return 'None';
    }
}

function formatDate(dateString) {
    try {
        const date = new Date(dateString);
        return date.toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch (e) {
        return dateString || 'Unknown date';
    }
}

function markOrderDone(orderId) {
    if (confirm(`Mark order #${orderId} as completed?`)) {
        fetch('../backend/delete_order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                order_id: orderId
            })
        })
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success') {
                alert("Order completed!");
                location.reload();
            } else {
                alert("Error: " + result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error completing order.');
        });
    }
}