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

  // Initial activation
  setActive(currentIndex);

  /* BUTTONS */
  nextBtn?.addEventListener('click', () => {
    currentIndex = (currentIndex + 1) % cards.length;
    setActive(currentIndex);
  });

  prevBtn?.addEventListener('click', () => {
    currentIndex = (currentIndex - 1 + cards.length) % cards.length;
    setActive(currentIndex);
  });

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
});
