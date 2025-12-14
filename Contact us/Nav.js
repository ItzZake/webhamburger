const navbar = document.getElementById("navbar");
const toggle = document.getElementById("switch");
const html = document.documentElement;
const logo = document.getElementById("logo");
const savedTheme = localStorage.getItem("theme");

function OpenSideBar() {
  navbar.classList.add("show");
}
function CloseSideBar() {
  navbar.classList.remove("show");
}

function setLogo(theme) {
  if (theme === "dark") {
    logo.src = "media/dark-logo-no-text.png";
  } else {
    logo.src = "media/logo-without-text.png";
  }
}

if (savedTheme) {
  html.setAttribute("data-theme", savedTheme);
  toggle.checked = savedTheme === "dark";
  setLogo(savedTheme);
} else {
  html.setAttribute("data-theme", "dark");
  toggle.checked = true;
  setLogo("dark");
}

toggle.addEventListener("change", () => {
  if (toggle.checked) {
    html.setAttribute("data-theme", "dark");
    localStorage.setItem("theme", "dark");
    setLogo("dark");
  } else {
    html.setAttribute("data-theme", "light");
    localStorage.setItem("theme", "light");
    setLogo("light");
  }
});