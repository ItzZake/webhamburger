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

  // ---------- BLOB ORBS INTERACTION ----------
  function initBlobOrbs() {
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

    // Reset blob positions when mouse leaves window
    document.addEventListener("mouseleave", () => {
      blobs.forEach((blob) => {
        blob.style.setProperty("--dx", "0px");
        blob.style.setProperty("--dy", "0px");
      });
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
    const email = contactForm.email.value.trim();
    const message = contactForm.message.value.trim();
    const privacy = contactForm.privacyPolicy.checked;

    // Email validation regex
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!fullName || !email || !message || !privacy) {
      shakeButton();
      alert("Please fill in all fields and agree to the Privacy Policy.");
      return;
    }

    if (!emailRegex.test(email)) {
      shakeButton();
      alert("Please enter a valid email address.");
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

  // ---------- INITIALIZE EVERYTHING ----------
  initBlobOrbs();
  initInputEffects();

  // Add resize handler for better mobile experience
  window.addEventListener("resize", () => {
    if (window.innerWidth > 1000) {
      CloseSideBar();
    }
  });
});
