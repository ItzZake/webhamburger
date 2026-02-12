// Initialize AOS
if (typeof AOS !== 'undefined') {
  AOS.init({
    duration: 800,
    once: true,
    offset: 100
  });
}

// Theme and sidebar are handled by dashboard.js and dashboard_loader.js

// Blob movement effect
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

// Global state
let mealPlanData = null;
let consumedMeals = new Set();
let currentCalories = 0;
let currentCarbs = 0;
let currentProtein = 0;
let currentFats = 0;

// Load meal plan from API
async function loadMealPlan() {
  try {
    // Use absolute path from root
    const apiPath = '/a/api/member/mealplan/get.php';
    
    const response = await fetch(apiPath, {
      method: 'GET',
      credentials: 'include',
      headers: {
        'Accept': 'application/json'
      }
    });
    
    if (!response.ok) {
      // Try to get error message from response
      let errorMsg = `API error ${response.status}`;
      try {
        const errorData = await response.json();
        errorMsg = errorData.error || errorMsg;
        console.error("API Error Details:", errorData);
      } catch (e) {
        // If response is not JSON, try to get text
        try {
          const text = await response.text();
          console.error("API Error Response (text):", text);
          errorMsg = text || response.statusText || errorMsg;
        } catch (e2) {
          errorMsg = response.statusText || errorMsg;
        }
      }
      throw new Error(errorMsg);
    }
    
    const result = await response.json();
    
    if (!result.success) {
      console.error("Failed to load meal plan:", result.error);
      // Check if user has a nutritionist but no plan yet
      if (result.has_nutritionist && result.message) {
        showEmptyState(result.message, true);
      } else {
        showEmptyState(result.error || "No meal plan found");
      }
      return;
    }
    
    mealPlanData = result;
    
    // Ensure target calories is a number (not cumulative) - it's the DAILY target
    if (mealPlanData.meal_plan && mealPlanData.meal_plan.target_calories) {
      mealPlanData.meal_plan.target_calories = parseInt(mealPlanData.meal_plan.target_calories);
    }
    
    // Check if it's a new day - reset if needed
    const today = new Date().toISOString().split('T')[0];
    const lastDate = localStorage.getItem('last_calorie_date');
    
    // If it's a new day, reset everything
    if (lastDate !== today) {
      // Clear old data
      const keys = Object.keys(localStorage);
      keys.forEach(key => {
        if (key.startsWith('consumed_meals_')) {
          localStorage.removeItem(key);
        }
      });
      // Reset current values
      consumedMeals = new Set();
      currentCalories = 0;
      currentCarbs = 0;
      currentProtein = 0;
      currentFats = 0;
      targetReachedMessageShown = false;
      // Save new date
      localStorage.setItem('last_calorie_date', today);
    } else {
      // Load consumed meals from localStorage for today
      const stored = localStorage.getItem(`consumed_meals_${today}`);
      if (stored) {
        const parsed = JSON.parse(stored);
        consumedMeals = new Set(parsed.mealIds || []);
        currentCalories = parsed.calories || 0;
        currentCarbs = parsed.carbs || 0;
        currentProtein = parsed.protein || 0;
        currentFats = parsed.fats || 0;
      } else {
        // Initialize from API consumed meals (if any)
        if (result.consumed_meal_ids) {
          consumedMeals = new Set(result.consumed_meal_ids);
        }
      }
    }
    
    // Update calorie bar
    updateCalorieBar();
    
    // Render meals
    renderMeals();
    
    // If limit is exceeded (target + 100), disable meals and show message
    const target = parseInt(mealPlanData?.meal_plan?.target_calories) || 2000;
    const tolerance = 150;
    const maxAllowed = target + tolerance;
    if (currentCalories > maxAllowed) {
      disableAllMealItems();
      showTargetReachedMessage(true);
    }
    
  } catch (error) {
    console.error("Error loading meal plan:", error);
    showEmptyState("Failed to load meal plan. Please try again later.");
  }
}

// Update calorie bar
function updateCalorieBar() {
  if (!mealPlanData) return;
  
  // Get daily target from meal plan (this is the DAILY target, not cumulative)
  // Ensure it's parsed as an integer and not cumulative
  let target = parseInt(mealPlanData.meal_plan?.target_calories) || 2000;
  
  // Safety check: if target seems too high (likely cumulative), use default
  if (target > 5000) {
    console.warn(`Target calories (${target}) seems too high, using default 2000`);
    target = 2000;
  }
  
  // Ensure we're using daily calories (reset at midnight)
  const today = new Date().toISOString().split('T')[0];
  const lastDate = localStorage.getItem('last_calorie_date');
  
  // If it's a new day, reset calories
  if (lastDate !== today) {
    currentCalories = 0;
    currentCarbs = 0;
    currentProtein = 0;
    currentFats = 0;
    consumedMeals = new Set();
    targetReachedMessageShown = false;
    localStorage.setItem('last_calorie_date', today);
    // Clear old data
    const keys = Object.keys(localStorage);
    keys.forEach(key => {
      if (key.startsWith('consumed_meals_') && key !== `consumed_meals_${today}`) {
        localStorage.removeItem(key);
      }
    });
  }
  
  // Cap consumed at target for display (don't show more than target)
  const consumed = Math.min(currentCalories, target);
  const percentage = target > 0 ? Math.min((consumed / target) * 100, 100) : 0;
  
  const progressBar = document.getElementById("calorie-progress");
  const consumedEl = document.getElementById("calories-consumed");
  const targetEl = document.getElementById("calories-target");
  
  if (progressBar) {
    progressBar.style.width = `${percentage}%`;
    progressBar.textContent = percentage > 5 ? `${Math.round(percentage)}%` : '';
    
    // Change color when target is reached
    if (currentCalories >= target) {
      progressBar.style.background = 'linear-gradient(135deg, #10b981, #059669)'; // Green
    } else {
      progressBar.style.background = 'linear-gradient(135deg, var(--accent-clr), var(--accent-secondary))';
    }
  }
  
  if (consumedEl) consumedEl.textContent = consumed;
  if (targetEl) targetEl.textContent = target; // Always show the daily target (2000, not cumulative)
  
  // Update macros
  const carbsEl = document.getElementById("carbs-consumed");
  const proteinEl = document.getElementById("protein-consumed");
  const fatsEl = document.getElementById("fats-consumed");
  
  if (carbsEl) carbsEl.textContent = `${currentCarbs}g`;
  if (proteinEl) proteinEl.textContent = `${currentProtein}g`;
  if (fatsEl) fatsEl.textContent = `${currentFats}g`;
  
  // Show congratulations message if target is reached (show button when target is reached or exceeded)
  const targetCal = parseInt(mealPlanData?.meal_plan?.target_calories) || 2000;
  const tolerance = 150;
  const maxAllowed = targetCal + tolerance;
  // Show message when target is reached (even if within tolerance)
  showTargetReachedMessage(currentCalories >= targetCal);
}

// Render meals
function renderMeals() {
  if (!mealPlanData || !mealPlanData.meals) return;
  
  const container = document.getElementById("meal-categories");
  if (!container) return;
  
  container.innerHTML = "";
  
  const mealOrder = ['Breakfast', 'Lunch', 'Dinner', 'Pre-Workout', 'Post-Workout', 'Snacks'];
  
  mealOrder.forEach((category, idx) => {
    if (!mealPlanData.meals[category] || mealPlanData.meals[category].length === 0) return;
    
    const meals = mealPlanData.meals[category];
    
    const card = document.createElement("div");
    card.className = "meal-card";
    card.setAttribute("data-aos", "fade-up");
    card.setAttribute("data-aos-delay", String(idx * 100));
    card.setAttribute("data-category", category);
    
    // Check if all meals in this category are consumed
    const allConsumed = meals.every(m => consumedMeals.has(m.meal_id));
    if (allConsumed && meals.length > 0) {
      card.classList.add("consumed");
    }
    
    // Get icon SVG based on category
    const iconSvg = getCategoryIcon(category);
    
    card.innerHTML = `
      <div class="meal-icon">
        ${iconSvg}
      </div>
      <h3 class="meal-name">${category}</h3>
      <p class="meal-description">${getCategoryDescription(category)}</p>
      <button class="btn-view-meals" data-category="${category}">View Meals</button>
    `;
    
    // Add click handler to "View Meals" button - opens modal
    const viewMealsBtn = card.querySelector('.btn-view-meals');
    if (viewMealsBtn) {
      viewMealsBtn.addEventListener('click', function() {
        showMealsModal(category, meals);
      });
    }
    
    
    container.appendChild(card);
  });
}

// Toggle meal consumed state (only allows checking, not unchecking)
function toggleMealConsumed(mealId, calories, element, mealData = null) {
  // Ensure we're using daily target
  const target = parseInt(mealPlanData?.meal_plan?.target_calories) || 2000;
  const tolerance = 150; // Allow up to 150 calories over target
  const maxAllowed = target + tolerance;
  
  // Check if already consumed - don't allow unchecking
  if (consumedMeals.has(mealId)) {
    if (typeof showToast === 'function') {
      showToast("You've already logged this meal. Use 'Start Next Day' to reset.", "info");
    }
    return;
  }
  
  // Check if adding this meal would exceed the maximum allowed (target + 150 tolerance)
  const newCalories = currentCalories + calories;
  if (newCalories > maxAllowed) {
    // CRITICAL: Exit immediately - do NOT mark as consumed, do NOT update any state
    showTargetReachedMessage(currentCalories >= target);
    // Disable all meal items visually
    disableAllMealItems();
    if (typeof showToast === 'function') {
      showToast(`Cannot mark this meal as consumed. It would exceed your daily limit of ${maxAllowed} calories (target: ${target} + ${tolerance} tolerance).`, "error");
    }
    return; // EXIT IMMEDIATELY - no state changes occur after this point
  }
  
  // Get macros from element dataset or from mealData parameter
  let protein = 0;
  let carbs = 0;
  let fats = 0;
  
  if (mealData) {
    // Use provided meal data
    protein = parseInt(mealData.protein || 0);
    carbs = parseInt(mealData.carbs || 0);
    fats = parseInt(mealData.fats || 0);
  } else if (element && element.dataset) {
    // Fall back to element dataset if available
    protein = parseInt(element.dataset.protein || 0);
    carbs = parseInt(element.dataset.carbs || 0);
    fats = parseInt(element.dataset.fats || 0);
  }
  
  consumedMeals.add(mealId);
  currentCalories = newCalories;
  currentProtein += protein;
  currentCarbs += carbs;
  currentFats += fats;
  
  // Update element if it exists
  if (element) {
    element.classList.add('consumed');
    const checkmark = document.createElement('span');
    checkmark.className = 'checkmark';
    checkmark.textContent = '✓';
    element.appendChild(checkmark);
  }
  
  // Check if we've exceeded the maximum allowed (target + tolerance)
  if (currentCalories > maxAllowed) {
    showTargetReachedMessage(true);
    // Disable all remaining unchecked meals
    disableAllMealItems();
  } else if (currentCalories >= target && currentCalories <= maxAllowed) {
    // Within tolerance - show a subtle message but don't disable
    // User can still add meals up to the tolerance limit
  }
  
  // Update category card consumed state if element exists
  if (element) {
    const categoryCard = element.closest('.meal-card');
    if (categoryCard) {
      const category = categoryCard.dataset.category;
      const allMealsInCategory = Array.from(categoryCard.querySelectorAll('.meal-item'))
        .map(el => parseInt(el.dataset.mealId));
      const allConsumed = allMealsInCategory.every(id => consumedMeals.has(id));
      
      if (allConsumed && allMealsInCategory.length > 0) {
        categoryCard.classList.add('consumed');
      } else {
        categoryCard.classList.remove('consumed');
      }
    }
  }
  
  // Save to localStorage
  const today = new Date().toISOString().split('T')[0];
  localStorage.setItem(`consumed_meals_${today}`, JSON.stringify({
    mealIds: Array.from(consumedMeals),
    calories: currentCalories,
    carbs: currentCarbs,
    protein: currentProtein,
    fats: currentFats
  }));
  
  // Update calorie bar
  updateCalorieBar();
  
  // Log to API (optional, non-blocking)
  logMealToAPI(mealId, true);
}

// Log meal to API
async function logMealToAPI(mealId, consumed) {
  if (!mealPlanData) return;
  
  try {
    const apiPath = '/a/api/member/mealplan/log_meal.php';
    await fetch(apiPath, {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      credentials: 'same-origin',
      body: JSON.stringify({
        meal_id: mealId,
        meal_plan_id: mealPlanData.meal_plan.id,
        consumed: consumed
      })
    });
  } catch (error) {
    console.warn("Failed to log meal to API:", error);
  }
}

// Get category icon
function getCategoryIcon(category) {
  const icons = {
    'Breakfast': '<svg xmlns="http://www.w3.org/2000/svg" height="40px" viewBox="0 -960 960 960" width="40px" fill="currentColor"><path d="M160-120v-80h80v-240q0-50 25.5-95T327-606q-2-25 15.5-42.5T385-666q27 0 45.5 17.5T445-606q43 21 68.5 66T539-445v240h80v80H160Zm60-80h520v-240q0-40-20-75t-55-56q-35-21-75-21-40 0-75 21t-55 56q-20 35-20 75v240Zm170-240v-240h80v240h-80Zm220 240v-240h80v240h-80Zm-440 80h640v-80H170v80Z"/></svg>',
    'Lunch': '<svg xmlns="http://www.w3.org/2000/svg" height="40px" viewBox="0 -960 960 960" width="40px" fill="currentColor"><path d="M280-80q-33 0-56.5-23.5T200-160v-320q0-33 23.5-56.5T280-560h40v-240q0-33 23.5-56.5T400-880q33 0 56.5 23.5T480-800v240h80v-240q0-33 23.5-56.5T640-880q33 0 56.5 23.5T720-800v240h40q33 0 56.5 23.5T840-480v320q0 33-23.5 56.5T760-80H280Zm0-80h480v-320H280v320Zm100-80h80v-160h-80v160Zm160 0h80v-160h-80v160Zm160 0h80v-160h-80v160Z"/></svg>',
    'Dinner': '<svg xmlns="http://www.w3.org/2000/svg" height="40px" viewBox="0 -960 960 960" width="40px" fill="currentColor"><path d="M440-80q-25 0-42.5-17.5T380-140q0-25 17.5-42.5T440-200q25 0 42.5 17.5T500-140q0 25-17.5 42.5T440-80Zm0-120q-58 0-99-41t-41-99q0-58 41-99t99-41q58 0 99 41t41 99q0 58-41 99t-99 41Zm0-80q33 0 56.5-23.5T520-360q0-33-23.5-56.5T440-440q-33 0-56.5 23.5T360-360q0 33 23.5 56.5T440-280Zm0-280q-92 0-156-64t-64-156h80q0 66 47 113t113 47q66 0 113-47t47-113h80q0 92-64 156t-156 64Z"/></svg>',
    'Pre-Workout': '<svg xmlns="http://www.w3.org/2000/svg" height="40px" viewBox="0 -960 960 960" width="40px" fill="currentColor"><path d="M440-280q17 0 28.5-11.5T480-320q0-17-11.5-28.5T440-360q-17 0-28.5 11.5T400-320q0 17 11.5 28.5T440-280Zm-80-160q0-50 35-85t85-35q50 0 85 35t35 85q0 50-35 85t-85 35q-50 0-85-35t-35-85Zm160-160q17 0 28.5-11.5T600-640q0-17-11.5-28.5T560-680q-17 0-28.5 11.5T520-640q0 17 11.5 28.5T560-600Zm-160 0q17 0 28.5-11.5T440-640q0-17-11.5-28.5T400-680q-17 0-28.5 11.5T360-640q0 17 11.5 28.5T400-600Z"/></svg>',
    'Post-Workout': '<svg xmlns="http://www.w3.org/2000/svg" height="40px" viewBox="0 -960 960 960" width="40px" fill="currentColor"><path d="M480-280q17 0 28.5-11.5T520-320q0-17-11.5-28.5T480-360q-17 0-28.5 11.5T440-320q0 17 11.5 28.5T480-280Zm-80-160q0-50 35-85t85-35q50 0 85 35t35 85q0 50-35 85t-85 35q-50 0-85-35t-35-85Zm160-160q17 0 28.5-11.5T600-640q0-17-11.5-28.5T560-680q-17 0-28.5 11.5T520-640q0 17 11.5 28.5T560-600Zm-160 0q17 0 28.5-11.5T440-640q0-17-11.5-28.5T400-680q-17 0-28.5 11.5T360-640q0 17 11.5 28.5T400-600Z"/></svg>',
    'Snacks': '<svg xmlns="http://www.w3.org/2000/svg" height="40px" viewBox="0 -960 960 960" width="40px" fill="currentColor"><path d="M280-200q-33 0-56.5-23.5T200-280v-400q0-33 23.5-56.5T280-760h480q33 0 56.5 23.5T840-680v400q0 33-23.5 56.5T760-200H280Zm0-80h480v-400H280v400Zm100-80h80v-80h-80v80Zm120 0h80v-80h-80v80Zm120 0h80v-80h-80v80Z"/></svg>'
  };
  return icons[category] || icons['Lunch'];
}

// Get category description
function getCategoryDescription(category) {
  const descriptions = {
    'Breakfast': 'Start your day with energy and nutrition',
    'Lunch': 'Fuel your afternoon performance',
    'Dinner': 'Nighttime recovery nutrition',
    'Pre-Workout': 'Optimize your training performance',
    'Post-Workout': 'Recovery and muscle building nutrition',
    'Snacks': 'Healthy snack options between meals'
  };
  return descriptions[category] || '';
}

// Show target reached message
let targetReachedMessageShown = false;
function showTargetReachedMessage(show) {
  const container = document.getElementById("calorie-bar-container");
  if (!container) return;
  
  // Remove existing message if any
  const existingMsg = document.getElementById('target-reached-message');
  if (existingMsg) {
    existingMsg.remove();
  }
  
  if (show) {
    targetReachedMessageShown = true;
    const target = mealPlanData?.meal_plan?.target_calories || 2000;
    const tolerance = 150;
    const maxAllowed = target + tolerance;
    const isOverLimit = currentCalories > maxAllowed;
    
    const message = document.createElement('div');
    message.id = 'target-reached-message';
    message.style.cssText = `
      margin-top: 20px;
      padding: 20px;
      background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(5, 150, 105, 0.1));
      border: 2px solid #10b981;
      border-radius: 12px;
      text-align: center;
      animation: slideDown 0.5s ease;
    `;
    
    let messageText = '';
    if (isOverLimit) {
      messageText = `
        <div style="font-size: 1.2rem; font-weight: 700; color: #10b981; margin-bottom: 8px;">
          🎉 Congratulations! 🎉
        </div>
        <div style="font-size: 1rem; color: var(--text-clr); margin-bottom: 8px;">
          You've reached your daily calorie goal of ${target} calories!
        </div>
        <div style="font-size: 0.9rem; color: var(--text-secondary-clr); margin-bottom: 16px;">
          Great job today! Keep up the excellent work! 💪
        </div>
      `;
    } else {
      messageText = `
        <div style="font-size: 1.2rem; font-weight: 700; color: #10b981; margin-bottom: 8px;">
          🎯 Daily Goal Reached! 🎯
        </div>
        <div style="font-size: 1rem; color: var(--text-clr); margin-bottom: 8px;">
          You've reached your daily calorie goal of ${target} calories!
        </div>
        <div style="font-size: 0.9rem; color: var(--text-secondary-clr); margin-bottom: 16px;">
          You can still add meals up to ${maxAllowed} calories (${tolerance} calorie tolerance).
        </div>
      `;
    }
    
    message.innerHTML = messageText + `
      <button id="next-day-btn" class="btn-next-day" onclick="advanceToNextDay()">
        Start Next Day
      </button>
    `;
    container.appendChild(message);
  } else {
    targetReachedMessageShown = false;
  }
}

// Disable all meal items when limit is exceeded (only unchecked meals)
function disableAllMealItems() {
  const target = parseInt(mealPlanData?.meal_plan?.target_calories) || 2000;
  const tolerance = 100;
  const maxAllowed = target + tolerance;
  
  const allMealItems = document.querySelectorAll('.meal-item');
  allMealItems.forEach(item => {
    const mealId = parseInt(item.dataset.mealId);
    // Only disable unchecked meals when we've exceeded the limit
    if (!consumedMeals.has(mealId) && currentCalories > maxAllowed) {
      item.classList.add('disabled');
      item.style.opacity = '0.5';
      item.style.cursor = 'not-allowed';
      item.style.pointerEvents = 'none';
      item.title = `Daily calorie limit reached (${maxAllowed} cal). Start next day to continue.`;
    }
  });
}

// Enable all meal items (only unchecked ones)
function enableAllMealItems() {
  const allMealItems = document.querySelectorAll('.meal-item');
  allMealItems.forEach(item => {
    const mealId = parseInt(item.dataset.mealId);
    // Only enable unchecked meals (consumed meals stay consumed)
    if (!consumedMeals.has(mealId)) {
      item.classList.remove('disabled');
      item.style.opacity = '1';
      item.style.cursor = 'pointer';
      item.style.pointerEvents = 'auto';
      item.title = '';
    }
  });
}

// Advance to next day (make it globally accessible)
window.advanceToNextDay = function advanceToNextDay() {
  // Get tomorrow's date
  const tomorrow = new Date();
  tomorrow.setDate(tomorrow.getDate() + 1);
  const tomorrowStr = tomorrow.toISOString().split('T')[0];
  
  // Reset all values
  consumedMeals = new Set();
  currentCalories = 0;
  currentCarbs = 0;
  currentProtein = 0;
  currentFats = 0;
  targetReachedMessageShown = false;
  
  // Update date in localStorage
  localStorage.setItem('last_calorie_date', tomorrowStr);
  
  // Clear old data
  const keys = Object.keys(localStorage);
  keys.forEach(key => {
    if (key.startsWith('consumed_meals_')) {
      localStorage.removeItem(key);
    }
  });
  
  // Remove congratulations message
  const message = document.getElementById('target-reached-message');
  if (message) {
    message.remove();
  }
  
  // Enable all meal items
  enableAllMealItems();
  
  // Update calorie bar
  updateCalorieBar();
  
  // Re-render meals to update consumed states
  renderMeals();
  
  // Show success message
  showToast("Started a new day! Your progress has been reset. Good luck! 🎯", "success");
}

// Show empty state
function showEmptyState(message, isCreating = false) {
  const container = document.getElementById("meal-categories");
  if (!container) return;
  
  const title = isCreating ? "Meal Plan Being Created" : "No Meal Plan Available";
  const icon = isCreating ? "⏳" : "📋";
  
  container.innerHTML = `
    <div style="text-align: center; padding: 4rem 2rem; background: var(--card-bg); border-radius: 18px; margin: 2rem 0; position: relative; z-index: 10; grid-column: 1 / -1;">
      <div style="font-size: 4rem; margin-bottom: 1rem;">${icon}</div>
      <h2 style="color: var(--text-clr); margin-bottom: 1rem;">${title}</h2>
      <p style="color: var(--text-secondary-clr); margin-bottom: 2rem; font-size: 1.1rem; line-height: 1.6;">${message}</p>
      ${isCreating ? `
        <div style="background: rgba(166, 111, 255, 0.1); border: 2px solid var(--accent-clr); border-radius: 12px; padding: 1.5rem; margin-top: 2rem;">
          <p style="color: var(--text-clr); margin: 0; font-weight: 500;">Your nutritionist is working on your personalized meal plan. You'll be notified once it's ready!</p>
        </div>
      ` : `
        <p style="color: var(--text-secondary-clr);">Your nutritionist will create a personalized meal plan for you soon.</p>
      `}
    </div>
  `;
}

// Modal functionality
function showModal(title, content) {
  const modal = document.getElementById("modal");
  const modalTitle = document.getElementById("modal-title");
  const modalBody = document.getElementById("modal-body");
  
  if (modalTitle) modalTitle.textContent = title;
  if (modalBody) modalBody.innerHTML = content;
  if (modal) {
    modal.classList.add("active");
    document.body.style.overflow = "hidden";
  }
}

function closeModal() {
  const modal = document.getElementById("modal");
  if (modal) {
    modal.classList.remove("active");
    document.body.style.overflow = "";
  }
}

// Show meals in modal
function showMealsModal(category, meals) {
  if (!meals || meals.length === 0) {
    showModal(category, '<p style="text-align: center; padding: 2rem; color: var(--text-secondary-clr);">No meals found for this category.</p>');
    return;
  }
  
  // Calculate totals for this category
  let totalCalories = 0;
  let totalProtein = 0;
  let totalCarbs = 0;
  let totalFats = 0;
  
  meals.forEach(meal => {
    totalCalories += meal.calories || 0;
    totalProtein += meal.protein || 0;
    totalCarbs += meal.carbs || 0;
    totalFats += meal.fats || 0;
  });
  
  const target = parseInt(mealPlanData?.meal_plan?.target_calories) || 2000;
  
  const mealsHTML = meals.map(meal => {
    const isConsumed = consumedMeals.has(meal.meal_id);
    const isDisabled = currentCalories >= target && !isConsumed;
    
    const foodItemsHtml = meal.food_items && meal.food_items.length > 0 
      ? `<div style="border-top: 1px solid var(--border-color); padding-top: 1rem; margin-top: 1rem;">
           <strong style="color: var(--text-secondary-clr); font-size: 0.9rem; display: block; margin-bottom: 0.5rem;">Food Items:</strong>
           <ul style="margin: 0; padding-left: 1.5rem; color: var(--text-clr);">
             ${meal.food_items.map(food => `
               <li style="margin-bottom: 0.5rem;">
                 ${food.name} 
                 <span style="color: var(--text-secondary-clr); font-size: 0.85rem;">
                   (${food.servings} serving${food.servings !== 1 ? 's' : ''})
                 </span>
                 <span style="color: var(--accent-clr); font-size: 0.85rem; margin-left: 8px;">
                   - ${food.calories || 0} cal
                 </span>
               </li>
             `).join('')}
           </ul>
         </div>`
      : '';
    
    return `
      <div class="meal-card-modal" style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 1.5rem; margin-bottom: 1rem; ${isConsumed ? 'opacity: 0.7;' : ''} ${isDisabled ? 'opacity: 0.5;' : ''}">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
          <h4 style="color: var(--text-clr); margin: 0; display: flex; align-items: center; gap: 10px;">
            ${meal.name}
            ${isConsumed ? '<span style="color: var(--success-color); font-size: 1.2rem;">✓</span>' : ''}
          </h4>
          <div style="display: flex; gap: 10px; align-items: center;">
            <span style="background: var(--accent-clr); color: white; padding: 4px 12px; border-radius: 6px; font-size: 0.9rem; font-weight: 600;">
              ${meal.calories || 0} cal
            </span>
          </div>
        </div>
        <div style="display: flex; gap: 10px; margin-bottom: 1rem; flex-wrap: wrap;">
          <span style="background: rgba(59, 130, 246, 0.1); color: var(--info-color); padding: 4px 12px; border-radius: 6px; font-size: 0.85rem;">
            P: ${meal.protein || 0}g
          </span>
          <span style="background: rgba(16, 185, 129, 0.1); color: var(--success-color); padding: 4px 12px; border-radius: 6px; font-size: 0.85rem;">
            C: ${meal.carbs || 0}g
          </span>
          <span style="background: rgba(245, 158, 11, 0.1); color: var(--warning-color); padding: 4px 12px; border-radius: 6px; font-size: 0.85rem;">
            F: ${meal.fats || 0}g
          </span>
        </div>
        ${foodItemsHtml}
        ${!isConsumed && !isDisabled ? `
          <button onclick="toggleMealFromModal(${meal.meal_id}, ${meal.calories || 0}, ${meal.protein || 0}, ${meal.carbs || 0}, ${meal.fats || 0})" 
                  style="margin-top: 1rem; padding: 0.5rem 1rem; background: var(--success-color); color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
            Mark as Consumed
          </button>
        ` : ''}
        ${isDisabled && !isConsumed ? `
          <p style="margin-top: 1rem; color: var(--warning-color); font-size: 0.85rem;">
            Daily target reached. Use 'Start Next Day' to continue.
          </p>
        ` : ''}
      </div>
    `;
  }).join('');
  
  const modalContent = `
    <div style="max-width: 800px;">
      <div style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem;">
        <h3 style="color: var(--accent-secondary); margin-top: 0; margin-bottom: 1rem;">${category} Summary</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 1rem;">
          <div style="text-align: center;">
            <div style="color: var(--text-secondary-clr); font-size: 0.9rem; margin-bottom: 0.25rem;">Total Calories</div>
            <div style="color: var(--accent-clr); font-size: 1.5rem; font-weight: 600;">${totalCalories}</div>
          </div>
          <div style="text-align: center;">
            <div style="color: var(--text-secondary-clr); font-size: 0.9rem; margin-bottom: 0.25rem;">Protein</div>
            <div style="color: var(--info-color); font-size: 1.5rem; font-weight: 600;">${totalProtein}g</div>
          </div>
          <div style="text-align: center;">
            <div style="color: var(--text-secondary-clr); font-size: 0.9rem; margin-bottom: 0.25rem;">Carbs</div>
            <div style="color: var(--success-color); font-size: 1.5rem; font-weight: 600;">${totalCarbs}g</div>
          </div>
          <div style="text-align: center;">
            <div style="color: var(--text-secondary-clr); font-size: 0.9rem; margin-bottom: 0.25rem;">Fats</div>
            <div style="color: var(--warning-color); font-size: 1.5rem; font-weight: 600;">${totalFats}g</div>
          </div>
        </div>
      </div>
      
      <div>
        <h3 style="color: var(--accent-secondary); margin-bottom: 1.5rem;">Meals (${meals.length})</h3>
        ${mealsHTML}
      </div>
    </div>
  `;
  
  showModal(category, modalContent);
}

// Toggle meal consumed from modal
function toggleMealFromModal(mealId, calories, protein, carbs, fats) {
  // Check limit BEFORE attempting to mark as consumed
  const target = parseInt(mealPlanData?.meal_plan?.target_calories) || 2000;
  const tolerance = 150;
  const maxAllowed = target + tolerance;
  const newCalories = currentCalories + calories;
  
  // If limit would be exceeded, prevent marking as consumed
  if (newCalories > maxAllowed) {
    // CRITICAL: Exit immediately - do NOT mark as consumed, do NOT update any state
    if (typeof showToast === 'function') {
      showToast(`Cannot mark this meal as consumed. It would exceed your daily limit of ${maxAllowed} calories (target: ${target} + ${tolerance} tolerance).`, "error");
    }
    showTargetReachedMessage(currentCalories >= target);
    disableAllMealItems();
    return; // EXIT IMMEDIATELY - no state changes occur after this point
  }
  
  // Find the meal data from mealPlanData
  let mealData = null;
  if (mealPlanData && mealPlanData.meals) {
    for (const category in mealPlanData.meals) {
      const meal = mealPlanData.meals[category].find(m => m.meal_id === mealId);
      if (meal) {
        mealData = {
          protein: meal.protein || 0,
          carbs: meal.carbs || 0,
          fats: meal.fats || 0
        };
        break;
      }
    }
  }
  
  // Use provided values or meal data
  const mealMacros = mealData || {
    protein: parseInt(protein || 0),
    carbs: parseInt(carbs || 0),
    fats: parseInt(fats || 0)
  };
  
  // Try to find the meal element in the DOM
  const mealElement = document.querySelector(`[data-meal-id="${mealId}"]`);
  
  // Check if already consumed
  if (consumedMeals.has(mealId)) {
    if (typeof showToast === 'function') {
      showToast("You've already logged this meal. Use 'Start Next Day' to reset.", "info");
    }
    return;
  }
  
  // Store state before calling toggleMealConsumed
  const wasConsumedBefore = consumedMeals.has(mealId);
  
  // Call toggleMealConsumed with meal data
  toggleMealConsumed(mealId, calories, mealElement, mealMacros);
  
  // Only show success and close modal if meal was actually consumed
  // Check if meal is NOW in consumedMeals (it wasn't before, but is now)
  const isConsumedAfter = consumedMeals.has(mealId);
  
  if (isConsumedAfter && !wasConsumedBefore) {
    // Meal was successfully consumed - close modal and show success
    closeModal();
    
    // Re-render meals to update the UI
    setTimeout(() => {
      renderMeals();
    }, 100);
    
    // Show success message
    if (typeof showToast === 'function') {
      showToast(`Meal marked as consumed! +${calories} calories`, "success");
    }
  } else if (!isConsumedAfter) {
    // Meal was NOT consumed (limit check prevented it)
    // Don't close modal, don't show success - error already shown in toggleMealConsumed
    // Just ensure the "Start Next Day" button is visible if target is reached
    showTargetReachedMessage(currentCalories >= target);
  }
}

// Initialize modal close handlers
document.addEventListener("DOMContentLoaded", () => {
  // Close modal when clicking close button
  const modalClose = document.querySelector(".modal-close");
  if (modalClose) {
    modalClose.addEventListener("click", closeModal);
  }
  
  // Close modal when clicking outside
  const modal = document.getElementById("modal");
  if (modal) {
    modal.addEventListener("click", function(e) {
      if (e.target === modal) {
        closeModal();
      }
    });
  }
  
  // Close modal with Escape key
  document.addEventListener("keydown", function(e) {
    if (e.key === "Escape") {
      closeModal();
    }
  });
  
  loadMealPlan();
});
