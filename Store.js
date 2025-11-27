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
  const allProducts = storeProducts;
  let filteredProducts = [...allProducts];
  let cart = JSON.parse(localStorage.getItem("cart")) || [];
  let sortBy = "default";
  const perPage = 8;
  let currentPage = 1;

  function updateCartCount() {
    $("#cart-count").text(cart.length);
  }

  function saveCart() {
    localStorage.setItem("cart", JSON.stringify(cart));
    updateCartCount();
  }

  function getFilteredProducts() {
    let result = [...filteredProducts];

    // Apply sorting
    if (sortBy === "price-low") {
      result.sort((a, b) => parseInt(a.price) - parseInt(b.price));
    } else if (sortBy === "price-high") {
      result.sort((a, b) => parseInt(b.price) - parseInt(a.price));
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
        $(this).css("animation", "slideDown 0.4s ease-in forwards");
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
                    <div class="stars">${renderStars(p.rating)}</div>
                    <span class="review-count">(${p.reviews})</span>
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
        if (product) {
          cart.push(product);
          saveCart();
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
        }
      });

      // (Wishlist removed) favorite button handler was removed.

      // Animate cards in with staggered fade & slide
      $(".grid-store .card").each(function (index) {
        const card = $(this);
        const content = card.find(".card-content");
        setTimeout(() => {
          content.animate({ opacity: 1 }, 800);
          card.css(
            "animation",
            `slideUp 0.8s cubic-bezier(0.34, 1.56, 0.64, 1) ${
              index * 0.1
            }s both`
          );
        }, 30);
      });

      renderPageNumbers();
    }, 400);
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
  $("#modal-add-btn").on("click", function () {
    const productId = $(this).data("product-id");
    const product = allProducts.find((p) => p.id === productId);
    if (product) {
      cart.push(product);
      saveCart();
      const $button = $(this);
      $button.text("Added!").prop("disabled", true).addClass("added-animation");
      setTimeout(() => {
        $button
          .text(getButtonText(product.category))
          .prop("disabled", false)
          .removeClass("added-animation");
      }, 1500);
    }
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
