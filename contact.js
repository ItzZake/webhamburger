document.addEventListener("DOMContentLoaded", () => {
  const contactForm = document.getElementById("contactForm");
  const btn = document.querySelector(".submit-btn");
  const themeSwitch = document.getElementById("switch");
  const html = document.documentElement;

  // ---------- CREATE ELEMENT HELPERS ----------
  const createElement = (tag, className = "") => {
    const el = document.createElement(tag);
    if (className) el.className = className;
    return el;
  };

  // ---------- ORBS ----------
  function createOrbs() {
    const container = document.getElementById("orb-container");
    // Clear any existing orbs
    container.innerHTML = "";
    Array.from({ length: 3 }).forEach((_, i) => {
      container.appendChild(createElement("div", `orb orb-${i + 1}`));
    });
  }

  // ---------- PARTICLES ----------
  function createParticles() {
    const container = document.querySelector(".particles");
    // Clear any existing particles
    container.innerHTML = "";
    Array.from({ length: 25 }).forEach(() => {
      const p = createElement("div", "particle");
      const size = Math.random() * 6 + 2;
      Object.assign(p.style, {
        width: `${size}px`,
        height: `${size}px`,
        left: `${Math.random() * 100}%`,
        animationDuration: `${Math.random() * 20 + 10}s`,
        animationDelay: `${Math.random() * 5}s`,
      });
      container.appendChild(p);
    });
  }

  // ---------- INPUT EFFECTS ----------
  function initInputEffects() {
    document.querySelectorAll("input, textarea").forEach((input) => {
      input.addEventListener("focus", () => {
        input.parentElement.style.transform = "translateY(-2px)";
      });
      input.addEventListener("blur", () => {
        input.parentElement.style.transform = "translateY(0)";
      });
    });
  }

  // ---------- SHAKE BUTTON ----------
  function shakeButton() {
    btn.style.animation = "shake .5s ease-in-out";
    setTimeout(() => (btn.style.animation = ""), 500);

    if (!document.querySelector("#shake-animation")) {
      const style = createElement("style");
      style.id = "shake-animation";
      style.textContent = `
        @keyframes shake {
          0%,100% { transform: translateX(0); }
          25% { transform: translateX(-5px); }
          75% { transform: translateX(5px); }
        }
      `;
      document.head.appendChild(style);
    }
  }

  // ---------- THEME TOGGLE ----------
  const savedTheme = localStorage.getItem("theme");

  if (savedTheme) {
    html.setAttribute("data-theme", savedTheme);
    if (themeSwitch) {
      themeSwitch.checked = savedTheme === "dark";
    }
  } else {
    html.setAttribute("data-theme", "dark");
    if (themeSwitch) {
      themeSwitch.checked = true;
    }
  }

  if (themeSwitch) {
    themeSwitch.addEventListener("change", () => {
      if (themeSwitch.checked) {
        html.setAttribute("data-theme", "dark");
        localStorage.setItem("theme", "dark");
      } else {
        html.setAttribute("data-theme", "light");
        localStorage.setItem("theme", "light");
      }
    });
  }

  // ---------- FORM SUBMIT ----------
  contactForm.addEventListener("submit", (e) => {
    e.preventDefault();

    const fullName = contactForm.fullName.value.trim();
    const message = contactForm.message.value.trim();
    const privacy = contactForm.privacyPolicy.checked;

    if (!fullName || !message || !privacy) {
      shakeButton();
      alert("Please fill in all fields and agree to the Privacy Policy.");
      return;
    }

    btn.textContent = "Sending...";
    btn.disabled = true;
    btn.style.background = "linear-gradient(to right, #4CAF50, #45a049)";

    setTimeout(() => {
      btn.textContent = "✓ Message Sent!";
      setTimeout(() => {
        contactForm.reset();
        // Show success message
        showSuccessMessage();
        setTimeout(() => {
          btn.textContent = "Send Message";
          btn.disabled = false;
          btn.style.background = "";
        }, 2000);
      }, 500);
    }, 1500);
  });

  // ---------- SUCCESS MESSAGE ----------
  function showSuccessMessage() {
    // Create success message element if it doesn't exist
    let successMessage = document.querySelector(".success-message");
    if (!successMessage) {
      successMessage = createElement("div", "success-message");
      successMessage.innerHTML = `
        <i class="fas fa-check-circle"></i>
        <h3>Message Sent!</h3>
        <p>Thank you for contacting us. We'll get back to you soon.</p>
        <button onclick="this.parentElement.style.display='none'">Close</button>
      `;
      document.body.appendChild(successMessage);
    }
    successMessage.style.display = "block";
  }

  // ---------- NAVIGATION FUNCTIONS ----------
  window.OpenSideBar = function () {
    const navbar = document.getElementById("navbar");
    navbar.classList.add("show");
  };

  window.CloseSideBar = function () {
    const navbar = document.getElementById("navbar");
    navbar.classList.remove("show");
  };

  // Close sidebar when clicking on overlay
  document.getElementById("overlay").addEventListener("click", CloseSideBar);

  // Close sidebar when clicking on nav links (for mobile)
  document.querySelectorAll("nav a").forEach((link) => {
    link.addEventListener("click", () => {
      if (window.innerWidth <= 1000) {
        CloseSideBar();
      }
    });
  });

  // ---------- RUN ----------
  createOrbs();
  createParticles();
  initInputEffects();
});
