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

    const repelRadius = 250; // distance for repulsion
    const repelPower = 3.0; // strength of repulsion
    const attractPower = 0.2; // increased — follows closer, same speed

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

// Card hover effects - attach to existing and dynamically added cards
const attachCardHover = (root = document) => {
  root.querySelectorAll(".doctor-card").forEach((card) => {
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

  card.addEventListener("mouseenter", () => {
    hover = true;
  });

  card.addEventListener("mouseleave", () => {
    hover = false;
    card.style.transform = "";
  });
});
};

// Dynamic doctors load + button functionality
document.addEventListener("DOMContentLoaded", () => {
  const grid = document.querySelector(".doctors-grid");
  if (!grid) {
    // Grid doesn't exist (access denied), but still attach handlers to any existing static cards
    attachDoctorCardHandlers(document);
    attachCardHover(document);
    return;
  }

  const attachDoctorCardHandlers = (root = document) => {
    const gridRoot = root || document;
    gridRoot.querySelectorAll(".btn-pick-nutritionist").forEach((btn) => {
      if (btn.dataset.attached) return;
      btn.dataset.attached = "1";
      
      // Change button text to "Pick Nutritionist" if needed
      if (btn.textContent.includes("Chat") || btn.textContent.includes("View Meal Plans")) {
        btn.textContent = "Pick Nutritionist";
      }
      
      btn.addEventListener("click", async function (e) {
        e.preventDefault();
        e.stopPropagation();
        
        const doctorCard = this.closest(".doctor-card");
        if (!doctorCard) return;
        
        const nutritionistId = doctorCard.dataset.nutritionistId;
        const nutritionistName = doctorCard.querySelector(".doctor-name")?.textContent || "Nutritionist";
        
        if (!nutritionistId) {
          alert("Error: Nutritionist ID not found");
          return;
        }
        
        // Confirm selection
        const confirmed = confirm(`Are you sure you want to pick ${nutritionistName} as your nutritionist?`);
        if (!confirmed) return;
        
        // Disable button during request
        this.disabled = true;
        this.textContent = "Assigning...";
        
        try {
          const apiPath = new URL("../api/member/assign_nutritionist.php", window.location.href).href;
          const response = await fetch(apiPath, {
            method: "POST",
            headers: {
              "Content-Type": "application/json"
            },
            body: JSON.stringify({
              nutritionist_id: parseInt(nutritionistId)
            })
          });
          
          const result = await response.json();
          
          if (result.success) {
            alert(`Successfully assigned ${nutritionistName} as your nutritionist! Redirecting to meal plans...`);
            // Wait a moment for database to update, then redirect
            // Use a longer delay to ensure database transaction is committed
            setTimeout(() => {
              // Use replace to prevent back navigation issues
              // Add a parameter to indicate we just assigned
              window.location.replace("MealPlans.php?just_assigned=1");
            }, 1000);
          } else {
            alert("Failed to assign nutritionist: " + (result.error || "Unknown error"));
            this.disabled = false;
            this.textContent = "Pick Nutritionist";
          }
        } catch (error) {
          console.error("Error assigning nutritionist:", error);
          alert("An error occurred while assigning the nutritionist. Please try again.");
          this.disabled = false;
          this.textContent = "Pick Nutritionist";
        }
      });
    });
  };

  // compute API path relative to current page and log it for debugging
  const apiPath = new URL("../api/doctors/get.php", window.location.href).href;
  console.log(
    "Doctors API path:",
    apiPath,
    "window.location:",
    window.location.href
  );

  fetch(apiPath)
    .then((r) => {
      if (!r.ok) throw new Error(`API error ${r.status}`);
      const ct = r.headers.get("content-type") || "";
      if (!ct.includes("application/json")) {
        return r.text().then((t) => {
          throw new Error("Invalid JSON response: " + t.slice(0, 300));
        });
      }
      return r.json();
    })
    .then((list) => {
      console.log("Received doctors data:", list);
      if (!Array.isArray(list)) {
        console.error("Response is not an array:", list);
        attachDoctorCardHandlers(grid);
        attachCardHover(grid);
        return;
      }
      if (list.length === 0) {
        console.log("API returned empty array, keeping static content");
        attachDoctorCardHandlers(grid);
        attachCardHover(grid);
        return;
      }
      console.log(`Rendering ${list.length} doctors`);
      // Only clear grid if we have data to replace it with
      grid.innerHTML = "";
      list.forEach((doc, idx) => {
        const delay = idx * 100;
        const card = document.createElement("div");
        card.className = "doctor-card";
        card.setAttribute("data-nutritionist-id", doc.id);
        card.setAttribute("data-aos", "fade-up");
        card.setAttribute("data-aos-delay", String(delay));
        card.innerHTML = `
          <div class="doctor-image">
            <div class="avatar" style="background: linear-gradient(135deg, #a66fff, #5c4e9c)">
              <img src="${doc.img || "https://via.placeholder.com/80"}" alt="${
          doc.name
        }" style="width:64px;height:64px;border-radius:50%;object-fit:cover;" />
            </div>
          </div>
          <div class="doctor-info">
            <h3 class="doctor-name">${doc.name}</h3>
            <p class="doctor-specialty">${doc.specialty || ""}</p>
            <p class="doctor-description">${doc.bio || ""}</p>
          </div>
          <div class="doctor-actions">
            <button class="btn-pick-nutritionist">Pick Nutritionist</button>
          </div>
        `;
        grid.appendChild(card);
      });
      
      // Verify cards are in DOM
      const renderedCards = grid.querySelectorAll(".doctor-card");
      console.log(`Cards in DOM: ${renderedCards.length}`, renderedCards);
      
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
      
      attachDoctorCardHandlers(grid);
      attachCardHover(grid);
      console.log("Doctors rendered successfully");
      
      // Load and display recommendations
      loadRecommendations();
    })
    .catch((err) => {
      console.error("Doctors API error:", err);
      console.warn("Falling back to static HTML");
      attachDoctorCardHandlers(grid);
      attachCardHover(grid);
    });
  
  // Attach handlers for existing static content as well
  attachDoctorCardHandlers(document);
  attachCardHover(document);
});

// Load recommendations and add badges to recommended nutritionists
async function loadRecommendations() {
  try {
    console.log('=== LOADING RECOMMENDATIONS ===');
    const response = await fetch('../api/recommendations/get.php');
    
    // Always read the response text first to see what we got
    const responseText = await response.text();
    console.log('=== RESPONSE STATUS:', response.status, '===');
    console.log('=== RESPONSE TEXT (first 1000 chars):', responseText.substring(0, 1000), '===');
    
    // Check if response is ok
    if (!response.ok) {
      let errorData;
      try {
        errorData = JSON.parse(responseText);
        console.error('=== ERROR (JSON):', JSON.stringify(errorData, null, 2), '===');
        console.error('=== ERROR MESSAGE:', errorData.error || errorData.message || 'Unknown error', '===');
        alert('Recommendations Error: ' + (errorData.error || errorData.message || 'Unknown error'));
      } catch (e) {
        console.error('=== ERROR (NON-JSON):', responseText, '===');
        console.error('=== PARSE ERROR:', e, '===');
        alert('Recommendations Error: Server returned non-JSON response. Check console for details.');
      }
      return;
    }
    
    const data = JSON.parse(responseText);

    if (data.success && data.recommended_nutritionists && Array.isArray(data.recommended_nutritionists)) {
      data.recommended_nutritionists.forEach(nutritionistId => {
        const doctorCard = document.querySelector(`[data-nutritionist-id="${nutritionistId}"]`);
        if (doctorCard && !doctorCard.querySelector('.recommended-badge')) {
          doctorCard.classList.add('recommended');
          const badge = document.createElement('div');
          badge.className = 'recommended-badge';
          badge.textContent = 'Recommended';
          doctorCard.style.position = 'relative';
          doctorCard.insertBefore(badge, doctorCard.firstChild);
        }
      });
    } else if (data.error) {
      console.warn('Recommendations API returned error:', data.error);
    }
  } catch (e) {
    console.error('Failed to load recommendations:', e);
  }
}
