document.addEventListener("DOMContentLoaded", function () {
  const contactForm = document.getElementById("contactForm");

  function createOrbs() {
    for (let i = 1; i <= 3; i++) {
      const orb = document.createElement("div");
      orb.className = `orb orb-${i}`;
      document.getElementById("orb-container").appendChild(orb);
    }
  }

  function createParticles() {
    const container = document.querySelector(".particles");
    for (let i = 0; i < 25; i++) {
      const p = document.createElement("div");
      p.className = "particle";
      const size = Math.random() * 6 + 2;
      const left = Math.random() * 100;
      const duration = Math.random() * 20 + 10;
      const delay = Math.random() * 5;
      p.style.width = p.style.height = `${size}px`;
      p.style.left = `${left}%`;
      p.style.animationDuration = `${duration}s`;
      p.style.animationDelay = `${delay}s`;
      container.appendChild(p);
    }
  }

  function initFloatingLabels() {
    const inputs = document.querySelectorAll("input, textarea");
    inputs.forEach((input) => {
      const label = document.createElement("span");
      label.className = "floating-label";
      label.textContent = input.getAttribute("placeholder");
      input.parentNode.insertBefore(label, input.nextSibling);
      input.removeAttribute("placeholder");
    });
  }

  function initInputEffects() {
    const inputs = document.querySelectorAll("input, textarea");
    inputs.forEach((input) => {
      input.addEventListener("focus", () => {
        input.parentElement.style.transform = "translateY(-2px)";
      });
      input.addEventListener("blur", () => {
        input.parentElement.style.transform = "translateY(0)";
      });
    });
  }

  contactForm.addEventListener("submit", function (e) {
    e.preventDefault();
    const fullName = document.getElementById("fullName").value;
    const message = document.getElementById("message").value;
    const privacyPolicy = document.getElementById("privacyPolicy").checked;
    if (!fullName || !message || !privacyPolicy) {
      const btn = document.querySelector(".submit-btn");
      btn.style.animation = "shake 0.5s ease-in-out";
      setTimeout(() => {
        btn.style.animation = "";
      }, 500);
      if (!document.querySelector("#shake-animation")) {
        const style = document.createElement("style");
        style.id = "shake-animation";
        style.textContent =
          "@keyframes shake {0%,100%{transform:translateX(0);}25%{transform:translateX(-5px);}75%{transform:translateX(5px);}}";
        document.head.appendChild(style);
      }
      alert(
        "Please fill in all required fields and agree to the Privacy Policy."
      );
      return;
    }
    const btn = document.querySelector(".submit-btn");
    btn.textContent = "Sending...";
    btn.style.background = "linear-gradient(to right,#4CAF50,#45a049)";
    setTimeout(() => {
      btn.textContent = "✓ Message Sent!";
      setTimeout(() => {
        contactForm.reset();
        btn.textContent = "Send Message";
        btn.style.background = "linear-gradient(to right,#7b3ef3,#a66fff)";
        document.querySelectorAll(".floating-label").forEach((label) => {
          label.style.top = "14px";
          label.style.left = "14px";
          label.style.fontSize = "1rem";
          label.style.color = "#6b6390";
        });
      }, 2000);
    }, 1500);
  });

  createOrbs();
  createParticles();
  initFloatingLabels();
  initInputEffects();
});
