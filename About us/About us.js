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
// Particle Orb Interaction
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
    const repelPower = 5.0; // strength of repulsion
    const attractPower = 0.25; // attraction strength

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
