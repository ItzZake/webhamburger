
const track = document.getElementById("image-track");
const computedTrack = window.getComputedStyle(track);
const bg_images = document.getElementById("bg-images")
const images = bg_images.querySelectorAll(".image");
const mainTextsContainer = document.getElementsByClassName(".main-texts-container");
const dwTapeContent = document.querySelectorAll(".dw-tape-content");
const indexText = document.getElementById("index-text")
const tapeContent = document.querySelectorAll(".tape-content")[0]
const workoutInfo = document.getElementById("workout-info")
const workoutInfoTrack = document.getElementById("workout-info-track")
const exercises = workoutInfoTrack.querySelector(".exercises")
const allExercises = workoutInfoTrack.querySelectorAll(".exercises")

const lenis = new Lenis({
	autoRaf: true,
	wrapper: workoutInfoTrack,
})

function sleep(ms) {
	return new Promise(resolve => setTimeout(resolve, ms));
}

const prevButton = document.getElementById("prevButton");
const nextButton = document.getElementById("nextButton");
const cycleButtons = document.querySelectorAll(".cycleButton")
let curImg = 0;

images[curImg].style.transform = `translate(0%, 0%)`
allExercises[curImg].style.transform = `translate(0%, 0%)`

const updateActiveSlide = (index) => {
    // 1. Reset all slides (set them to absolute/inactive state)
    allExercises.forEach((exercise, i) => {
        exercise.classList.remove('active-slide');
    });

    // 2. Set the current slide as active
    const activeSlide = allExercises[index];
    activeSlide.classList.add('active-slide');

    // --- NEW CODE: Force Card Position Reset ---
    const cards = activeSlide.querySelectorAll('.card, .first-card');
    
    // This assumes your default card CSS is still:
    // transform: translateY(-30%); opacity: 0;
    
    // We force them to their final resting state:
    cards.forEach(card => {
        card.style.transform = 'translateY(0%)'; 
        card.style.opacity = '1'; 
    });
    // ---------------------------------------------
};

for (let i = 1; i < images.length; i++) {
	updateActiveSlide(i);
	allExercises[i].style.transform = `translateX(100%)`
}

async function toggleIndexText(on) {
	indexText.style.transform = `translate(-50%, -50%) scale(${on ? 1 : 0})`
}
toggleIndexText(true)

async function toggleCycleButton(on) {
	Array.from(cycleButtons).forEach(button => {
		button.style.transform = `scale(${on ? 1 : 0}) rotate(${90 * curImg}deg)`
	})
}
toggleCycleButton(true)

const dayTexts = document.getElementById("day-texts-container")
const workoutTexts = document.getElementById("workouts-texts-container")
const tipsHints = document.querySelectorAll(".tips-hints")
async function toggleDwTapes(on) {
	dayTexts.style.height = `${on ? 3 : 0}rem`
	workoutTexts.style.height = `${on ? 2 : 0}rem`
	//Array.from(dwTapeContent).forEach(tape => {
	//	tape.style.transform = `translateY(${(-200 * curImg) + (on ? 0 : -100)}%)`
	//});
}
toggleDwTapes(true)

function updateTapes() {
	Array.from(dwTapeContent).forEach(tape => {
		tape.style.transform = `translateY(${-200 * curImg}%)`
	});

	Array.from(cycleButtons).forEach(button => {
		button.style.transform = `scale(1) rotate(${90 * curImg}deg)`
	})

	tapeContent.style.transform = `translateY(${-200 * curImg}%)`
}

prevButton.onmouseup = e => {
	lenis.scrollTo(0, { immediate: true });
	if (curImg == 0) {
		curImg = 4;
		Array.from(images).forEach(image => {
			image.style.transform = `translateX(0%)`
		})

		Array.from(allExercises).forEach(exercise => {
			exercise.style.transform = `translateX(100%)`
		})
	}
	else {
		images[curImg].style.transform = `translateX(100%)`
		allExercises[curImg].style.transform = `translateX(100%)`

		curImg--;
	}

	images[curImg].style.transform = `translateX(0%)`
	allExercises[curImg].style.transform = `translateX(0%)`

	updateActiveSlide(curImg)
	
	updateTapes()
}

workoutInfoTrack.addEventListener('wheel', e => {
	console.log(e.deltaY);
})

nextButton.onmouseup = e => {
	lenis.scrollTo(0, { immediate: true });
	if (curImg == images.length - 1) {
		curImg = -1;
		Array.from(images).forEach(image => {
			image.style.transform = `translateX(100%)`
		})

		Array.from(allExercises).forEach(exercise => {
			exercise.style.transform = `translateX(100%)`
		})
	}
	else {
		allExercises[curImg].style.transform = `translateX(100%)`
	}
	curImg++;
	images[curImg].style.transform = `translateX(0%)`
	allExercises[curImg].style.transform = `translateX(0%)`

	updateActiveSlide(curImg)

	updateTapes()
}

let startedScrolling = false

function toggleFirstCard(card, on) {
	card.style.transform = `translateY(${on ? 0 : 30}%)`
	card.style.opacity = `${on ? 1 : 0}`;
}

const firstCards = document.querySelectorAll(".first-card")
async function enterScroll() {
	if (!startedScrolling) {
		startedScrolling = true;
		track.style.transform = `translateY(-100%)`
		workoutInfo.style.transform = `translateY(-100%)`
		toggleCycleButton(false)
		toggleIndexText(false)
		toggleDwTapes(false)
		await sleep(500)
		toggleFirstCard(firstCards[curImg], true)
		exitImg.style.transform = `scale(1)`
	}
}

Array.from(dwTapeContent).forEach(text => {
	text.onmouseup = enterScroll
});

const takemeup = document.getElementById("take-me-up-yabo-3amo")
const takemeupImg = document.getElementById("take-me-up-yabo-3amo-img")

function takemeupVisibility(scroll) {
	takemeupImg.style.transform = `translateY(${scroll > 0 ? 0 : 100}%)`
}

takemeup.onmouseup = e => {
	curScroll = 0
	takemeupVisibility(0)
	goingUp = true;
	lenis.scrollTo(0)
}

const exit = document.getElementById("exit-workout")
const exitImg = document.getElementById("exit-workout-img")
exit.onmouseup = async e => {
	startedScrolling = false
	track.style.transform = `translateY(0%)`
	workoutInfo.style.transform = `translateY(0%)`
	exitImg.style.transform = `scale(0)`
	takemeupVisibility(0)
	lenis.scrollTo(0)
	await sleep(500)
	toggleCycleButton(true)
	toggleIndexText(true)
	toggleDwTapes(true)
	toggleFirstCard(firstCards[curImg], false)
}

const progressBar = document.getElementById("progressBar");

let goingUp = false
lenis.on('scroll', (e) => {
	console.log("huh")
	if (goingUp && e.scroll > prevScroll)
		goingUp = false;

	if (!goingUp && startedScrolling)
		takemeupVisibility(e.scroll)

	prevScroll = e.scroll;
	progressBar.style.height = `${e.progress * 100}%`
})