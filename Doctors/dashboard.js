const body = document.body;

function initSidebar() {
  const toggleButton = document.getElementById("toggle-btn");
  const sidebar = document.getElementById("sidebar");

  if (!sidebar) return;

  // Set active state based on current page
  const currentPage = window.location.pathname;
  const sidebarLinks = sidebar.querySelectorAll('a[data-section]');
  
  sidebarLinks.forEach(link => {
    const href = link.getAttribute('href');
    const li = link.closest('li');
    
    // Check if this link matches the current page
    if (currentPage.includes(href) || 
        (currentPage.includes('MealPlans.php') && link.getAttribute('data-section') === 'meals') ||
        (currentPage.includes('workouts.php') && link.getAttribute('data-section') === 'workouts')) {
      // Remove active from all items
      sidebar.querySelectorAll('li').forEach(item => item.classList.remove('active'));
      // Add active to current item
      if (li) li.classList.add('active');
    }
    
    // Prevent default navigation and handle active state
    link.addEventListener('click', function(e) {
      // Only prevent default if it's a hash link or same-page navigation
      if (href === '#' || href.startsWith('#')) {
        e.preventDefault();
      }
      
      // Update active state
      sidebar.querySelectorAll('li').forEach(item => item.classList.remove('active'));
      if (li) li.classList.add('active');
    });
  });

  sidebar.addEventListener("mouseenter", () => {
    sidebar.classList.remove("close");
    body.classList.add("sidebar-open");
  });

  sidebar.addEventListener("mouseleave", () => {
    sidebar.classList.add("close");
    body.classList.remove("sidebar-open");
    if (toggleButton) toggleButton.classList.remove("rotate");

    // Close all open dropdowns on hover out
    Array.from(sidebar.getElementsByClassName("show")).forEach((ul) => {
      ul.classList.remove("show");
      if (ul.previousElementSibling) {
        ul.previousElementSibling.classList.remove("rotate");
      }
    });
  });
}

function updateLogo(theme) {
  const PATH_PREFIX = "../Doctors/";
  const logoImg = document.querySelector("#sidebar img");
  if (logoImg) {
    logoImg.src =
      PATH_PREFIX +
      (theme === "light"
        ? "../Store-20251210T213336Z-3-001/Store/Images/dark-logo-no-text.png"
        : "../Store-20251210T213336Z-3-001/Store/Images/fokin_logo_without_text.png");
  }
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

// Setup theme toggle - call this after sidebar loads
function setupThemeToggle() {
  const themeToggleBtn = document.getElementById("light-mode");
  if (!themeToggleBtn) {
    setTimeout(setupThemeToggle, 100);
    return;
  }
  
  // Check if already initialized to prevent duplicate listeners
  if (themeToggleBtn.dataset.themeInitialized === 'true') {
    return;
  }
  
  // Mark as initialized
  themeToggleBtn.dataset.themeInitialized = 'true';
  
  const savedTheme = localStorage.getItem("theme") || "dark";
  document.documentElement.setAttribute("data-theme", savedTheme);
  updateThemeButtonText(themeToggleBtn, savedTheme);
  updateLogo(savedTheme);

  themeToggleBtn.addEventListener("click", function (e) {
    e.preventDefault();
    e.stopPropagation();
    const currentTheme = document.documentElement.getAttribute("data-theme");
    const newTheme = currentTheme === "light" ? "dark" : "light";
    document.documentElement.setAttribute("data-theme", newTheme);
    localStorage.setItem("theme", newTheme);
    updateThemeButtonText(this, newTheme);
    updateLogo(newTheme);
  });
}
