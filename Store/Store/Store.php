<?php
session_start();

// Handle logout
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");
    header("Location: ../../Home Full/Home.php");
    exit;
}

require_once __DIR__ . '/../../DB.php';
// Serve the existing HTML (DB connection included for future server-side rendering)
?>

<link rel="stylesheet" href="Store.css" />
    <link rel="stylesheet" href="rating-styles.css" />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
      rel="stylesheet"
    />
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet" />
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="data.js"></script>
    <script src="Store.js" defer></script>
    <title>Power</title>


    <main id="Main-Content">
  <div class="container">
    <!-- Blobs stay as background layer -->
    <div class="blobs">
      <div class="blob-dodge"><div class="blob a"></div></div>
      <div class="blob-dodge"><div class="blob b"></div></div>
      <div class="blob-dodge"><div class="blob c"></div></div>
    </div>

    <!-- Search and Filter Section -->
    <div class="search-filter-container">
      <div class="search-box">
        <input type="text" id="search-input" placeholder="Search products...">
        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor">
          <path d="M784-120 532-372q-30 24-69 37.5T378-321q-110.62 0-187.81-77.19Q113-475.38 113-586q0-110.62 77.19-187.81Q267.38-851 378-851q110.62 0 187.81 77.19Q643-696.62 643-586q0 40-13.5 79T592-438l252 252-60 60ZM378-391q77.6 0 132.3-54.7 54.7-54.7 54.7-132.3 0-77.6-54.7-132.3Q455.6-765 378-765q-77.6 0-132.3 54.7-54.7 54.7-54.7 132.3 0 77.6 54.7 132.3Q300.4-391 378-391Z"/>
        </svg>
      </div>
      <div class="filter-box">
        <select id="category-filter">
          <option value="">All Categories</option>
          <option value="Supplements">Supplements</option>
          <option value="Nutrition">Nutrition</option>
          <option value="Wellness">Wellness</option>
          <option value="Accessories">Accessories</option>
          <option value="Gear">Gear</option>
          <option value="Cardio">Cardio</option>
          <option value="Training">Training</option>
          <option value="Apparel">Apparel</option>
        </select>
      </div>
      <div class="filter-box">
        <select id="sort-select">
          <option value="default">Sort By</option>
          <option value="price-low">Price: Low to High</option>
          <option value="price-high">Price: High to Low</option>
          <option value="rating">Top Rated</option>
          <option value="newest">Newest</option>
        </select>
      </div>
    </div>

    <!-- Product grid sits above the blobs and is centered -->
    <div id="product-container" class="grid-store"></div>
    <!-- Pagination sits above the blobs -->
    <div class="pagination">
      <button id="prev">Prev</button>
      <div id="page-numbers"></div>
      <button id="next">Next</button>
    </div>
  </div>

  <?php include 'Nav.php'; ?>
  <!-- Product Detail Modal -->
  <div id="product-modal" class="modal">
    <div class="modal-overlay"></div>
    <div class="modal-content">
      <button class="modal-close">&times;</button>
      <div class="modal-body">
        <div class="modal-image">
          <img id="modal-img" src="" alt="">
        </div>
        <div class="modal-details">
          <h2 id="modal-name"></h2>
          <div id="modal-rating" class="modal-rating"></div>
          <p id="modal-desc" class="modal-desc"></p>
          <div class="modal-info">
            <div class="info-row">
              <span>Category:</span>
              <span id="modal-category"></span>
            </div>
            <div class="info-row">
              <span>Price:</span>
              <span id="modal-price" class="modal-price"></span>
            </div>
          </div>
          <button class="modal-add-to-cart" id="modal-add-btn">Add to Cart</button>
          <h3>Related Products</h3>
          <div id="related-products" class="related-products"></div>
        </div>
      </div>
    </div>
  </div>
</main>