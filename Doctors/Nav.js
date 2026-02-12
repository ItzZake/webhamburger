const navbar = document.getElementById("navbar");
toggle = document.getElementById("switch");
html = document.documentElement;
const logo = document.getElementById("logo");
savedTheme = localStorage.getItem("theme");



function setLogo(theme) {
  if (theme === "dark") {
    logo.src = "media/logo-without-text.png";
  } else {
    logo.src = "media/dark-logo-no-text.png";
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