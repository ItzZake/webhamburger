// Coach Dashboard - Complete implementation with all features
document.addEventListener("DOMContentLoaded", function () {
  initCoachDashboard();
});

let currentPreviewData = null;
let currentMemberId = null;

// Initialize dashboard
function initCoachDashboard() {
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
    case "programs":
      contentArea.innerHTML = getProgramsHTML();
      initProgramsFunctionality();
      break;
    case "workouts":
      contentArea.innerHTML = getWorkoutsHTML();
      initWorkoutsFunctionality();
      break;
    case "generate":
      contentArea.innerHTML = getGenerateHTML();
      initGenerateFunctionality();
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
        <h1>Coach Dashboard</h1>
        <p class="subtitle">Manage your members and workout programs</p>
      </div>
    </header>

    <div class="dashboard-alert">
      <svg xmlns="http://www.w3.org/2000/svg" height="20" viewBox="0 -960 960 960" width="20">
        <path d="M440-280h80v-240h-80v240Zm40-320q17 0 28.5-11.5T520-640q0-17-11.5-28.5T480-680q-17 0-28.5 11.5T440-640q0 17 11.5 28.5T480-600Zm0 520q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z"/>
      </svg>
      <span>Welcome back! Here's your coaching overview.</span>
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
          <h3>Active Programs</h3>
          <div class="stat-value" id="active-programs">0</div>
          <div class="stat-change positive">Running</div>
        </div>
        <div class="stat-card">
          <h3>Workouts Created</h3>
          <div class="stat-value" id="total-workouts">0</div>
          <div class="stat-change positive">Total</div>
        </div>
        <div class="stat-card">
          <h3>This Week</h3>
          <div class="stat-value" id="week-programs">0</div>
          <div class="stat-change positive">Programs</div>
        </div>
      </div>
    </section>

    <section class="quick-actions">
      <h2>Quick Actions</h2>
      <div class="action-grid">
        <button class="action-card" onclick="loadSection('generate')">
          <svg xmlns="http://www.w3.org/2000/svg" height="32px" viewBox="0 -960 960 960" width="32px">
            <path d="M440-280h80v-240h-80v240Zm40-320q17 0 28.5-11.5T520-640q0-17-11.5-28.5T480-680q-17 0-28.5 11.5T440-640q0 17 11.5 28.5T480-600Zm0 520q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z"/>
          </svg>
          <span>Generate AI Workout</span>
        </button>
        <button class="action-card" onclick="loadSection('members')">
          <svg xmlns="http://www.w3.org/2000/svg" height="32px" viewBox="0 -960 960 960" width="32px">
            <path d="M480-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM160-160v-112q0-34 17.5-62.5T224-378q62-31 126-46.5T480-440q66 0 130 15.5T736-378q29 15 46.5 43.5T800-272v112H160Zm80-80h480v-32q0-11-5.5-20T700-306q-54-27-109-40.5T480-360q-56 0-111 13.5T260-306q-9 5-14.5 14t-5.5 20v32Zm240-320q33 0 56.5-23.5T560-640q0-33-23.5-56.5T480-720q-33 0-56.5 23.5T400-640q0 33 23.5 56.5T480-560Zm0-80Zm0 400Z"/>
          </svg>
          <span>View Members</span>
        </button>
        <button class="action-card" onclick="loadSection('programs')">
          <svg xmlns="http://www.w3.org/2000/svg" height="32px" viewBox="0 -960 960 960" width="32px">
            <path d="m826-585-56-56 30-31-128-128-31 30-57-57 30-31q23-23 57-22.5t57 23.5l129 129q23 23 23 56.5T857-615l-31 30ZM346-104q-23 23-56.5 23T233-104L104-233q-23-23-23-56.5t23-56.5l30-30 57 57-31 30 129 129 30-31 57 57-30 30Zm397-336 57-57-303-303-57 57 303 303ZM463-160l57-58-302-302-58 57 303 303Zm-6-234 110-109-64-64-109 110 63 63Zm63 290q-23 23-57 23t-57-23L104-406q-23-23-23-57t23-57l57-57q23-23 56.5-23t56.5 23l63 63 110-110-63-62q-23-23-23-57t23-57l57-57q23-23 56.5-23t56.5 23l303 303q23 23 23 56.5T857-441l-57 57q-23 23-57 23t-57-23l-62-63-110 110 63 63q23 23 23 56.5T577-161l-57 57Z"/>
          </svg>
          <span>Workout Programs</span>
        </button>
        <button class="action-card" onclick="loadSection('workouts')">
          <svg xmlns="http://www.w3.org/2000/svg" height="32px" viewBox="0 -960 960 960" width="32px">
            <path d="m826-585-56-56 30-31-128-128-31 30-57-57 30-31q23-23 57-22.5t57 23.5l129 129q23 23 23 56.5T857-615l-31 30ZM346-104q-23 23-56.5 23T233-104L104-233q-23-23-23-56.5t23-56.5l30-30 57 57-31 30 129 129 30-31 57 57-30 30Zm397-336 57-57-303-303-57 57 303 303ZM463-160l57-58-302-302-58 57 303 303Zm-6-234 110-109-64-64-109 110 63 63Zm63 290q-23 23-57 23t-57-23L104-406q-23-23-23-57t23-57l57-57q23-23 56.5-23t56.5 23l63 63 110-110-63-62q-23-23-23-57t23-57l57-57q23-23 56.5-23t56.5 23l303 303q23 23 23 56.5T857-441l-57 57q-23 23-57 23t-57-23l-62-63-110 110 63 63q23 23 23 56.5T577-161l-57 57Z"/>
          </svg>
          <span>My Workouts</span>
        </button>
      </div>
    </section>
  `;
}

function initDashboardFunctionality() {
  // Dashboard-specific functionality can be added here
  // Theme toggle is handled globally in setupThemeToggle()
}

async function loadDashboardStats() {
  try {
    const [members, programs, workouts] = await Promise.all([
      fetchJSON(new URL("../api/coach/members/list.php", window.location.href).href),
      fetchJSON(new URL("../api/coach/workoutprograms/list.php", window.location.href).href),
      fetchJSON(new URL("../api/coach/workouts/list.php", window.location.href).href)
    ]);
    
    document.getElementById("total-members").textContent = members.length || 0;
    document.getElementById("active-programs").textContent = programs.filter(p => p.Status === 'Active').length || 0;
    document.getElementById("total-workouts").textContent = workouts.length || 0;
    
    const weekAgo = new Date();
    weekAgo.setDate(weekAgo.getDate() - 7);
    const weekPrograms = programs.filter(p => new Date(p.Created_at) >= weekAgo).length;
    document.getElementById("week-programs").textContent = weekPrograms || 0;
  } catch (err) {
    console.error("Error loading stats:", err);
  }
}

// ==================== MEMBERS SECTION ====================
function getMembersHTML() {
  return `
    <div class="section-header">
      <h1>My Members</h1>
      <button class="action-btn" onclick="loadSection('generate')">
        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px">
          <path d="M440-280h80v-240h-80v240Zm40-320q17 0 28.5-11.5T520-640q0-17-11.5-28.5T480-680q-17 0-28.5 11.5T440-640q0 17 11.5 28.5T480-600Zm0 520q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z"/>
        </svg>
        Generate Workout
      </button>
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
          <th>Training Goals</th>
          <th>Programs</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="members-table-body">
        <tr><td colspan="6" style="text-align: center; padding: 2rem;">Loading members...</td></tr>
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

let allMembers = [];

async function loadMembers() {
  try {
    allMembers = await fetchJSON(new URL("../api/coach/members/list.php", window.location.href).href);
    renderMembers(allMembers);
  } catch (err) {
    console.error("Error loading members:", err);
    showToast("Failed to load members", "error");
    document.getElementById("members-table-body").innerHTML = 
      '<tr><td colspan="6" style="text-align: center; padding: 2rem; color: var(--danger-color);">Error loading members</td></tr>';
  }
}

function renderMembers(members) {
  const tbody = document.getElementById("members-table-body");
  if (!tbody) return;
  
  if (members.length === 0) {
    tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 2rem;">No members assigned</td></tr>';
        return;
      }
  
  tbody.innerHTML = members.map(m => `
    <tr>
      <td>${m.Member_Id}</td>
      <td>${m.First_Name} ${m.Last_Name}</td>
      <td>${m.Email || 'N/A'}</td>
      <td>${m.Training_Goals || 'Not specified'}</td>
      <td><button class="btn-small btn-view" onclick="viewMemberPrograms(${m.Member_Id}, '${m.First_Name} ${m.Last_Name}')">View All</button></td>
      <td class="action-buttons">
        <button class="btn-small" style="background: var(--btn-primary-bg); color: white;" onclick="viewCurrentPlan(${m.Member_Id}, '${m.First_Name} ${m.Last_Name}')">Current Plan</button>
        <button class="btn-small action-btn secondary" onclick="transferMember(${m.Member_Id}, '${m.First_Name} ${m.Last_Name}')">Transfer</button>
        <button class="btn-small" style="background: var(--btn-primary-bg); color: white;" onclick="generateForMember(${m.Member_Id}, '${m.First_Name} ${m.Last_Name}')">Generate</button>
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

async function viewMemberPrograms(memberId, memberName) {
  try {
    const programs = await fetchJSON(new URL(`../api/coach/workoutprograms/list.php?member_id=${memberId}`, window.location.href).href);
    showModal("All Programs for " + memberName, `
      <div style="max-height: 400px; overflow-y: auto;">
        ${programs.length === 0 ? '<p>No programs found</p>' : programs.map(p => `
          <div style="padding: 10px; margin: 10px 0; background: var(--card-bg); border-radius: 8px; border-left: 3px solid ${p.Status === 'Active' ? 'var(--success-color)' : 'var(--muted)'};">
            <h4>${p.Title} ${p.Status === 'Active' ? '<span class="status-badge status-active">Active</span>' : ''}</h4>
            <p><strong>Status:</strong> <span class="status-badge status-${(p.Status || 'inactive').toLowerCase()}">${p.Status || 'Inactive'}</span></p>
            <p><strong>Goal:</strong> ${p.Goal || 'N/A'}</p>
            <p><strong>Duration:</strong> ${p.Weeks_Duration} weeks</p>
            <p><strong>Created:</strong> ${new Date(p.Created_at).toLocaleDateString()}</p>
          </div>
        `).join('')}
      </div>
    `);
    } catch (err) {
      console.error(err);
    showToast("Failed to load programs", "error");
  }
}

async function viewCurrentPlan(memberId, memberName) {
  try {
    const programs = await fetchJSON(new URL(`../api/coach/workoutprograms/list.php?member_id=${memberId}`, window.location.href).href);
    
    // Find active program
    const activeProgram = programs.find(p => p.Status === 'Active');
    const allPrograms = programs.filter(p => p.Status !== 'Active');
    
    if (!activeProgram && programs.length === 0) {
      showModal("Current Plan for " + memberName, `
        <div style="padding: 20px; text-align: center;">
          <p style="color: var(--muted); margin-bottom: 20px;">No programs found for this member.</p>
          <button class="action-btn" onclick="generateForMember(${memberId}, '${memberName}'); document.getElementById('modal').classList.remove('active');">Generate New Plan</button>
        </div>
      `);
      return;
    }
    
    if (!activeProgram) {
      showModal("Current Plan for " + memberName, `
        <div style="padding: 20px;">
          <p style="color: var(--warning-color); margin-bottom: 20px;">No active plan found. All plans are inactive.</p>
          ${allPrograms.length > 0 ? `
            <h4 style="margin-top: 20px;">Available Plans:</h4>
            <div style="margin-top: 15px;">
              ${allPrograms.map(p => `
                <div style="padding: 10px; margin: 10px 0; background: var(--card-bg); border-radius: 8px;">
                  <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                      <h5 style="margin: 0 0 5px 0;">${p.Title}</h5>
                      <p style="margin: 0; color: var(--muted); font-size: 0.9rem;">${p.Goal || 'N/A'} • ${p.Weeks_Duration} weeks</p>
                    </div>
                    <button class="btn-small" style="background: var(--btn-primary-bg); color: white;" onclick="switchToPlan(${memberId}, ${p.Workout_ID}, '${memberName}')">Activate</button>
                  </div>
                </div>
              `).join('')}
            </div>
          ` : ''}
          <button class="action-btn" style="margin-top: 20px; width: 100%;" onclick="generateForMember(${memberId}, '${memberName}'); document.getElementById('modal').classList.remove('active');">Generate New Plan</button>
        </div>
      `);
      return;
    }
    
    // Get exercises for the active program
    let exercises = [];
    try {
      exercises = await fetchJSON(new URL(`../api/coach/workoutexercise/list.php?workout_id=${activeProgram.Workout_ID}`, window.location.href).href);
    } catch (e) {
      console.error("Error loading exercises:", e);
    }
    
    // Group exercises by day
    const exercisesByDay = {};
    exercises.forEach(ex => {
      const day = ex.Day_Number || 0;
      if (!exercisesByDay[day]) exercisesByDay[day] = [];
      exercisesByDay[day].push(ex);
    });
    
    let exercisesHTML = '';
    Object.keys(exercisesByDay).sort((a, b) => a - b).forEach(day => {
      const dayExercises = exercisesByDay[day];
      exercisesHTML += `
        <div style="margin-bottom: 20px; padding: 15px; background: var(--card-bg); border-radius: 8px;">
          <h5 style="margin: 0 0 10px 0; color: var(--accent-secondary);">Day ${day}</h5>
          ${dayExercises.length === 0 ? '<p style="color: var(--muted);">Rest day</p>' : dayExercises.map(ex => `
            <div style="padding: 8px; margin: 5px 0; background: var(--base-clr); border-radius: 6px; border-left: 3px solid var(--accent-secondary);">
              <div style="font-weight: 600;">${ex.Sequence_Order || ''}. Exercise ID: ${ex.Exercise_ID}</div>
              <div style="font-size: 0.9rem; color: var(--muted); margin-top: 5px;">
                ${ex.Sets ? `${ex.Sets} sets` : ''} ${ex.Reps ? `× ${ex.Reps} reps` : ''} ${ex.Rest_Time ? `| Rest: ${ex.Rest_Time}` : ''}
              </div>
              ${ex.Notes ? `<div style="font-size: 0.85rem; margin-top: 5px; color: var(--text-secondary-clr);">${ex.Notes}</div>` : ''}
            </div>
          `).join('')}
        </div>
      `;
    });
    
    showModal("Current Plan for " + memberName, `
      <div style="max-height: 500px; overflow-y: auto;">
        <div style="padding: 15px; background: var(--card-bg); border-radius: 8px; margin-bottom: 20px; border-left: 4px solid var(--success-color);">
          <h3 style="margin: 0 0 10px 0;">${activeProgram.Title}</h3>
          <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-top: 15px;">
            <div>
              <div style="font-size: 0.85rem; color: var(--muted);">Status</div>
              <div style="font-weight: 600;"><span class="status-badge status-active">Active</span></div>
            </div>
            <div>
              <div style="font-size: 0.85rem; color: var(--muted);">Goal</div>
              <div style="font-weight: 600;">${activeProgram.Goal || 'N/A'}</div>
            </div>
            <div>
              <div style="font-size: 0.85rem; color: var(--muted);">Duration</div>
              <div style="font-weight: 600;">${activeProgram.Weeks_Duration || 0} weeks</div>
            </div>
            <div>
              <div style="font-size: 0.85rem; color: var(--muted);">Created</div>
              <div style="font-weight: 600;">${new Date(activeProgram.Created_at).toLocaleDateString()}</div>
            </div>
          </div>
          ${activeProgram.Description ? `<p style="margin-top: 15px; color: var(--muted);">${activeProgram.Description}</p>` : ''}
        </div>
        
        <h4 style="margin-bottom: 15px;">Workout Schedule</h4>
        ${exercises.length === 0 ? '<p style="color: var(--muted);">No exercises found in this plan.</p>' : exercisesHTML}
        
        ${allPrograms.length > 0 ? `
          <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border-color);">
            <h4 style="margin-bottom: 15px;">Switch to Different Plan</h4>
            <p style="color: var(--muted); margin-bottom: 15px; font-size: 0.9rem;">Select a different plan to activate. The current plan will be marked as inactive.</p>
            ${allPrograms.map(p => `
              <div style="padding: 12px; margin: 10px 0; background: var(--card-bg); border-radius: 8px; border: 1px solid var(--border-color);">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                  <div style="flex: 1;">
                    <h5 style="margin: 0 0 5px 0;">${p.Title}</h5>
                    <p style="margin: 0; color: var(--muted); font-size: 0.85rem;">${p.Goal || 'N/A'} • ${p.Weeks_Duration} weeks • Created ${new Date(p.Created_at).toLocaleDateString()}</p>
                    <p style="margin: 5px 0 0 0; color: var(--muted); font-size: 0.8rem;"><span class="status-badge status-${(p.Status || 'inactive').toLowerCase()}">${p.Status || 'Inactive'}</span></p>
                  </div>
                  <button class="btn-small" style="background: var(--btn-primary-bg); color: white; margin-left: 15px;" onclick="switchToPlan(${memberId}, ${p.Workout_ID}, '${memberName}')">Switch To This</button>
                </div>
              </div>
            `).join('')}
          </div>
        ` : ''}
      </div>
    `);
    } catch (err) {
      console.error(err);
    showToast("Failed to load current plan", "error");
  }
}

async function switchToPlan(memberId, newPlanId, memberName) {
  if (!confirm(`Are you sure you want to switch ${memberName} to this plan? The current active plan will be marked as inactive.`)) {
    return;
  }
  
  try {
    // Get all programs for this member
    const programs = await fetchJSON(new URL(`../api/coach/workoutprograms/list.php?member_id=${memberId}`, window.location.href).href);
    
    // Find current active program
    const activeProgram = programs.find(p => p.Status === 'Active');
    
    // Deactivate current active program
    if (activeProgram && activeProgram.Workout_ID != newPlanId) {
      await fetchJSON(new URL("../api/coach/workoutprograms/update.php", window.location.href).href, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          id: activeProgram.Workout_ID,
          status: 'Inactive'
        })
      });
    }
    
    // Activate new program
    await fetchJSON(new URL("../api/coach/workoutprograms/update.php", window.location.href).href, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        id: newPlanId,
        status: 'Active'
      })
    });
    
    showToast(`Plan switched successfully for ${memberName}`, "success");
    document.getElementById("modal").classList.remove("active");
    
    // Refresh the view
    viewCurrentPlan(memberId, memberName);
  } catch (err) {
    console.error(err);
    showToast("Failed to switch plan", "error");
  }
}

async function transferMember(memberId, memberName) {
  try {
    const coaches = await fetchJSON(new URL("../api/coaches/get.php", window.location.href).href);
    
    const coachOptions = coaches.map(c => 
      `<option value="${c.id || c.user_id}">${c.name}</option>`
    ).join('');
    
    const coachSelect = `
      <div style="margin: 20px 0;">
        <label style="display: block; margin-bottom: 8px;">Select New Coach:</label>
        <select id="transfer-coach-select" class="form-control" style="width: 100%;">
          <option value="">-- Select Coach --</option>
          ${coachOptions}
        </select>
      </div>
      <button class="action-btn" onclick="confirmTransfer(${memberId}, '${memberName}')">Transfer</button>
    `;
    
    showModal(`Transfer ${memberName}`, coachSelect);
      } catch (err) {
        console.error(err);
    showToast("Failed to load coaches", "error");
  }
}

async function confirmTransfer(memberId, memberName) {
  const select = document.getElementById("transfer-coach-select");
  const newCoachId = select.value;
  
  if (!newCoachId) {
    showToast("Please select a coach", "error");
    return;
  }
  
  try {
    // Use the dedicated transfer endpoint
    const response = await fetchJSON(new URL("../api/coach/members/transfer.php", window.location.href).href, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        member_id: memberId,
        new_coach_id: parseInt(newCoachId)
      })
    });
    
    if (response.success) {
      showToast(`Member ${memberName} transferred successfully to new coach`, "success");
      document.getElementById("modal").classList.remove("active");
      // Reload members list - the member will no longer appear in current coach's list
      // because all their programs now have the new coach's ID
      await loadMembers();
      // Also refresh dashboard stats to update member count
      if (typeof loadDashboardStats === 'function') {
        loadDashboardStats();
      }
    } else {
      showToast(response.error || "Failed to transfer member", "error");
    }
  } catch (err) {
    console.error("Transfer error:", err);
    // Try to parse error message from response
    let errorMsg = "Failed to transfer member";
    try {
      const errorText = err.message;
      if (errorText.includes('API error:')) {
        const jsonMatch = errorText.match(/\{.*\}/);
        if (jsonMatch) {
          const errorObj = JSON.parse(jsonMatch[0]);
          errorMsg = errorObj.error || errorObj.message || errorMsg;
        }
      }
      } catch (e) {
      // If parsing fails, use default message
    }
    showToast(errorMsg, "error");
  }
}

function generateForMember(memberId, memberName) {
  currentMemberId = memberId;
  loadSection('generate');
  document.getElementById("generate-member-select").value = memberId;
}

// ==================== PROGRAMS SECTION ====================
function getProgramsHTML() {
  return `
    <div class="section-header">
      <h1>Workout Programs</h1>
    </div>
    
    <div class="filters">
      <input type="text" class="form-control" placeholder="Search programs..." id="program-search-input">
      <select class="form-control" id="program-status-filter">
        <option value="all">All Status</option>
        <option value="Active">Active</option>
        <option value="Completed">Completed</option>
        <option value="Paused">Paused</option>
      </select>
    </div>
    
    <table class="data-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Title</th>
          <th>Member</th>
          <th>Goal</th>
          <th>Weeks</th>
          <th>Status</th>
          <th>Created</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="programs-table-body">
        <tr><td colspan="8" style="text-align: center; padding: 2rem;">Loading programs...</td></tr>
      </tbody>
    </table>
  `;
}

async function initProgramsFunctionality() {
  await loadPrograms();
  
  const searchInput = document.getElementById("program-search-input");
  if (searchInput) {
    searchInput.addEventListener("input", (e) => filterPrograms(e.target.value));
  }
  
  const statusFilter = document.getElementById("program-status-filter");
  if (statusFilter) {
    statusFilter.addEventListener("change", (e) => filterProgramsByStatus(e.target.value));
  }
}

let allPrograms = [];

async function loadPrograms() {
  try {
    allPrograms = await fetchJSON(new URL("../api/coach/workoutprograms/list.php", window.location.href).href);
    renderPrograms(allPrograms);
  } catch (err) {
    console.error("Error loading programs:", err);
    showToast("Failed to load programs", "error");
  }
}

function renderPrograms(programs) {
  const tbody = document.getElementById("programs-table-body");
  if (!tbody) return;
  
  if (programs.length === 0) {
    tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 2rem;">No programs found</td></tr>';
    return;
  }
  
  tbody.innerHTML = programs.map(p => `
    <tr>
      <td>${p.Workout_ID}</td>
      <td>${p.Title}</td>
      <td>${p.Member_First || ''} ${p.Member_Last || ''}</td>
      <td>${p.Goal || 'N/A'}</td>
      <td>${p.Weeks_Duration || 0}</td>
      <td><span class="status-badge status-${p.Status?.toLowerCase() || 'active'}">${p.Status || 'Active'}</span></td>
      <td>${new Date(p.Created_at).toLocaleDateString()}</td>
      <td>
        <button class="btn-small btn-view" onclick="viewProgramExercises(${p.Workout_ID})">Exercises</button>
        <button class="btn-small btn-edit" onclick="editProgram(${p.Workout_ID})">Edit</button>
        <button class="btn-small btn-delete" onclick="deleteProgram(${p.Workout_ID})">Delete</button>
      </td>
    </tr>
  `).join('');
}

function filterPrograms(query) {
  const filtered = allPrograms.filter(p => 
    `${p.Title} ${p.Member_First} ${p.Member_Last}`.toLowerCase().includes(query.toLowerCase())
  );
  renderPrograms(filtered);
}

function filterProgramsByStatus(status) {
  if (status === 'all') {
    renderPrograms(allPrograms);
  } else {
    const filtered = allPrograms.filter(p => p.Status === status);
    renderPrograms(filtered);
  }
}

async function viewProgramExercises(workoutId) {
  try {
    const exercises = await fetchJSON(new URL(`../api/coach/workoutexercise/list.php?workout_id=${workoutId}`, window.location.href).href);
    showModal("Exercises", `
      <div style="max-height: 400px; overflow-y: auto;">
        ${exercises.length === 0 ? '<p>No exercises found</p>' : exercises.map(e => `
          <div style="padding: 10px; margin: 10px 0; background: var(--card-bg); border-radius: 8px;">
            <p><strong>Day ${e.Day_Number}</strong> - Sequence ${e.Sequence_Order}</p>
            <p>Sets: ${e.Sets}, Reps: ${e.Reps}</p>
            ${e.Rest_Time ? `<p>Rest: ${e.Rest_Time}</p>` : ''}
            ${e.Notes ? `<p>Notes: ${e.Notes}</p>` : ''}
          </div>
        `).join('')}
      </div>
    `);
  } catch (err) {
    console.error(err);
    showToast("Failed to load exercises", "error");
  }
}

async function editProgram(workoutId) {
  const program = allPrograms.find(p => p.Workout_ID == workoutId);
  if (!program) return;
  
  const newTitle = prompt("Enter new title:", program.Title);
  if (!newTitle) return;
  
  try {
    await fetchJSON(new URL("../api/coach/workoutprograms/update.php", window.location.href).href, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: workoutId, title: newTitle })
    });
    showToast("Program updated", "success");
    loadPrograms();
  } catch (err) {
    console.error(err);
    showToast("Failed to update program", "error");
  }
}

async function deleteProgram(workoutId) {
  if (!confirm("Are you sure you want to delete this program?")) return;
  
  try {
    await fetchJSON(new URL("../api/coach/workoutprograms/delete.php", window.location.href).href, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: workoutId })
    });
    showToast("Program deleted", "success");
    loadPrograms();
  } catch (err) {
    console.error(err);
    showToast("Failed to delete program", "error");
  }
}

// ==================== WORKOUTS SECTION ====================
function getWorkoutsHTML() {
  return `
    <div class="section-header">
      <h1>My Workouts</h1>
      <button class="action-btn" onclick="showCreateWorkoutForm()">
        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px">
          <path d="M440-440H200v-80h240v-240h80v240h240v80H520v240h-80v-240Z"/>
        </svg>
        Create Workout
      </button>
    </div>
    
    <table class="data-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Title</th>
          <th>Duration</th>
          <th>Difficulty</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="workouts-table-body">
        <tr><td colspan="5" style="text-align: center; padding: 2rem;">Loading workouts...</td></tr>
      </tbody>
    </table>
  `;
}

async function initWorkoutsFunctionality() {
  await loadWorkouts();
}

async function loadWorkouts() {
  try {
    const workouts = await fetchJSON(new URL("../api/coach/workouts/list.php", window.location.href).href);
    
    // Check if response is an error object
    if (workouts && workouts.error) {
      showToast("Error: " + workouts.error, "error");
      const tbody = document.getElementById("workouts-table-body");
      if (tbody) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 2rem; color: var(--danger-color);">Error: ' + workouts.error + '</td></tr>';
      }
          return;
        }
    
    // Ensure workouts is an array
    const workoutsArray = Array.isArray(workouts) ? workouts : [];
    renderWorkouts(workoutsArray);
      } catch (err) {
    console.error("Error loading workouts:", err);
    const errorMsg = err.message || "Failed to load workouts";
    showToast(errorMsg, "error");
    const tbody = document.getElementById("workouts-table-body");
    if (tbody) {
      tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 2rem; color: var(--danger-color);">' + errorMsg + '</td></tr>';
    }
  }
}

function renderWorkouts(workouts) {
  const tbody = document.getElementById("workouts-table-body");
  if (!tbody) return;
  
  if (workouts.length === 0) {
    tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 2rem;">No workouts found</td></tr>';
      return;
    }
  
  tbody.innerHTML = workouts.map(w => `
    <tr>
      <td>${w.Workout_ID}</td>
      <td>${w.Title}</td>
      <td>${w.Duration_Minutes || 0} min</td>
      <td>${w.Difficulty || 'N/A'}</td>
      <td>
        <button class="btn-small btn-edit" onclick="editWorkout(${w.Workout_ID})">Edit</button>
        <button class="btn-small btn-delete" onclick="deleteWorkout(${w.Workout_ID})">Delete</button>
      </td>
    </tr>
  `).join('');
}

function showCreateWorkoutForm() {
  const form = `
    <form id="create-workout-form" style="padding: 20px;">
      <div style="margin-bottom: 15px;">
        <label style="display: block; margin-bottom: 5px;">Title:</label>
        <input type="text" name="title" class="form-control" required>
      </div>
      <div style="margin-bottom: 15px;">
        <label style="display: block; margin-bottom: 5px;">Description:</label>
        <textarea name="description" class="form-control" rows="3"></textarea>
      </div>
      <div style="margin-bottom: 15px;">
        <label style="display: block; margin-bottom: 5px;">Duration (minutes):</label>
        <input type="number" name="duration" class="form-control">
      </div>
      <div style="margin-bottom: 15px;">
        <label style="display: block; margin-bottom: 5px;">Difficulty:</label>
        <select name="difficulty" class="form-control">
          <option value="">Select...</option>
          <option value="Beginner">Beginner</option>
          <option value="Intermediate">Intermediate</option>
          <option value="Advanced">Advanced</option>
        </select>
      </div>
      <button type="submit" class="action-btn">Create</button>
    </form>
  `;
  
  showModal("Create Workout", form);
  
  document.getElementById("create-workout-form").addEventListener("submit", async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData);
    
    try {
      await fetchJSON(new URL("../api/coach/workouts/create.php", window.location.href).href, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });
      showToast("Workout created", "success");
      document.getElementById("modal").classList.remove("active");
      loadWorkouts();
      } catch (err) {
        console.error(err);
      showToast("Failed to create workout", "error");
    }
  });
}

async function editWorkout(workoutId) {
  const newTitle = prompt("Enter new title:");
  if (!newTitle) return;
  
  try {
    await fetchJSON(new URL("../api/coach/workouts/update.php", window.location.href).href, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: workoutId, title: newTitle })
    });
    showToast("Workout updated", "success");
    loadWorkouts();
          } catch (err) {
            console.error(err);
    showToast("Failed to update workout", "error");
  }
}

async function deleteWorkout(workoutId) {
  if (!confirm("Are you sure you want to delete this workout?")) return;
  
  try {
    await fetchJSON(new URL("../api/coach/workouts/delete.php", window.location.href).href, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: workoutId })
    });
    showToast("Workout deleted", "success");
    loadWorkouts();
          } catch (err) {
            console.error(err);
    showToast("Failed to delete workout", "error");
  }
}

// ==================== AI GENERATOR SECTION ====================
function getGenerateHTML() {
  return `
    <div class="section-header">
      <h1>AI Workout Generator</h1>
      <p class="subtitle">Generate personalized workout plans for your members</p>
    </div>
    
    <div class="card" style="max-width: 600px; margin: 0 auto;">
      <form id="generate-workout-form" style="padding: 20px;">
        <div style="margin-bottom: 20px;">
          <label style="display: block; margin-bottom: 8px; font-weight: 500;">Select Member:</label>
          <select id="generate-member-select" name="member_id" class="form-control" required>
            <option value="">-- Select Member --</option>
          </select>
        </div>
        
        <div style="margin-bottom: 20px;">
          <label style="display: block; margin-bottom: 8px; font-weight: 500;">Training Goal:</label>
          <input type="text" name="goal" class="form-control" placeholder="e.g., Build Muscle, Lose Weight, General Fitness">
        </div>
        
        <div style="margin-bottom: 20px;">
          <label style="display: block; margin-bottom: 8px; font-weight: 500;">Duration (weeks):</label>
          <input type="number" name="weeks" class="form-control" value="4" min="1" max="12">
        </div>
        
        <button type="submit" class="action-btn" style="width: 100%;">
          <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" style="vertical-align: middle; margin-right: 8px;">
            <path d="M440-280h80v-240h-80v240Zm40-320q17 0 28.5-11.5T520-640q0-17-11.5-28.5T480-680q-17 0-28.5 11.5T440-640q0 17 11.5 28.5T480-600Zm0 520q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z"/>
          </svg>
          Generate Workout Plan
        </button>
      </form>
    </div>
  `;
}

async function initGenerateFunctionality() {
  await loadMembersForGenerator();
  
  const form = document.getElementById("generate-workout-form");
  if (form) {
    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      await generateWorkout(e.target);
    });
  }
  
  // Set member if coming from members page
  if (currentMemberId) {
    const select = document.getElementById("generate-member-select");
    if (select) {
      select.value = currentMemberId;
      currentMemberId = null;
    }
  }
}

async function loadMembersForGenerator() {
  try {
    const members = await fetchJSON(new URL("../api/coach/members/list.php", window.location.href).href);
    const select = document.getElementById("generate-member-select");
    if (select) {
      select.innerHTML = '<option value="">-- Select Member --</option>' +
        members.map(m => `<option value="${m.Member_Id}">${m.First_Name} ${m.Last_Name}</option>`).join('');
    }
  } catch (err) {
    console.error("Error loading members:", err);
    showToast("Failed to load members", "error");
  }
}

async function generateWorkout(form) {
  const formData = new FormData(form);
  const data = Object.fromEntries(formData);
  
  if (!data.member_id) {
    showToast("Please select a member", "error");
    return;
  }
  
  const generateBtn = form.querySelector('button[type="submit"]');
  const originalText = generateBtn.innerHTML;
  generateBtn.disabled = true;
  generateBtn.innerHTML = 'Generating...';
  
  try {
    const response = await fetchJSON(new URL("../api/coach/workouts/generate.php", window.location.href).href, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });
    
    if (response.error) {
      showToast(response.error, "error");
      generateBtn.disabled = false;
      generateBtn.innerHTML = originalText;
      return;
    }
    
    // Store preview data
    currentPreviewData = response;
    
    // Show preview
    showWorkoutPreview(response);
    
    generateBtn.disabled = false;
    generateBtn.innerHTML = originalText;
  } catch (err) {
    console.error(err);
    showToast("Failed to generate workout. Please try again.", "error");
    generateBtn.disabled = false;
    generateBtn.innerHTML = originalText;
  }
}

function showWorkoutPreview(data) {
  const workout = data.workout;
  const program = workout.program || {};
  const days = workout.days || {};
  
  let previewHTML = `
    <div style="padding: 20px;">
      <h3 style="margin-bottom: 10px;">${program.title || 'Workout Plan'}</h3>
      <p style="color: var(--muted); margin-bottom: 20px;">${program.description || ''}</p>
      <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 30px;">
        <div style="background: var(--card-bg); padding: 15px; border-radius: 8px;">
          <div style="font-size: 0.9rem; color: var(--muted);">Goal</div>
          <div style="font-weight: 600;">${program.goal || 'N/A'}</div>
        </div>
        <div style="background: var(--card-bg); padding: 15px; border-radius: 8px;">
          <div style="font-size: 0.9rem; color: var(--muted);">Duration</div>
          <div style="font-weight: 600;">${program.weeks || 0} weeks</div>
        </div>
        <div style="background: var(--card-bg); padding: 15px; border-radius: 8px;">
          <div style="font-size: 0.9rem; color: var(--muted);">Member</div>
          <div style="font-weight: 600;">${data.member.name}</div>
        </div>
      </div>
      
      <h4 style="margin-bottom: 15px;">Workout Schedule</h4>
      <div style="max-height: 400px; overflow-y: auto;">
  `;
  
  Object.keys(days).sort().forEach(day => {
    const exercises = days[day] || [];
    if (exercises.length === 0) {
      previewHTML += `
        <div style="background: var(--card-bg); padding: 15px; margin-bottom: 10px; border-radius: 8px;">
          <h5 style="margin: 0 0 10px 0;">${day.charAt(0).toUpperCase() + day.slice(1)} - Rest Day</h5>
        </div>
      `;
    } else {
      previewHTML += `
        <div style="background: var(--card-bg); padding: 15px; margin-bottom: 10px; border-radius: 8px;">
          <h5 style="margin: 0 0 15px 0;">${day.charAt(0).toUpperCase() + day.slice(1)}</h5>
          ${exercises.map((ex, idx) => `
            <div style="padding: 10px; margin-bottom: 10px; background: var(--base-clr); border-radius: 6px; border-left: 3px solid var(--accent-secondary);">
              <div style="font-weight: 600; margin-bottom: 5px;">${idx + 1}. ${ex.name || 'Exercise'}</div>
              <div style="font-size: 0.9rem; color: var(--muted);">
                ${ex.sets ? `${ex.sets} sets` : ''} ${ex.reps ? `× ${ex.reps} reps` : ''} ${ex.rest ? `| Rest: ${ex.rest}` : ''}
              </div>
              ${ex.targetMuscleGroup ? `<div style="font-size: 0.85rem; color: var(--accent-secondary); margin-top: 5px;">Target: ${ex.targetMuscleGroup}</div>` : ''}
              ${ex.description ? `<div style="font-size: 0.85rem; margin-top: 5px;">${ex.description}</div>` : ''}
            </div>
          `).join('')}
        </div>
      `;
    }
  });
  
  previewHTML += `
      </div>
    </div>
  `;
  
  document.getElementById("workout-preview-body").innerHTML = previewHTML;
  document.getElementById("workout-preview-modal").classList.add("active");
  document.body.style.overflow = "hidden";
  
  // Setup confirm button
  const confirmBtn = document.getElementById("confirm-workout-btn");
  confirmBtn.onclick = () => confirmWorkoutSave();
}

function closeWorkoutPreview() {
  document.getElementById("workout-preview-modal").classList.remove("active");
  document.body.style.overflow = "";
  currentPreviewData = null;
}

async function confirmWorkoutSave() {
  if (!currentPreviewData) return;
  
  const confirmBtn = document.getElementById("confirm-workout-btn");
  confirmBtn.disabled = true;
  confirmBtn.textContent = "Saving...";
  
  try {
    const response = await fetchJSON(new URL("../api/coach/workouts/save_generated.php", window.location.href).href, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        member_id: currentPreviewData.member.id,
        workout: currentPreviewData.workout
      })
    });
    
    if (response.error) {
      showToast(response.error, "error");
      confirmBtn.disabled = false;
      confirmBtn.textContent = "Confirm & Save";
      return;
    }
    
    showToast(`Workout plan saved successfully! ${response.exercises_created} exercises created.`, "success");
    closeWorkoutPreview();
    
    // Reload programs
    if (document.getElementById("programs-table-body")) {
      loadPrograms();
    }
    
    // Reset form
    const form = document.getElementById("generate-workout-form");
    if (form) form.reset();
    } catch (err) {
      console.error(err);
    showToast("Failed to save workout", "error");
    confirmBtn.disabled = false;
    confirmBtn.textContent = "Confirm & Save";
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

// Profile section
function getProfileHTML() {
  return `
    <div class="section-header">
      <h1>My Profile</h1>
    </div>
    
    <div class="profile-container">
      <div class="profile-card">
        <div class="profile-header">
          <div class="profile-avatar" id="profile-avatar">C</div>
          <div class="profile-info">
            <h2 id="profile-name">Loading...</h2>
            <p id="profile-role">Coach</p>
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
    const profile = await fetchJSON(new URL("../api/coach/profile/get.php", window.location.href).href);
    
    // Update avatar with first letter
    const avatar = document.getElementById("profile-avatar");
    if (avatar && profile.First_Name) {
      avatar.textContent = profile.First_Name.charAt(0).toUpperCase();
    }
    
    // Update name
    const nameEl = document.getElementById("profile-name");
    if (nameEl) {
      nameEl.textContent = `${profile.First_Name || ''} ${profile.Last_Name || ''}`.trim() || 'Coach';
    }
    
    // Update role
    const roleEl = document.getElementById("profile-role");
    if (roleEl) {
      roleEl.textContent = profile.Specialization_Main || 'Fitness Coach';
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
        ${profile.Specialization_Other ? `
        <div class="detail-item">
          <strong>Other Specializations:</strong> ${profile.Specialization_Other}
        </div>
        ` : ''}
        <div class="detail-item">
          <strong>Certifications:</strong> ${profile.Certifications || 'N/A'}
        </div>
        <div class="detail-item">
          <strong>Rating:</strong> ${profile.Avg_rating ? `${profile.Avg_rating}/5 (${profile.rating_count || 0} reviews)` : 'No ratings yet'}
        </div>
        <div class="detail-item">
          <strong>Max Clients:</strong> ${profile.Max_Clients || 'N/A'}
        </div>
        <div class="detail-item">
          <strong>Current Members:</strong> ${profile.member_count || 0}
        </div>
        <div class="detail-item">
          <strong>Active Programs:</strong> ${profile.active_programs_count || 0}
        </div>
        <div class="detail-item">
          <strong>Accepting New Clients:</strong> ${profile.Is_Accepting_new ? 'Yes' : 'No'}
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
        ${profile.Youtube_Url || profile.Instagram_Url ? `
        <div class="detail-item">
          <strong>Social Media:</strong><br>
          ${profile.Youtube_Url ? `<a href="${profile.Youtube_Url}" target="_blank" style="color: var(--accent-clr); margin-right: 10px;">YouTube</a>` : ''}
          ${profile.Instagram_Url ? `<a href="${profile.Instagram_Url}" target="_blank" style="color: var(--accent-clr);">Instagram</a>` : ''}
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

// Theme button text update
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

// Make loadSection available globally
window.loadSection = loadSection;
window.viewMemberPrograms = viewMemberPrograms;
window.viewCurrentPlan = viewCurrentPlan;
window.switchToPlan = switchToPlan;
window.transferMember = transferMember;
window.generateForMember = generateForMember;
window.viewProgramExercises = viewProgramExercises;
window.editProgram = editProgram;
window.deleteProgram = deleteProgram;
window.showCreateWorkoutForm = showCreateWorkoutForm;
window.editWorkout = editWorkout;
window.deleteWorkout = deleteWorkout;
window.closeWorkoutPreview = closeWorkoutPreview;
