// Blob interaction
const blobs = document.querySelectorAll(".blob-dodge");

document.addEventListener("mousemove", (e) => {
  blobs.forEach((blob) => {
    const rect = blob.getBoundingClientRect();
    const cx = rect.left + rect.width / 2;
    const cy = rect.top + rect.height / 2;

    const dx = cx - e.clientX;
    const dy = cy - e.clientY;
    const dist = Math.sqrt(dx * dx + dy * dy);

    const repelRadius = 250;
    const repelPower = 3.0;
    const attractPower = 0.2;

    if (dist < repelRadius) {
      const force = (repelRadius - dist) / repelRadius;
      blob.style.setProperty("--dx", `${dx * force * repelPower}px`);
      blob.style.setProperty("--dy", `${dy * force * repelPower}px`);
    } else {
      const ax = -dx * attractPower;
      const ay = -dy * attractPower;
      blob.style.setProperty("--dx", `${ax}px`);
      blob.style.setProperty("--dy", `${ay}px`);
    }
  });
});

// Cart functionality
document.addEventListener("DOMContentLoaded", () => {
  // Determine API base path - Cart is in Cart/Cart/ folder, so go up two levels to reach api/
  const apiBase = '../../api';
  
  function parsePrice(str) {
    if (!str) return 0;
    return Number(String(str).replace(/[^0-9.-]+/g, "")) || 0;
  }

  // Render cart either from server (if available) or from localStorage
  function renderCart() {
    const itemsRoot = document.getElementById("cart-items");
    itemsRoot.innerHTML = "";

    // Fetch cart from server using member_id (from session)
    fetch(`${apiBase}/cart/get.php`)
        .then(r => r.json())
        .then(j => {
          // Check if user is not logged in
          if (j && j.logged_in === false) {
            itemsRoot.innerHTML = `
              <div class="empty-cart">
                <p style="font-size: 1.1em; margin-bottom: 1rem;">You must create an account and log in to view your cart.</p>
                <p style="margin-bottom: 1rem;">
                  <a href="../../Login/Loginsignup.php" style="color: var(--accent-secondary); text-decoration: none; font-weight: 600;">Create Account / Login →</a>
                </p>
                <p><a href="../../Store/Store/Store.php" style="color: var(--muted); text-decoration: none;">Continue shopping →</a></p>
              </div>
            `;
            document.getElementById("item-count").textContent = "0 items";
            document.getElementById("summary-items").textContent = "0";
            document.getElementById("summary-sub").textContent = "$0.00";
            document.getElementById("summary-ship").textContent = "$0.00";
            document.getElementById("summary-total").textContent = "$0.00";
            
            // Update cart count badge
            const cartCountEl = document.getElementById("cart-count");
            if (cartCountEl) cartCountEl.textContent = "0";
            
            // Hide checkout button for non-logged-in users
            const checkoutBtn = document.getElementById("checkout");
            if (checkoutBtn) checkoutBtn.style.display = "none";
            return;
          }
          
          // Check for memberships in localStorage
          const localCart = JSON.parse(localStorage.getItem("cart") || "[]");
          const memberships = localCart.filter(item => item.type === 'membership');
          
          // Check if cart is truly empty (no products and no memberships)
          if ((!j || !Array.isArray(j.items) || j.items.length === 0) && memberships.length === 0) {
            itemsRoot.innerHTML = `
              <div class="empty-cart">
                <p>Your cart is empty.</p>
                <p><a href="../../Store/Store/Store.php" style="color: var(--accent-secondary); text-decoration: none;">Continue shopping →</a></p>
              </div>
            `;
            // Use order totals even if empty (should be 0)
            const orderSubtotal = j ? (j.order_subtotal || 0) : 0;
            const orderItemCount = j ? (j.order_item_count || 0) : 0;
            
            document.getElementById("item-count").textContent = "0 items";
            document.getElementById("summary-items").textContent = "0";
            document.getElementById("summary-sub").textContent = "$0.00";
            document.getElementById("summary-ship").textContent = "$0.00";
            document.getElementById("summary-total").textContent = "$0.00";
            
            // Update cart count badge using same API as store page
            updateCartCount();
            
            // Show checkout button (user is logged in, just empty cart)
            const checkoutBtn = document.getElementById("checkout");
            if (checkoutBtn) checkoutBtn.style.display = "block";
            return;
          }
          
          // Show checkout button for logged-in users with items
          const checkoutBtn = document.getElementById("checkout");
          if (checkoutBtn) checkoutBtn.style.display = "block";
          
          // Get totals from order table (corder) - these are the source of truth
          let orderSubtotal = j ? (j.order_subtotal || 0) : 0;
          let orderItemCount = j ? (j.order_item_count || 0) : 0;
          
          // Render cart items from database (if any)
          if (j && Array.isArray(j.items)) {
            j.items.forEach((it) => {
            // Use subtotal from database (already calculated) or calculate if missing
            const itemSubtotal = it.subtotal || (it.unit_price * it.quantity);

            const el = document.createElement("div");
            el.className = "cart-item";
            el.innerHTML = `
              <img src="${it.product.img || '../../Store/Store/Images/product1.png'}" alt="${it.product.name || 'Product'}" onerror="this.src='../../Store/Store/Images/product1.png'" />
              <div class="meta">
                <h4>${it.product.name || 'Product'}</h4>
                <p style="color: var(--muted); font-size: 0.9em;">$${it.unit_price.toFixed(2)} each</p>
              </div>
              <div class="qty">
                <button data-cart-item-id="${it.cart_item_id}" data-product-id="${it.product.id}" data-qty="${it.quantity}" data-unit-price="${it.unit_price}" data-op="dec">−</button>
                <div style="min-width:32px; text-align:center; font-weight: 600;">${it.quantity}</div>
                <button data-cart-item-id="${it.cart_item_id}" data-product-id="${it.product.id}" data-qty="${it.quantity}" data-unit-price="${it.unit_price}" data-op="inc">+</button>
              </div>
              <div style="width:100px; text-align:right;">
                <div class="price">$${itemSubtotal.toFixed(2)}</div>
                <div><button data-cart-item-id="${it.cart_item_id}" data-product-id="${it.product.id}" data-qty="${it.quantity}" data-op="rm" class="remove-btn">Remove</button></div>
              </div>
            `;

              itemsRoot.appendChild(el);
            });
          }
          
          // Render memberships from localStorage
          memberships.forEach((membership) => {
            const price = parseFloat(String(membership.price).replace(/[^0-9.]/g, "")) || 0;
            orderSubtotal += price;
            orderItemCount += 1;
            
            const el = document.createElement("div");
            el.className = "cart-item";
            el.innerHTML = `
              <img src="${membership.img || '../../Store/Store/Images/product1.png'}" alt="${membership.name}" onerror="this.src='../../Store/Store/Images/product1.png'" />
              <div class="meta">
                <h4>${membership.name}</h4>
                <p style="color: var(--muted); font-size: 0.9em;">Membership</p>
              </div>
              <div class="qty">
                <div style="min-width:32px; text-align:center; font-weight: 600; color: var(--muted);">1</div>
              </div>
              <div style="width:100px; text-align:right;">
                <div class="price">${membership.price}</div>
                <div><button data-membership-id="${membership.id}" data-op="rm" class="remove-btn">Remove</button></div>
              </div>
            `;
            
            itemsRoot.appendChild(el);
          });
          
          // Attach remove listeners for memberships
          itemsRoot.querySelectorAll("button[data-membership-id]").forEach((btn) => {
            btn.addEventListener("click", (e) => {
              const membershipId = btn.dataset.membershipId;
              const localCart = JSON.parse(localStorage.getItem("cart") || "[]");
              const updatedCart = localCart.filter(item => item.id !== membershipId);
              localStorage.setItem("cart", JSON.stringify(updatedCart));
              renderCart(); // Re-render to update display
              updateCartCount();
            });
          });

          // Use totals from order table + memberships
          document.getElementById("item-count").textContent =
            orderItemCount + (orderItemCount === 1 ? " item" : " items");
          document.getElementById("summary-items").textContent = orderItemCount;
          document.getElementById("summary-sub").textContent = "$" + orderSubtotal.toFixed(2);
          const ship = orderItemCount > 0 ? 5 : 0;
          document.getElementById("summary-ship").textContent = "$" + ship.toFixed(2);
          document.getElementById("summary-total").textContent = "$" + (orderSubtotal + ship).toFixed(2);
          
          // Update cart count badge using the same API as store page
          updateCartCount();

          // Attach listeners
          itemsRoot.querySelectorAll("button[data-op]").forEach((btn) => {
            btn.addEventListener("click", (e) => {
              const op = btn.dataset.op;
              const cartItemId = Number(btn.dataset.cartItemId);
              const productId = Number(btn.dataset.productId);
              const qty = Number(btn.dataset.qty);
              const unitPrice = parseFloat(btn.dataset.unitPrice) || 0;
              modifyCartServer(cartItemId, productId, qty, op, unitPrice);
            });
          });
        })
        .catch((err) => {
          console.error('Failed to fetch cart from server', err);
          const itemsRoot = document.getElementById("cart-items");
          itemsRoot.innerHTML = `
            <div class="empty-cart">
              <p>Failed to load cart. Please refresh the page.</p>
              <p><a href="../../Store/Store/Store.php" style="color: var(--accent-secondary); text-decoration: none;">Continue shopping →</a></p>
            </div>
          `;
          document.getElementById("item-count").textContent = "0 items";
          document.getElementById("summary-items").textContent = "0";
          document.getElementById("summary-sub").textContent = "$0.00";
          document.getElementById("summary-ship").textContent = "$0.00";
          document.getElementById("summary-total").textContent = "$0.00";
          const cartCountEl = document.getElementById("cart-count");
          if (cartCountEl) cartCountEl.textContent = "0";
        });
  }

  // Checkout button
  const checkoutBtn = document.getElementById("checkout");
  if (checkoutBtn) {
    checkoutBtn.addEventListener("click", async () => {
      try {
        // Check for memberships in localStorage
        const localCart = JSON.parse(localStorage.getItem("cart") || "[]");
        const memberships = localCart.filter(item => item.type === 'membership');
        
        // Check if cart has products (from server)
        const cartResponse = await fetch(`${apiBase}/cart/get.php`);
        const cartData = await cartResponse.json();
        const hasProducts = cartData.items && cartData.items.length > 0;
        
        let orderResult = null;
        
        // Create order from cart (for products) - only if there are products
        if (hasProducts) {
          const orderResponse = await fetch(`${apiBase}/cart/create_order.php`, { 
            method: 'POST', 
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({})
          });
          
          orderResult = await orderResponse.json();
          
          if (!orderResult.success) {
            alert('Checkout failed: ' + (orderResult.error || 'Please try again.'));
            return;
          }
        }
        
        // If there are memberships, process them using process_purchase.php
        if (memberships.length > 0) {
          const purchaseResponse = await fetch(`${apiBase}/cart/process_purchase.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ items: memberships })
          });
          
          const purchaseResult = await purchaseResponse.json();
          
          if (purchaseResult.success) {
            // Clear memberships from localStorage
            const remainingCart = localCart.filter(item => item.type !== 'membership');
            localStorage.setItem("cart", JSON.stringify(remainingCart));
            
            // Update cart count
            updateCartCount();
            
            // Show success message
            let message = 'Thank you for your purchase! 🎉\n\n';
            if (orderResult && orderResult.order_id) {
              message += 'Order #' + orderResult.order_id + ' processed successfully.\n';
            }
            message += 'Your membership has been activated!';
            
            if (purchaseResult.needs_profile_completion) {
              message += '\n\nPlease complete your member profile to fully activate your membership.';
              alert(message);
              window.location.href = '../../UserProfile/userprofile.php';
            } else {
              alert(message);
              window.location.href = '../../Home Full/Home.php';
            }
          } else {
            // Membership processing failed
            if (orderResult && orderResult.success) {
              alert('Order created but membership activation failed: ' + (purchaseResult.error || 'Please contact support.'));
            } else {
              alert('Membership activation failed: ' + (purchaseResult.error || 'Please try again.'));
            }
          }
        } else if (hasProducts && orderResult) {
          // No memberships, just products
          alert('Thank you for your purchase! 🎉\n\nOrder #' + orderResult.order_id + ' processed successfully. Redirecting to shop...');
          window.location.href = '../../Store/Store/Store.php';
        } else {
          alert('Your cart is empty. Please add items before checkout.');
        }
      } catch (err) {
        console.error('Checkout error:', err);
        alert('Checkout failed. Please try again.');
      }
    });
  }

  // Server cart helpers
  function modifyCartServer(cartItemId, productId, qty, op, unitPrice) {
    // No need to store cart_id in localStorage, server handles it via session

    if (op === 'inc') {
      // Add a single quantity using add_item endpoint - same as store page
      fetch(`${apiBase}/cart/add_item.php`, { 
        method: 'POST', 
        headers: { 'Content-Type': 'application/json' }, 
        body: JSON.stringify({ 
          product_id: productId, 
          quantity: 1,
          unit_price: unitPrice
        }) 
      })
        .then(r => {
          if (r.status === 401) {
            return r.json().then(j => {
              alert(j.message || 'You must be logged in to modify your cart.');
              renderCart(); // Re-render to show login message
              throw new Error('login_required');
            });
          }
          return r.json();
        })
        .then(j => {
          if (j && (j.inserted || j.updated)) {
            renderCart();
            updateCartCount();
          } else if (j && j.error) {
            alert('Failed to update cart: ' + (j.message || j.error));
            renderCart();
          } else {
            renderCart();
            updateCartCount();
          }
        })
        .catch((err) => {
          if (err.message !== 'login_required') {
            console.error('Add to cart failed:', err);
            renderCart();
          }
        });
      return;
    }

    if (op === 'dec') {
      // decrease: set quantity = qty - 1
      const newQty = Math.max(0, qty - 1);
      fetch(`${apiBase}/cart/update_item.php`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ cart_item_id: cartItemId, quantity: newQty }) })
        .then(r => {
          if (r.status === 401) {
            return r.json().then(j => {
              alert(j.message || 'You must be logged in to modify your cart.');
              renderCart(); // Re-render to show login message
              throw new Error('login_required');
            });
          }
          return r.json();
        })
        .then(j => {
          if (j && (j.updated || j.deleted)) {
            renderCart();
            updateCartCount();
          } else if (j && j.error) {
            alert('Error: ' + j.error);
            renderCart();
          } else {
            renderCart();
            updateCartCount();
          }
        })
        .catch((err) => {
          if (err.message !== 'login_required') {
            console.error('Remove failed:', err);
            renderCart();
          }
        });
      return;
    }

    if (op === 'rm') {
      fetch(`${apiBase}/cart/remove_item.php`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ cart_item_id: cartItemId }) })
        .then(r => {
          if (r.status === 401) {
            return r.json().then(j => {
              alert(j.message || 'You must be logged in to modify your cart.');
              renderCart(); // Re-render to show login message
              throw new Error('login_required');
            });
          }
          return r.json();
        })
        .then(j => {
          if (j && j.deleted) {
            renderCart();
            updateCartCount();
          } else if (j && j.error) {
            alert('Error: ' + j.error);
            renderCart();
          } else {
            renderCart();
            updateCartCount();
          }
        })
        .catch((err) => {
          if (err.message !== 'login_required') {
            console.error('Remove failed:', err);
            renderCart();
          }
        });
      return;
    }
  }
  
  // Function to update cart count badge - uses same API as store page
  function updateCartCount() {
    fetch(`${apiBase}/cart/count.php`)
      .then(r => r.json())
      .then(data => {
        if (data && typeof data.count === 'number') {
          const cartCountEl = document.getElementById("cart-count");
          if (cartCountEl) {
            cartCountEl.textContent = data.count;
          }
        }
      })
      .catch(err => {
        console.warn('Failed to fetch cart count:', err);
        const cartCountEl = document.getElementById("cart-count");
        if (cartCountEl) {
          cartCountEl.textContent = "0";
        }
      });
  }

  // Initial render
  renderCart();
  
  // Update cart count on page load (uses same API as store page)
  updateCartCount();
  
  // Update cart count periodically (same as store page)
  setInterval(updateCartCount, 5000);
});
