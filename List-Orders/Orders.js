document.addEventListener('DOMContentLoaded', () => {
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
                    <small class="payment-method">${getPaymentMethodDisplay(order.payment_method)}</small>
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
    // Show size and sugar info when available
    if (item.size) {
        optionsHTML += `<div class="item-option"><strong>Size:</strong> ${item.size}</div>`;
    }
    if (item.sugar_level) {
        optionsHTML += `<div class="item-option"><strong>Sugar Level:</strong> ${item.sugar_level}</div>`;
    }

    // Always show add-ons if present (handle different category naming)
    if (item.addons && Array.isArray(item.addons) && item.addons.length > 0) {
        optionsHTML += `
            <div class="item-option">
                <strong>Add-ons:</strong> ${formatAddonsExtras(item.addons)}
            </div>
        `;
    }

    // Always show extras if present (be lenient about shape)
    if (item.extras && Array.isArray(item.extras) && item.extras.length > 0) {
        // Consider any non-empty object or string as valid
        const validExtras = item.extras.filter(extra => {
            if (!extra) return false;
            if (typeof extra === 'string') return extra.trim().length > 0;
            if (typeof extra === 'object') return Object.keys(extra).length > 0;
            return false;
        });
        if (validExtras.length > 0) {
            optionsHTML += `
                <div class="item-option">
                    <strong>Extras:</strong> ${formatAddonsExtras(validExtras)}
                </div>
            `;
        }
    }

    return optionsHTML;
}

function formatAddonsExtras(data) {
    if (!data || !Array.isArray(data) || data.length === 0) {
        return 'None';
    }
    
    try {
        const nameKeys = ['name','extraName','extra_name','label','title','description','option'];
        const priceKeys = ['price','extraPrice','extra_price','cost','amount','value'];

        const parts = data.map(item => {
            if (!item) return null;
            if (typeof item === 'string') return item.trim() || null;

            // item is an object - try to extract name and price
            let name = null;
            for (const k of nameKeys) {
                if (k in item && item[k]) { name = String(item[k]).trim(); break; }
            }

            let price = null;
            for (const k of priceKeys) {
                if (k in item && item[k] !== null && item[k] !== undefined && item[k] !== '') {
                    const p = parseFloat(item[k]);
                    if (!isNaN(p)) { price = p; break; }
                }
            }

            if (name && price !== null) return `${name} (+₱${price.toFixed(2)})`;
            if (name) return name;
            if (price !== null) return `(+₱${price.toFixed(2)})`;

            // Last resort: stringify object briefly
            try { return Object.values(item).filter(v => v !== null && v !== undefined && String(v).trim() !== '').join(' / '); } catch(e) { return null; }
        }).filter(Boolean);

        if (parts.length === 0) return 'None';
        return parts.join(', ');
    } catch (e) {
        console.error('Error formatting addons/extras:', e, data);
        return 'None';
    }
}

function getPaymentMethodDisplay(paymentMethod) {
    if (!paymentMethod) return 'Cash';

    const lowerMethod = paymentMethod.toLowerCase();

    // Handle GCash with reference number (e.g., "GCash (TEST123)")
    if (lowerMethod.startsWith('gcash')) {
        return 'GCash';
    }

    // Handle QRPH
    if (lowerMethod === 'qrph') {
        return 'QRPH';
    }

    // Handle Cash
    if (lowerMethod === 'cash') {
        return 'Cash';
    }

    // Default: capitalize first letter
    return paymentMethod.charAt(0).toUpperCase() + paymentMethod.slice(1);
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
                alert("Order completed! Data will now appear in Sales Dashboard.");

                // Trigger real-time update for Sales Dashboard
                localStorage.setItem('dashboardRefresh', Date.now().toString());

                // Remove the completed order from the list
                const orderCard = document.querySelector(`[data-id="${orderId}"]`).closest('.order-card');
                if (orderCard) {
                    orderCard.remove();
                }

                // Check if no orders left
                const container = document.getElementById('ordersContainer');
                const remainingCards = container.querySelectorAll('.order-card');
                if (remainingCards.length === 0) {
                    container.innerHTML = '<div class="no-orders">No pending orders to complete.</div>';
                }
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
