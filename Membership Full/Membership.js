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
const blobs = document.querySelectorAll(".blob-dodge");

document.addEventListener("mousemove", e => {
    blobs.forEach(blob => {

        const rect = blob.getBoundingClientRect();
        const cx = rect.left + rect.width / 2;
        const cy = rect.top + rect.height / 2;

        const dx = cx - e.clientX;
        const dy = cy - e.clientY;
        const dist = Math.sqrt(dx * dx + dy * dy);

        const repelRadius = 250;   // distance for repulsion
        const repelPower = 5.0;    // strength of repulsion
        const attractPower = 0.25; // increased — follows closer, same speed

        // -----------------------------
        // REPULSION (same)
        // -----------------------------
        if (dist < repelRadius) {
            const force = (repelRadius - dist) / repelRadius;

            blob.style.setProperty("--dx", `${dx * force * repelPower}px`);
            blob.style.setProperty("--dy", `${dy * force * repelPower}px`);
        }

        // -----------------------------
        // ATTRACTION (closer follow)
        // -----------------------------
        else {
            const ax = -dx * attractPower;
            const ay = -dy * attractPower;

            blob.style.setProperty("--dx", `${ax}px`);
            blob.style.setProperty("--dy", `${ay}px`);
        }

    });
});
document.addEventListener('DOMContentLoaded', () => {
  const carousel = document.querySelector('.carousel');
  const cards = Array.from(document.querySelectorAll('.carousel .card'));
  const prevBtn = document.querySelector('.prev');
  const nextBtn = document.querySelector('.next');

  if (!carousel || cards.length === 0) return;

  let currentIndex = 1; // start on center card
  let isProgrammaticScroll = false;
  let scrollTimeout;

  function setActive(index) {
    isProgrammaticScroll = true; // block auto-detect during this scroll

    cards.forEach(c => c.classList.remove('active'));
    cards[index].classList.add('active');

    cards[index].scrollIntoView({
      behavior: "smooth",
      inline: "center",
      block: "nearest"
    });

    // re-enable scroll detection AFTER scroll animation finishes
    setTimeout(() => { 
      isProgrammaticScroll = false;
    }, 350);
  }

  /* AUTO-DETECT ONLY WHEN USER SCROLLS */
  carousel.addEventListener('scroll', () => {
    if (isProgrammaticScroll) return; // ignore scroll from scrollIntoView()

    clearTimeout(scrollTimeout);
    scrollTimeout = setTimeout(() => {
      const wrapperRect = carousel.getBoundingClientRect();
      const centerX = wrapperRect.left + wrapperRect.width / 2;

      let closestIdx = 0;
      let closestDist = Infinity;

      cards.forEach((card, idx) => {
        const rect = card.getBoundingClientRect();
        const cardCenter = rect.left + rect.width / 2;
        const dist = Math.abs(cardCenter - centerX);
        if (dist < closestDist) {
          closestDist = dist;
          closestIdx = idx;
        }
      });

      currentIndex = closestIdx;
      cards.forEach(c => c.classList.remove('active'));
      cards[currentIndex].classList.add('active');
    }, 80);
  });
  document.addEventListener("DOMContentLoaded", () => {

    const cards = document.querySelectorAll(".carousel .card");

    cards.forEach(card => {
        card.addEventListener("click", () => {
            card.classList.add("active");
        });
    });
    
});
cards.forEach(card => {
    card.addEventListener("click", () => {
        cards.forEach(c => c.classList.remove("active"));
        card.classList.add("active");
    });
});
const buttons = document.querySelectorAll(".Buttons .super-button");

    // Card values by membership type
    const membershipPrices = {
        "1 Month":  { Silver: "350 L.E", Gold: "600 L.E", Platinum: "1000 L.E" },
        "3 Months": { Silver: "900 L.E", Gold: "1600 L.E", Platinum: "2000 L.E" },
        "1 Year":   { Silver: "2000 L.E", Gold: "2600 L.E", Platinum: "3000 L.E" }
    };

    // Attach click listener to each button
    buttons.forEach(button => {
        button.addEventListener("click", () => {

            const selectedTime = button.innerText.trim();

            // Get all card elements
            const cards = document.querySelectorAll(".card");

            cards.forEach(card => {
                const name = card.querySelector("h2").innerText.trim(); // Silver / Gold / Platinum
                const amountField = card.querySelector(".Amount");
                const timeField = card.querySelector(".Time");
                const addBtn = card.querySelector(".add-to-cart-btn");

                // Update price + time
                amountField.innerText = membershipPrices[selectedTime][name];
                timeField.innerText = selectedTime;

                // Update button data attributes
                if (addBtn) {
                    const price = membershipPrices[selectedTime][name].replace(/[^0-9]/g, "");
                    addBtn.setAttribute("data-price", price);
                    addBtn.setAttribute("data-duration", selectedTime);
                }
            });

        });
    });

    // Add to cart functionality - using database cart
    document.querySelectorAll(".add-to-cart-btn").forEach(btn => {
        btn.addEventListener("click", async function() {
            const plan = this.getAttribute("data-plan");
            const price = this.getAttribute("data-price");
            const duration = this.getAttribute("data-duration");
            const priceText = price + " L.E";

            // Disable button during request
            const originalText = this.querySelector("span").textContent;
            this.querySelector("span").textContent = "Adding...";
            this.disabled = true;

            try {
                // Add to database cart - use absolute path from root
                const apiBase = window.location.pathname.includes('/Membership Full/') 
                    ? '../../api' 
                    : '/a/api';
                const response = await fetch(`${apiBase}/cart/add_membership.php`, {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        plan: plan,
                        duration: duration,
                        price: price
                    })
                });

                if (!response.ok) {
                    // If response is not OK, try to get error message
                    const errorText = await response.text();
                    let errorData;
                    try {
                        errorData = JSON.parse(errorText);
                    } catch (e) {
                        throw new Error(`Server error: ${response.status} - ${errorText.substring(0, 100)}`);
                    }
                    throw new Error(errorData.error || `Server error: ${response.status}`);
                }

                const result = await response.json();

                if (result.success) {
                    // Store cart_id if returned
                    if (result.cart_id) {
                        localStorage.setItem("server_cart_id", result.cart_id);
                    }

                    // Also add to localStorage for compatibility
                    let cart = JSON.parse(localStorage.getItem("cart") || "[]");
                    const membershipItem = {
                        id: `membership-${plan}-${duration.replace(/\s/g, "-")}`,
                        name: `${plan} Membership - ${duration}`,
                        desc: `Membership plan: ${plan} tier for ${duration}`,
                        price: priceText,
                        img: "../Store/Store/Images/product1.png",
                        type: "membership",
                        plan: plan,
                        duration: duration,
                        planId: plan === "Silver" ? 1 : plan === "Gold" ? 2 : 3
                    };
                    cart.push(membershipItem);
                    localStorage.setItem("cart", JSON.stringify(cart));

                    // Show success feedback
                    this.querySelector("span").textContent = "Added!";
                    this.style.background = "linear-gradient(135deg, #10b981, #059669)";
                    
                    // Immediately redirect to cart/checkout page
                    setTimeout(() => {
                        window.location.href = "../Cart/Cart/Cart.php";
                    }, 1000);
                } else {
                    throw new Error(result.error || "Failed to add membership to cart");
                }
            } catch (error) {
                console.error("Error adding membership to cart:", error);
                alert("Failed to add membership to cart: " + error.message);
                this.querySelector("span").textContent = originalText;
                this.disabled = false;
            }
        });
    });
});
