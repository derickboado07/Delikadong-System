window.onload = function () {
  // Show current date & time
  const now = new Date();
  const paymentDateEl = document.getElementById("paymentDate");
  if (paymentDateEl) {
    paymentDateEl.innerText = now.toLocaleString();
  }

  // Get order data from hidden fields
  const orderIds = JSON.parse(document.getElementById('orderIds').value);
  const orderTotal = parseFloat(document.getElementById('orderTotal').value);
  
  let numericTotal = orderTotal || 0;
  let finalTotal = numericTotal;
  let discountValue = 0;
  let discountType = 'none';

  // Set base totals
  const totalPayEl = document.getElementById("totalPay");
  const finalPayEl = document.getElementById("finalPay");
  const finalPayCashEl = document.getElementById("finalPayCash");

  if (totalPayEl) totalPayEl.innerText = "₱" + numericTotal.toFixed(2);
  if (finalPayEl) finalPayEl.innerText = "₱" + numericTotal.toFixed(2);
  if (finalPayCashEl) finalPayCashEl.innerText = "₱" + numericTotal.toFixed(2);

  const discountInfoEl = document.getElementById("discountInfo");

  // --- Discount Handling ---
  const discountCheckboxes = document.querySelectorAll('input[name="discount"]');
  discountCheckboxes.forEach(checkbox => {
    checkbox.addEventListener("change", () => {
      discountType = 'none';
      discountValue = 0;

      // Priority: Senior > Student
      if (document.querySelector('input[value="senior"]')?.checked) {
        discountType = 'senior';
        discountValue = numericTotal * 0.20;
      } else if (document.querySelector('input[value="student"]')?.checked) {
        discountType = 'student';
        discountValue = numericTotal * 0.10;
      }

      // Calculate final total
      finalTotal = numericTotal - discountValue;

      // Update UI
      if (discountInfoEl) {
        discountInfoEl.innerText = discountType === 'none' ? 'None' : 
          (discountType === 'senior' ? 'Senior Citizen 20%' : 'Student 10%');
      }
      
      if (finalPayEl) finalPayEl.innerText = "₱" + finalTotal.toFixed(2);
      if (finalPayCashEl) finalPayCashEl.innerText = "₱" + finalTotal.toFixed(2);

      const discountedSpan = document.getElementById("discountedAmount");
      if (discountedSpan) discountedSpan.innerText = "₱" + discountValue.toFixed(2);
    });
  });

  // --- Cash Payment Section ---
  const cashInput = document.getElementById("cashInput");
  const enterBtn = document.getElementById("enterCashBtn");
  const confirmBtn = document.getElementById("confirmPaymentBtn");
  const discountedSpan = document.getElementById("discountedAmount");
  const changeSpan = document.getElementById("changeAmount");
  const listOrdersLink = document.getElementById("listOrdersLink");

  if (cashInput) {
    // Prevent non-digits
    cashInput.addEventListener("input", (e) => {
      const cleaned = e.target.value.replace(/\D+/g, "");
      if (e.target.value !== cleaned) {
        e.target.value = cleaned;
      }
    });

    cashInput.addEventListener("keydown", (e) => {
      if (e.key === "Enter") {
        e.preventDefault();
        if (enterBtn) enterBtn.click();
      }
    });
  }

  if (enterBtn) {
    enterBtn.addEventListener("click", () => {
      const raw = cashInput ? cashInput.value.trim() : "";
      const cash = raw === "" ? NaN : parseInt(raw, 10);

      if (isNaN(cash) || cash < 0) {
        alert("Please enter a valid whole-number cash amount (e.g. 250).");
        if (cashInput) cashInput.value = "";
        if (discountedSpan) discountedSpan.innerText = "₱0.00";
        if (changeSpan) changeSpan.innerText = "₱0.00";
        confirmBtn.disabled = true;
        return;
      }

      // Show discount amount
      if (discountedSpan) discountedSpan.innerText = "₱" + discountValue.toFixed(2);

      // Compute and show change
      if (cash < finalTotal) {
        if (changeSpan) changeSpan.innerText = "Not enough cash";
        confirmBtn.disabled = true;
      } else {
        const change = cash - finalTotal;
        if (changeSpan) changeSpan.innerText = "₱" + change.toFixed(2);
        confirmBtn.disabled = false;
      }
    });
  }

  // --- Confirm Payment Button (Cash) ---
  if (confirmBtn) {
    confirmBtn.addEventListener("click", async () => {
        const raw = cashInput ? cashInput.value.trim() : "";
        const cash = raw === "" ? NaN : parseInt(raw, 10);
        
        if (isNaN(cash) || cash < finalTotal) {
            alert("Please enter valid cash amount first.");
            return;
        }

        try {
            const paymentData = {
                order_ids: orderIds,
                payment_method: 'cash',
                discount_type: discountType,
                discount_amount: discountValue,
                final_total: finalTotal,
                cash_amount: cash,
                change_amount: cash - finalTotal
            };

            console.log("Sending payment data:", paymentData);

            const response = await fetch('../backend/process_payment.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(paymentData)
            });

            const result = await response.json();
            console.log("Payment response:", result);

            if (result.status === 'success') {
              alert('Payment processed successfully! Redirecting to order list.');
              confirmBtn.style.display = 'none';
              if (listOrdersLink) listOrdersLink.style.display = 'block';
              
              // Add auto-redirect after 2 seconds
              setTimeout(() => {
                  window.location.href = "../List-Orders/Orderlist.php";
              }, 2000);
          } else {
                alert('Error processing payment: ' + (result.message || 'Unknown error'));
                if (result.debug) {
                    console.error('Debug info:', result.debug);
                }
            }
        } catch (error) {
            console.error('Payment error:', error);
            alert('Network error processing payment. Check console for details.');
        }
    });
  }

  // --- GCash Payment Section ---
const successfulPaymentLink = document.getElementById("successfulPaymentLink");
const referenceInput = document.getElementById("referenceNumber");

if (successfulPaymentLink) {
  successfulPaymentLink.addEventListener("click", async function(e) {
      e.preventDefault();

      // Validate reference number
      const referenceNumber = referenceInput ? referenceInput.value.trim() : '';
      
      if (!referenceNumber) {
          alert("Please enter the GCash reference number before confirming payment.");
          if (referenceInput) referenceInput.focus();
          return;
      }

      if (referenceNumber.length < 5) {
          alert("Please enter a valid GCash reference number (at least 5 characters).");
          if (referenceInput) referenceInput.focus();
          return;
      }

      try {
          // FIXED: Use single order_id instead of order_ids array
          const paymentData = {
              order_id: orderIds[0], // Use the first order ID from array
              payment_method: 'gcash',
              discount_type: discountType,
              discount_amount: discountValue,
              final_total: finalTotal,
              cash_amount: finalTotal,
              change_amount: 0,
              reference_number: referenceNumber
          };

          console.log("Sending GCash payment data:", paymentData);

          const response = await fetch('../backend/process_payment.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify(paymentData)
          });

          const result = await response.json();
          console.log("GCash payment response:", result);

          if (result.status === 'success') {
              alert('✅ GCash payment processed successfully!\nReference: ' + referenceNumber + '\nRedirecting to order list.');
              window.location.href = "../List-Orders/Orderlist.php";
          } else {
              alert('❌ Error processing payment: ' + (result.message || 'Unknown error'));
              if (result.debug) {
                  console.error('Debug info:', result.debug);
              }
          }
      } catch (error) {
          console.error('GCash payment error:', error);
          alert('⚠️ Network error processing payment. Check console for details.');
      }
  });
}

  // NEW: Auto-format reference number input
  if (referenceInput) {
    referenceInput.addEventListener('input', function(e) {
      // Convert to uppercase and remove special characters
      this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
    });

    // Allow Enter key to submit
    referenceInput.addEventListener('keydown', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        if (successfulPaymentLink) successfulPaymentLink.click();
      }
    });
  }
};