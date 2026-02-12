<?php
require_once __DIR__ . '/../../DB.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");
    header("Location: ../../Home Full/Home.php");
    exit;
}

 include "Nav.php";
?>

<link rel="stylesheet" href="Cart.css" />
<script src="Cart.js" defer></script>

<div class="cart-wrapper">
      <div class="blobs">
        <div class="blob-dodge"><div class="blob a"></div></div>
        <div class="blob-dodge"><div class="blob b"></div></div>
        <div class="blob-dodge"><div class="blob c"></div></div>
      </div>

      <div class="cart-card">
        <div class="cart-pane">
          <div
            style="
              display: flex;
              justify-content: space-between;
              align-items: center;
              margin-bottom: 18px;
            "
          >
            <h2 style="margin: 0; color: #e3e3ff">Shopping Cart</h2>
            <div id="item-count" style="color: var(--muted)"></div>
          </div>

          <div id="cart-items"></div>

          <div class="back-to-shop">
            <a href="../../Store/Store/Store.php" style="color: #d9caff">← Back to shop</a>
          </div>
        </div>

        <div class="summary-pane">
          <h3 style="margin-top: 0; color: #e3e3ff">Summary</h3>
          <div
            style="
              margin: 18px 0;
              border-top: 1px solid rgba(255, 255, 255, 0.04);
              padding-top: 18px;
            "
          >
            <div
              style="
                display: flex;
                justify-content: space-between;
                margin-bottom: 8px;
              "
            >
              <div>Items</div>
              <div id="summary-items">0</div>
            </div>
            <div
              style="
                display: flex;
                justify-content: space-between;
                margin-bottom: 8px;
              "
            >
              <div>Subtotal</div>
              <div id="summary-sub">$0.00</div>
            </div>
            <div
              style="
                display: flex;
                justify-content: space-between;
                margin-bottom: 8px;
              "
            >
              <div>Shipping</div>
              <div id="summary-ship">$5.00</div>
            </div>
            <div
              style="
                display: flex;
                justify-content: space-between;
                margin-top: 14px;
                font-weight: 700;
              "
            >
              <div>Total</div>
              <div id="summary-total">$0.00</div>
            </div>
          </div>
          <button
            id="checkout"
            class="card-btn"
            style="width: 140%; margin-top: 16px"
            onclick="handleCheckoutClick(event)"
          >
            CHECKOUT
          </button>
        </div>
      </div>
    </div>
