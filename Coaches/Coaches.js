// Initialize AOS if available
if (typeof AOS !== 'undefined') {
  AOS.init({
    duration: 800,
    once: true,
    offset: 100
  });
}

// Wait for DOM to be ready before accessing elements
document.addEventListener("DOMContentLoaded", () => {
  const navbar = document.getElementById("navbar");
  const toggle = document.getElementById("switch");
  const html = document.documentElement;
  const savedTheme = localStorage.getItem("theme");

  if (!navbar || !toggle) return;

  window.OpenSideBar = function() {
  navbar.classList.add("show");
    const overlay = document.getElementById("overlay");
    if (overlay) overlay.style.display = "block";
  };

  window.CloseSideBar = function() {
  navbar.classList.remove("show");
    const overlay = document.getElementById("overlay");
    if (overlay) overlay.style.display = "none";
  };

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
});

// Blob movement effect - wait for DOM to be ready
document.addEventListener("DOMContentLoaded", () => {
const blobs = document.querySelectorAll(".blob-dodge");
  if (blobs.length === 0) return;
  
document.addEventListener("mousemove", (e) => {
  blobs.forEach((blob) => {
    const rect = blob.getBoundingClientRect();
    const cx = rect.left + rect.width / 2;
    const cy = rect.top + rect.height / 2;

    const dx = cx - e.clientX;
    const dy = cy - e.clientY;
    const dist = Math.sqrt(dx * dx + dy * dy);

    const repelRadius = 250;
    const repelPower = 3.0;
    const attractPower = 0.2;

    if (dist < repelRadius) {
      const force = (repelRadius - dist) / repelRadius;
      blob.style.setProperty("--dx", `${dx * force * repelPower}px`);
      blob.style.setProperty("--dy", `${dy * force * repelPower}px`);
    } else {
      const ax = -dx * attractPower;
      const ay = -dy * attractPower;
      blob.style.setProperty("--dx", `${ax}px`);
      blob.style.setProperty("--dy", `${ay}px`);
    }
    });
  });
});

// Attaches hover effect to coach cards (safe for dynamic additions)
const attachCardHover = (root = document) => {
  root.querySelectorAll(".coach-card").forEach((card) => {
    if (card.dataset.hoverAttached) return;
    card.dataset.hoverAttached = "1";
    let hover = false;
    card.addEventListener("mousemove", (e) => {
      if (!hover) return;
      const rect = card.getBoundingClientRect();
      const x = e.clientX - rect.left - rect.width / 2;
      const y = e.clientY - rect.top - rect.height / 2;
      card.style.transform = `translate(${x * 0.02}px, ${
        y * 0.02
      }px) scale(1.02)`;
    });
    card.addEventListener("mouseenter", () => (hover = true));
    card.addEventListener("mouseleave", () => {
      hover = false;
      card.style.transform = "";
    });
  });
};

// Pick Coach button handlers
const attachBookingHandlers = (root = document) => {
  const r = root || document;
  r.querySelectorAll(".btn-book-schedule").forEach((btn) => {
    if (btn.dataset.attached) return;
    btn.dataset.attached = "1";
    
    // Change button text to "Pick Coach"
    if (btn.textContent.includes("Book Schedule")) {
      btn.textContent = "Pick Coach";
    }
    
    btn.addEventListener("click", async function (e) {
      e.preventDefault();
      e.stopPropagation();
      
      const coachCard = this.closest(".coach-card");
      if (!coachCard) return;
      
      const coachId = coachCard.dataset.coachId;
      const coachName = coachCard.querySelector(".coach-name")?.textContent || "Coach";
      
      if (!coachId) {
        alert("Error: Coach ID not found");
        return;
      }
      
      // Confirm selection
      const confirmed = confirm(`Are you sure you want to pick ${coachName} as your coach?`);
      if (!confirmed) return;
      
      // Disable button during request
      this.disabled = true;
      this.textContent = "Assigning...";
      
      try {
        const apiPath = new URL("../api/member/assign_coach.php", window.location.href).href;
        const response = await fetch(apiPath, {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({
            coach_id: parseInt(coachId)
          })
        });
        
        const result = await response.json();
        
        if (result.success) {
          alert(`Successfully assigned ${coachName} as your coach! Redirecting to workouts page...`);
          // Redirect to workouts page
          window.location.href = "../Workouts Full/workouts.php";
      } else {
          alert("Failed to assign coach: " + (result.error || "Unknown error"));
          this.disabled = false;
          this.textContent = "Pick Coach";
        }
      } catch (error) {
        console.error("Error assigning coach:", error);
        alert("An error occurred while assigning the coach. Please try again.");
        this.disabled = false;
        this.textContent = "Pick Coach";
      }
    });
  });

  r.querySelectorAll(".btn-generated-workout").forEach((btn) => {
    if (btn.dataset.attached) return;
    btn.dataset.attached = "1";
    btn.addEventListener("click", function (e) {
      const coachCard = this.closest(".coach-card");
      const coachName = coachCard.querySelector(".coach-name").textContent;
      sessionStorage.setItem("selectedCoach", coachName);
      window.location.href = "../Workouts/workouts full/workouts.html";
    });
  });
};

// Close dropdown when clicking outside (attach once)
if (!document.body.dataset.closeHandlerAttached) {
  document.body.dataset.closeHandlerAttached = "1";
  document.addEventListener("click", function (e) {
    if (!e.target.closest(".coach-actions")) {
      document.querySelectorAll(".booking-dropdown.show").forEach((d) => {
        d.classList.remove("show");
        const coachCard = d.closest(".coach-card");
        if (coachCard) coachCard.classList.remove("dropdown-open");
      });
    }
  });
}

// On load: render coaches from backend (if available) then attach handlers
window.addEventListener("load", () => {
  const grid = document.querySelector(".coaches-grid");
  if (!grid) {
    // Grid doesn't exist (access denied), but still attach handlers to any existing static cards
    attachBookingHandlers(document);
    attachCardHover(document);
    return;
  }

  const apiPath = new URL("../api/coaches/get.php", window.location.href).href;
  console.log("Fetching coaches from", apiPath);
  
  // Add timeout to fetch
  const fetchWithTimeout = (url, options = {}, timeout = 10000) => {
    return Promise.race([
      fetch(url, options),
      new Promise((_, reject) =>
        setTimeout(() => reject(new Error("Fetch timeout after " + timeout + "ms")), timeout)
      )
    ]);
  };
  
  fetchWithTimeout(apiPath, {}, 10000)
    .then((r) => {
      console.log("Response status:", r.status, r.statusText);
      console.log("Response headers:", Object.fromEntries(r.headers.entries()));
      if (!r.ok) {
        return r.text().then((text) => {
          console.error("API error response:", text);
          throw new Error("API error " + r.status + ": " + text.slice(0, 200));
        });
      }
      const ct = r.headers.get("content-type") || "";
      console.log("Content-Type:", ct);
      if (!ct.includes("application/json")) {
        return r.text().then((t) => {
          console.error("Non-JSON response:", t);
          throw new Error("Invalid JSON: " + t.slice(0, 200));
        });
      }
      return r.json();
    })
    .then((list) => {
      console.log("Received coaches data:", list);
      if (!Array.isArray(list)) {
        console.error("Response is not an array:", list);
        attachBookingHandlers(document);
        attachCardHover(document);
        return;
      }
      if (list.length === 0) {
        console.log("API returned empty array, keeping static content");
        attachBookingHandlers(document);
        attachCardHover(document);
        return;
      }
      console.log(`Rendering ${list.length} coaches`);
      grid.innerHTML = "";
      list.forEach((c, idx) => {
        const card = document.createElement("div");
        card.className = "coach-card";
        card.setAttribute("data-aos", "fade-up");
        card.setAttribute("data-aos-delay", String(idx * 100));
        card.setAttribute("data-coach-id", c.id);
        card.innerHTML = `
          <div class="coach-image">
            <div class="avatar" style="background: linear-gradient(135deg, #a66fff, #5c4e9c)">
              ${
                c.img
                  ? `<img src="${c.img}" alt="${c.name}" style="width:64px;height:64px;border-radius:50%;object-fit:cover;"/>`
                  : "CO"
              }
            </div>
          </div>
          <div class="coach-info">
            <h3 class="coach-name">${c.name}</h3>
            <p class="coach-specialty">${c.specialty || ""}</p>
            <p class="coach-description">${c.bio || ""}</p>
          </div>
          <div class="coach-actions">
            <button class="btn-book-schedule">Pick Coach</button>
          </div>
        `;
        grid.appendChild(card);
      });
      
      // Verify cards are in DOM
      const renderedCards = grid.querySelectorAll(".coach-card");
      console.log(`Cards in DOM: ${renderedCards.length}`, renderedCards);
      console.log("Grid element:", grid);
      console.log("Grid computed style:", window.getComputedStyle(grid));
      
      // Ensure cards are visible (AOS might hide them initially)
      renderedCards.forEach((card, idx) => {
        const computedStyle = window.getComputedStyle(card);
        console.log(`Card ${idx} - display: ${computedStyle.display}, visibility: ${computedStyle.visibility}, opacity: ${computedStyle.opacity}`);
        // Force visibility if AOS is hiding them
        if (computedStyle.opacity === '0' || computedStyle.visibility === 'hidden') {
          card.style.opacity = '1';
          card.style.visibility = 'visible';
        }
      });
      
      // Refresh AOS if available and force animation
      if (typeof AOS !== 'undefined') {
        AOS.refresh();
        // Force AOS to show elements immediately
        setTimeout(() => {
          renderedCards.forEach(card => {
            card.classList.add('aos-animate');
          });
          AOS.refresh();
        }, 100);
        console.log("AOS refreshed");
      }
      
      // attach handlers to newly created elements
      attachBookingHandlers(grid);
      attachCardHover(grid);
      console.log("Coaches rendered successfully");
      
      // Load and display recommendations
      loadRecommendations();
    })
    .catch((err) => {
      console.error("Coaches API error:", err);
      console.warn("Falling back to static HTML");
      attachBookingHandlers(document);
      attachCardHover(document);
    });

  // Attach handlers for existing static content as well
  attachBookingHandlers(document);
  attachCardHover(document);
});

// Load recommendations and add badges to recommended coaches
async function loadRecommendations() {
  try {
    const response = await fetch('../api/recommendations/get.php');
    const data = await response.json();

    if (data.success && data.recommended_coaches && Array.isArray(data.recommended_coaches)) {
      data.recommended_coaches.forEach(coachId => {
        const coachCard = document.querySelector(`[data-coach-id="${coachId}"]`);
        if (coachCard && !coachCard.querySelector('.recommended-badge')) {
          coachCard.classList.add('recommended');
          const badge = document.createElement('div');
          badge.className = 'recommended-badge';
          badge.textContent = 'Recommended';
          coachCard.style.position = 'relative';
          coachCard.insertBefore(badge, coachCard.firstChild);
        }
      });
    }
  } catch (e) {
    console.error('Failed to load recommendations:', e);
  }
}
