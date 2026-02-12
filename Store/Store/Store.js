const navbar = document.getElementById("navbar");
const toggle = document.getElementById("switch");
const html = document.documentElement;
const savedTheme = localStorage.getItem("theme");
AOS.init();
function OpenSideBar() {
  navbar.classList.add("show");
}
function CloseSideBar() {
  navbar.classList.remove("show");
}

if (savedTheme) {
  html.setAttribute("data-theme", savedTheme);
  toggle.checked = savedTheme === "dark";
} else {
  html.setAttribute("data-theme", "dark");
  toggle.checked = true;
}

toggle.addEventListener("change", () => {
  if (toggle.checked) {
    html.setAttribute("data-theme", "dark");
    localStorage.setItem("theme", "dark");
  } else {
    html.setAttribute("data-theme", "light");
    localStorage.setItem("theme", "light");
  }
});
const blobs = document.querySelectorAll(".blob-dodge");

document.addEventListener("mousemove", (e) => {
  blobs.forEach((blob) => {
    const rect = blob.getBoundingClientRect();
    const cx = rect.left + rect.width / 2;
    const cy = rect.top + rect.height / 2;

    const dx = cx - e.clientX;
    const dy = cy - e.clientY;
    const dist = Math.sqrt(dx * dx + dy * dy);

    const repelRadius = 250; // distance for repulsion
    const repelPower = 3.0; // strength of repulsion
    const attractPower = 0.2; // increased — follows closer, same speed

    // -----------------------------
    // REPULSION (same)
    // -----------------------------
    if (dist < repelRadius) {
      const force = (repelRadius - dist) / repelRadius;

      blob.style.setProperty("--dx", `${dx * force * repelPower}px`);
      blob.style.setProperty("--dy", `${dy * force * repelPower}px`);
    }

    // -----------------------------
    // ATTRACTION (closer follow)
    // -----------------------------
    else {
      const ax = -dx * attractPower;
      const ay = -dy * attractPower;

      blob.style.setProperty("--dx", `${ax}px`);
      blob.style.setProperty("--dy", `${ay}px`);
    }
  });
});
// Gufram-style tiny movement on hover
document.querySelectorAll(".product-card").forEach((card) => {
  let hover = false;

  card.addEventListener("mousemove", (e) => {
    if (!hover) return;

    const rect = card.getBoundingClientRect();
    const x = e.clientX - rect.left - rect.width / 2;
    const y = e.clientY - rect.top - rect.height / 2;

    card.style.transform =
      `translate(${x * 0.03}px, ${y * 0.03}px) ` + "scale(1.04) rotate(0.5deg)";
  });

  card.addEventListener("mouseenter", () => {
    hover = true;
  });

  card.addEventListener("mouseleave", () => {
    hover = false;
    card.style.transform = "";
  });
});

$(document).ready(function () {
  let allProducts = [];
  let filteredProducts = [];
  // Cart is now server-side only, no localStorage
  
  // Determine API base path - Store is in Store/Store/ folder, so go up two levels to reach api/
  const apiBase = '../../api';

  // Try to fetch products from backend API first, fallback to local `storeProducts`
  fetch(`${apiBase}/store/get_products.php`)
    .then((resp) => {
      if (!resp.ok) throw new Error("Network response not ok");
      return resp.json();
    })
    .then((data) => {
      if (Array.isArray(data) && data.length > 0) {
        allProducts = data;
      } else {
        allProducts = storeProducts;
      }
    })
    .catch((err) => {
      console.warn("Fetch products failed, falling back to local data", err);
      allProducts = storeProducts;
    })
    .finally(() => {
      filteredProducts = [...allProducts];
      // Update cart count from server
      updateCartCount();
      renderProducts();
    });

  // Ensure initial render uses backend data when available
  let sortBy = "default";
  const perPage = 8;
  let currentPage = 1;

  function updateCartCount() {
    // Fetch cart count from server
    fetch(`${apiBase}/cart/count.php`)
      .then(r => r.json())
      .then(data => {
        if (data && typeof data.count === 'number') {
          $("#cart-count").text(data.count);
        } else {
          $("#cart-count").text("0");
        }
      })
      .catch(err => {
        console.warn('Failed to fetch cart count from server:', err);
        $("#cart-count").text("0");
      });
  }

  function getFilteredProducts() {
    let result = [...filteredProducts];

    // Apply sorting
    if (sortBy === "price-low") {
      result.sort((a, b) => {
        const pa = parseFloat(String(a.price).replace(/[^0-9.]/g, "")) || 0;
        const pb = parseFloat(String(b.price).replace(/[^0-9.]/g, "")) || 0;
        return pa - pb;
      });
    } else if (sortBy === "price-high") {
      result.sort((a, b) => {
        const pa = parseFloat(String(a.price).replace(/[^0-9.]/g, "")) || 0;
        const pb = parseFloat(String(b.price).replace(/[^0-9.]/g, "")) || 0;
        return pb - pa;
      });
    } else if (sortBy === "rating") {
      result.sort((a, b) => b.rating - a.rating);
    } else if (sortBy === "newest") {
      result.sort((a, b) => b.is_new - a.is_new);
    }

    return result;
  }

  function updateTotalPages() {
    return Math.ceil(getFilteredProducts().length / perPage);
  }

  function getButtonText(category) {
    const buttonMap = {
      Supplements: "Add to Cart",
      Nutrition: "Add to Cart",
      Wellness: "Add to Cart",
      Accessories: "Add to Cart",
      Gear: "Add to Cart",
      Cardio: "Add to Cart",
      Training: "Add to Cart",
      Apparel: "Add to Cart",
    };
    return buttonMap[category] || "Add to Cart";
  }

  function renderStars(rating) {
    const fullStars = Math.floor(rating);
    const hasHalfStar = rating % 1 !== 0;
    let stars = "";
    for (let i = 0; i < fullStars; i++) {
      stars += '<span class="star filled">★</span>';
    }
    if (hasHalfStar) {
      stars += '<span class="star half">★</span>';
    }
    for (let i = fullStars + (hasHalfStar ? 1 : 0); i < 5; i++) {
      stars += '<span class="star">★</span>';
    }
    return stars;
  }

  function renderProducts() {
    const existingCards = $(".grid-store .card");
    if (existingCards.length > 0) {
      existingCards.each(function () {
        $(this).css("animation", "slideDown 0.3s ease-in forwards");
      });
    }

    setTimeout(() => {
      $(".grid-store").empty();

      let start = (currentPage - 1) * perPage;
      let end = start + perPage;
      let pageProducts = getFilteredProducts().slice(start, end);

      if (pageProducts.length === 0) {
        $(".grid-store").append(
          '<div class="no-results">No products found</div>'
        );
        renderPageNumbers();
        return;
      }

      pageProducts.forEach((p) => {
        const badgesHTML = `
          ${p.is_new ? '<div class="badge badge-new">NEW</div>' : ""}
          ${p.is_sale ? '<div class="badge badge-sale">SALE</div>' : ""}
        `;

        $(".grid-store").append(`
          <div class="card" data-product-id="${p.id}">
            <div class="card-badge">${p.category}</div>
            <div class="card-badges-top">${badgesHTML}</div>
            <div class="card-content" style="opacity: 0;">
              <div class="card-media">
                <img src="${p.img}" alt="${p.name}" style="cursor: pointer;">
              </div>
              <div class="card-body">
                <div class="card-main">
                  <h3 class="card-name" style="cursor: pointer;">${p.name}</h3>
                  <div class="card-rating">
                    <div class="rating-group">
                      <span class="rating-number">${p.rating}</span>
                      <div class="stars">${renderStars(p.rating)}</div>
                      <span class="review-count">(${p.reviews})</span>
                    </div>
                    <span class="price">${p.price}</span>
                  </div>
                  <p class="card-desc">${p.desc}</p>
                </div>
                
              </div>
            </div>
            <button class="card-cta card-btn add-to-cart" data-product-id="${
              p.id
            }">${getButtonText(p.category)}</button>
          </div>
        `);
      });

      // Add to cart click handler
      $(".add-to-cart").on("click", function (e) {
        e.preventDefault();
        const productId = $(this).data("product-id");
        const product = allProducts.find((p) => p.id === productId);
        if (!product) return;

        const unitPrice =
          parseFloat(String(product.price).replace(/[^0-9.]/g, "")) || 0;

        // Try to add to server cart first (best-effort). If it fails, fallback to local only.
        fetch(`${apiBase}/cart/add_item.php`, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            product_id: productId,
            quantity: 1,
            unit_price: unitPrice,
          }),
        })
        .then((r) => {
          if (r.status === 401) {
            // User not logged in
            return r.json().then(j => {
              const message = j.message || 'You must create an account and log in to add items to your cart.';
              if (confirm(message + '\n\nWould you like to go to the login page?')) {
                window.location.href = '../../Login/Loginsignup.php';
              }
              throw new Error('login_required');
            });
          }
          return r.json();
        })
        .then((j) => {
          if (j && (j.inserted || j.updated)) {
            // Update cart count from server
            updateCartCount();
            
            const $button = $(this);
            $button
              .text("Added!")
              .prop("disabled", true)
              .addClass("added-animation");
            setTimeout(() => {
              $button
                .text(getButtonText(product.category))
                .prop("disabled", false)
                .removeClass("added-animation");
            }, 1500);
          } else if (j && j.error) {
            if (j.error === 'login_required') {
              // Already handled in the 401 check above
              return;
            }
            alert('Failed to add item to cart: ' + (j.message || j.error));
          } else {
            alert('Failed to add item to cart. Please try again.');
          }
        })
        .catch((err) => {
          if (err.message !== 'login_required') {
            console.error("Add to cart failed", err);
            alert('Failed to add item to cart. Please try again.');
          }
        });
      });

      // (Wishlist removed) favorite button handler was removed.

      // Animate cards in with staggered fade & slide
      $(".grid-store .card").each(function (index) {
        const card = $(this);
        const content = card.find(".card-content");
        setTimeout(() => {
          content.animate({ opacity: 1 }, 600);
          card.css(
            "animation",
            `slideUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) ${
              index * 0.08
            }s both`
          );
        }, 20);
      });

      renderPageNumbers();
    }, 250);
  }

  function renderPageNumbers() {
    $("#page-numbers").empty();
    const totalPages = updateTotalPages();
    for (let i = 1; i <= totalPages; i++) {
      $("#page-numbers").append(
        `<button class="page-btn ${
          i == currentPage ? "active" : ""
        }">${i}</button>`
      );
    }

    $(".page-btn").on("click", function () {
      currentPage = Number($(this).text());
      renderProducts();
      window.scrollTo({ top: 0, behavior: "smooth" });
    });
  }

  // Search functionality
  $("#search-input").on("input", function () {
    const searchTerm = $(this).val().toLowerCase();
    const categoryFilter = $("#category-filter").val();

    filteredProducts = allProducts.filter((p) => {
      const matchesSearch =
        p.name.toLowerCase().includes(searchTerm) ||
        p.desc.toLowerCase().includes(searchTerm);
      const matchesCategory = !categoryFilter || p.category === categoryFilter;
      return matchesSearch && matchesCategory;
    });

    currentPage = 1;
    renderProducts();
  });

  // Category filter functionality
  $("#category-filter").on("change", function () {
    const categoryFilter = $(this).val();
    const searchTerm = $("#search-input").val().toLowerCase();

    filteredProducts = allProducts.filter((p) => {
      const matchesSearch =
        p.name.toLowerCase().includes(searchTerm) ||
        p.desc.toLowerCase().includes(searchTerm);
      const matchesCategory = !categoryFilter || p.category === categoryFilter;
      return matchesSearch && matchesCategory;
    });

    currentPage = 1;
    renderProducts();
  });

  // Sort functionality
  $("#sort-select").on("change", function () {
    sortBy = $(this).val();
    currentPage = 1;
    renderProducts();
  });

  $("#prev").on("click", function () {
    if (currentPage > 1) {
      currentPage--;
      renderProducts();
      window.scrollTo({ top: 0, behavior: "smooth" });
    }
  });

  $("#next").on("click", function () {
    const totalPages = updateTotalPages();
    if (currentPage < totalPages) {
      currentPage++;
      renderProducts();
      window.scrollTo({ top: 0, behavior: "smooth" });
    }
  });

  // Product Modal Functionality
  function showProductModal(productId) {
    const product = allProducts.find((p) => p.id === productId);
    if (!product) return;

    // Update modal content
    $("#modal-name").text(product.name);
    $("#modal-img").attr("src", product.img);
    $("#modal-desc").text(product.desc);
    $("#modal-category").text(product.category);
    $("#modal-price").text(product.price);
    $("#modal-rating").html(
      `<div class="stars">${renderStars(product.rating)}</div>` +
        `<span class="review-count">(${product.reviews} reviews)</span>`
    );

    // Get related products (same category, different product)
    const related = allProducts
      .filter((p) => p.category === product.category && p.id !== productId)
      .slice(0, 3);

    const relatedHTML = related
      .map(
        (p) => `
      <div class="related-item" data-product-id="${p.id}">
        <img src="${p.img}" alt="${p.name}">
        <p>${p.name}</p>
        <small>${p.price}</small>
      </div>
    `
      )
      .join("");

    $("#related-products").html(
      relatedHTML ||
        '<p style="grid-column: 1/-1; text-align: center;">No related products</p>'
    );

    // Set add to cart button
    $("#modal-add-btn")
      .data("product-id", productId)
      .text(getButtonText(product.category));

    // Show modal
    $("#product-modal").addClass("show");
    $("body").css("overflow", "hidden");
  }

  // Click on card to open modal (use image or use a view button)
  $(document).on("click", ".card-media, .card-name", function () {
    const productId = $(this).closest(".card").data("product-id");
    showProductModal(productId);
  });

  // Close modal
  $(".modal-close, .modal-overlay").on("click", function () {
    $("#product-modal").removeClass("show");
    $("body").css("overflow", "auto");
  });

  // Add to cart from modal
  $("#modal-add-btn").on("click", function (e) {
    e.preventDefault();
    const productId = $(this).data("product-id");
    const product = allProducts.find((p) => p.id === productId);
    if (!product) return;

    const unitPrice =
      parseFloat(String(product.price).replace(/[^0-9.]/g, "")) || 0;

    const $button = $(this);
    
    // Try to add to server cart first (best-effort). If it fails, fallback to local only.
    fetch(`${apiBase}/cart/add_item.php`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        product_id: productId,
        quantity: 1,
        unit_price: unitPrice,
      }),
    })
    .then((r) => {
      if (r.status === 401) {
        // User not logged in
        return r.json().then(j => {
          const message = j.message || 'You must create an account and log in to add items to your cart.';
          if (confirm(message + '\n\nWould you like to go to the login page?')) {
            window.location.href = '../../Login/Loginsignup.php';
          }
          throw new Error('login_required');
        });
      }
      return r.json();
    })
    .then((j) => {
      if (j && (j.inserted || j.updated)) {
        // Update cart count from server
        updateCartCount();
        
        $button
          .text("Added!")
          .prop("disabled", true)
          .addClass("added-animation");
        setTimeout(() => {
          $button
            .text(getButtonText(product.category))
            .prop("disabled", false)
            .removeClass("added-animation");
        }, 1500);
      } else if (j && j.error) {
        if (j.error === 'login_required') {
          // Already handled in the 401 check above
          return;
        }
        alert('Failed to add item to cart: ' + (j.message || j.error));
      } else {
        alert('Failed to add item to cart. Please try again.');
      }
    })
    .catch((err) => {
      if (err.message !== 'login_required') {
        console.error("Add to cart failed", err);
        alert('Failed to add item to cart. Please try again.');
      }
    });
  });

  // Open related products in modal
  $(document).on("click", ".related-item", function () {
    const productId = $(this).data("product-id");
    showProductModal(productId);
  });

  // Initialize
  updateCartCount();
  renderProducts();
});
