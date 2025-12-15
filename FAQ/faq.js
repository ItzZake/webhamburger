const track = document.querySelector(".carousel-track");
const progressBar = document.getElementById("progress-bar")

let isDown = false;
let startX;
let scrollLeft;
let velocity = 0;
let lastX = 0;
let raf;

const friction = 0.93;  // smooth slowdown
let edgeOffset = 0;
const EDGE_RESISTANCE = 0.3; // lower = stiffer
const EDGE_MAX = 50; // px max stretch

track.addEventListener("mousedown", startDrag);
track.addEventListener("mouseleave", endDrag);
track.addEventListener("mouseup", endDrag);
track.addEventListener("mousemove", onDrag);

// --- Touch events ---
track.addEventListener("touchstart", startDrag, { passive: false });
track.addEventListener("touchend", endDrag);
track.addEventListener("touchcancel", endDrag);
track.addEventListener("touchmove", onDrag, { passive: false });

function clamp(v, min, max) {
    return Math.min(Math.max(v, min), max);
}

function endDrag() {
	if (!isDown) return;
	isDown = false;
	momentumScroll();
}

function getScrollPercentage() {
    const track = document.querySelector(".carousel-track");
    const scrollLeft = track.scrollLeft;
    const maxScroll = track.scrollWidth - track.clientWidth;

    const percent = (scrollLeft / maxScroll) * 100;
    return Math.min(Math.max(percent, 0), 100); // clamp 0-100
}

// --- Handlers ---
function startDrag(e) {
    isDown = true;
    startX = e.type.includes("touch") ? e.touches[0].pageX : e.pageX;
    scrollLeft = track.scrollLeft;
    lastX = scrollLeft;
    cancelAnimationFrame(raf);
}

function onDrag(e) {
    if (!isDown) return;
    e.preventDefault();

    const x = e.type.includes("touch") ? e.touches[0].pageX : e.pageX;
    const delta = x - startX;

    const maxScroll = track.scrollWidth - track.clientWidth;
    const atStart = track.scrollLeft <= 0;
    const atEnd = track.scrollLeft >= maxScroll;

    // If pulling past edges → elastic
    if ((atStart && delta > 0) || (atEnd && delta < 0)) {
        edgeOffset = clamp(delta * EDGE_RESISTANCE, -EDGE_MAX, EDGE_MAX);
        track.style.transform = `translateX(${edgeOffset}px)`;
        return;
    }

    // Normal scrolling
    track.scrollLeft = scrollLeft - delta;
    velocity = track.scrollLeft - lastX;
    lastX = track.scrollLeft;
}

function endDrag() {
    if (!isDown) return;
    isDown = false;

    // animate elastic return
    if (edgeOffset !== 0) {
        track.style.transition = "transform 0.35s cubic-bezier(.22,1.28,.32,1)";
        track.style.transform = "translateX(0)";

        setTimeout(() => {
            track.style.transition = "";
            edgeOffset = 0;
        }, 350);

        return;
    }

    momentumScroll();
}

function momentumScroll() {
    track.scrollLeft += velocity;
    velocity *= friction;
	
    if (Math.abs(velocity) > 0.5) {
        raf = requestAnimationFrame(momentumScroll);
    } else {
        snapToNearest();
    }
}

function snapToNearest() {
    const cards = [...document.querySelectorAll(".faq-card")];
    const trackRect = track.getBoundingClientRect();

    let closestCard = null;
    let closestDist = Infinity;

    cards.forEach((card) => {
        const rect = card.getBoundingClientRect();
        const cardCenter = rect.left + rect.width / 2;
        const trackCenter = trackRect.left + trackRect.width / 2;

        const dist = Math.abs(cardCenter - trackCenter);
        if (dist < closestDist) {
            closestDist = dist;
            closestCard = card;
        }
    });

    if (closestCard) {
        const left = closestCard.offsetLeft - (track.clientWidth / 2 - closestCard.clientWidth / 2);
        track.scrollTo({
            left,
            behavior: "smooth"
        });
    }
}

track.addEventListener("scroll", function() {
    let percentage = getScrollPercentage();
	progressBar.style.width = `${percentage}%`
})