// Global helper functions
function createRipple(button, event = null) {
  const ripple = document.createElement("span");
  const rect = button.getBoundingClientRect();

  let x, y;
  if (event) {
    x = event.clientX - rect.left;
    y = event.clientY - rect.top;
  } else {
    x = rect.width / 2;
    y = rect.height / 2;
  }

  ripple.style.left = `${x}px`;
  ripple.style.top = `${y}px`;
  ripple.classList.add("ripple-effect");

  // Remove existing ripples
  const existingRipples = button.querySelectorAll(".ripple-effect");
  existingRipples.forEach((r) => r.remove());

  button.appendChild(ripple);

  setTimeout(() => {
    if (ripple.parentNode === button) {
      ripple.remove();
    }
  }, 600);
}

function updateThemeButtonText(button, theme) {
  if (theme === "light") {
    button.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px">
      <path d="M480-120q-150 0-255-105T120-480q0-150 105-255t255-105q14 0 27.5 1t26.5 3q-41 29-65.5 75.5T444-660q0 90 63 153t153 63q55 0 101-24.5t75-65.5q2 13 3 26.5t1 27.5q0 150-105 255T480-120Z"/>
    </svg>Dark Mode`;
  } else {
    button.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px">
      <path d="M480-360q50 0 85-35t35-85q0-50-35-85t-85-35q-50 0-85 35t-35 85q0 50 35 85t85 35Zm0 80q-83 0-141.5-58.5T280-480q0-83 58.5-141.5T480-680q83 0 141.5 58.5T680-480q0 83-58.5 141.5T480-280ZM200-440H40v-80h160v80Zm720 0H760v-80h160v80ZM440-760v-160h80v160h-80Zm0 720v-160h80v160h-80ZM256-650l-101-97 57-59 96 100-52 56Zm492 496-97-101 53-55 101 97-57 59Zm-98-550 97-101 59 57-100 96-56-52ZM154-212l101-97 55 53-97 101-59-57Zm326-268Z"/>
    </svg>Light Mode`;
  }
}

function closeAllSubMenus() {
  const sidebar = document.getElementById("sidebar");
  if (!sidebar) return;

  Array.from(sidebar.getElementsByClassName("show")).forEach((ul) => {
    ul.classList.remove("show");
    if (ul.previousElementSibling) {
      ul.previousElementSibling.classList.remove("rotate");
    }
  });
}

function toggleSubMenu(button) {
  const subMenu = button.nextElementSibling;
  if (!subMenu) return;

  if (!subMenu.classList.contains("show")) {
    closeAllSubMenus();
  }
  subMenu.classList.toggle("show");
  button.classList.toggle("rotate");

  const sidebar = document.getElementById("sidebar");
  if (sidebar.classList.contains("close")) {
    sidebar.classList.toggle("close");
    const toggleButton = document.getElementById("toggle-btn");
    if (toggleButton) {
      toggleButton.classList.toggle("rotate");
    }
  }
}

// Main sidebar functionality
document.addEventListener("DOMContentLoaded", function () {
  const toggleButton = document.getElementById("toggle-btn");
  const sidebar = document.getElementById("sidebar");
  const mobileMenuBtn = document.querySelector(".mobile-menu-btn");

  // Initialize sidebar state
  function initializeSidebar() {
    if (window.innerWidth <= 768) {
      // On small screens start hidden (off-canvas)
      sidebar.classList.add("hidden-mobile");
      sidebar.classList.remove("show-mobile");
      sidebar.classList.remove("close");
      if (mobileMenuBtn) mobileMenuBtn.style.display = "block";
    } else {
      // On desktop start collapsed
      sidebar.classList.add("close");
      sidebar.classList.remove("hidden-mobile");
      if (mobileMenuBtn) mobileMenuBtn.style.display = "none";
    }
  }

  // Toggle sidebar function
  function toggleSidebar() {
    if (window.innerWidth <= 768) {
      const isShown = sidebar.classList.toggle("show-mobile");
      if (isShown) {
        sidebar.classList.remove("hidden-mobile");
      } else {
        sidebar.classList.add("hidden-mobile");
      }
    } else {
      sidebar.classList.toggle("close");
      if (toggleButton) toggleButton.classList.toggle("rotate");
    }
    closeAllSubMenus();
  }

  // Set up toggle button
  if (toggleButton) {
    toggleButton.addEventListener("click", function (e) {
      e.stopPropagation();
      toggleSidebar();
      createRipple(this, e);
    });
  }

  // Set up mobile menu button
  if (mobileMenuBtn) {
    mobileMenuBtn.addEventListener("click", function (e) {
      e.stopPropagation();
      sidebar.classList.toggle("show-mobile");
      createRipple(this, e);
    });
  }

  // Desktop hover behavior
  if (sidebar && window.innerWidth > 768) {
    sidebar.addEventListener("mouseenter", () => {
      if (window.innerWidth > 768) {
        sidebar.classList.remove("close");
        toggleButton.classList.remove("rotate");
      }
    });

    sidebar.addEventListener("mouseleave", () => {
      if (window.innerWidth > 768) {
        sidebar.classList.add("close");
        closeAllSubMenus();
      }
    });
  }

  // Close mobile menu when clicking outside
  document.addEventListener("click", function (event) {
    if (
      window.innerWidth <= 768 &&
      sidebar &&
      mobileMenuBtn &&
      !sidebar.contains(event.target) &&
      !mobileMenuBtn.contains(event.target) &&
      sidebar.classList.contains("show-mobile")
    ) {
      sidebar.classList.remove("show-mobile");
    }
  });

  // Close mobile menu when clicking a link
  document.querySelectorAll("#sidebar a").forEach((link) => {
    link.addEventListener("click", function () {
      if (window.innerWidth <= 768) {
        sidebar.classList.remove("show-mobile");
      }
    });
  });

  // Handle window resize
  function handleResize() {
    if (window.innerWidth <= 768) {
      if (mobileMenuBtn) mobileMenuBtn.style.display = "block";
      if (!sidebar.classList.contains("show-mobile")) {
        sidebar.classList.add("hidden-mobile");
        sidebar.classList.remove("close");
      }
    } else {
      if (mobileMenuBtn) mobileMenuBtn.style.display = "none";
      sidebar.classList.remove("show-mobile");
      sidebar.classList.remove("hidden-mobile");
      // Keep sidebar closed on desktop by default
      if (!sidebar.classList.contains("close")) {
        sidebar.classList.add("close");
      }
    }
  }

  // Initial setup
  initializeSidebar();
  handleResize();

  // Listen for resize events
  window.addEventListener("resize", handleResize);

  // Add ripple effect to all buttons
  document.addEventListener("click", function (e) {
    if (e.target.tagName === "BUTTON" || e.target.closest("button")) {
      const button =
        e.target.tagName === "BUTTON" ? e.target : e.target.closest("button");
      if (button && !button.classList.contains("modal-close")) {
        createRipple(button, e);
      }
    }
  });

  // Prevent body scroll when mobile menu is open
  const observer = new MutationObserver(function (mutations) {
    mutations.forEach(function (mutation) {
      if (mutation.attributeName === "class") {
        if (sidebar.classList.contains("show-mobile")) {
          document.body.style.overflow = "hidden";
        } else {
          document.body.style.overflow = "";
        }
      }
    });
  });

  if (sidebar) {
    observer.observe(sidebar, { attributes: true });
  }

  // Close all submenus when clicking outside on desktop
  document.addEventListener("click", function (event) {
    if (window.innerWidth > 768 && !sidebar.contains(event.target)) {
      closeAllSubMenus();
    }
  });
});
