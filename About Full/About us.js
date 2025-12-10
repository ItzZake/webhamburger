// Navbar functions
const navbar = document.getElementById("navbar");
const toggle = document.getElementById("switch");
const html = document.documentElement;
const savedTheme = localStorage.getItem("theme");

function OpenSideBar() {
  navbar.classList.add("show");
}

function CloseSideBar() {
  navbar.classList.remove("show");
}

// Learn More button functionality
document.getElementById("learnMoreBtn").addEventListener("click", () => {
  document.getElementById("mission").scrollIntoView({ behavior: "smooth" });
});

// Theme Toggle
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
