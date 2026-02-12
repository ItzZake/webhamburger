
<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://kit.fontawesome.com/a2d9d6a66a.js" crossorigin="anonymous"></script>
<link rel="stylesheet" href="Nav.css">
<script src="Nav.js" defer></script>

<button id="open-sidebar-button" onclick="OpenSideBar()">
        <svg xmlns="http://www.w3.org/2000/svg" height="40px" viewBox="0 -960 960 960" width="40px" fill="#c9c9c9"><path d="M165.13-254.62q-10.68 0-17.9-7.26-7.23-7.26-7.23-18t7.23-17.86q7.22-7.13 17.9-7.13h629.74q10.68 0 17.9 7.26 7.23 7.26 7.23 18t-7.23 17.87q-7.22 7.12-17.9 7.12H165.13Zm0-200.25q-10.68 0-17.9-7.27-7.23-7.26-7.23-17.99 0-10.74 7.23-17.87 7.22-7.13 17.9-7.13h629.74q10.68 0 17.9 7.27 7.23 7.26 7.23 17.99 0 10.74-7.23 17.87-7.22 7.13-17.9 7.13H165.13Zm0-200.26q-10.68 0-17.9-7.26-7.23-7.26-7.23-18t7.23-17.87q7.22-7.12 17.9-7.12h629.74q10.68 0 17.9 7.26 7.23 7.26 7.23 18t-7.23 17.86q-7.22 7.13-17.9 7.13H165.13Z"/></svg>
    </button>
    <nav id="navbar">
        <ul>
            <li><button id="close-sidebar-button" onclick="CloseSideBar()"><svg xmlns="http://www.w3.org/2000/svg" height="40px" viewBox="0 -960 960 960" width="40px" fill="#c9c9c9"><path d="m480-444.62-209.69 209.7q-7.23 7.23-17.5 7.42-10.27.19-17.89-7.42-7.61-7.62-7.61-17.7 0-10.07 7.61-17.69L444.62-480l-209.7-209.69q-7.23-7.23-7.42-17.5-.19-10.27 7.42-17.89 7.62-7.61 17.7-7.61 10.07 0 17.69 7.61L480-515.38l209.69-209.7q7.23-7.23 17.5-7.42 10.27-.19 17.89 7.42 7.61 7.62 7.61 17.7 0 10.07-7.61 17.69L515.38-480l209.7 209.69q7.23 7.23 7.42 17.5.19 10.27-7.42 17.89-7.62 7.61-17.7 7.61-10.07 0-17.69-7.61L480-444.62Z"/></svg></button></li>
            <li class="Home-li"><a href="../../Home Full/Home.php"><img id="logo" src="Images/dark-logo-no-text.png" alt=""></a> </li>
            <li><a href="Store.php">Store</a></li>
            <li><a href="../../About us/Aboutus.php">About</a></li>
            <li><a href="../../FAQ/Faq.php">FAQ</a></li>
            <li><a href="../../Contact us/contact.php">Contact Us</a></li>
           <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
            <!-- USER IS LOGGED IN -->
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <li><a href="../../Admin/admin.php">Admin Panel</a></li>
            <?php endif; ?>

            <?php if ($_SESSION['role'] === 'member'): ?>
                <li><a href="../../Workouts Full/workouts.php">Member Dashboard</a></li>
                <li><a href="../../UserProfile/userprofile.php">My Profile</a></li>
            <?php endif; ?>

            <?php if ($_SESSION['role'] === 'coach'): ?>
                <li><a href="../../Coaches/CoachDashboard.php">Coach Dashboard</a></li>
            <?php endif; ?>

            <?php if ($_SESSION['role'] === 'nutritionist'): ?>
                <li><a href="../../Nutritionists/nutritionistprofile.php">Nutritionist Dashboard</a></li>
            <?php endif; ?>

            <li><a href="Store.php?logout=1">Logout</a></li>

        <?php else: ?>

            <!-- USER IS NOT LOGGED IN -->
            <li><a href="../../Login/Loginsignup.php">Login</a></li>

        <?php endif; ?>
            <li>
                <label class="switch">
                    <input type="checkbox" id="switch" />
                    <span class="slider"></span>
                </label>
            </li>
            <li class="Cart"><a href="../../Cart/Cart/Cart.php"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M280-80q-33 0-56.5-23.5T200-160q0-33 23.5-56.5T280-240q33 0 56.5 23.5T360-160q0 33-23.5 56.5T280-80Zm400 0q-33 0-56.5-23.5T600-160q0-33 23.5-56.5T680-240q33 0 56.5 23.5T760-160q0 33-23.5 56.5T680-80ZM246-720l96 200h280l110-200H246Zm-38-80h590q23 0 35 20.5t1 41.5L692-482q-11 20-29.5 31T622-440H324l-44 80h480v80H280q-45 0-68-39.5t-2-78.5l54-98-144-304H40v-80h130l38 80Zm134 280h280-280Z"/></svg><span id="cart-count" class="cart-count">0</span></a></li>
        </ul>
    </nav>
    <div id="overlay" onclick="CloseSideBar()"></div>

    <script>
      (function () {
        // Utility to parse price like "$25" -> 25
        function parsePrice(str) {
          if (!str) return 0;
          return Number(String(str).replace(/[^0-9.-]+/g, "")) || 0;
        }

        // Cart is now server-side only, no localStorage rendering needed

        // Fetch cart count from server on page load
        function updateCartCount() {
          // Store is in Store/Store/ folder, so go up two levels to reach api/
          const apiBase = '../../api';
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
        
        // Update cart count on page load
        updateCartCount();
        
        // Update cart count periodically (every 5 seconds)
        setInterval(updateCartCount, 5000);
        
        // Cart is server-side only, no storage events needed
        // No need to render cart here - this is just the navigation with cart count
      })();
    </script>

    <script>
      (function () {
        // Blob Mouse Interaction
        const blobs = document.querySelectorAll(".blob-dodge");

        if (blobs && blobs.length) {
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
              const attractPower = 0.2; // closer follow

              // REPULSION
              if (dist < repelRadius) {
                const force = (repelRadius - dist) / repelRadius;

                blob.style.setProperty("--dx", `${dx * force * repelPower}px`);
                blob.style.setProperty("--dy", `${dy * force * repelPower}px`);
              }
              // ATTRACTION
              else {
                const ax = -dx * attractPower;
                const ay = -dy * attractPower;

                blob.style.setProperty("--dx", `${ax}px`);
                blob.style.setProperty("--dy", `${ay}px`);
              }
            });
          });
        }
      })();
    </script>

    <script>
      (function () {
        // Theme Toggle
        const themeSwitch = document.getElementById("switch");
        const html = document.documentElement;

        // Load saved theme preference
        const savedTheme = localStorage.getItem("theme") || "dark";
        html.setAttribute("data-theme", savedTheme);
        if (savedTheme === "dark" && themeSwitch) themeSwitch.checked = true;

        // Toggle theme
        if (themeSwitch) {
          themeSwitch.addEventListener("change", () => {
            const newTheme = themeSwitch.checked ? "dark" : "light";
            html.setAttribute("data-theme", newTheme);
            localStorage.setItem("theme", newTheme);
          });
        }

        // Navbar Sidebar Functions
        function OpenSideBar() {
          document.getElementById("navbar").classList.add("show");
          document.getElementById("overlay").classList.add("show");
        }

        function CloseSideBar() {
          document.getElementById("navbar").classList.remove("show");
          document.getElementById("overlay").classList.remove("show");
        }

        // Close sidebar when clicking on a nav link
        document.querySelectorAll("#navbar a").forEach((link) => {
          link.addEventListener("click", CloseSideBar);
        });

        // expose sidebar functions globally
        window.OpenSideBar = OpenSideBar;
        window.CloseSideBar = CloseSideBar;
      })();
    </script>
