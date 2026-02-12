// Week-based workout management
let currentWeek = null;
let completedWeeks = new Set();
const DAYS_PER_WEEK = 5;
let currentWorkoutPlanId = null;

// Load completed weeks from localStorage (scoped to current workout plan)
function loadCompletedWeeks() {
  // Get current workout plan ID from window.workoutData
  if (window.workoutData && window.workoutData.workoutPlanId) {
    currentWorkoutPlanId = window.workoutData.workoutPlanId;
  }
  
  if (!currentWorkoutPlanId) {
    completedWeeks = new Set();
    return;
  }
  
  const storageKey = `completed_weeks_${currentWorkoutPlanId}`;
  const stored = localStorage.getItem(storageKey);
  if (stored) {
    try {
      const data = JSON.parse(stored);
      // Verify this is for the current workout plan
      if (data.workoutPlanId === currentWorkoutPlanId) {
        completedWeeks = new Set(data.weeks || []);
      } else {
        // Different workout plan, reset
        completedWeeks = new Set();
        localStorage.removeItem(storageKey);
      }
    } catch (e) {
      console.error('Error loading completed weeks:', e);
      completedWeeks = new Set();
    }
  } else {
    completedWeeks = new Set();
  }
}

// Save completed weeks to localStorage (scoped to current workout plan)
function saveCompletedWeeks() {
  if (!currentWorkoutPlanId) return;
  
  const storageKey = `completed_weeks_${currentWorkoutPlanId}`;
  const data = {
    workoutPlanId: currentWorkoutPlanId,
    weeks: Array.from(completedWeeks)
  };
  localStorage.setItem(storageKey, JSON.stringify(data));
}

// Check if all weeks are completed
function areAllWeeksCompleted() {
  if (!window.workoutData || !window.workoutData.workoutsByWeek) return false;
  const totalWeeks = Object.keys(window.workoutData.workoutsByWeek).length;
  return completedWeeks.size >= totalWeeks && totalWeeks > 0;
}

// Unlock all weeks (when all are completed)
function unlockAllWeeks() {
  completedWeeks.clear();
  saveCompletedWeeks();
  renderWeeks();
  // Show "Finish Current Plan" button after unlocking
  addFinishPlanButton();
}

// Render week selection view
function renderWeeks() {
  const weeksGrid = document.getElementById('weeks-grid');
  if (!weeksGrid || !window.workoutData || !window.workoutData.workoutsByWeek) return;
  
  const allCompleted = areAllWeeksCompleted();
  const weeks = window.workoutData.workoutsByWeek;
  const weekNumbers = Object.keys(weeks).map(Number).sort((a, b) => a - b);
  
  weeksGrid.innerHTML = '';
  
  if (weekNumbers.length === 0) {
    weeksGrid.innerHTML = '<div class="week-empty">No weeks found yet.</div>';
    return;
  }
  
  weekNumbers.forEach(weekNum => {
    const isCompleted = completedWeeks.has(weekNum);
    const isLocked = isCompleted && !allCompleted;
    const weekDays = weeks[weekNum];
    const dayCount = Object.keys(weekDays).length;
    
    const weekCard = document.createElement('div');
    weekCard.className = `week-card ${isLocked ? 'locked' : ''} ${isCompleted ? 'completed' : ''}`;
    weekCard.dataset.week = weekNum;
    
    weekCard.innerHTML = `
      <div class="week-card-header">
        <h2>Week ${weekNum}</h2>
        ${isCompleted ? '<span class="week-checkmark">✓</span>' : ''}
      </div>
      <div class="week-card-body">
        <p class="week-days">${dayCount} Days</p>
        ${isLocked ? '<p class="week-locked-text">Completed</p>' : '<p class="week-status">Click to view workouts</p>'}
      </div>
      <div class="week-card-actions">
        ${!isLocked ? '<button class="btn-view-week">View Week</button>' : ''}
        ${!isLocked && !isCompleted ? '<button class="btn-complete-week-card">Complete Week</button>' : ''}
      </div>
    `;
    
    if (!isLocked) {
      const viewBtn = weekCard.querySelector('.btn-view-week');
      const completeBtn = weekCard.querySelector('.btn-complete-week-card');

      viewBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        viewWeek(weekNum);
      });
      
      completeBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        completeWeek(weekNum);
      });

      // Card click -> view week (unless clicking buttons)
      weekCard.addEventListener('click', (e) => {
        if (!e.target.closest('button')) viewWeek(weekNum);
      });
    }
    
    weeksGrid.appendChild(weekCard);
  });
  
  // Force immediate visual update by triggering a reflow
  if (weeksGrid.children.length > 0) {
    void weeksGrid.offsetHeight; // Trigger reflow
  }
}

// View a specific week's workouts
function viewWeek(weekNum) {
  currentWeek = weekNum;
  const weeksSelection = document.getElementById('weeks-selection');
  const imageTrack = document.getElementById('image-track');
  const workoutInfo = document.getElementById('workout-info');
  
  if (!weeksSelection || !imageTrack || !workoutInfo) return;
  
  // Hide week selection, show workout view
  weeksSelection.style.display = 'none';
  imageTrack.style.display = 'block';
  workoutInfo.style.display = 'block';
  
  // Get workouts for this week
  const weekData = window.workoutData.workoutsByWeek[weekNum];
  if (!weekData) return;
  
  // Reorganize workouts for this week (days 1-5 of the week)
  const weekWorkouts = {};
  const weekDays = Object.keys(weekData).map(Number).sort((a, b) => a - b);
  
  weekDays.forEach((originalDay, index) => {
    const weekDay = index + 1; // Day 1-5 within the week
    weekWorkouts[weekDay] = weekData[originalDay];
  });
  
  // Update the workout display for this week
  updateWorkoutDisplay(weekWorkouts);
  
  // Add "Complete Week" button if not already completed
  if (!completedWeeks.has(weekNum)) {
    addCompleteWeekButton(weekNum);
  }
  
  // Ensure back to weeks button is visible
  ensureBackToWeeksButton();
  
  // Initialize workout navigation for this week
  initializeWeekNavigation(weekWorkouts);
}

// Update workout display with week-specific data
function updateWorkoutDisplay(weekWorkouts) {
  // Update day texts
  const dayTextsContainer = document.getElementById('day-texts-container');
  const workoutTextsContainer = document.getElementById('workouts-texts-container');
  const indexText = document.getElementById('index-text');
  
  if (dayTextsContainer) {
    const dayContent = dayTextsContainer.querySelector('.dw-tape-content');
    if (dayContent) {
      dayContent.innerHTML = '';
      for (let i = 1; i <= 5; i++) {
        const h1 = document.createElement('h1');
        h1.textContent = `DAY ${i}`;
        dayContent.appendChild(h1);
      }
    }
  }
  
  if (workoutTextsContainer) {
    const workoutContent = workoutTextsContainer.querySelector('.dw-tape-content');
    if (workoutContent) {
      workoutContent.innerHTML = '';
      for (let i = 1; i <= 5; i++) {
        const h2 = document.createElement('h2');
        const dayWorkouts = weekWorkouts[i] || [];
        h2.textContent = getDayMuscleGroup(dayWorkouts);
        workoutContent.appendChild(h2);
      }
    }
  }
  
  if (indexText) {
    const tapeContent = indexText.querySelector('.tape-content');
    if (tapeContent) {
      tapeContent.innerHTML = '';
      for (let i = 1; i <= 5; i++) {
        const h1 = document.createElement('h1');
        h1.textContent = i;
        tapeContent.appendChild(h1);
      }
    }
    const totalH1 = indexText.querySelector('h1:last-of-type');
    if (totalH1) {
      totalH1.textContent = '5';
    }
  }
  
  // Update workout info track
  const workoutInfoTrack = document.getElementById('workout-info-track');
  if (workoutInfoTrack) {
    workoutInfoTrack.innerHTML = '';
    
    for (let day = 1; day <= 5; day++) {
      const exercisesDiv = document.createElement('div');
      exercisesDiv.className = 'exercises';
      exercisesDiv.dataset.day = day;
      
      const dayWorkouts = weekWorkouts[day] || [];
      
      if (dayWorkouts.length > 0) {
        dayWorkouts.forEach((exercise, index) => {
          const card = document.createElement('div');
          card.className = `card ${index === 0 ? 'first-card' : ''}`;
          
          card.innerHTML = `
            <div class="left">
              <img class="workout-visual" alt="Not Found" onerror="this.src='icons/no-visual.png';" 
                   src="workout-visuals/${exercise.Name.toLowerCase().replace(/\s+/g, '-')}.gif">
            </div>
            <div class="right">
              <div class="sets-reps">
                <p>${exercise.sets}x${exercise.reps}</p>
              </div>
              <div class="workout-name">
                <p>${exercise.Name.toUpperCase()}</p>
              </div>
              <div class="target-muscles">
                <p>Primary: ${exercise.Target_Muscle_Group}</p>
                <p>Secondary: ${exercise.Secondary_Muscles}.</p>
              </div>
              <div class="tips-hints">
                <div>
                  <p><b>SETUP</b></p>
                  <p>${exercise.Instuctions || ''}</p>
                  <p><b>EXECUTION</b></p>
                  <p></p>
                  <p><b>STRENGTH/SAFETY</b></p>
                  <p>${exercise.notes || ''}</p>
                </div>
              </div>
            </div>
          `;
          
          exercisesDiv.appendChild(card);
        });
      } else {
        // Rest day
        const card = document.createElement('div');
        card.className = 'card first-card';
        card.innerHTML = `
          <div class="left"></div>
          <div class="right">
            <div class="workout-name">
              <p>REST DAY</p>
            </div>
          </div>
        `;
        exercisesDiv.appendChild(card);
      }
      
      workoutInfoTrack.appendChild(exercisesDiv);
    }
  }
}

// Get muscle group for a day
function getDayMuscleGroup(dayExercises) {
  if (!dayExercises || dayExercises.length === 0) return 'REST';
  const muscles = [...new Set(dayExercises.map(ex => ex.Target_Muscle_Group))];
  return muscles.join(', ');
}

// Add "Complete Week" button
function addCompleteWeekButton(weekNum) {
  // Remove existing button if any
  const existing = document.getElementById('complete-week-btn');
  if (existing) existing.remove();
  
  const workoutTextsMain = document.getElementById('workout-texts-main');
  if (!workoutTextsMain) return;
  
  const completeBtn = document.createElement('button');
  completeBtn.id = 'complete-week-btn';
  completeBtn.className = 'btn-complete-week';
  completeBtn.textContent = 'Complete Week';
  completeBtn.addEventListener('click', () => completeWeek(weekNum));
  
  // Insert at the end of workout-texts-main (after the day and workout text containers)
  workoutTextsMain.appendChild(completeBtn);
}

// Complete a week
function completeWeek(weekNum) {
  if (completedWeeks.has(weekNum)) return;
  
  completedWeeks.add(weekNum);
  saveCompletedWeeks();
  
  // Show success message
  showToast(`Week ${weekNum} completed! ✓`, 'success');
  
  // Check if we're currently viewing a week (need to return to selection)
  const isViewingWeek = currentWeek !== null;
  
  if (isViewingWeek) {
    // We're viewing a week, return to selection and update
    returnToWeekSelection();
  } else {
    // We're already on week selection, just update the UI immediately
    renderWeeks();
    
    // Force browser repaint
    requestAnimationFrame(() => {
      if (areAllWeeksCompleted()) {
        addFinishPlanButton();
      }
    });
  }
  
  // Check if all weeks are completed
  if (areAllWeeksCompleted()) {
    // Unlock all weeks after a short delay
    setTimeout(() => {
      unlockAllWeeks();
      showToast('🎉 Congratulations! You\'ve completed all weeks! All weeks are now unlocked for review.', 'success');
    }, 800);
  }
}

// Return to week selection
function returnToWeekSelection() {
  currentWeek = null;
  const weeksSelection = document.getElementById('weeks-selection');
  const imageTrack = document.getElementById('image-track');
  const workoutInfo = document.getElementById('workout-info');
  
  if (weeksSelection) weeksSelection.style.display = 'block';
  if (imageTrack) imageTrack.style.display = 'none';
  if (workoutInfo) workoutInfo.style.display = 'none';
  
  // Remove complete week button
  const completeBtn = document.getElementById('complete-week-btn');
  if (completeBtn) completeBtn.remove();
  // Remove back button
  const backBtn = document.getElementById('back-to-weeks-btn');
  if (backBtn) backBtn.remove();
  
  // Re-render weeks to update completion status immediately (synchronous)
  renderWeeks();
  
  // Force browser repaint to ensure UI updates are visible
  requestAnimationFrame(() => {
    // Show finish plan button if all weeks are completed
    if (areAllWeeksCompleted()) {
      addFinishPlanButton();
    }
  });
}

// Ensure there is a "Back to Weeks" button inside workout view
function ensureBackToWeeksButton() {
  const workoutTextsMain = document.getElementById('workout-texts-main');
  if (!workoutTextsMain) return;
  if (document.getElementById('back-to-weeks-btn')) return;

  const backBtn = document.createElement('button');
  backBtn.id = 'back-to-weeks-btn';
  backBtn.className = 'btn-back-weeks';
  backBtn.textContent = 'Back to Weeks';
  backBtn.addEventListener('click', () => {
    returnToWeekSelection();
  });

  // Insert at the end of workout-texts-main (after the day and workout text containers)
  workoutTextsMain.appendChild(backBtn);
}

// Store current week workouts for navigation
let currentWeekWorkouts = null;

// Initialize week navigation (modify existing script.js behavior)
function initializeWeekNavigation(weekWorkouts) {
  // Store week workouts for later use
  currentWeekWorkouts = weekWorkouts;
  
  // Wait for DOM to update, then re-initialize script.js variables
  setTimeout(() => {
    const workoutInfoTrack = document.getElementById('workout-info-track');
    if (!workoutInfoTrack) return;
    
    // Re-query all exercises after DOM update
    const allExercises = workoutInfoTrack.querySelectorAll('.exercises');
    const images = document.querySelectorAll('#bg-images .image');
    
    // Reset all exercises to hidden state (day 1 visible, others hidden)
    allExercises.forEach((exercise, i) => {
      if (i === 0) {
        exercise.style.transform = 'translateX(0%)';
        exercise.classList.add('active-slide');
      } else {
        exercise.style.transform = 'translateX(100%)';
        exercise.classList.remove('active-slide');
      }
    });
    
    // Reset images (first image visible, others hidden)
    images.forEach((image, i) => {
      if (i === 0) {
        image.style.transform = 'translateX(0%)';
      } else {
        image.style.transform = 'translateX(100%)';
      }
    });
    
    // Reset curImg to 0 (first day)
    let curImg = 0;
    if (window.curImg !== undefined) {
      window.curImg = 0;
    }
    
    // Update active slide to day 1
    const activeSlide = allExercises[0];
    if (activeSlide) {
      const cards = activeSlide.querySelectorAll('.card, .first-card');
      cards.forEach(card => {
        card.style.transform = 'translateY(0%)';
        card.style.opacity = '1';
      });
    }
    
    // Reset tapes to show day 1
    const dwTapeContent = document.querySelectorAll('.dw-tape-content');
    const tapeContent = document.querySelector('.tape-content');
    const cycleButtons = document.querySelectorAll('.cycleButton');
    
    dwTapeContent.forEach(tape => {
      tape.style.transform = `translateY(0%)`; // Day 1 = 0%
    });
    
    cycleButtons.forEach(button => {
      button.style.transform = `scale(1) rotate(0deg)`; // Day 1 = 0deg
    });
    
    if (tapeContent) {
      tapeContent.style.transform = `translateY(0%)`; // Day 1 = 0%
    }
    
    // Scroll to top
    const lenis = window.lenis;
    if (lenis) {
      lenis.scrollTo(0, { immediate: true });
    }
    
    // Re-initialize prev/next button handlers
    reinitializeNavigationButtons(allExercises, images);
  }, 100);
}

// Re-initialize navigation buttons to work with current week's exercises
function reinitializeNavigationButtons(allExercises, images) {
  const prevButton = document.getElementById('prevButton');
  const nextButton = document.getElementById('nextButton');
  const workoutInfoTrack = document.getElementById('workout-info-track');
  
  if (!prevButton || !nextButton || !workoutInfoTrack) return;
  
  // Remove old handlers by cloning and replacing
  const newPrevButton = prevButton.cloneNode(true);
  const newNextButton = nextButton.cloneNode(true);
  prevButton.parentNode.replaceChild(newPrevButton, prevButton);
  nextButton.parentNode.replaceChild(newNextButton, nextButton);
  
  // Get fresh references
  const lenis = window.lenis || new Lenis({ autoRaf: true, wrapper: workoutInfoTrack });
  
  let curImg = 0;
  
  // Helper to update active slide
  const updateActiveSlide = (index) => {
    allExercises.forEach((exercise, i) => {
      exercise.classList.remove('active-slide');
      if (i === index) {
        exercise.classList.add('active-slide');
      }
    });
    
    const activeSlide = allExercises[index];
    if (activeSlide) {
      const cards = activeSlide.querySelectorAll('.card, .first-card');
      cards.forEach(card => {
        card.style.transform = 'translateY(0%)';
        card.style.opacity = '1';
      });
    }
  };
  
  // Helper to update tapes
  const updateTapes = () => {
    const dwTapeContent = document.querySelectorAll('.dw-tape-content');
    const tapeContent = document.querySelector('.tape-content');
    const cycleButtons = document.querySelectorAll('.cycleButton');
    
    dwTapeContent.forEach(tape => {
      tape.style.transform = `translateY(${-200 * curImg}%)`;
    });
    
    cycleButtons.forEach(button => {
      button.style.transform = `scale(1) rotate(${90 * curImg}deg)`;
    });
    
    if (tapeContent) {
      tapeContent.style.transform = `translateY(${-200 * curImg}%)`;
    }
  };
  
  // Prev button handler
  newPrevButton.onmouseup = (e) => {
    lenis.scrollTo(0, { immediate: true });
    if (curImg === 0) {
      curImg = allExercises.length - 1;
      images.forEach(image => {
        image.style.transform = 'translateX(0%)';
      });
      allExercises.forEach(exercise => {
        exercise.style.transform = 'translateX(100%)';
      });
    } else {
      images[curImg].style.transform = 'translateX(100%)';
      allExercises[curImg].style.transform = 'translateX(100%)';
      curImg--;
    }
    
    images[curImg].style.transform = 'translateX(0%)';
    allExercises[curImg].style.transform = 'translateX(0%)';
    updateActiveSlide(curImg);
    updateTapes();
  };
  
  // Next button handler
  newNextButton.onmouseup = (e) => {
    lenis.scrollTo(0, { immediate: true });
    if (curImg === allExercises.length - 1) {
      curImg = -1;
      images.forEach(image => {
        image.style.transform = 'translateX(100%)';
      });
      allExercises.forEach(exercise => {
        exercise.style.transform = 'translateX(100%)';
      });
    } else {
      allExercises[curImg].style.transform = 'translateX(100%)';
    }
    curImg++;
    images[curImg].style.transform = 'translateX(0%)';
    allExercises[curImg].style.transform = 'translateX(0%)';
    updateActiveSlide(curImg);
    updateTapes();
  };
  
  // Update global curImg
  window.curImg = curImg;
  
  // Initialize first slide
  updateActiveSlide(0);
  updateTapes();
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
  // Get workout plan ID first
  if (window.workoutData && window.workoutData.workoutPlanId) {
    currentWorkoutPlanId = window.workoutData.workoutPlanId;
  }
  
  // Load completed weeks (will check if they match current plan)
  loadCompletedWeeks();
  
  // Wait for workout data to be available
  if (window.workoutData && window.workoutData.hasWorkoutPlan) {
    renderWeeks();
    // Check if all weeks are completed and show finish button
    if (areAllWeeksCompleted()) {
      setTimeout(() => {
        addFinishPlanButton();
      }, 300);
    }
  } else {
    // Wait a bit for data to load
    setTimeout(() => {
      // Re-check workout plan ID in case it wasn't available initially
      if (window.workoutData && window.workoutData.workoutPlanId) {
        const newPlanId = window.workoutData.workoutPlanId;
        // If plan ID changed, reset completed weeks
        if (currentWorkoutPlanId && currentWorkoutPlanId !== newPlanId) {
          completedWeeks.clear();
        }
        currentWorkoutPlanId = newPlanId;
        loadCompletedWeeks();
      }
      
      if (window.workoutData && window.workoutData.hasWorkoutPlan) {
        renderWeeks();
        // Check if all weeks are completed and show finish button
        if (areAllWeeksCompleted()) {
          setTimeout(() => {
            addFinishPlanButton();
          }, 300);
        }
      }
    }, 500);
  }
});

// Make returnToWeekSelection globally accessible
window.returnToWeekSelection = returnToWeekSelection;

// Add "Finish Current Plan" button when all weeks are completed
function addFinishPlanButton() {
  // Remove existing button if any
  const existing = document.getElementById('finish-plan-btn');
  if (existing) existing.remove();
  
  const weeksSelection = document.getElementById('weeks-selection');
  if (!weeksSelection) return;
  
  const finishBtn = document.createElement('button');
  finishBtn.id = 'finish-plan-btn';
  finishBtn.className = 'btn-finish-plan';
  finishBtn.textContent = 'Finish Current Plan';
  finishBtn.addEventListener('click', finishCurrentPlan);
  
  // Insert at the top of weeks-selection, after the container
  const weeksContainer = weeksSelection.querySelector('.weeks-container');
  if (weeksContainer) {
    weeksContainer.insertBefore(finishBtn, weeksContainer.firstChild);
  } else {
    weeksSelection.insertBefore(finishBtn, weeksSelection.firstChild);
  }
}

// Finish current workout plan
async function finishCurrentPlan() {
  if (!confirm('Are you sure you want to finish this workout plan? Your coach will be notified to create a new plan for you.')) {
    return;
  }
  
  const finishBtn = document.getElementById('finish-plan-btn');
  if (finishBtn) {
    finishBtn.disabled = true;
    finishBtn.textContent = 'Finishing...';
  }
  
  try {
    const response = await fetch('/a/api/member/workoutprogram/complete.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      }
    });
    
    const data = await response.json();
    
    if (data.success) {
      showToast('✅ Workout plan completed! Your coach will create a new plan for you.', 'success');
      
      // Clear completed weeks from localStorage for this workout plan
      if (currentWorkoutPlanId) {
        const storageKey = `completed_weeks_${currentWorkoutPlanId}`;
        localStorage.removeItem(storageKey);
      }
      // Also clear the old generic key if it exists
      localStorage.removeItem('completed_weeks');
      completedWeeks.clear();
      currentWorkoutPlanId = null;
      
      // Immediately update UI to show empty state or redirect
      const weeksSelection = document.getElementById('weeks-selection');
      if (weeksSelection) {
        const weeksContainer = weeksSelection.querySelector('.weeks-container');
        if (weeksContainer) {
          weeksContainer.innerHTML = `
            <div style="text-align: center; padding: 3rem;">
              <h1 class="weeks-title" style="color: var(--accent-secondary);">Plan Completed!</h1>
              <p class="weeks-subtitle">Your coach will create a new plan for you soon.</p>
              <p style="margin-top: 2rem; color: var(--text-secondary-clr);">Redirecting to home page...</p>
            </div>
          `;
        }
      }
      
      // Redirect to home after a short delay
      setTimeout(() => {
        window.location.href = '../Home Full/Home.php';
      }, 1500);
    } else {
      showToast(data.error || 'Failed to complete workout plan', 'error');
      if (finishBtn) {
        finishBtn.disabled = false;
        finishBtn.textContent = 'Finish Current Plan';
      }
    }
  } catch (error) {
    console.error('Error finishing plan:', error);
    showToast('An error occurred while finishing the plan. Please try again.', 'error');
    if (finishBtn) {
      finishBtn.disabled = false;
      finishBtn.textContent = 'Finish Current Plan';
    }
  }
}

