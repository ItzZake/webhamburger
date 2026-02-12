// Attach event listener immediately
const contactForm = document.getElementById("contactForm");
if (contactForm) {
  console.log('Contact form found, attaching event listener');
  contactForm.addEventListener("submit", handleFormSubmit);
} else {
  console.error('Contact form not found!');
}

// ---------- CREATE ELEMENT HELPERS ----------
const createElement = (tag, className = "") => {
  const el = document.createElement(tag);
  if (className) el.className = className;
  return el;
};

// Form submit handler
function handleFormSubmit(e) {
  console.log('Form submit intercepted by JavaScript');
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

  const btn = document.querySelector(".submit-btn");
  btn.textContent = "Sending...";
  btn.disabled = true;
  btn.style.background = "linear-gradient(to right, #4CAF50, #45a049)";

  // Send the request
  const xhr = new XMLHttpRequest();
  xhr.open('POST', '/a/api/contact/submit.php', true);
  xhr.setRequestHeader('Content-Type', 'application/json');
  
  xhr.onreadystatechange = function() {
    if (xhr.readyState === 4) {
      console.log('API Response:', xhr.status, xhr.responseText);
      
      try {
        const result = JSON.parse(xhr.responseText);
        
        if (result.success) {
          contactForm.reset();
          showSuccessMessage();
        } else {
          alert('Failed to send message: ' + (result.error || 'Unknown error'));
          shakeButton();
        }
      } catch (error) {
        console.error('Parse error:', error);
        alert('Failed to send message. Please try again.');
        shakeButton();
      }
      
      // Reset button
      btn.textContent = "Send Message";
      btn.disabled = false;
      btn.style.background = "";
    }
  };
  
  xhr.onerror = function() {
    console.error('Network error');
    alert('Failed to send message. Please try again.');
    shakeButton();
    btn.textContent = "Send Message";
    btn.disabled = false;
    btn.style.background = "";
  };
  
  xhr.send(JSON.stringify({
    fullName,
    email,
    message
  }));
}

// ---------- SHAKE BUTTON ----------
function shakeButton() {
  const btn = document.querySelector(".submit-btn");
  if (btn) {
    btn.style.animation = "shake .5s ease-in-out";
    setTimeout(() => (btn.style.animation = ""), 500);
  }

  // Add shake animation style if not exists
  if (!document.querySelector("#shake-animation")) {
    const style = document.createElement("style");
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

// ---------- SUCCESS MESSAGE ----------
function showSuccessMessage() {
  // Create success message element if it doesn't exist
  let successMessage = document.querySelector(".success-message");
  if (!successMessage) {
    successMessage = document.createElement("div");
    successMessage.className = "success-message";
    successMessage.innerHTML = `
      <i class="fas fa-check-circle"></i>
      <h3>Message Sent!</h3>
      <p>Thank you for contacting us. We'll get back to you soon.</p>
      <button onclick="this.parentElement.style.display='none'">Close</button>
    `;
    successMessage.style.cssText = `
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background: white;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.3);
      text-align: center;
      z-index: 10000;
      border: 2px solid #4CAF50;
    `;
    document.body.appendChild(successMessage);
  }
  successMessage.style.display = "block";
}

document.addEventListener("DOMContentLoaded", () => {
  console.log('Contact page fully loaded');
  const themeSwitch = document.getElementById("switch");
  const html = document.documentElement;

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
