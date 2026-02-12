// Nutritionist/Doctor Dashboard - Complete implementation
document.addEventListener("DOMContentLoaded", function () {
  initDoctorDashboard();
});

let currentPreviewData = null;
let currentMemberId = null;
let allMembers = [];
let allFoodItems = [];

// Initialize dashboard
function initDoctorDashboard() {
  setupModal();
  setupSectionNavigation();
  setupThemeToggle();
  loadSection('dashboard');
}

// Setup theme toggle
function setupThemeToggle() {
  const themeToggleBtn = document.getElementById("light-mode");
  if (themeToggleBtn) {
    const savedTheme = localStorage.getItem("theme") || "dark";
    document.documentElement.setAttribute("data-theme", savedTheme);
    updateThemeButtonText(themeToggleBtn, savedTheme);

    themeToggleBtn.addEventListener("click", function (e) {
      e.preventDefault();
      const currentTheme = document.documentElement.getAttribute("data-theme");
      const newTheme = currentTheme === "light" ? "dark" : "light";

      document.documentElement.setAttribute("data-theme", newTheme);
      localStorage.setItem("theme", newTheme);

      updateThemeButtonText(this, newTheme);
    });
  }
}

function updateThemeButtonText(button, theme) {
  if (theme === "light") {
    button.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px">
      <path d="M480-120q-150 0-255-105T120-480q0-150 105-255t255-105q14 0 27.5 1t26.5 3q-41 29-65.5 75.5T444-660q0 90 63 153t153 63q55 0 101-24.5t75-65.5q2 13 3 26.5t1 27.5q0 150-105 255T480-120Z"/>
    </svg>Dark Mode`;
  } else {
    button.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px">
      <path d="M480-360q50 0 85-35t35-85q0-50-35-85t-85-35q-50 0-85 35t-35 85q0 50 35 85t85 35Zm0 80q-83 0-141.5-58.5T280-480q0-83 58.5-141.5T480-680q83 0 141.5 58.5T680-480q0 83-58.5 141.5T480-280ZM200-440H40v-80h160v80Zm720 0H760v-80h160v80ZM440-760v-160h80v160h-80Zm0 720v-160h80v160h-80ZM256-650l-101-97 57-59 96 100-52 56Zm492 496-97-101 53-55 101 97-57 59Zm-98-550 97-101 59 57-100 96-56-52ZM154-212l101-97 55 53-97 101-59-57Zm326-268Z"/>
    </svg>Light Mode`;
  }
}

// Setup modal functionality
function setupModal() {
  const modal = document.getElementById("modal");
  const modalClose = document.querySelector(".modal-close");
  
  if (modalClose) {
    modalClose.addEventListener("click", () => {
      modal.classList.remove("active");
      document.body.style.overflow = "";
    });
  }
  
  if (modal) {
    modal.addEventListener("click", (e) => {
      if (e.target === modal) {
        modal.classList.remove("active");
        document.body.style.overflow = "";
      }
    });
  }
}

// Setup section navigation
function setupSectionNavigation() {
  document.querySelectorAll("#sidebar a[data-section]").forEach((link) => {
    link.addEventListener("click", (e) => {
      e.preventDefault();
      const section = link.getAttribute("data-section");
      
      // Update active state
      document.querySelectorAll("#sidebar li").forEach((li) => li.classList.remove("active"));
      link.closest("li").classList.add("active");
      
      loadSection(section);
    });
  });
}

// Load section content
function loadSection(section) {
  const contentArea = document.getElementById("content-area");
  
  switch (section) {
    case "dashboard":
      contentArea.innerHTML = getDashboardHTML();
      initDashboardFunctionality();
      loadDashboardStats();
      break;
    case "members":
      contentArea.innerHTML = getMembersHTML();
      initMembersFunctionality();
      break;
    case "mealplans":
      contentArea.innerHTML = getMealPlansHTML();
      initMealPlansFunctionality();
      break;
    case "create":
      contentArea.innerHTML = getCreateMealPlanHTML();
      initCreateMealPlanFunctionality();
      break;
    case "profile":
      contentArea.innerHTML = getProfileHTML();
      initProfileFunctionality();
      break;
    default:
      contentArea.innerHTML = getDashboardHTML();
      initDashboardFunctionality();
      loadDashboardStats();
  }
}

// Fetch helper
async function fetchJSON(url, opts = {}) {
  opts = Object.assign({ credentials: 'same-origin' }, opts);
  const res = await fetch(url, opts);
  const text = await res.text();
  
  if (!res.ok) {
    console.error('API error', res.status, text);
    throw new Error('API error: ' + text);
  }
  
  const ctype = res.headers.get('content-type') || '';
  if (!ctype.includes('application/json')) {
    console.error('Invalid JSON response', ctype, text);
    throw new Error('Invalid JSON');
  }
  
  try {
    return JSON.parse(text);
  } catch (e) {
    console.error('JSON parse failed', text);
    throw e;
  }
}

// ==================== DASHBOARD SECTION ====================
function getDashboardHTML() {
  return `
    <header class="dashboard-header">
      <div class="header-content">
        <h1>Nutritionist Dashboard</h1>
        <p class="subtitle">Manage your members and meal plans</p>
      </div>
    </header>

    <div class="dashboard-alert">
      <svg xmlns="http://www.w3.org/2000/svg" height="20" viewBox="0 -960 960 960" width="20">
        <path d="M440-280h80v-240h-80v240Zm40-320q17 0 28.5-11.5T520-640q0-17-11.5-28.5T480-680q-17 0-28.5 11.5T440-640q0 17 11.5 28.5T480-600Zm0 520q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z"/>
      </svg>
      <span>Welcome back! Here's your nutrition overview.</span>
    </div>

    <section class="welcome-section">
      <h2>Overview</h2>
      <div class="stats-grid">
        <div class="stat-card">
          <h3>My Members</h3>
          <div class="stat-value" id="total-members">0</div>
          <div class="stat-change positive">Active</div>
        </div>
        <div class="stat-card">
          <h3>Active Meal Plans</h3>
          <div class="stat-value" id="active-plans">0</div>
          <div class="stat-change positive">Running</div>
        </div>
        <div class="stat-card">
          <h3>Meal Plans Created</h3>
          <div class="stat-value" id="total-plans">0</div>
          <div class="stat-change positive">Total</div>
        </div>
        <div class="stat-card">
          <h3>This Week</h3>
          <div class="stat-value" id="week-plans">0</div>
          <div class="stat-change positive">Plans</div>
        </div>
      </div>
    </section>

    <section class="quick-actions">
      <h2>Quick Actions</h2>
      <div class="action-grid">
        <button class="action-card" onclick="loadSection('create')">
          <svg xmlns="http://www.w3.org/2000/svg" height="32px" viewBox="0 -960 960 960" width="32px">
            <path d="M440-280h80v-240h-80v240Zm40-320q17 0 28.5-11.5T520-640q0-17-11.5-28.5T480-680q-17 0-28.5 11.5T440-640q0 17 11.5 28.5T480-600Zm0 520q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z"/>
          </svg>
          <span>Create Meal Plan</span>
        </button>
        <button class="action-card" onclick="loadSection('members')">
          <svg xmlns="http://www.w3.org/2000/svg" height="32px" viewBox="0 -960 960 960" width="32px">
            <path d="M480-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM160-160v-112q0-34 17.5-62.5T224-378q62-31 126-46.5T480-440q66 0 130 15.5T736-378q29 15 46.5 43.5T800-272v112H160Zm80-80h480v-32q0-11-5.5-20T700-306q-54-27-109-40.5T480-360q-56 0-111 13.5T260-306q-9 5-14.5 14t-5.5 20v32Zm240-320q33 0 56.5-23.5T560-640q0-33-23.5-56.5T480-720q-33 0-56.5 23.5T400-640q0 33 23.5 56.5T480-560Zm0-80Zm0 400Z"/>
          </svg>
          <span>View Members</span>
        </button>
        <button class="action-card" onclick="loadSection('mealplans')">
          <svg xmlns="http://www.w3.org/2000/svg" height="32px" viewBox="0 -960 960 960" width="32px">
            <path d="M280-200q-33 0-56.5-23.5T200-280v-400q0-33 23.5-56.5T280-760h480q33 0 56.5 23.5T840-680v400q0 33-23.5 56.5T760-200H280Zm0-80h480v-400H280v400Zm100-80h80v-80h-80v80Zm120 0h80v-80h-80v80Zm120 0h80v-80h-80v80Z"/>
          </svg>
          <span>Meal Plans</span>
        </button>
      </div>
    </section>
  `;
}

function initDashboardFunctionality() {
  // Dashboard-specific functionality
}

async function loadDashboardStats() {
  try {
    const members = await fetchJSON(new URL("../api/nutritionist/members/list.php", window.location.href).href);
    
    document.getElementById("total-members").textContent = members.length || 0;
    
    // Get meal plans for all members
    let totalPlans = 0;
    let activePlans = 0;
    const weekAgo = new Date();
    weekAgo.setDate(weekAgo.getDate() - 7);
    
    for (const member of members) {
      try {
        const plans = await fetchJSON(new URL(`../api/doctor/mealplans/list.php?member_id=${member.Member_Id}`, window.location.href).href);
        totalPlans += plans.length;
        activePlans += plans.filter(p => p.Status === 'Active').length;
      } catch (e) {
        console.error(`Error loading plans for member ${member.Member_Id}:`, e);
      }
    }
    
    document.getElementById("active-plans").textContent = activePlans || 0;
    document.getElementById("total-plans").textContent = totalPlans || 0;
    document.getElementById("week-plans").textContent = totalPlans || 0; // Simplified for now
  } catch (err) {
    console.error("Error loading stats:", err);
  }
}

// ==================== MEMBERS SECTION ====================
function getMembersHTML() {
  return `
    <div class="section-header">
      <h1>My Members</h1>
    </div>
    
    <div class="filters">
      <input type="text" class="form-control" placeholder="Search members..." id="member-search-input">
    </div>
    
    <table class="data-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Email</th>
          <th>Height</th>
          <th>Weight</th>
          <th>BMI</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="members-table-body">
        <tr><td colspan="7" style="text-align: center; padding: 2rem;">Loading members...</td></tr>
      </tbody>
    </table>
  `;
}

async function initMembersFunctionality() {
  await loadMembers();
  
  const searchInput = document.getElementById("member-search-input");
  if (searchInput) {
    searchInput.addEventListener("input", (e) => {
      filterMembers(e.target.value);
    });
  }
}

async function loadMembers() {
  try {
    allMembers = await fetchJSON(new URL("../api/nutritionist/members/list.php", window.location.href).href);
    renderMembers(allMembers);
  } catch (err) {
    console.error("Error loading members:", err);
    showToast("Failed to load members", "error");
    document.getElementById("members-table-body").innerHTML = 
      '<tr><td colspan="7" style="text-align: center; padding: 2rem; color: var(--danger-color);">Error loading members</td></tr>';
  }
}

function renderMembers(members) {
  const tbody = document.getElementById("members-table-body");
  if (!tbody) return;
  
  if (members.length === 0) {
    tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 2rem;">No members assigned</td></tr>';
    return;
  }
  
  tbody.innerHTML = members.map(m => `
    <tr>
      <td>${m.Member_Id}</td>
      <td>${m.First_Name} ${m.Last_Name}</td>
      <td>${m.Email || 'N/A'}</td>
      <td>${m.Height ? m.Height + ' cm' : 'N/A'}</td>
      <td>${m.Weight ? m.Weight + ' kg' : 'N/A'}</td>
      <td>${m.BMI ? m.BMI.toFixed(1) : 'N/A'}</td>
      <td>
        <button class="btn-small btn-view" onclick="viewMedicalRecord(${m.Member_Id}, '${m.First_Name} ${m.Last_Name}')">Medical Record</button>
        <button class="btn-small" style="background: var(--btn-primary-bg); color: white;" onclick="viewMemberMealPlans(${m.Member_Id}, '${m.First_Name} ${m.Last_Name}')">Meal Plans</button>
        <button class="btn-small" style="background: var(--btn-primary-bg); color: white;" onclick="createMealPlanForMember(${m.Member_Id}, '${m.First_Name} ${m.Last_Name}')">Create Plan</button>
      </td>
    </tr>
  `).join('');
}

function filterMembers(query) {
  const filtered = allMembers.filter(m => 
    `${m.First_Name} ${m.Last_Name} ${m.Email}`.toLowerCase().includes(query.toLowerCase())
  );
  renderMembers(filtered);
}

async function viewMedicalRecord(memberId, memberName) {
  try {
    const response = await fetchJSON(new URL(`../api/nutritionist/medical_records/get.php?member_id=${memberId}`, window.location.href).href);
    
    if (!response.success) {
      showToast(response.error || "Failed to load medical record", "error");
      return;
    }
    
    const { medical_record, member_profile, conditions } = response;
    
    let conditionsHTML = '';
    if (conditions && conditions.length > 0) {
      conditionsHTML = `
        <div style="margin-top: 15px;">
          <h4 style="margin-bottom: 10px; color: var(--accent-secondary);">Medical Conditions:</h4>
          <div style="display: flex; flex-wrap: wrap; gap: 8px;">
            ${conditions.map(c => `<span class="status-badge" style="background: rgba(239, 68, 68, 0.1); color: var(--danger-color); padding: 6px 12px; border-radius: 6px;">${c}</span>`).join('')}
          </div>
        </div>
      `;
    } else {
      conditionsHTML = '<p style="color: var(--muted); margin-top: 15px;">No medical conditions recorded</p>';
    }
    
    let profileHTML = '';
    if (member_profile) {
      profileHTML = `
        <div style="margin-top: 20px; padding: 15px; background: var(--card-bg); border-radius: 8px;">
          <h4 style="margin-bottom: 10px;">Physical Profile</h4>
          <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">
            <div>
              <div style="font-size: 0.85rem; color: var(--muted);">Height</div>
              <div style="font-weight: 600;">${member_profile.Height ? member_profile.Height + ' cm' : 'N/A'}</div>
            </div>
            <div>
              <div style="font-size: 0.85rem; color: var(--muted);">Weight</div>
              <div style="font-weight: 600;">${member_profile.Weight ? member_profile.Weight + ' kg' : 'N/A'}</div>
            </div>
            <div>
              <div style="font-size: 0.85rem; color: var(--muted);">BMI</div>
              <div style="font-weight: 600;">${member_profile.BMI ? member_profile.BMI.toFixed(1) : 'N/A'}</div>
            </div>
          </div>
          ${member_profile.Medical_Condition ? `<div style="margin-top: 10px;"><strong>Medical Notes:</strong> ${member_profile.Medical_Condition}</div>` : ''}
          ${member_profile.Injuries ? `<div style="margin-top: 10px;"><strong>Injuries:</strong> ${member_profile.Injuries}</div>` : ''}
        </div>
      `;
    }
    
    showModal("Medical Record - " + memberName, `
      <div style="max-height: 600px; overflow-y: auto;">
        ${conditionsHTML}
        ${profileHTML}
      </div>
    `);
  } catch (err) {
    console.error(err);
    showToast("Failed to load medical record", "error");
  }
}

async function viewMemberMealPlans(memberId, memberName) {
  try {
    const plans = await fetchJSON(new URL(`../api/doctor/mealplans/list.php?member_id=${memberId}`, window.location.href).href);
    
    if (plans.length === 0) {
      showModal("Meal Plans for " + memberName, `
        <div style="padding: 20px; text-align: center;">
          <p style="color: var(--muted); margin-bottom: 20px;">No meal plans found for this member.</p>
          <button class="action-btn" onclick="createMealPlanForMember(${memberId}, '${memberName}'); document.getElementById('modal').classList.remove('active');">Create Meal Plan</button>
        </div>
      `);
      return;
    }
    
    showModal("Meal Plans for " + memberName, `
      <div style="max-height: 500px; overflow-y: auto;">
        ${plans.map(p => `
          <div style="padding: 15px; margin: 10px 0; background: var(--card-bg); border-radius: 8px; border-left: 3px solid ${p.Status === 'Active' ? 'var(--success-color)' : 'var(--muted)'};">
            <h4 style="margin: 0 0 10px 0;">${p.Title} ${p.Status === 'Active' ? '<span class="status-badge status-active">Active</span>' : ''}</h4>
            <p><strong>Status:</strong> <span class="status-badge status-${(p.Status || 'inactive').toLowerCase()}">${p.Status || 'Inactive'}</span></p>
            <p><strong>Description:</strong> ${p.Description || 'N/A'}</p>
            <p><strong>Daily Calories:</strong> ${p.Total_daily_Calories || 0} cal</p>
            <p><strong>Start Date:</strong> ${p.Start_Date ? new Date(p.Start_Date).toLocaleDateString() : 'N/A'}</p>
            <p><strong>End Date:</strong> ${p.End_Date ? new Date(p.End_Date).toLocaleDateString() : 'N/A'}</p>
            <p><strong>Created:</strong> ${new Date(p.Created_at).toLocaleDateString()}</p>
          </div>
        `).join('')}
      </div>
    `);
  } catch (err) {
    console.error(err);
    showToast("Failed to load meal plans", "error");
  }
}

function createMealPlanForMember(memberId, memberName) {
  currentMemberId = memberId;
  loadSection('create');
  
  // Set member in form
  setTimeout(() => {
    const memberSelect = document.getElementById('create-member-select');
    if (memberSelect) {
      memberSelect.value = memberId;
      memberSelect.dispatchEvent(new Event('change'));
    }
  }, 100);
}

// ==================== MEAL PLANS SECTION ====================
function getMealPlansHTML() {
  return `
    <div class="section-header">
      <h1>Meal Plans</h1>
    </div>
    
    <div class="filters">
      <select class="form-control" id="mealplan-member-filter" style="max-width: 300px;">
        <option value="">All Members</option>
      </select>
    </div>
    
    <table class="data-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Title</th>
          <th>Member</th>
          <th>Daily Calories</th>
          <th>Status</th>
          <th>Start Date</th>
          <th>End Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="mealplans-table-body">
        <tr><td colspan="8" style="text-align: center; padding: 2rem;">Loading meal plans...</td></tr>
      </tbody>
    </table>
  `;
}

async function initMealPlansFunctionality() {
  await loadMealPlans();
  
  const memberFilter = document.getElementById("mealplan-member-filter");
  if (memberFilter) {
    memberFilter.addEventListener("change", () => {
      loadMealPlans();
    });
  }
}

async function loadMealPlans() {
  try {
    const members = await fetchJSON(new URL("../api/nutritionist/members/list.php", window.location.href).href);
    
    // Populate member filter
    const memberFilter = document.getElementById("mealplan-member-filter");
    if (memberFilter) {
      const currentValue = memberFilter.value;
      memberFilter.innerHTML = '<option value="">All Members</option>' + 
        members.map(m => `<option value="${m.Member_Id}">${m.First_Name} ${m.Last_Name}</option>`).join('');
      if (currentValue) memberFilter.value = currentValue;
    }
    
    const selectedMemberId = memberFilter?.value || '';
    let allPlans = [];
    
    if (selectedMemberId) {
      allPlans = await fetchJSON(new URL(`../api/doctor/mealplans/list.php?member_id=${selectedMemberId}`, window.location.href).href);
    } else {
      for (const member of members) {
        try {
          const plans = await fetchJSON(new URL(`../api/doctor/mealplans/list.php?member_id=${member.Member_Id}`, window.location.href).href);
          plans.forEach(p => {
            p.Member_Name = `${member.First_Name} ${member.Last_Name}`;
            allPlans.push(p);
          });
        } catch (e) {
          console.error(`Error loading plans for member ${member.Member_Id}:`, e);
        }
      }
    }
    
    renderMealPlans(allPlans);
  } catch (err) {
    console.error("Error loading meal plans:", err);
    showToast("Failed to load meal plans", "error");
  }
}

function renderMealPlans(plans) {
  const tbody = document.getElementById("mealplans-table-body");
  if (!tbody) return;
  
  if (plans.length === 0) {
    tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 2rem;">No meal plans found</td></tr>';
    return;
  }
  
  tbody.innerHTML = plans.map(p => {
    const isActive = p.Status === 'Active';
    const isPending = p.Status === 'Pending';
    const isInactive = p.Status === 'Inactive' || !p.Status;
    
    return `
    <tr>
      <td>${p.Meal_Plan_ID}</td>
      <td>${p.Title}</td>
      <td>${p.Member_Name || 'N/A'}</td>
      <td>${p.Total_daily_Calories || 0} cal</td>
      <td><span class="status-badge status-${(p.Status || 'inactive').toLowerCase()}">${p.Status || 'Inactive'}</span></td>
      <td>${p.Start_Date ? new Date(p.Start_Date).toLocaleDateString() : 'N/A'}</td>
      <td>${p.End_Date ? new Date(p.End_Date).toLocaleDateString() : 'N/A'}</td>
      <td>
        <div style="display: flex; gap: 5px; flex-wrap: wrap;">
          <button class="btn-small btn-view" onclick="viewMealPlanDetails(${p.Meal_Plan_ID})">View</button>
          ${!isActive ? `<button class="btn-small" style="background: var(--success-color); color: white;" onclick="activateMealPlan(${p.Meal_Plan_ID})">Activate</button>` : ''}
          ${isActive ? `<button class="btn-small" style="background: var(--warning-color); color: white;" onclick="deactivateMealPlan(${p.Meal_Plan_ID})">Deactivate</button>` : ''}
          <button class="btn-small" style="background: var(--danger-color); color: white;" onclick="deleteMealPlan(${p.Meal_Plan_ID}, '${p.Title}')">Delete</button>
        </div>
      </td>
    </tr>
  `;
  }).join('');
}

async function viewMealPlanDetails(mealPlanId) {
  try {
    const response = await fetchJSON(new URL(`../api/doctor/mealplans/get.php?id=${mealPlanId}`, window.location.href).href);
    
    if (!response.success) {
      showToast(response.error || "Failed to load meal plan details", "error");
      return;
    }
    
    const { meal_plan, meals } = response;
    
    // Calculate totals from meals
    let totalCalories = 0;
    let totalProtein = 0;
    let totalCarbs = 0;
    let totalFats = 0;
    
    Object.values(meals).forEach(timeMeals => {
      timeMeals.forEach(meal => {
        totalCalories += meal.calories || 0;
        totalProtein += meal.protein || 0;
        totalCarbs += meal.carbs || 0;
        totalFats += meal.fats || 0;
      });
    });
    
    // Build HTML for meal plan details
    let mealsHTML = '';
    const mealOrder = ['Breakfast', 'Lunch', 'Dinner', 'Pre-Workout', 'Post-Workout', 'Snacks'];
    
    mealOrder.forEach(timeName => {
      if (meals[timeName] && meals[timeName].length > 0) {
        mealsHTML += `
          <div class="meal-time-section" style="margin-bottom: 2rem;">
            <h3 style="color: var(--accent-secondary); margin-bottom: 1rem; font-size: 1.2rem;">${timeName}</h3>
            ${meals[timeName].map(meal => `
              <div class="meal-card" style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 1.5rem; margin-bottom: 1rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                  <h4 style="color: var(--text-clr); margin: 0;">${meal.name}</h4>
                  <div style="display: flex; gap: 10px;">
                    <span style="background: var(--accent-clr); color: white; padding: 4px 12px; border-radius: 6px; font-size: 0.9rem;">
                      ${meal.calories} cal
                    </span>
                  </div>
                </div>
                <div style="display: flex; gap: 10px; margin-bottom: 1rem; flex-wrap: wrap;">
                  <span style="background: rgba(59, 130, 246, 0.1); color: var(--info-color); padding: 4px 12px; border-radius: 6px; font-size: 0.85rem;">
                    P: ${meal.protein}g
                  </span>
                  <span style="background: rgba(16, 185, 129, 0.1); color: var(--success-color); padding: 4px 12px; border-radius: 6px; font-size: 0.85rem;">
                    C: ${meal.carbs}g
                  </span>
                  <span style="background: rgba(245, 158, 11, 0.1); color: var(--warning-color); padding: 4px 12px; border-radius: 6px; font-size: 0.85rem;">
                    F: ${meal.fats}g
                  </span>
                </div>
                ${meal.food_items && meal.food_items.length > 0 ? `
                  <div style="border-top: 1px solid var(--border-color); padding-top: 1rem;">
                    <strong style="color: var(--text-secondary-clr); font-size: 0.9rem;">Food Items:</strong>
                    <ul style="margin-top: 0.5rem; padding-left: 1.5rem; color: var(--text-clr);">
                      ${meal.food_items.map(item => `
                        <li style="margin-bottom: 0.5rem;">
                          ${item.name} 
                          <span style="color: var(--text-secondary-clr); font-size: 0.85rem;">
                            (${item.servings} serving${item.servings !== 1 ? 's' : ''} × ${item.serving_size}g)
                          </span>
                          <span style="color: var(--accent-clr); font-size: 0.85rem; margin-left: 8px;">
                            - ${item.calories} cal
                          </span>
                        </li>
                      `).join('')}
                    </ul>
                  </div>
                ` : ''}
              </div>
            `).join('')}
          </div>
        `;
      }
    });
    
    const modalContent = `
      <div style="max-width: 800px;">
        <div style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 2rem; margin-bottom: 2rem;">
          <h2 style="color: var(--accent-secondary); margin-top: 0;">${meal_plan.title}</h2>
          ${meal_plan.description ? `<p style="color: var(--text-clr); margin-bottom: 1rem;">${meal_plan.description}</p>` : ''}
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-top: 1.5rem;">
            <div>
              <div style="color: var(--text-secondary-clr); font-size: 0.9rem; margin-bottom: 0.25rem;">Member</div>
              <div style="color: var(--text-clr); font-weight: 600;">${meal_plan.member_name || 'N/A'}</div>
            </div>
            <div>
              <div style="color: var(--text-secondary-clr); font-size: 0.9rem; margin-bottom: 0.25rem;">Status</div>
              <div style="color: var(--text-clr); font-weight: 600;">
                <span class="status-badge status-${(meal_plan.status || 'inactive').toLowerCase()}">${meal_plan.status || 'Inactive'}</span>
              </div>
            </div>
            <div>
              <div style="color: var(--text-secondary-clr); font-size: 0.9rem; margin-bottom: 0.25rem;">Start Date</div>
              <div style="color: var(--text-clr); font-weight: 600;">${meal_plan.start_date ? new Date(meal_plan.start_date).toLocaleDateString() : 'N/A'}</div>
            </div>
            <div>
              <div style="color: var(--text-secondary-clr); font-size: 0.9rem; margin-bottom: 0.25rem;">End Date</div>
              <div style="color: var(--text-clr); font-weight: 600;">${meal_plan.end_date ? new Date(meal_plan.end_date).toLocaleDateString() : 'N/A'}</div>
            </div>
          </div>
        </div>
        
        <div style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem;">
          <h3 style="color: var(--accent-secondary); margin-top: 0; margin-bottom: 1rem;">Daily Targets</h3>
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 1rem;">
            <div style="text-align: center;">
              <div style="color: var(--text-secondary-clr); font-size: 0.9rem; margin-bottom: 0.25rem;">Target Calories</div>
              <div style="color: var(--accent-clr); font-size: 1.5rem; font-weight: 600;">${meal_plan.target_calories}</div>
              <div style="color: var(--text-secondary-clr); font-size: 0.85rem; margin-top: 0.25rem;">Meals: ${totalCalories} cal</div>
            </div>
            <div style="text-align: center;">
              <div style="color: var(--text-secondary-clr); font-size: 0.9rem; margin-bottom: 0.25rem;">Protein</div>
              <div style="color: var(--info-color); font-size: 1.5rem; font-weight: 600;">${meal_plan.protein}g</div>
              <div style="color: var(--text-secondary-clr); font-size: 0.85rem; margin-top: 0.25rem;">Meals: ${totalProtein}g</div>
            </div>
            <div style="text-align: center;">
              <div style="color: var(--text-secondary-clr); font-size: 0.9rem; margin-bottom: 0.25rem;">Carbs</div>
              <div style="color: var(--success-color); font-size: 1.5rem; font-weight: 600;">${meal_plan.carbs}g</div>
              <div style="color: var(--text-secondary-clr); font-size: 0.85rem; margin-top: 0.25rem;">Meals: ${totalCarbs}g</div>
            </div>
            <div style="text-align: center;">
              <div style="color: var(--text-secondary-clr); font-size: 0.9rem; margin-bottom: 0.25rem;">Fats</div>
              <div style="color: var(--warning-color); font-size: 1.5rem; font-weight: 600;">${meal_plan.fats}g</div>
              <div style="color: var(--text-secondary-clr); font-size: 0.85rem; margin-top: 0.25rem;">Meals: ${totalFats}g</div>
            </div>
          </div>
        </div>
        
        <div>
          <h3 style="color: var(--accent-secondary); margin-bottom: 1.5rem;">Meals</h3>
          ${mealsHTML || '<p style="color: var(--text-secondary-clr);">No meals found in this plan.</p>'}
        </div>
      </div>
    `;
    
    showModal(`Meal Plan Details - ${meal_plan.title}`, modalContent);
  } catch (err) {
    console.error("Error loading meal plan details:", err);
    showToast("Failed to load meal plan details", "error");
  }
}

async function activateMealPlan(mealPlanId) {
  if (!confirm('Are you sure you want to activate this meal plan?')) return;
  
  try {
    const response = await fetchJSON(new URL("../api/nutritionist/mealplans/update_status.php", window.location.href).href, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        meal_plan_id: mealPlanId,
        status: 'Active'
      })
    });
    
    if (response.success) {
      showToast("Meal plan activated successfully", "success");
      loadMealPlans();
    } else {
      showToast(response.error || "Failed to activate meal plan", "error");
    }
  } catch (err) {
    console.error(err);
    showToast("Failed to activate meal plan", "error");
  }
}

async function deactivateMealPlan(mealPlanId) {
  if (!confirm('Are you sure you want to deactivate this meal plan?')) return;
  
  try {
    const response = await fetchJSON(new URL("../api/nutritionist/mealplans/update_status.php", window.location.href).href, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        meal_plan_id: mealPlanId,
        status: 'Inactive'
      })
    });
    
    if (response.success) {
      showToast("Meal plan deactivated successfully", "success");
      loadMealPlans();
    } else {
      showToast(response.error || "Failed to deactivate meal plan", "error");
    }
  } catch (err) {
    console.error(err);
    showToast("Failed to deactivate meal plan", "error");
  }
}

async function deleteMealPlan(mealPlanId, mealPlanTitle) {
  if (!confirm(`Are you sure you want to DELETE "${mealPlanTitle}"?\n\nThis will permanently delete the meal plan and all associated meals. This action cannot be undone.`)) return;
  
  if (!confirm('This is your final warning. Are you absolutely sure?')) return;
  
  try {
    const response = await fetchJSON(new URL("../api/nutritionist/mealplans/delete.php", window.location.href).href, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        meal_plan_id: mealPlanId
      })
    });
    
    if (response.success) {
      showToast(`Meal plan deleted successfully. ${response.meals_deleted || 0} meals removed.`, "success");
      loadMealPlans();
      
      // Reload dashboard stats if on dashboard
      if (document.getElementById("active-plans")) {
        loadDashboardStats();
      }
    } else {
      showToast(response.error || "Failed to delete meal plan", "error");
    }
  } catch (err) {
    console.error(err);
    showToast("Failed to delete meal plan", "error");
  }
}

// ==================== CREATE MEAL PLAN SECTION ====================
function getCreateMealPlanHTML() {
  return `
    <div class="section-header">
      <h1>Create Meal Plan</h1>
      <p class="subtitle">Design a personalized meal plan for your member</p>
    </div>
    
    <form id="create-mealplan-form" style="max-width: 1200px;">
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
        <div>
          <label class="form-label">Select Member *</label>
          <select class="form-control" id="create-member-select" name="member_id" required>
            <option value="">-- Select Member --</option>
          </select>
        </div>
        <div>
          <label class="form-label">Plan Title *</label>
          <input type="text" class="form-control" name="title" id="plan-title-input" placeholder="e.g., Weight Loss Plan" required>
        </div>
      </div>
      
      <div style="margin-bottom: 20px;">
        <label class="form-label">Description</label>
        <textarea class="form-control" name="description" rows="3" placeholder="Plan description..."></textarea>
      </div>
      
      <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 30px;">
        <div>
          <label class="form-label">Daily Calories *</label>
          <input type="number" class="form-control" name="total_daily_calories" id="daily-calories" placeholder="2000" min="1" required>
        </div>
        <div>
          <label class="form-label">Carbs (g/day) *</label>
          <input type="number" class="form-control" name="carbs_grams_per_day" id="daily-carbs" placeholder="250" min="1" required>
        </div>
        <div>
          <label class="form-label">Protein (g/day) *</label>
          <input type="number" class="form-control" name="protein_grams_per_day" id="daily-protein" placeholder="150" min="1" required>
        </div>
        <div>
          <label class="form-label">Fats (g/day) *</label>
          <input type="number" class="form-control" name="fats_grams_per_day" id="daily-fats" placeholder="70" min="1" required>
        </div>
      </div>
      
      <div style="margin-bottom: 20px;">
        <label class="form-label">Date Range</label>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
          <div>
            <label style="font-size: 0.85rem; color: var(--muted);">Start Date</label>
            <input type="date" class="form-control" name="start_date" value="${new Date().toISOString().split('T')[0]}">
          </div>
          <div>
            <label style="font-size: 0.85rem; color: var(--muted);">End Date</label>
            <input type="date" class="form-control" name="end_date" value="${new Date(Date.now() + 28 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]}">
          </div>
        </div>
      </div>
      
      <div style="margin-bottom: 30px; padding: 20px; background: var(--card-bg); border-radius: 12px; border: 1px solid var(--border-color);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
          <div>
            <h3 style="margin: 0 0 5px 0; color: var(--accent-secondary);">AI Meal Plan Generator</h3>
            <p style="margin: 0; color: var(--muted); font-size: 0.9rem;">Generate a personalized meal plan based on member's profile and goals</p>
          </div>
          <button type="button" class="action-btn" id="generate-ai-mealplan-btn" onclick="generateAIMealPlan()" style="min-width: 180px;" disabled>
            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" style="vertical-align: middle; margin-right: 8px;">
              <path d="M440-280h80v-240h-80v240Zm40-320q17 0 28.5-11.5T520-640q0-17-11.5-28.5T480-680q-17 0-28.5 11.5T440-640q0 17 11.5 28.5T480-600Zm0 520q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z"/>
            </svg>
            Generate with AI
          </button>
        </div>
        <div id="ai-generator-status" style="display: none; margin-top: 10px; padding: 10px; background: var(--base-clr); border-radius: 6px; font-size: 0.9rem; color: var(--muted);">
          <span id="ai-status-text">Generating meal plan...</span>
        </div>
      </div>
      
      <div id="meal-categories-container">
        <!-- Meal categories will be loaded here -->
      </div>
      
      <div style="margin-top: 30px; display: flex; gap: 15px; justify-content: flex-end;">
        <button type="button" class="action-btn secondary" onclick="previewMealPlan()">Preview</button>
        <button type="submit" class="action-btn">Create Meal Plan</button>
      </div>
    </form>
  `;
}

async function initCreateMealPlanFunctionality() {
  // Load members
  try {
    const members = await fetchJSON(new URL("../api/nutritionist/members/list.php", window.location.href).href);
    const memberSelect = document.getElementById('create-member-select');
    if (memberSelect) {
      memberSelect.innerHTML = '<option value="">-- Select Member --</option>' + 
        members.map(m => `<option value="${m.Member_Id}">${m.First_Name} ${m.Last_Name}</option>`).join('');
      
      if (currentMemberId) {
        memberSelect.value = currentMemberId;
      }
    }
  } catch (err) {
    console.error("Error loading members:", err);
  }
  
  // Load food items
  try {
    allFoodItems = await fetchJSON(new URL("../api/nutritionist/fooditems/list.php", window.location.href).href);
    renderMealCategories();
  } catch (err) {
    console.error("Error loading food items:", err);
    showToast("Failed to load food items", "error");
  }
  
  // Setup form submission
  const form = document.getElementById("create-mealplan-form");
  if (form) {
    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      await createMealPlan(form);
    });
  }
  
  // Auto-calculate macros when calories change
  const caloriesInput = document.getElementById('daily-calories');
  if (caloriesInput) {
    caloriesInput.addEventListener('input', calculateMacrosFromCalories);
  }
  
  // Setup validation for AI generate button
  setupAIGenerateButtonValidation();
  
  // Re-run validation after a short delay to ensure all elements are ready
  setTimeout(() => {
    setupAIGenerateButtonValidation();
  }, 100);
}

function setupAIGenerateButtonValidation() {
  const generateBtn = document.getElementById('generate-ai-mealplan-btn');
  if (!generateBtn) return;
  
  const titleInput = document.getElementById('plan-title-input') || document.querySelector('input[name="title"]');
  const caloriesInput = document.getElementById('daily-calories');
  const carbsInput = document.getElementById('daily-carbs');
  const proteinInput = document.getElementById('daily-protein');
  const fatsInput = document.getElementById('daily-fats');
  
  function checkValidation() {
    const title = titleInput ? titleInput.value.trim() : '';
    const calories = caloriesInput ? parseInt(caloriesInput.value) : 0;
    const carbs = carbsInput ? parseInt(carbsInput.value) : 0;
    const protein = proteinInput ? parseInt(proteinInput.value) : 0;
    const fats = fatsInput ? parseInt(fatsInput.value) : 0;
    
    const isValid = title.length > 0 && calories > 0 && carbs > 0 && protein > 0 && fats > 0;
    
    if (generateBtn) {
      generateBtn.disabled = !isValid;
      if (isValid) {
        generateBtn.style.opacity = '1';
        generateBtn.style.cursor = 'pointer';
        generateBtn.title = '';
      } else {
        generateBtn.style.opacity = '0.5';
        generateBtn.style.cursor = 'not-allowed';
        generateBtn.title = 'Please fill in Plan Title, Calories, and all macros first';
      }
    }
  }
  
  // Check on input
  if (titleInput) titleInput.addEventListener('input', checkValidation);
  if (caloriesInput) caloriesInput.addEventListener('input', checkValidation);
  if (carbsInput) carbsInput.addEventListener('input', checkValidation);
  if (proteinInput) proteinInput.addEventListener('input', checkValidation);
  if (fatsInput) fatsInput.addEventListener('input', checkValidation);
  
  // Initial check
  checkValidation();
}

function calculateMacrosFromCalories() {
  const calories = parseInt(document.getElementById('daily-calories').value) || 0;
  if (calories > 0) {
    // Standard macro distribution: 40% carbs, 30% protein, 30% fats
    const carbs = Math.round((calories * 0.4) / 4); // 4 cal per gram
    const protein = Math.round((calories * 0.3) / 4); // 4 cal per gram
    const fats = Math.round((calories * 0.3) / 9); // 9 cal per gram
    
    const carbsInput = document.getElementById('daily-carbs');
    const proteinInput = document.getElementById('daily-protein');
    const fatsInput = document.getElementById('daily-fats');
    
    if (carbsInput && !carbsInput.value) carbsInput.value = carbs;
    if (proteinInput && !proteinInput.value) proteinInput.value = protein;
    if (fatsInput && !fatsInput.value) fatsInput.value = fats;
  }
}

function renderMealCategories() {
  const container = document.getElementById("meal-categories-container");
  if (!container) return;
  
  const categories = [
    { name: 'Breakfast', timeOfDay: 1 },
    { name: 'Lunch', timeOfDay: 2 },
    { name: 'Dinner', timeOfDay: 3 },
    { name: 'Pre-Workout', timeOfDay: 4 },
    { name: 'Post-Workout', timeOfDay: 5 },
    { name: 'Snacks', timeOfDay: 6 }
  ];
  
  container.innerHTML = categories.map(cat => `
    <div class="meal-category-section" data-category="${cat.name}">
      <h3>${cat.name}</h3>
      <div style="margin-bottom: 15px;">
        <button type="button" class="btn-small" onclick="addMealToCategory('${cat.name}')" style="background: var(--btn-primary-bg); color: white;">
          + Add Meal
        </button>
      </div>
      <div class="meals-list" id="meals-${cat.name.toLowerCase()}">
        <!-- Meals will be added here -->
      </div>
    </div>
  `).join('');
}

function addMealToCategory(categoryName) {
  const mealsList = document.getElementById(`meals-${categoryName.toLowerCase()}`);
  if (!mealsList) return;
  
  const mealIndex = mealsList.children.length;
  const mealId = `meal-${categoryName.toLowerCase()}-${Date.now()}-${mealIndex}`;
  
  const mealHTML = `
    <div class="meal-item" data-meal-id="${mealId}" style="padding: 15px; margin-bottom: 15px; background: var(--base-clr); border-radius: 8px; border: 1px solid var(--border-color);">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <input type="text" class="form-control meal-name-input" placeholder="Meal name (e.g., Oatmeal Bowl)" style="max-width: 300px;" required>
        <button type="button" class="btn-small" onclick="removeMeal('${mealId}')" style="background: var(--danger-color); color: white;">Remove</button>
      </div>
      
      <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 15px; padding: 10px; background: rgba(166, 111, 255, 0.05); border-radius: 6px;">
        <div>
          <label style="font-size: 0.75rem; color: var(--muted); display: block; margin-bottom: 4px;">Calories *</label>
          <input type="number" class="form-control meal-calories-input" value="0" min="0" required style="font-weight: 600;" onchange="updateMealMacros('${mealId}')">
        </div>
        <div>
          <label style="font-size: 0.75rem; color: var(--muted); display: block; margin-bottom: 4px;">Protein (g) *</label>
          <input type="number" class="form-control meal-protein-input" value="0" min="0" required style="font-weight: 600;">
        </div>
        <div>
          <label style="font-size: 0.75rem; color: var(--muted); display: block; margin-bottom: 4px;">Carbs (g) *</label>
          <input type="number" class="form-control meal-carbs-input" value="0" min="0" required style="font-weight: 600;">
        </div>
        <div>
          <label style="font-size: 0.75rem; color: var(--muted); display: block; margin-bottom: 4px;">Fats (g) *</label>
          <input type="number" class="form-control meal-fats-input" value="0" min="0" required style="font-weight: 600;">
        </div>
      </div>
      
      <div style="margin-bottom: 15px;">
        <label style="font-size: 0.9rem; color: var(--muted); margin-bottom: 8px; display: block;">Select Food Items:</label>
        <div class="food-items-grid" id="food-grid-${mealId}">
          ${allFoodItems.map(food => `
            <div class="food-item-card" data-food-id="${food.Food_Item_ID}" onclick="toggleFoodItem('${mealId}', ${food.Food_Item_ID}, this)">
              <h4>${food.Name}</h4>
              <div class="nutrition-info">
                <span class="nutrition-badge">${food.Calories} cal</span>
                <span class="nutrition-badge">${food.Protein_Grams}g protein</span>
                <span class="nutrition-badge">${food.Carbs_Grams}g carbs</span>
                <span class="nutrition-badge">${food.Fats_Grams}g fats</span>
              </div>
              <div style="margin-top: 10px; display: none;" class="servings-input-container">
                <label style="font-size: 0.85rem; color: var(--muted);">Servings:</label>
                <input type="number" class="form-control" min="1" value="1" style="margin-top: 5px;" data-servings-input="${food.Food_Item_ID}" onchange="updateMealMacros('${mealId}')">
              </div>
            </div>
          `).join('')}
        </div>
      </div>
    </div>
  `;
  
  mealsList.insertAdjacentHTML('beforeend', mealHTML);
}

function toggleFoodItem(mealId, foodItemId, cardElement) {
  const isSelected = cardElement.classList.contains('selected');
  const servingsContainer = cardElement.querySelector('.servings-input-container');
  const servingsInput = cardElement.querySelector(`[data-servings-input="${foodItemId}"]`);
  
  if (isSelected) {
    cardElement.classList.remove('selected');
    if (servingsContainer) servingsContainer.style.display = 'none';
    if (servingsInput) servingsInput.value = '1';
  } else {
    cardElement.classList.add('selected');
    if (servingsContainer) servingsContainer.style.display = 'block';
  }
  
  // Update meal macros when food item is toggled
  updateMealMacros(mealId);
}

function updateMealMacros(mealId) {
  const mealElement = document.querySelector(`[data-meal-id="${mealId}"]`);
  if (!mealElement) return;
  
  const selectedFoods = mealElement.querySelectorAll('.food-item-card.selected');
  let totalCalories = 0, totalProtein = 0, totalCarbs = 0, totalFats = 0;
  
  selectedFoods.forEach(foodCard => {
    const foodId = parseInt(foodCard.getAttribute('data-food-id'));
    const servingsInput = foodCard.querySelector(`[data-servings-input="${foodId}"]`);
    const servings = parseInt(servingsInput ? servingsInput.value : 1);
    
    const food = allFoodItems.find(f => f.Food_Item_ID == foodId);
    if (food) {
      totalCalories += food.Calories * servings;
      totalProtein += food.Protein_Grams * servings;
      totalCarbs += food.Carbs_Grams * servings;
      totalFats += food.Fats_Grams * servings;
    }
  });
  
  // Update macro inputs
  const caloriesInput = mealElement.querySelector('.meal-calories-input');
  const proteinInput = mealElement.querySelector('.meal-protein-input');
  const carbsInput = mealElement.querySelector('.meal-carbs-input');
  const fatsInput = mealElement.querySelector('.meal-fats-input');
  
  if (caloriesInput) caloriesInput.value = Math.round(totalCalories);
  if (proteinInput) proteinInput.value = Math.round(totalProtein);
  if (carbsInput) carbsInput.value = Math.round(totalCarbs);
  if (fatsInput) fatsInput.value = Math.round(totalFats);
}

function removeMeal(mealId) {
  const mealElement = document.querySelector(`[data-meal-id="${mealId}"]`);
  if (mealElement) {
    mealElement.remove();
  }
}

function collectMealPlanData() {
  const form = document.getElementById("create-mealplan-form");
  if (!form) return null;
  
  const formData = new FormData(form);
  const data = {
    title: formData.get('title'),
    description: formData.get('description') || '',
    member_id: parseInt(formData.get('member_id')),
    total_daily_calories: parseInt(formData.get('total_daily_calories') || 0),
    carbs_grams_per_day: parseInt(formData.get('carbs_grams_per_day') || 0),
    protein_grams_per_day: parseInt(formData.get('protein_grams_per_day') || 0),
    fats_grams_per_day: parseInt(formData.get('fats_grams_per_day') || 0),
    start_date: formData.get('start_date'),
    end_date: formData.get('end_date'),
    meals: {}
  };
  
  // Collect meals from each category
  const categories = ['Breakfast', 'Lunch', 'Dinner', 'Pre-Workout', 'Post-Workout', 'Snacks'];
  
  categories.forEach(category => {
    const mealsList = document.getElementById(`meals-${category.toLowerCase()}`);
    if (!mealsList) return;
    
    const meals = [];
    const mealItems = mealsList.querySelectorAll('.meal-item');
    
    mealItems.forEach((mealItem, index) => {
      const mealNameInput = mealItem.querySelector('.meal-name-input');
      const mealName = mealNameInput ? mealNameInput.value.trim() : '';
      
      // Get macro values (required)
      const caloriesInput = mealItem.querySelector('.meal-calories-input');
      const proteinInput = mealItem.querySelector('.meal-protein-input');
      const carbsInput = mealItem.querySelector('.meal-carbs-input');
      const fatsInput = mealItem.querySelector('.meal-fats-input');
      
      const mealCalories = parseInt(caloriesInput ? caloriesInput.value : 0);
      const mealProtein = parseInt(proteinInput ? proteinInput.value : 0);
      const mealCarbs = parseInt(carbsInput ? carbsInput.value : 0);
      const mealFats = parseInt(fatsInput ? fatsInput.value : 0);
      
      // Validate required fields
      if (!mealName || mealCalories <= 0 || mealProtein < 0 || mealCarbs < 0 || mealFats < 0) {
        return; // Skip invalid meals
      }
      
      const selectedFoods = mealItem.querySelectorAll('.food-item-card.selected');
      const foodItems = [];
      
      selectedFoods.forEach(foodCard => {
        const foodId = parseInt(foodCard.getAttribute('data-food-id'));
        const servingsInput = foodCard.querySelector(`[data-servings-input="${foodId}"]`);
        const servings = parseInt(servingsInput ? servingsInput.value : 1);
        
        if (foodId && servings > 0) {
          foodItems.push({
            food_item_id: foodId,
            quantity_servings: servings
          });
        }
      });
      
      // Include meal even if no food items are selected (AI-generated meals might not have matched food items yet)
      // The doctor can add food items manually later
      meals.push({
        name: mealName,
        calories: mealCalories,
        protein: mealProtein,
        carbs: mealCarbs,
        fats: mealFats,
        food_items: foodItems // Can be empty array
      });
    });
    
    if (meals.length > 0) {
      data.meals[category] = meals;
    }
  });
  
  return data;
}

async function previewMealPlan() {
  const data = collectMealPlanData();
  
  if (!data || !data.member_id || !data.title) {
    showToast("Please fill in member and title fields", "error");
    return;
  }
  
  if (Object.keys(data.meals).length === 0) {
    showToast("Please add at least one meal", "error");
    return;
  }
  
  // Calculate totals from meals (for display purposes only)
  // But use user-specified values for the actual meal plan totals
  let calculatedCal = 0, calculatedCarbs = 0, calculatedProtein = 0, calculatedFats = 0;
  
  Object.values(data.meals).flat().forEach(meal => {
    // If meal has direct macro values, use those (from AI-generated meals)
    if (meal.calories > 0 && meal.protein >= 0 && meal.carbs >= 0 && meal.fats >= 0) {
      calculatedCal += meal.calories;
      calculatedCarbs += meal.carbs;
      calculatedProtein += meal.protein;
      calculatedFats += meal.fats;
    } else if (meal.food_items && meal.food_items.length > 0) {
      // Otherwise calculate from food items
      meal.food_items.forEach(foodItem => {
        const food = allFoodItems.find(f => f.Food_Item_ID == foodItem.food_item_id);
        if (food) {
          const servings = foodItem.quantity_servings;
          calculatedCal += food.Calories * servings;
          calculatedCarbs += food.Carbs_Grams * servings;
          calculatedProtein += food.Protein_Grams * servings;
          calculatedFats += food.Fats_Grams * servings;
        }
      });
    }
  });
  
  // Use user-specified values (from form inputs), not calculated totals
  const totalCal = data.total_daily_calories || calculatedCal;
  const totalCarbs = data.carbs_grams_per_day || calculatedCarbs;
  const totalProtein = data.protein_grams_per_day || calculatedProtein;
  const totalFats = data.fats_grams_per_day || calculatedFats;
  
  // Check if meal totals are within acceptable range (±100 calories)
  const calorieDifference = Math.abs(calculatedCal - totalCal);
  const isWithinRange = calorieDifference <= 100;
  
  // Get member name
  const members = await fetchJSON(new URL("../api/nutritionist/members/list.php", window.location.href).href);
  const member = members.find(m => m.Member_Id == data.member_id);
  const memberName = member ? `${member.First_Name} ${member.Last_Name}` : 'Member';
  
  // Build preview HTML
  let previewHTML = `
    <div style="padding: 20px;">
      <h3 style="margin-bottom: 10px;">${data.title}</h3>
      <p style="color: var(--muted); margin-bottom: 20px;">${data.description || 'No description'}</p>
      
      <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 30px;">
        <div style="background: var(--card-bg); padding: 15px; border-radius: 8px;">
          <div style="font-size: 0.9rem; color: var(--muted);">Member</div>
          <div style="font-weight: 600;">${memberName}</div>
        </div>
        <div style="background: var(--card-bg); padding: 15px; border-radius: 8px;">
          <div style="font-size: 0.9rem; color: var(--muted);">Total Calories (Target)</div>
          <div style="font-weight: 600;">${totalCal} cal</div>
          ${calculatedCal !== totalCal ? `
            <div style="font-size: 0.75rem; color: ${Math.abs(calculatedCal - totalCal) <= 100 ? 'var(--success-clr, #10b981)' : 'var(--warning-clr, #f59e0b)'}; margin-top: 4px;">
              Meals total: ${calculatedCal} cal ${Math.abs(calculatedCal - totalCal) <= 100 ? '✓' : '⚠'}
            </div>
          ` : ''}
        </div>
        <div style="background: var(--card-bg); padding: 15px; border-radius: 8px;">
          <div style="font-size: 0.9rem; color: var(--muted);">Carbs</div>
          <div style="font-weight: 600;">${totalCarbs}g</div>
        </div>
        <div style="background: var(--card-bg); padding: 15px; border-radius: 8px;">
          <div style="font-size: 0.9rem; color: var(--muted);">Protein</div>
          <div style="font-weight: 600;">${totalProtein}g</div>
        </div>
        <div style="background: var(--card-bg); padding: 15px; border-radius: 8px;">
          <div style="font-size: 0.9rem; color: var(--muted);">Fats</div>
          <div style="font-weight: 600;">${totalFats}g</div>
        </div>
      </div>
      
      <h4 style="margin-bottom: 15px;">Meal Schedule</h4>
      <div style="max-height: 400px; overflow-y: auto;">
  `;
  
  Object.keys(data.meals).forEach(category => {
    const meals = data.meals[category];
    if (meals.length === 0) return;
    
    previewHTML += `
      <div style="background: var(--card-bg); padding: 15px; margin-bottom: 10px; border-radius: 8px;">
        <h5 style="margin: 0 0 15px 0; color: var(--accent-secondary);">${category}</h5>
    `;
    
    meals.forEach(meal => {
      let mealCal = 0, mealCarbs = 0, mealProtein = 0, mealFats = 0;
      
      meal.food_items.forEach(foodItem => {
        const food = allFoodItems.find(f => f.Food_Item_ID == foodItem.food_item_id);
        if (food) {
          const servings = foodItem.quantity_servings;
          mealCal += food.Calories * servings;
          mealCarbs += food.Carbs_Grams * servings;
          mealProtein += food.Protein_Grams * servings;
          mealFats += food.Fats_Grams * servings;
        }
      });
      
      previewHTML += `
        <div style="padding: 10px; margin-bottom: 10px; background: var(--base-clr); border-radius: 6px; border-left: 3px solid var(--accent-secondary);">
          <div style="font-weight: 600; margin-bottom: 5px;">${meal.name}</div>
          <div style="font-size: 0.9rem; color: var(--muted); margin-bottom: 8px;">
            ${mealCal} cal | ${mealCarbs}g carbs | ${mealProtein}g protein | ${mealFats}g fats
          </div>
          <div style="font-size: 0.85rem;">
            <strong>Food Items:</strong>
            <ul style="margin: 5px 0 0 20px; padding: 0;">
              ${meal.food_items.map(foodItem => {
                const food = allFoodItems.find(f => f.Food_Item_ID == foodItem.food_item_id);
                return food ? `<li>${food.Name} (${foodItem.quantity_servings} serving${foodItem.quantity_servings > 1 ? 's' : ''})</li>` : '';
              }).join('')}
            </ul>
          </div>
        </div>
      `;
    });
    
    previewHTML += `</div>`;
  });
  
  previewHTML += `
      </div>
    </div>
  `;
  
  // Store preview data
  // Keep user-specified values from form (these are what will be saved)
  currentPreviewData = {
    ...data,
    // Ensure user-specified values are preserved
    total_daily_calories: data.total_daily_calories || totalCal,
    carbs_grams_per_day: data.carbs_grams_per_day || totalCarbs,
    protein_grams_per_day: data.protein_grams_per_day || totalProtein,
    fats_grams_per_day: data.fats_grams_per_day || totalFats,
    // Store calculated totals for display/reference only
    calculated_totals: {
      calories: calculatedCal,
      carbs: calculatedCarbs,
      protein: calculatedProtein,
      fats: calculatedFats
    },
    member: { id: data.member_id, name: memberName }
  };
  
  document.getElementById("mealplan-preview-body").innerHTML = previewHTML;
  document.getElementById("mealplan-preview-modal").classList.add("active");
  document.body.style.overflow = "hidden";
  
  // Setup confirm button
  const confirmBtn = document.getElementById("confirm-mealplan-btn");
  if (confirmBtn) {
    confirmBtn.onclick = () => confirmMealPlanSave();
  }
}

function closeMealPlanPreview() {
  document.getElementById("mealplan-preview-modal").classList.remove("active");
  document.body.style.overflow = "";
  currentPreviewData = null;
}

async function confirmMealPlanSave() {
  if (!currentPreviewData) return;
  
  const confirmBtn = document.getElementById("confirm-mealplan-btn");
  confirmBtn.disabled = true;
  confirmBtn.textContent = "Saving...";
  
  try {
    // Keep user-specified values - don't override with calculated totals
    // The user set these values in the form and they should be used as-is
    // Only use calculated values if user values are missing or zero
    if (!currentPreviewData.total_daily_calories || currentPreviewData.total_daily_calories <= 0) {
      currentPreviewData.total_daily_calories = currentPreviewData.calculated_totals.calories;
    }
    if (!currentPreviewData.carbs_grams_per_day || currentPreviewData.carbs_grams_per_day <= 0) {
      currentPreviewData.carbs_grams_per_day = currentPreviewData.calculated_totals.carbs;
    }
    if (!currentPreviewData.protein_grams_per_day || currentPreviewData.protein_grams_per_day <= 0) {
      currentPreviewData.protein_grams_per_day = currentPreviewData.calculated_totals.protein;
    }
    if (!currentPreviewData.fats_grams_per_day || currentPreviewData.fats_grams_per_day <= 0) {
      currentPreviewData.fats_grams_per_day = currentPreviewData.calculated_totals.fats;
    }
    
    const response = await fetchJSON(new URL("../api/nutritionist/mealplans/create_complete.php", window.location.href).href, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(currentPreviewData)
    });
    
    if (!response.success) {
      showToast(response.error || "Failed to create meal plan", "error");
      confirmBtn.disabled = false;
      confirmBtn.textContent = "Confirm & Save";
      return;
    }
    
    showToast(`Meal plan created successfully! ${response.meals_created} meals and ${response.food_items_created} food items added.`, "success");
    closeMealPlanPreview();
    
    // Reload meal plans if on that section
    if (document.getElementById("mealplans-table-body")) {
      loadMealPlans();
    }
    
    // Reset form
    const form = document.getElementById("create-mealplan-form");
    if (form) {
      form.reset();
      document.getElementById("meal-categories-container").innerHTML = '';
      renderMealCategories();
    }
    
    currentMemberId = null;
  } catch (err) {
    console.error(err);
    showToast("Failed to save meal plan", "error");
    confirmBtn.disabled = false;
    confirmBtn.textContent = "Confirm & Save";
  }
}

async function createMealPlan(form) {
  const data = collectMealPlanData();
  
  if (!data || !data.member_id || !data.title) {
    showToast("Please fill in all required fields", "error");
    return;
  }
  
  if (Object.keys(data.meals).length === 0) {
    showToast("Please add at least one meal", "error");
    return;
  }
  
  // Show preview first
  await previewMealPlan();
}

// ==================== PROFILE SECTION ====================
function getProfileHTML() {
  return `
    <header class="dashboard-header">
      <div class="header-content">
        <h1>Profile</h1>
        <p class="subtitle">Manage your nutritionist profile</p>
      </div>
    </header>
    
    <div class="profile-container">
      <div class="profile-card">
        <div class="profile-header">
          <div class="profile-avatar" id="profile-avatar">N</div>
          <div class="profile-info">
            <h2 id="profile-name">Loading...</h2>
            <p id="profile-role">Nutritionist</p>
          </div>
        </div>
        <div class="profile-details" id="profile-details">
          <div class="detail-item">
            <strong>Loading profile data...</strong>
          </div>
        </div>
      </div>
    </div>
  `;
}

async function initProfileFunctionality() {
  try {
    const profile = await fetchJSON(new URL("../api/nutritionist/profile/get.php", window.location.href).href);
    
    // Update avatar with first letter
    const avatar = document.getElementById("profile-avatar");
    if (avatar && profile.First_Name) {
      avatar.textContent = profile.First_Name.charAt(0).toUpperCase();
    }
    
    // Update name
    const nameEl = document.getElementById("profile-name");
    if (nameEl) {
      nameEl.textContent = `${profile.First_Name || ''} ${profile.Last_Name || ''}`.trim() || 'Nutritionist';
    }
    
    // Update role
    const roleEl = document.getElementById("profile-role");
    if (roleEl) {
      roleEl.textContent = profile.Specialization_Main || 'Nutritionist';
    }
    
    // Update details
    const detailsEl = document.getElementById("profile-details");
    if (detailsEl) {
      const formatDate = (dateStr) => {
        if (!dateStr || dateStr === '0000-00-00' || dateStr === '0000-00-00 00:00:00') return 'N/A';
        try {
          return new Date(dateStr).toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
          });
        } catch {
          return dateStr;
        }
      };
      
      const formatPhone = (phone) => {
        if (!phone || phone === '0') return 'N/A';
        return phone.toString();
      };
      
      detailsEl.innerHTML = `
        <div class="detail-item">
          <strong>Email:</strong> ${profile.Email || 'N/A'}
        </div>
        <div class="detail-item">
          <strong>Phone:</strong> ${formatPhone(profile.Phone_Number)}
        </div>
        <div class="detail-item">
          <strong>Date of Birth:</strong> ${formatDate(profile.DOB)}
        </div>
        <div class="detail-item">
          <strong>Gender:</strong> ${profile.Gender || 'N/A'}
        </div>
        <div class="detail-item">
          <strong>Specialization:</strong> ${profile.Specialization_Main || 'N/A'}
        </div>
        ${profile.Licence_Number ? `
        <div class="detail-item">
          <strong>License Number:</strong> ${profile.Licence_Number}
        </div>
        ` : ''}
        ${profile.Years_Experience ? `
        <div class="detail-item">
          <strong>Years of Experience:</strong> ${profile.Years_Experience}
        </div>
        ` : ''}
        ${profile.Clinic_Location ? `
        <div class="detail-item">
          <strong>Clinic Location:</strong> ${profile.Clinic_Location}
        </div>
        ` : ''}
        <div class="detail-item">
          <strong>Certifications:</strong> ${profile.Certifications || 'N/A'}
        </div>
        <div class="detail-item">
          <strong>Rating:</strong> ${profile.Avg_rating ? `${profile.Avg_rating}/5 (${profile.rating_count || 0} reviews)` : 'No ratings yet'}
        </div>
        <div class="detail-item">
          <strong>Current Members:</strong> ${profile.member_count || 0}
        </div>
        <div class="detail-item">
          <strong>Active Meal Plans:</strong> ${profile.active_plans_count || 0}
        </div>
        <div class="detail-item">
          <strong>Accepting New Clients:</strong> ${profile.Is_accepting_new ? 'Yes' : 'No'}
        </div>
        <div class="detail-item">
          <strong>Last Login:</strong> ${formatDate(profile.Last_Login)}
        </div>
        <div class="detail-item">
          <strong>Member Since:</strong> ${formatDate(profile.Created_at)}
        </div>
        ${profile.Bio ? `
        <div class="detail-item">
          <strong>Bio:</strong><br>
          <p style="margin-top: 8px; color: var(--text-clr); opacity: 0.9;">${profile.Bio}</p>
        </div>
        ` : ''}
      `;
    }
  } catch (err) {
    console.error("Error loading profile:", err);
    const detailsEl = document.getElementById("profile-details");
    if (detailsEl) {
      detailsEl.innerHTML = `
        <div class="detail-item" style="color: var(--danger-color);">
          <strong>Error:</strong> Failed to load profile data. ${err.message || ''}
        </div>
      `;
    }
  }
}

// Helper function for modal
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

// ==================== AI MEAL PLAN GENERATOR ====================
async function generateAIMealPlan() {
  const memberSelect = document.getElementById('create-member-select');
  const memberId = memberSelect ? parseInt(memberSelect.value) : 0;
  
  if (!memberId) {
    showToast("Please select a member first", "error");
    return;
  }
  
  // Validate required fields
  const titleInput = document.querySelector('input[name="title"]');
  const caloriesInput = document.getElementById('daily-calories');
  const carbsInput = document.getElementById('daily-carbs');
  const proteinInput = document.getElementById('daily-protein');
  const fatsInput = document.getElementById('daily-fats');
  
  const title = titleInput ? titleInput.value.trim() : '';
  const calories = caloriesInput ? parseInt(caloriesInput.value) : 0;
  const carbs = carbsInput ? parseInt(carbsInput.value) : 0;
  const protein = proteinInput ? parseInt(proteinInput.value) : 0;
  const fats = fatsInput ? parseInt(fatsInput.value) : 0;
  
  if (!title || calories <= 0 || carbs <= 0 || protein <= 0 || fats <= 0) {
    showToast("Please fill in Plan Title, Calories, and all macros (Carbs, Protein, Fats) first", "error");
    return;
  }
  
  const generateBtn = document.getElementById('generate-ai-mealplan-btn');
  const statusDiv = document.getElementById('ai-generator-status');
  const statusText = document.getElementById('ai-status-text');
  
  if (!generateBtn || !statusDiv || !statusText) return;
  
  // Disable button and show status
  generateBtn.disabled = true;
  generateBtn.innerHTML = '<span>Generating...</span>';
  statusDiv.style.display = 'block';
  statusText.textContent = 'Generating personalized meal plan with AI... This may take a moment.';
  statusText.style.color = 'var(--muted)';
  
  try {
    const response = await fetchJSON(new URL("../api/nutritionist/mealplans/generate_preview.php", window.location.href).href, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        member_id: memberId,
        days: 7,
        goal: `achieve ${calories} calories per day with ${carbs}g carbs, ${protein}g protein, and ${fats}g fats`
      })
    });
    
    if (!response.success) {
      throw new Error(response.error || 'Failed to generate meal plan');
    }
    
    statusText.textContent = 'Meal plan generated! Populating meals...';
    
    // Update description if available
    const program = response.program || {};
    const form = document.getElementById("create-mealplan-form");
    if (form) {
      const descInput = form.querySelector('textarea[name="description"]');
      if (descInput && program.description && !descInput.value.trim()) {
        descInput.value = program.description;
      }
    }
    
    // Populate meals
    const meals = response.meals || {};
    console.log("Received meals from API:", meals);
    console.log("Meal categories:", Object.keys(meals));
    
    // Ensure categories are rendered first
    const container = document.getElementById("meal-categories-container");
    if (!container || !container.innerHTML.trim()) {
      console.log("Rendering meal categories...");
      renderMealCategories();
      // Wait for DOM to update
      await new Promise(resolve => setTimeout(resolve, 300));
    }
    
    // Populate meals
    await populateMealsFromAI(meals);
    
    statusText.textContent = 'Meal plan generated successfully! You can now review and edit the meals below.';
    statusText.style.color = 'var(--success-color)';
    
    setTimeout(() => {
      statusDiv.style.display = 'none';
    }, 3000);
    
    showToast("Meal plan generated successfully! Review and edit as needed.", "success");
    
  } catch (err) {
    console.error("Error generating meal plan:", err);
    
    let errorMessage = err.message || 'Failed to generate meal plan';
    let userMessage = errorMessage;
    
    // Handle specific API errors
    if (errorMessage.includes('503') || errorMessage.includes('overloaded') || errorMessage.includes('UNAVAILABLE')) {
      userMessage = 'The AI service is currently overloaded. Please try again in a few moments.';
      statusText.textContent = 'AI Service Temporarily Unavailable - Please try again in a moment';
    } else if (errorMessage.includes('API request failed')) {
      userMessage = 'Failed to connect to AI service. Please check your internet connection and try again.';
      statusText.textContent = 'Connection Error - Please try again';
    } else if (errorMessage.includes('GEMINI_API_KEY')) {
      userMessage = 'AI service configuration error. Please contact support.';
      statusText.textContent = 'Configuration Error';
    } else {
      statusText.textContent = 'Error: ' + errorMessage;
    }
    
    statusText.style.color = 'var(--danger-color)';
    showToast(userMessage, "error");
  } finally {
    // Re-enable button and restore validation
    setupAIGenerateButtonValidation();
  }
}

async function populateMealsFromAI(meals) {
  console.log("Populating meals from AI:", meals);
  console.log("Available meal keys:", Object.keys(meals));
  console.log("allFoodItems length:", allFoodItems ? allFoodItems.length : 0);
  
  // Ensure allFoodItems is loaded
  if (!allFoodItems || allFoodItems.length === 0) {
    console.log("Loading food items...");
    try {
      allFoodItems = await fetchJSON(new URL("../api/nutritionist/fooditems/list.php", window.location.href).href);
      console.log("Loaded food items:", allFoodItems.length);
    } catch (err) {
      console.error("Error loading food items:", err);
      showToast("Failed to load food items", "error");
      return;
    }
  }
  
  const categoryMap = {
    'Breakfast': ['Breakfast', 'breakfast'],
    'Lunch': ['Lunch', 'lunch'],
    'Dinner': ['Dinner', 'dinner'],
    'Pre-Workout': ['Pre-Workout', 'Pre-Workout', 'pre-workout', 'preworkout', 'PreWorkout'],
    'Post-Workout': ['Post-Workout', 'Post-Workout', 'post-workout', 'postworkout', 'PostWorkout'],
    'Snacks': ['Snacks', 'snacks', 'Snack', 'snack']
  };
  
  const categories = ['Breakfast', 'Lunch', 'Dinner', 'Pre-Workout', 'Post-Workout', 'Snacks'];
  let mealsAdded = 0;
  
  // Wait a bit for DOM to be ready
  await new Promise(resolve => setTimeout(resolve, 200));
  
  categories.forEach(category => {
    // The ID format from renderMealCategories is: meals-${cat.name.toLowerCase()}
    // So "Pre-Workout" becomes "meals-pre-workout", "Post-Workout" becomes "meals-post-workout"
    const categoryId = `meals-${category.toLowerCase()}`;
    let mealsList = document.getElementById(categoryId);
    
    if (!mealsList) {
      console.warn(`Meals list not found for category: ${category} (looking for ID: ${categoryId})`);
      // List all available meal list elements for debugging
      const allMealLists = document.querySelectorAll('[id^="meals-"]');
      console.warn("Available meal list IDs:", Array.from(allMealLists).map(el => el.id));
      return;
    }
    
    // Try to find meals for this category using various key names
    let categoryMeals = null;
    const possibleKeys = categoryMap[category] || [category];
    
    // First try exact match
    if (meals[category] && Array.isArray(meals[category]) && meals[category].length > 0) {
      categoryMeals = meals[category];
      console.log(`✓ Found ${categoryMeals.length} meals for ${category} using exact key: ${category}`);
    } else {
      // Try alternative keys
      for (const key of possibleKeys) {
        if (meals[key] && Array.isArray(meals[key]) && meals[key].length > 0) {
          categoryMeals = meals[key];
          console.log(`✓ Found ${categoryMeals.length} meals for ${category} using key: ${key}`);
          break;
        }
      }
    }
    
    // Debug: log what we're looking for vs what exists
    if (!categoryMeals) {
      console.log(`✗ No meals found for category: ${category}`);
      console.log(`  Looking for keys:`, possibleKeys);
      console.log(`  Available keys in meals:`, Object.keys(meals));
      if (meals[category]) {
        console.log(`  meals[${category}] exists but is:`, typeof meals[category], Array.isArray(meals[category]) ? `array with ${meals[category].length} items` : 'not an array');
      }
    }
    
    if (categoryMeals && categoryMeals.length > 0) {
      console.log(`Populating ${categoryMeals.length} meals for ${category} into element:`, mealsList);
      console.log(`First meal structure:`, categoryMeals[0]);
      
      categoryMeals.forEach((meal, mealIndex) => {
          const mealId = `meal-${category.toLowerCase()}-${Date.now()}-${mealIndex}`;
          
          // Use macro values from API if available, otherwise calculate from food items
          let mealCalories = meal.calories || 0;
          let mealProtein = meal.protein || 0;
          let mealCarbs = meal.carbs || 0;
          let mealFats = meal.fats || 0;
          const selectedFoodItems = [];
          
          console.log(`Processing meal ${mealIndex + 1}: "${meal.name}" with ${meal.food_items ? meal.food_items.length : 0} food items`);
          
          if (meal.food_items && Array.isArray(meal.food_items) && meal.food_items.length > 0) {
            meal.food_items.forEach(fi => {
              const food = allFoodItems.find(f => f.Food_Item_ID == fi.food_item_id);
              if (food) {
                const servings = fi.quantity_servings || 1;
                
                // If macros not provided by API, calculate from food items
                if (!meal.calories || meal.calories === 0) {
                  mealCalories += food.Calories * servings;
                }
                if (!meal.protein || meal.protein === 0) {
                  mealProtein += food.Protein_Grams * servings;
                }
                if (!meal.carbs || meal.carbs === 0) {
                  mealCarbs += food.Carbs_Grams * servings;
                }
                if (!meal.fats || meal.fats === 0) {
                  mealFats += food.Fats_Grams * servings;
                }
                
                selectedFoodItems.push({
                  food_item_id: food.Food_Item_ID,
                  quantity_servings: servings
                });
              } else {
                console.warn(`Food item ${fi.food_item_id} not found in allFoodItems`);
              }
            });
          } else {
            console.warn(`Meal "${meal.name}" has no food_items or food_items is empty`);
          }
          
          const mealHTML = `
            <div class="meal-item" data-meal-id="${mealId}" style="padding: 15px; margin-bottom: 15px; background: var(--base-clr); border-radius: 8px; border: 1px solid var(--border-color);">
              <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <input type="text" class="form-control meal-name-input" placeholder="Meal name" value="${escapeHtml(meal.name || 'Meal')}" style="max-width: 300px;" required>
                <button type="button" class="btn-small" onclick="removeMeal('${mealId}')" style="background: var(--danger-color); color: white;">Remove</button>
              </div>
              
              <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 15px; padding: 10px; background: rgba(166, 111, 255, 0.05); border-radius: 6px;">
                <div>
                  <label style="font-size: 0.75rem; color: var(--muted); display: block; margin-bottom: 4px;">Calories *</label>
                  <input type="number" class="form-control meal-calories-input" value="${Math.round(mealCalories)}" min="0" required style="font-weight: 600;">
                </div>
                <div>
                  <label style="font-size: 0.75rem; color: var(--muted); display: block; margin-bottom: 4px;">Protein (g) *</label>
                  <input type="number" class="form-control meal-protein-input" value="${Math.round(mealProtein)}" min="0" required style="font-weight: 600;">
                </div>
                <div>
                  <label style="font-size: 0.75rem; color: var(--muted); display: block; margin-bottom: 4px;">Carbs (g) *</label>
                  <input type="number" class="form-control meal-carbs-input" value="${Math.round(mealCarbs)}" min="0" required style="font-weight: 600;">
                </div>
                <div>
                  <label style="font-size: 0.75rem; color: var(--muted); display: block; margin-bottom: 4px;">Fats (g) *</label>
                  <input type="number" class="form-control meal-fats-input" value="${Math.round(mealFats)}" min="0" required style="font-weight: 600;">
                </div>
              </div>
              
              <div style="margin-bottom: 15px;">
                <label style="font-size: 0.9rem; color: var(--muted); margin-bottom: 8px; display: block;">Food Items:</label>
                <div class="food-items-grid" id="food-grid-${mealId}">
                  ${allFoodItems.map(food => {
                    // Check if this food item is in the meal
                    const foodInMeal = selectedFoodItems.find(fi => fi.food_item_id == food.Food_Item_ID);
                    const isInMeal = !!foodInMeal;
                    const servings = foodInMeal ? foodInMeal.quantity_servings : 1;
                    
                    return `
                      <div class="food-item-card ${isInMeal ? 'selected' : ''}" data-food-id="${food.Food_Item_ID}" onclick="toggleFoodItem('${mealId}', ${food.Food_Item_ID}, this)">
                        <h4>${escapeHtml(food.Name)}</h4>
                        <div class="nutrition-info">
                          <span class="nutrition-badge">${food.Calories} cal</span>
                          <span class="nutrition-badge">${food.Protein_Grams}g protein</span>
                          <span class="nutrition-badge">${food.Carbs_Grams}g carbs</span>
                          <span class="nutrition-badge">${food.Fats_Grams}g fats</span>
                        </div>
                        <div style="margin-top: 10px; ${isInMeal ? '' : 'display: none;'}" class="servings-input-container">
                          <label style="font-size: 0.85rem; color: var(--muted);">Servings:</label>
                          <input type="number" class="form-control" min="1" value="${servings}" style="margin-top: 5px;" data-servings-input="${food.Food_Item_ID}" onchange="updateMealMacros('${mealId}')">
                        </div>
                      </div>
                    `;
                  }).join('')}
                </div>
              </div>
            </div>
          `;
          
          try {
            mealsList.insertAdjacentHTML('beforeend', mealHTML);
            mealsAdded++;
            console.log(`✓ Added meal "${meal.name || 'Meal'}" to ${category}`);
          } catch (err) {
            console.error(`Error adding meal to ${category}:`, err);
            console.error("Meal HTML length:", mealHTML.length);
            console.error("MealsList element:", mealsList);
          }
        });
      } else {
        console.log(`No meals found for category: ${category}`);
      }
    });
  
  console.log(`Total meals added: ${mealsAdded}`);
  console.log("Meal categories in response:", Object.keys(meals));
  console.log("Total categories processed:", categories.length);
  
  if (mealsAdded === 0) {
    console.error("No meals were added! Available meal categories:", Object.keys(meals));
    console.error("Meal data structure:", JSON.stringify(meals, null, 2));
    
    // Try to find what's wrong
    Object.keys(meals).forEach(key => {
      console.log(`Category "${key}":`, Array.isArray(meals[key]) ? `${meals[key].length} meals` : typeof meals[key]);
      if (Array.isArray(meals[key]) && meals[key].length > 0) {
        console.log(`  First meal in ${key}:`, meals[key][0]);
      }
    });
    
    showToast("Meals generated but failed to populate. Please check console for details.", "warning");
  } else {
    showToast(`Successfully added ${mealsAdded} meal(s) to the plan!`, "success");
  }
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

// Expose functions globally
window.closeMealPlanPreview = closeMealPlanPreview;
window.viewMedicalRecord = viewMedicalRecord;
window.viewMemberMealPlans = viewMemberMealPlans;
window.viewMealPlanDetails = viewMealPlanDetails;
window.createMealPlanForMember = createMealPlanForMember;
window.addMealToCategory = addMealToCategory;
window.toggleFoodItem = toggleFoodItem;
window.removeMeal = removeMeal;
window.previewMealPlan = previewMealPlan;
window.generateAIMealPlan = generateAIMealPlan;
window.updateMealMacros = updateMealMacros;
