// dashboard-content.js
document.addEventListener("DOMContentLoaded", function () {
  initDashboard();
});


function loadDashboardContent() {
  const contentArea = document.getElementById("content-area");
  contentArea.innerHTML = getDashboardHTML();
  initDashboardFunctionality();
}

function getDashboardHTML() {
  return `
        <!-- Header -->
        <header class="dashboard-header">
            <div class="header-content">
                <h1>Power Gym Admin</h1>
                <p class="subtitle">Complete Gym Management System</p>
            </div>
        </header>

        <!-- Dashboard Alert -->
        <div class="dashboard-alert">
            <svg xmlns="http://www.w3.org/2000/svg" height="20" viewBox="0 -960 960 960" width="20">
                <path d="M440-280h80v-240h-80v240Zm40-320q17 0 28.5-11.5T520-640q0-17-11.5-28.5T480-680q-17 0-28.5 11.5T440-640q0 17 11.5 28.5T480-600Zm0 520q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z"/>
            </svg>
            <span>System is running optimally. All services are operational.</span>
        </div>

        <!-- Welcome Section -->
        <section class="welcome-section">
            <h2>Welcome, Admin!</h2>
            <p>Manage your gym efficiently and monitor performance in real-time.</p>
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Active Members</h3>
                    <div class="stat-value" id="active-members">0</div>
                    <div class="stat-change positive">+0%</div>
                </div>
                <div class="stat-card">
                    <h3>Monthly Revenue</h3>
                    <div class="stat-value" id="monthly-revenue">$0</div>
                    <div class="stat-change positive">+0%</div>
                </div>
                <div class="stat-card">
                    <h3>Coaches</h3>
                    <div class="stat-value" id="total-coaches">0</div>
                    <div class="stat-change positive">+0</div>
                </div>
                <div class="stat-card">
                    <h3>Workout Programs</h3>
                    <div class="stat-value" id="active-workouts">0</div>
                    <div class="stat-change positive">Active</div>
                </div>
            </div>
        </section>

        <!-- Quick Actions -->
        <section class="quick-actions">
            <h2>Quick Actions</h2>
            <div class="action-grid">
                <button class="action-card" data-action="add-member">
                    <svg xmlns="http://www.w3.org/2000/svg" height="32px" viewBox="0 -960 960 960" width="32px">
                        <path d="M440-440H200v-80h240v-240h80v240h240v80H520v240h-80v-240Z"/>
                    </svg>
                    <span>Add Member</span>
                </button>
                <button class="action-card" data-action="add-coach">
                    <svg xmlns="http://www.w3.org/2000/svg" height="32px" viewBox="0 -960 960 960" width="32px">
                        <path d="M480-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM160-160v-112q0-34 17.5-62.5T224-378q62-31 126-46.5T480-440q66 0 130 15.5T736-378q29 15 46.5 43.5T800-272v112H160Zm80-80h480v-32q0-11-5.5-20T700-306q-54-27-109-40.5T480-360q-56 0-111 13.5T260-306q-9 5-14.5 14t-5.5 20v32Zm240-320q33 0 56.5-23.5T560-640q0-33-23.5-56.5T480-720q-33 0-56.5 23.5T400-640q0 33 23.5 56.5T480-560Zm0-80Zm0 400Z"/>
                    </svg>
                    <span>Add Coach</span>
                </button>
                <button class="action-card" data-action="add-nutritionist">
                    <svg xmlns="http://www.w3.org/2000/svg" height="32px" viewBox="0 -960 960 960" width="32px">
                        <path d="M480-80q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm-40-120h80v-80h80v-80h-80v-80h-80v80h-80v80h80v80Zm40-240q25 0 42.5-17.5T560-600q0-25-17.5-42.5T500-660q-25 0-42.5 17.5T440-600q0 25 17.5 42.5T500-540Zm0-60Zm0 340Z"/>
                    </svg>
                    <span>Add Nutritionist</span>
                </button>
                <button class="action-card" data-action="add-workout">
                    <svg xmlns="http://www.w3.org/2000/svg" height="32px" viewBox="0 -960 960 960" width="32px">
                        <path d="M826-585-56-56 30-31-128-128-31 30-57-57 30-31q23-23 57-22.5t57 23.5l129 129q23 23 23 56.5T857-615l-31 30ZM346-104q-23 23-56.5 23T233-104L104-233q-23-23-23-56.5t23-56.5l30-30 57 57-31 30 129 129 30-31 57 57-30 30Zm397-336 57-57-303-303-57 57 303 303ZM463-160l57-58-302-302-58 57 303 303Zm-6-234 110-109-64-64-109 110 63 63Zm63 290q-23 23-57 23t-57-23L104-406q-23-23-23-57t23-57l57-57q23-23 56.5-23t56.5 23l63 63 110-110-63-62q-23-23-23-57t23-57l57-57q23-23 56.5-23t56.5 23l303 303q23 23 23 56.5T857-441l-57 57q-23 23-57 23t-57-23l-62-63-110 110 63 63q23 23 23 56.5T577-161l-57 57Z"/>
                    </svg>
                    <span>Add Workout</span>
                </button>
            </div>
        </section>

        <!-- Recent Activity -->
        <section class="activity-section">
            <h2>Recent Activity</h2>
            <div class="activity-list" id="recent-activity">
                <div class="activity-item">
                    <div class="activity-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px">
                            <path d="M480-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM160-160v-112q0-34 17.5-62.5T224-378q62-31 126-46.5T480-440q66 0 130 15.5T736-378q29 15 46.5 43.5T800-272v112H160Zm80-80h480v-32q0-11-5.5-20T700-306q-54-27-109-40.5T480-360q-56 0-111 13.5T260-306q-9 5-14.5 14t-5.5 20v32Zm240-320q33 0 56.5-23.5T560-640q0-33-23.5-56.5T480-720q-33 0-56.5 23.5T400-640q0 33 23.5 56.5T480-560Zm0-80Zm0 400Z"/>
                        </svg>
                    </div>
                    <div class="activity-content">
                        <h4>Loading activities...</h4>
                        <p>Please wait</p>
                        <span class="activity-time">Just now</span>
                    </div>
                </div>
            </div>
        </section>
    `;
}

function setupNavigation() {
  document.querySelectorAll("#sidebar a[data-section]").forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault();
      const section = this.getAttribute("data-section");
      loadSection(section);

      document.querySelectorAll("#sidebar li").forEach((li) => {
        li.classList.remove("active");
      });
      this.closest("li").classList.add("active");

      closeAllSubMenus();

      if (window.innerWidth <= 768) {
        const sidebar = document.getElementById("sidebar");
        sidebar.classList.remove("show-mobile");
      }
    });
  });

  document.querySelectorAll("#sidebar .Sub-Menu a").forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault();
      const section = this.getAttribute("data-section");
      loadSection(section);

      document.querySelectorAll("#sidebar li").forEach((li) => {
        li.classList.remove("active");
      });
      this.closest("li").closest("li").classList.add("active");

      if (window.innerWidth <= 768) {
        const sidebar = document.getElementById("sidebar");
        sidebar.classList.remove("show-mobile");
      }
    });
  });
}

function setupQuickActions() {
  // Handled in initDashboardFunctionality
}

function setupModal() {
  const modal = document.getElementById("modal");
  const modalClose = document.querySelector(".modal-close");

  if (modalClose) {
    modalClose.addEventListener("click", () => {
      modal.classList.remove("active");
      document.body.style.overflow = "";
    });
  }

  modal.addEventListener("click", (e) => {
    if (e.target === modal) {
      modal.classList.remove("active");
      document.body.style.overflow = "";
    }
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && modal.classList.contains("active")) {
      modal.classList.remove("active");
      document.body.style.overflow = "";
    }
  });
}

function loadSection(section) {
  const contentArea = document.getElementById("content-area");

  switch (section) {
    case "dashboard":
      contentArea.innerHTML = getDashboardHTML();
      initDashboardFunctionality();
      loadDashboardStats();
      loadRecentActivity();
      break;
    case "members":
      contentArea.innerHTML = getMembersHTML();
      initMembersFunctionality();
      break;
    case "workouts":
      contentArea.innerHTML = getWorkoutsHTML();
      initWorkoutsFunctionality();
      break;
    case "schedule":
      contentArea.innerHTML = getScheduleHTML();
      initScheduleFunctionality();
      break;
    case "staff":
      contentArea.innerHTML = getStaffHTML("all");
      initStaffFunctionality("all");
      break;
    case "trainers":
      contentArea.innerHTML = getStaffHTML("coach");
      initStaffFunctionality("coach");
      break;
    case "nutritionists":
      contentArea.innerHTML = getStaffHTML("nutritionist");
      initStaffFunctionality("nutritionist");
      break;
    case "reception":
      contentArea.innerHTML = getStaffHTML("reception");
      initStaffFunctionality("reception");
      break;
    case "profile":
      contentArea.innerHTML = getProfileHTML();
      initProfileFunctionality();
      break;
    case "contact-messages":
      contentArea.innerHTML = getContactMessagesHTML();
      initContactMessagesFunctionality();
      break;
    default:
      contentArea.innerHTML = getDashboardHTML();
      initDashboardFunctionality();
      loadDashboardStats();
      loadRecentActivity();
  }
}

function showModal(title, content) {
  const modal = document.getElementById("modal");
  const modalTitle = document.getElementById("modal-title");
  const modalBody = document.getElementById("modal-body");

  modalTitle.textContent = title;
  modalBody.innerHTML = content;
  modal.classList.add("active");
  document.body.style.overflow = "hidden";
}

// Dashboard functionality
function initDashboardFunctionality() {
  const themeToggleBtn = document.getElementById("light-mode");
  if (themeToggleBtn) {
    const savedTheme = localStorage.getItem("theme") || "dark";
    document.documentElement.setAttribute("data-theme", savedTheme);
    updateThemeButtonText(themeToggleBtn, savedTheme);
    updateLogo(savedTheme);

    themeToggleBtn.addEventListener("click", function (e) {
      const currentTheme = document.documentElement.getAttribute("data-theme");
      const newTheme = currentTheme === "light" ? "dark" : "light";

      document.documentElement.setAttribute("data-theme", newTheme);
      localStorage.setItem("theme", newTheme);

      updateThemeButtonText(this, newTheme);
      updateLogo(newTheme);
      createRipple(this, e);
    });
  }

  const logoutBtn = document.getElementById("logout-btn");
  if (logoutBtn) {
    logoutBtn.addEventListener("click", function (e) {
      showNotification("Logging out...", "info");
      setTimeout(() => {
        window.location.href = "login.html";
      }, 1000);
      createRipple(this, e);
    });
  }

  document.querySelectorAll(".action-card[data-action]").forEach((button) => {
    button.addEventListener("click", function (e) {
      const action = this.getAttribute("data-action");
      handleQuickAction(action);
      createRipple(this, e);
    });
  });
}

function handleQuickAction(action) {
  switch (action) {
    case "add-member":
      showAddMemberForm();
      break;
    case "add-coach":
      showAddStaffForm("coach");
      break;
    case "add-nutritionist":
      showAddStaffForm("nutritionist");
      break;
    case "add-workout":
      showAddWorkoutForm();
      break;
  }
}

function loadDashboardStats() {
  fetch("api.php?action=getDashboardStats")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        const stats = data.data;
        document.getElementById("active-members").textContent =
          stats.activeMembers;
        document.getElementById("monthly-revenue").textContent =
          "$" + stats.totalRevenue;
        document.getElementById("total-coaches").textContent =
          stats.totalCoaches;
        document.getElementById("active-workouts").textContent =
          stats.activeWorkouts;
      }
    })
    .catch((error) => {
      console.error("Error loading dashboard stats:", error);
    });
}

function loadRecentActivity() {
  fetch("api.php?action=getRecentActivity")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        const activityList = document.getElementById("recent-activity");
        if (activityList) {
          activityList.innerHTML = "";

          if (data.data.length === 0) {
            activityList.innerHTML = `
              <div class="activity-item">
                <div class="activity-content">
                  <h4>No recent activity</h4>
                  <p>Activities will appear here</p>
                </div>
              </div>
            `;
            return;
          }

          data.data.forEach((activity) => {
            const timeAgo = formatTimeAgo(new Date(activity.time));
            const activityItem = document.createElement("div");
            activityItem.className = "activity-item";
            activityItem.innerHTML = `
              <div class="activity-icon">
                <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px">
                  <path d="M480-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM160-160v-112q0-34 17.5-62.5T224-378q62-31 126-46.5T480-440q66 0 130 15.5T736-378q29 15 46.5 43.5T800-272v112H160Zm80-80h480v-32q0-11-5.5-20T700-306q-54-27-109-40.5T480-360q-56 0-111 13.5T260-306q-9 5-14.5 14t-5.5 20v32Zm240-320q33 0 56.5-23.5T560-640q0-33-23.5-56.5T480-720q-33 0-56.5 23.5T400-640q0 33 23.5 56.5T480-560Zm0-80Zm0 400Z"/>
                </svg>
              </div>
              <div class="activity-content">
                <h4>${activity.type}</h4>
                <p>Admin ${activity.adminId} ${activity.details}</p>
                <span class="activity-time">${timeAgo}</span>
              </div>
            `;
            activityList.appendChild(activityItem);
          });
        }
      }
    })
    .catch((error) => {
      console.error("Error loading recent activity:", error);
    });
}

function formatTimeAgo(date) {
  const seconds = Math.floor((new Date() - date) / 1000);

  let interval = seconds / 31536000;
  if (interval > 1) return Math.floor(interval) + " years ago";

  interval = seconds / 2592000;
  if (interval > 1) return Math.floor(interval) + " months ago";

  interval = seconds / 86400;
  if (interval > 1) return Math.floor(interval) + " days ago";

  interval = seconds / 3600;
  if (interval > 1) return Math.floor(interval) + " hours ago";

  interval = seconds / 60;
  if (interval > 1) return Math.floor(interval) + " minutes ago";

  return "Just now";
}

// Members section
function getMembersHTML() {
  return `
        <div class="section-header">
            <h1>Members Management</h1>
            <button class="action-btn" id="add-member-btn">
                <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px">
                    <path d="M440-440H200v-80h240v-240h80v240h240v80H520v240h-80v-240Z"/>
                </svg>
                Add New Member
            </button>
        </div>
        
        <div class="filters">
            <input type="text" class="form-control" placeholder="Search members..." id="member-search">
            <select class="form-control" id="member-filter">
                <option value="all">All Members</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="beginner">Beginner</option>
                <option value="intermediate">Intermediate</option>
                <option value="advanced">Advanced</option>
            </select>
        </div>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Experience</th>
                    <th>Status</th>
                    <th>Join Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="members-table-body">
                <tr><td colspan="8" style="text-align: center; padding: 2rem;">Loading members...</td></tr>
            </tbody>
        </table>
        
        <div class="card-grid" id="members-cards">
            <div class="user-card">
                <div class="user-details">
                    <p>Loading members...</p>
                </div>
            </div>
        </div>
    `;
}

function initMembersFunctionality() {
  loadMembers();

  const addMemberBtn = document.getElementById("add-member-btn");
  if (addMemberBtn) {
    addMemberBtn.addEventListener("click", function (e) {
      showAddMemberForm();
      createRipple(this, e);
    });
  }

  const searchInput = document.getElementById("member-search");
  const filterSelect = document.getElementById("member-filter");

  if (searchInput) {
    searchInput.addEventListener("input", function () {
      searchMembers(this.value);
    });
  }
  if (filterSelect) {
    filterSelect.addEventListener("change", filterMembers);
  }
}

function loadMembers() {
  fetch("api.php?action=getMembers")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        displayMembers(data.data);
      } else {
        showNotification("Failed to load members", "error");
      }
    })
    .catch((error) => {
      console.error("Error loading members:", error);
      showNotification("Network error. Please try again.", "error");
    });
}

function searchMembers(searchTerm) {
  if (searchTerm.length < 2) {
    loadMembers();
    return;
  }

  fetch(`api.php?action=searchMembers&q=${encodeURIComponent(searchTerm)}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        displayMembers(data.data);
      }
    })
    .catch((error) => {
      console.error("Error searching members:", error);
    });
}

function displayMembers(members) {
  const tableBody = document.getElementById("members-table-body");
  const cardsContainer = document.getElementById("members-cards");

  if (!tableBody || !cardsContainer) return;

  if (members.length === 0) {
    tableBody.innerHTML =
      '<tr><td colspan="8" style="text-align: center; padding: 2rem;">No members found</td></tr>';
    cardsContainer.innerHTML =
      '<div class="user-card"><div class="user-details"><p>No members found</p></div></div>';
    return;
  }

  tableBody.innerHTML = "";
  cardsContainer.innerHTML = "";

  members.forEach((member) => {
    const joinDate = new Date(member.Created_at);
    const formattedDate = joinDate.toLocaleDateString();

    // Table row
    const row = document.createElement("tr");
    row.innerHTML = `
            <td>${member.User_ID}</td>
            <td><strong>${member.First_Name} ${member.Last_Name}</strong></td>
            <td>${member.Email}</td>
            <td>${member.Phone_Number || "N/A"}</td>
            <td><span class="membership-badge ${
              member.Experience_Level?.toLowerCase() || "beginner"
            }">${member.Experience_Level || "Beginner"}</span></td>
            <td><span class="status-badge ${
              member.Is_Active ? "active" : "inactive"
            }">${member.Is_Active ? "Active" : "Inactive"}</span></td>
            <td>${formattedDate}</td>
            <td class="action-buttons">
                <button class="btn-small btn-view" onclick="viewMember(${
                  member.User_ID
                })">View</button>
                <button class="btn-small btn-edit" onclick="editMember(${
                  member.User_ID
                })">Edit</button>
                <button class="btn-small btn-delete" onclick="deleteMember(${
                  member.User_ID
                })">Delete</button>
            </td>
        `;
    tableBody.appendChild(row);

    // Card for mobile
    const card = document.createElement("div");
    card.className = "user-card";
    card.innerHTML = `
            <div class="user-header">
                <div class="user-avatar">${member.First_Name.charAt(
                  0
                )}${member.Last_Name.charAt(0)}</div>
                <div class="user-info">
                    <h3>${member.First_Name} ${member.Last_Name}</h3>
                    <p>ID: ${member.User_ID} • ${
      member.Experience_Level || "Beginner"
    }</p>
                </div>
            </div>
            <div class="user-details">
                <p><strong>Email:</strong> ${member.Email}</p>
                <p><strong>Phone:</strong> ${member.Phone_Number || "N/A"}</p>
                <p><strong>Status:</strong> <span class="status-badge ${
                  member.Is_Active ? "active" : "inactive"
                }">${member.Is_Active ? "Active" : "Inactive"}</span></p>
                <p><strong>Joined:</strong> ${formattedDate}</p>
            </div>
            <div class="user-stats">
                <div class="stat-item">
                    <div class="stat-value">${member.Height || 0} cm</div>
                    <div class="stat-label">Height</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">${member.Weight || 0} kg</div>
                    <div class="stat-label">Weight</div>
                </div>
            </div>
            <div class="action-buttons" style="margin-top: 1rem;">
                <button class="btn-small btn-view" onclick="viewMember(${
                  member.User_ID
                })">View</button>
                <button class="btn-small btn-edit" onclick="editMember(${
                  member.User_ID
                })">Edit</button>
            </div>
        `;
    cardsContainer.appendChild(card);
  });
}

function filterMembers() {
  const filterValue = document.getElementById("member-filter").value;
  const rows = document.querySelectorAll("#members-table-body tr");

  rows.forEach((row) => {
    if (row.cells) {
      let show = true;
      const experience = row.cells[4]?.textContent?.toLowerCase();
      const status = row.cells[5]?.textContent?.toLowerCase();

      if (filterValue !== "all") {
        if (filterValue === "active" && status !== "active") show = false;
        if (filterValue === "inactive" && status !== "inactive") show = false;
        if (filterValue === "beginner" && experience !== "beginner")
          show = false;
        if (filterValue === "intermediate" && experience !== "intermediate")
          show = false;
        if (filterValue === "advanced" && experience !== "advanced")
          show = false;
      }

      row.style.display = show ? "" : "none";
    }
  });
}

// Staff section
function getStaffHTML(role) {
  let title = "Staff Management";
  if (role === "coach") title = "Coaches";
  if (role === "nutritionist") title = "Nutritionists";
  if (role === "reception") title = "Reception Staff";

  return `
        <div class="section-header">
            <h1>${title}</h1>
            <button class="action-btn" id="add-staff-btn">
                <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px">
                    <path d="M440-440H200v-80h240v-240h80v240h240v80H520v240h-80v-240Z"/>
                </svg>
                Add New ${
                  role === "coach"
                    ? "Coach"
                    : role === "nutritionist"
                    ? "Nutritionist"
                    : "Staff"
                }
            </button>
        </div>
        
        <div class="card-grid" id="staff-container">
            <div class="user-card">
                <div class="user-details">
                    <p>Loading staff...</p>
                </div>
            </div>
        </div>
    `;
}

function initStaffFunctionality(role) {
  loadStaff(role);

  const addStaffBtn = document.getElementById("add-staff-btn");
  if (addStaffBtn) {
    addStaffBtn.addEventListener("click", function (e) {
      showAddStaffForm(role);
      createRipple(this, e);
    });
  }
}

function loadStaff(role) {
  let url = "api.php?action=getStaff";
  if (role !== "all") {
    url += `&role=${role}`;
  }

  fetch(url)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        displayStaff(data.data, role);
      } else {
        showNotification("Failed to load staff", "error");
      }
    })
    .catch((error) => {
      console.error("Error loading staff:", error);
      showNotification("Network error. Please try again.", "error");
    });
}

function displayStaff(staff, role) {
  const container = document.getElementById("staff-container");
  if (!container) return;

  if (staff.length === 0) {
    container.innerHTML =
      '<div class="user-card"><div class="user-details"><p>No staff found</p></div></div>';
    return;
  }

  container.innerHTML = "";

  staff.forEach((person) => {
    const card = document.createElement("div");
    card.className = "user-card";

    let roleInfo = "";
    if (role === "coach") {
      roleInfo = `<p><strong>Specialization:</strong> ${
        person.details || "Not specified"
      }</p>`;
    } else if (role === "nutritionist") {
      roleInfo = `<p><strong>Specialization:</strong> ${
        person.details || "Not specified"
      }</p>`;
    }

    card.innerHTML = `
      <div class="user-header">
        <div class="user-avatar">${person.First_Name.charAt(
          0
        )}${person.Last_Name.charAt(0)}</div>
        <div class="user-info">
          <h3>${person.First_Name} ${person.Last_Name}</h3>
          <p>${person.Role.charAt(0).toUpperCase() + person.Role.slice(1)}</p>
        </div>
      </div>
      <div class="user-details">
        <p><strong>Email:</strong> ${person.Email}</p>
        <p><strong>Phone:</strong> ${person.Phone_Number || "N/A"}</p>
        <p><strong>Status:</strong> <span class="status-badge ${
          person.Is_Active ? "active" : "inactive"
        }">${person.Is_Active ? "Active" : "Inactive"}</span></p>
        ${roleInfo}
      </div>
      <div class="action-buttons" style="margin-top: 1rem;">
        <button class="btn-small btn-view" onclick="viewStaff(${
          person.User_ID
        }, '${person.Role}')">View</button>
        <button class="btn-small btn-edit" onclick="editStaff(${
          person.User_ID
        }, '${person.Role}')">Edit</button>
        <button class="btn-small btn-delete" onclick="deleteStaff(${
          person.User_ID
        })">Delete</button>
      </div>
    `;
    container.appendChild(card);
  });
}

// Workouts section
function getWorkoutsHTML() {
  return `
        <div class="section-header">
            <h1>Workouts & Exercises</h1>
            <button class="action-btn" id="add-workout-btn">
                <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px">
                    <path d="M440-440H200v-80h240v-240h80v240h240v80H520v240h-80v-240Z"/>
                </svg>
                Add New Exercise
            </button>
        </div>
        
        <div class="filters">
            <input type="text" class="form-control" placeholder="Search exercises..." id="workout-search">
            <select class="form-control" id="workout-filter">
                <option value="all">All Exercises</option>
                <option value="beginner">Beginner</option>
                <option value="intermediate">Intermediate</option>
                <option value="advanced">Advanced</option>
            </select>
        </div>
        
        <div class="card-grid">
            <div class="workout-card" onclick="showWorkoutDetails(1)">
                <h3>Loading exercises...</h3>
                <p>Please wait</p>
                <div class="workout-stats">
                    <span>Loading</span>
                </div>
            </div>
        </div>
        
        <div class="section-header" style="margin-top: 3rem;">
            <h2>Workout Programs</h2>
        </div>
        
        <div class="card-grid" id="workout-programs">
            <div class="workout-card">
                <h3>Loading programs...</h3>
                <p>Please wait</p>
                <div class="workout-stats">
                    <span>Loading</span>
                </div>
            </div>
        </div>
    `;
}

function initWorkoutsFunctionality() {
  loadExercises();
  loadWorkoutPrograms();

  const addWorkoutBtn = document.getElementById("add-workout-btn");
  if (addWorkoutBtn) {
    addWorkoutBtn.addEventListener("click", function (e) {
      showAddExerciseForm();
      createRipple(this, e);
    });
  }

  const searchInput = document.getElementById("workout-search");
  const filterSelect = document.getElementById("workout-filter");

  if (searchInput) {
    searchInput.addEventListener("input", filterExercises);
  }
  if (filterSelect) {
    filterSelect.addEventListener("change", filterExercises);
  }
}

function loadExercises() {
  fetch("api.php?action=getWorkouts")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        displayExercises(data.data);
      }
    })
    .catch((error) => {
      console.error("Error loading exercises:", error);
    });
}

function loadWorkoutPrograms() {
  fetch("api.php?action=getWorkoutPrograms")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        displayWorkoutPrograms(data.data);
      }
    })
    .catch((error) => {
      console.error("Error loading workout programs:", error);
    });
}

function displayExercises(exercises) {
  const container = document.querySelector(".card-grid");
  if (!container || exercises.length === 0) return;

  container.innerHTML = "";

  exercises.forEach((exercise) => {
    const card = document.createElement("div");
    card.className = "workout-card";
    card.onclick = () => showExerciseDetails(exercise.Exercise_ID);
    card.innerHTML = `
      <h3>${exercise.Name}</h3>
      <p>${exercise.Description.substring(0, 80)}${
      exercise.Description.length > 80 ? "..." : ""
    }</p>
      <div class="workout-stats">
        <span class="difficulty-${
          exercise.Difficultly?.toLowerCase() || "beginner"
        }">${exercise.Difficultly || "Beginner"}</span>
        <span>${exercise.Target_Muscle_Group}</span>
      </div>
    `;
    container.appendChild(card);
  });
}

function displayWorkoutPrograms(programs) {
  const container = document.getElementById("workout-programs");
  if (!container || programs.length === 0) return;

  container.innerHTML = "";

  programs.forEach((program) => {
    const card = document.createElement("div");
    card.className = "workout-card";
    card.onclick = () => showWorkoutProgramDetails(program.Workout_ID);
    card.innerHTML = `
      <h3>${program.Title}</h3>
      <p>${program.Description}</p>
      <div class="workout-stats">
        <span>${program.Weeks_Duration} weeks</span>
        <span>${program.Status}</span>
      </div>
      <div class="workout-footer">
        <small>For: ${program.Member_First} ${program.Member_Last}</small>
        <small>By: ${program.Coach_First} ${program.Coach_Last}</small>
      </div>
    `;
    container.appendChild(card);
  });
}

function filterExercises() {
  const searchInput = document.getElementById("workout-search");
  const filterSelect = document.getElementById("workout-filter");
  const searchTerm = searchInput.value.toLowerCase();
  const filterValue = filterSelect.value;

  const cards = document.querySelectorAll(".workout-card");
  cards.forEach((card) => {
    const title = card.querySelector("h3").textContent.toLowerCase();
    const difficulty = card
      .querySelector(".workout-stats span:first-child")
      .textContent.toLowerCase();
    let show = true;

    if (searchTerm && !title.includes(searchTerm)) {
      show = false;
    }

    if (filterValue !== "all" && !difficulty.includes(filterValue)) {
      show = false;
    }

    card.style.display = show ? "" : "none";
  });
}

// Schedule section
function getScheduleHTML() {
  return `
        <div class="section-header">
            <h1>Class Schedule</h1>
            <button class="action-btn" id="add-class-btn">
                <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px">
                    <path d="M440-440H200v-80h240v-240h80v240h240v80H520v240h-80v-240Z"/>
                </svg>
                Schedule New Class
            </button>
        </div>
        
        <div class="card-grid" id="schedule-container">
            <div class="workout-card">
                <h3>Coming Soon</h3>
                <p>Schedule feature will be available soon</p>
                <div class="workout-stats">
                    <span>Feature</span>
                    <span>In Development</span>
                </div>
            </div>
        </div>
    `;
}

function initScheduleFunctionality() {
  const addClassBtn = document.getElementById("add-class-btn");
  if (addClassBtn) {
    addClassBtn.addEventListener("click", function (e) {
      showNotification("Schedule feature coming soon!", "info");
      createRipple(this, e);
    });
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
                    <div class="profile-avatar">A</div>
                    <div class="profile-info">
                        <h2>Admin User</h2>
                        <p>System Administrator</p>
                    </div>
                </div>
                <div class="profile-details">
                    <div class="detail-item">
                        <strong>Email:</strong> admin@powergym.com
                    </div>
                    <div class="detail-item">
                        <strong>Role:</strong> Administrator
                    </div>
                    <div class="detail-item">
                        <strong>Last Login:</strong> Today, 09:30 AM
                    </div>
                    <div class="detail-item">
                        <strong>Permissions:</strong> Full Access
                    </div>
                </div>
            </div>
        </div>
    `;
}

function initProfileFunctionality() {
  // Profile functionality
}

function getContactMessagesHTML() {
  return `
        <div class="section-header">
            <h1>Contact Messages</h1>
        </div>
        
        <div class="filters">
            <input type="text" class="form-control" placeholder="Search messages..." id="message-search">
            <select class="form-control" id="message-filter">
                <option value="all">All Messages</option>
                <option value="unread">Unread</option>
                <option value="read">Read</option>
            </select>
        </div>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Message</th>
                    <th>Submitted</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="messages-table-body">
                <tr><td colspan="7" style="text-align: center; padding: 2rem;">Loading messages...</td></tr>
            </tbody>
        </table>
    `;
}

function initContactMessagesFunctionality() {
  loadContactMessages();
  
  const searchInput = document.getElementById("message-search");
  const filterSelect = document.getElementById("message-filter");
  
  searchInput.addEventListener("input", filterMessages);
  filterSelect.addEventListener("change", filterMessages);
}

function loadContactMessages() {
  fetch('/a/api/contact/get_messages.php')
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        displayContactMessages(data.messages);
      } else {
        console.error('Failed to load messages:', data.error);
      }
    })
    .catch(error => {
      console.error('Error loading messages:', error);
    });
}

function displayContactMessages(messages) {
  const tbody = document.getElementById("messages-table-body");
  
  if (messages.length === 0) {
    tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 2rem;">No messages found</td></tr>';
    return;
  }
  
  tbody.innerHTML = messages.map(msg => `
    <tr data-message-id="${msg.Contact_ID}">
      <td>${msg.Contact_ID}</td>
      <td>${msg.Full_Name}</td>
      <td>${msg.Email}</td>
      <td>${msg.Message.length > 50 ? msg.Message.substring(0, 50) + '...' : msg.Message}</td>
      <td>${new Date(msg.Submitted_At).toLocaleDateString()}</td>
      <td><span class="status ${msg.Is_Read ? 'read' : 'unread'}">${msg.Is_Read ? 'Read' : 'Unread'}</span></td>
      <td>
        <button class="action-btn small" onclick="viewContactMessage(${msg.Contact_ID})">View</button>
        <button class="action-btn small danger" onclick="deleteContactMessage(${msg.Contact_ID})">Delete</button>
      </td>
    </tr>
  `).join('');
}

function filterMessages() {
  const searchTerm = document.getElementById("message-search").value.toLowerCase();
  const filter = document.getElementById("message-filter").value;
  const rows = document.querySelectorAll("#messages-table-body tr");
  
  rows.forEach(row => {
    if (row.cells.length < 7) return; // Skip loading/no messages row
    
    const name = row.cells[1].textContent.toLowerCase();
    const email = row.cells[2].textContent.toLowerCase();
    const message = row.cells[3].textContent.toLowerCase();
    const status = row.cells[5].textContent.toLowerCase();
    
    const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm) || message.includes(searchTerm);
    const matchesFilter = filter === 'all' || 
                         (filter === 'read' && status === 'read') || 
                         (filter === 'unread' && status === 'unread');
    
    row.style.display = matchesSearch && matchesFilter ? '' : 'none';
  });
}

function viewContactMessage(messageId) {
  fetch(`/a/api/contact/get_message.php?id=${messageId}`)
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        const msg = data.message;
        const content = `
          <div class="message-details">
            <div class="detail-row">
              <strong>From:</strong> ${msg.Full_Name} (${msg.Email})
            </div>
            <div class="detail-row">
              <strong>Submitted:</strong> ${new Date(msg.Submitted_At).toLocaleString()}
            </div>
            <div class="detail-row">
              <strong>Status:</strong> ${msg.Is_Read ? 'Read' : 'Unread'}
            </div>
            <div class="detail-row">
              <strong>Message:</strong>
            </div>
            <div class="message-content">
              ${msg.Message.replace(/\n/g, '<br>')}
            </div>
          </div>
        `;
        showModal('Contact Message Details', content);
        
        // Mark as read if not already
        if (!msg.Is_Read) {
          markMessageAsRead(messageId);
        }
      } else {
        alert('Failed to load message details');
      }
    })
    .catch(error => {
      console.error('Error loading message:', error);
      alert('Error loading message details');
    });
}

function deleteContactMessage(messageId) {
  if (!confirm('Are you sure you want to delete this message?')) return;
  
  fetch(`/a/api/contact/delete_message.php?id=${messageId}`, {
    method: 'DELETE'
  })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        loadContactMessages(); // Reload the messages
      } else {
        alert('Failed to delete message');
      }
    })
    .catch(error => {
      console.error('Error deleting message:', error);
      alert('Error deleting message');
    });
}

function markMessageAsRead(messageId) {
  fetch(`/a/api/contact/mark_read.php?id=${messageId}`, {
    method: 'POST'
  })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Update the status in the table
        const row = document.querySelector(`tr[data-message-id="${messageId}"]`);
        if (row) {
          const statusCell = row.cells[5];
          statusCell.innerHTML = '<span class="status read">Read</span>';
        }
      }
    })
    .catch(error => console.error('Error marking message as read:', error));
}

// Modal Forms
function showAddMemberForm() {
  const formHTML = `
        <div class="form-group">
            <label for="member-first-name">First Name *</label>
            <input type="text" id="member-first-name" class="form-control" placeholder="Enter first name" required>
        </div>
        <div class="form-group">
            <label for="member-last-name">Last Name *</label>
            <input type="text" id="member-last-name" class="form-control" placeholder="Enter last name" required>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="member-email">Email *</label>
                <input type="email" id="member-email" class="form-control" placeholder="Enter email" required>
            </div>
            <div class="form-group">
                <label for="member-phone">Phone</label>
                <input type="tel" id="member-phone" class="form-control" placeholder="Enter phone number">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="member-dob">Date of Birth</label>
                <input type="date" id="member-dob" class="form-control">
            </div>
            <div class="form-group">
                <label for="member-gender">Gender</label>
                <select id="member-gender" class="form-control">
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="member-height">Height (cm)</label>
                <input type="number" id="member-height" class="form-control" placeholder="Height in cm">
            </div>
            <div class="form-group">
                <label for="member-weight">Weight (kg)</label>
                <input type="number" id="member-weight" class="form-control" placeholder="Weight in kg">
            </div>
        </div>
        <div class="form-group">
            <label for="member-goals">Training Goals</label>
            <textarea id="member-goals" class="form-control" placeholder="Enter training goals" rows="3"></textarea>
        </div>
        <div class="form-group">
            <label for="member-experience">Experience Level</label>
            <select id="member-experience" class="form-control">
                <option value="Beginner">Beginner</option>
                <option value="Intermediate">Intermediate</option>
                <option value="Advanced">Advanced</option>
            </select>
        </div>
        <button class="action-btn" style="width: 100%; margin-top: 1rem;" onclick="submitMemberForm()">
            Add Member
        </button>
        <p style="margin-top: 0.5rem; font-size: 0.8rem; color: var(--text-clr); opacity: 0.7;">* Required fields</p>
    `;

  showModal("Add New Member", formHTML);
}

function showAddStaffForm(role) {
  const roleName =
    role === "coach"
      ? "Coach"
      : role === "nutritionist"
      ? "Nutritionist"
      : "Staff";

  const formHTML = `
        <div class="form-group">
            <label for="staff-first-name">First Name *</label>
            <input type="text" id="staff-first-name" class="form-control" placeholder="Enter first name" required>
        </div>
        <div class="form-group">
            <label for="staff-last-name">Last Name *</label>
            <input type="text" id="staff-last-name" class="form-control" placeholder="Enter last name" required>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="staff-email">Email *</label>
                <input type="email" id="staff-email" class="form-control" placeholder="Enter email" required>
            </div>
            <div class="form-group">
                <label for="staff-phone">Phone</label>
                <input type="tel" id="staff-phone" class="form-control" placeholder="Enter phone number">
            </div>
        </div>
        ${
          role === "coach" || role === "nutritionist"
            ? `
        <div class="form-group">
            <label for="staff-bio">Bio</label>
            <textarea id="staff-bio" class="form-control" placeholder="Enter bio" rows="3"></textarea>
        </div>
        <div class="form-group">
            <label for="staff-specialization">Specialization</label>
            <input type="text" id="staff-specialization" class="form-control" placeholder="Enter specialization">
        </div>
        <div class="form-group">
            <label for="staff-certifications">Certifications</label>
            <textarea id="staff-certifications" class="form-control" placeholder="Enter certifications" rows="2"></textarea>
        </div>
        `
            : ""
        }
        <input type="hidden" id="staff-role" value="${role}">
        <button class="action-btn" style="width: 100%; margin-top: 1rem;" onclick="submitStaffForm()">
            Add ${roleName}
        </button>
        <p style="margin-top: 0.5rem; font-size: 0.8rem; color: var(--text-clr); opacity: 0.7;">* Required fields</p>
    `;

  showModal(`Add New ${roleName}`, formHTML);
}

function showAddExerciseForm() {
  const formHTML = `
        <div class="form-group">
            <label for="exercise-name">Exercise Name *</label>
            <input type="text" id="exercise-name" class="form-control" placeholder="Enter exercise name" required>
        </div>
        <div class="form-group">
            <label for="exercise-description">Description *</label>
            <textarea id="exercise-description" class="form-control" placeholder="Enter exercise description" rows="3" required></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="exercise-difficulty">Difficulty Level</label>
                <select id="exercise-difficulty" class="form-control">
                    <option value="Beginner">Beginner</option>
                    <option value="Intermediate">Intermediate</option>
                    <option value="Advanced">Advanced</option>
                </select>
            </div>
            <div class="form-group">
                <label for="exercise-muscle">Target Muscle Group *</label>
                <input type="text" id="exercise-muscle" class="form-control" placeholder="e.g., Chest, Back, Legs" required>
            </div>
        </div>
        <div class="form-group">
            <label for="exercise-instructions">Instructions</label>
            <textarea id="exercise-instructions" class="form-control" placeholder="Enter exercise instructions" rows="3"></textarea>
        </div>
        <div class="form-group">
            <label for="exercise-equipment">Equipment Required</label>
            <input type="text" id="exercise-equipment" class="form-control" placeholder="e.g., Dumbbells, Bench, Bodyweight">
        </div>
        <button class="action-btn" style="width: 100%; margin-top: 1rem;" onclick="submitExerciseForm()">
            Add Exercise
        </button>
        <p style="margin-top: 0.5rem; font-size: 0.8rem; color: var(--text-clr); opacity: 0.7;">* Required fields</p>
    `;

  showModal("Add New Exercise", formHTML);
}

function showAddWorkoutForm() {
  const formHTML = `
        <div class="form-group">
            <label for="workout-title">Workout Title *</label>
            <input type="text" id="workout-title" class="form-control" placeholder="Enter workout title" required>
        </div>
        <div class="form-group">
            <label for="workout-description">Description</label>
            <textarea id="workout-description" class="form-control" placeholder="Enter workout description" rows="3"></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="workout-goal">Goal</label>
                <select id="workout-goal" class="form-control">
                    <option value="Build Muscle">Build Muscle</option>
                    <option value="Lose Weight">Lose Weight</option>
                    <option value="Increase Strength">Increase Strength</option>
                    <option value="Improve Endurance">Improve Endurance</option>
                    <option value="General Fitness">General Fitness</option>
                </select>
            </div>
            <div class="form-group">
                <label for="workout-weeks">Weeks Duration</label>
                <input type="number" id="workout-weeks" class="form-control" value="4" min="1" max="52">
            </div>
        </div>
        <div class="form-group">
            <label for="workout-status">Status</label>
            <select id="workout-status" class="form-control">
                <option value="Active">Active</option>
                <option value="Draft">Draft</option>
                <option value="Archived">Archived</option>
            </select>
        </div>
        <button class="action-btn" style="width: 100%; margin-top: 1rem;" onclick="submitWorkoutForm()">
            Create Workout Program
        </button>
        <p style="margin-top: 0.5rem; font-size: 0.8rem; color: var(--text-clr); opacity: 0.7;">* Required fields</p>
    `;

  showModal("Create Workout Program", formHTML);
}

// Form Submission Functions
function submitMemberForm() {
  const firstName = document.getElementById("member-first-name")?.value.trim();
  const lastName = document.getElementById("member-last-name")?.value.trim();
  const email = document.getElementById("member-email")?.value.trim();
  const phone = document.getElementById("member-phone")?.value.trim();
  const dob = document.getElementById("member-dob")?.value;
  const gender = document.getElementById("member-gender")?.value;
  const height = document.getElementById("member-height")?.value;
  const weight = document.getElementById("member-weight")?.value;
  const goals = document.getElementById("member-goals")?.value.trim();
  const experience = document.getElementById("member-experience")?.value;

  if (!firstName || !lastName || !email) {
    showNotification("Please fill in all required fields", "error");
    return;
  }

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email)) {
    showNotification("Please enter a valid email address", "error");
    return;
  }

  const submitBtn = document.querySelector("#modal .action-btn");
  const originalText = submitBtn.textContent;
  submitBtn.innerHTML = "Adding...";
  submitBtn.disabled = true;

  const memberData = {
    adminId: 1,
    Email: email,
    First_Name: firstName,
    Last_Name: lastName,
    Phone_Number: phone,
    DOB: dob || "2000-01-01",
    Gender: gender,
    Height: parseInt(height) || 0,
    Weight: parseFloat(weight) || 0,
    Training_Goals: goals,
    Experience_Level: experience,
  };

  fetch("api.php?action=addMember", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(memberData),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showNotification(
          `New member "${firstName} ${lastName}" added successfully!`,
          "success"
        );
        const modal = document.getElementById("modal");
        if (modal) {
          modal.classList.remove("active");
        }

        // Refresh members list if on members page
        if (document.getElementById("members-table-body")) {
          loadMembers();
        }
        loadDashboardStats();
        loadRecentActivity();
      } else {
        showNotification(data.message || "Failed to add member", "error");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showNotification("Network error. Please try again.", "error");
    })
    .finally(() => {
      submitBtn.textContent = originalText;
      submitBtn.disabled = false;
    });
}

function submitStaffForm() {
  const firstName = document.getElementById("staff-first-name")?.value.trim();
  const lastName = document.getElementById("staff-last-name")?.value.trim();
  const email = document.getElementById("staff-email")?.value.trim();
  const phone = document.getElementById("staff-phone")?.value.trim();
  const role = document.getElementById("staff-role")?.value;
  const bio = document.getElementById("staff-bio")?.value.trim();
  const specialization = document
    .getElementById("staff-specialization")
    ?.value.trim();
  const certifications = document
    .getElementById("staff-certifications")
    ?.value.trim();

  if (!firstName || !lastName || !email) {
    showNotification("Please fill in all required fields", "error");
    return;
  }

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email)) {
    showNotification("Please enter a valid email address", "error");
    return;
  }

  const submitBtn = document.querySelector("#modal .action-btn");
  const originalText = submitBtn.textContent;
  submitBtn.innerHTML = "Adding...";
  submitBtn.disabled = true;

  const staffData = {
    adminId: 1,
    Email: email,
    First_Name: firstName,
    Last_Name: lastName,
    Phone_Number: phone,
    Role: role,
    Bio: bio,
    Specialization: specialization,
    Certifications: certifications,
  };

  fetch("api.php?action=addStaff", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(staffData),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showNotification(
          `New ${role} "${firstName} ${lastName}" added successfully!`,
          "success"
        );
        const modal = document.getElementById("modal");
        if (modal) {
          modal.classList.remove("active");
        }

        // Refresh staff list if on staff page
        if (document.getElementById("staff-container")) {
          loadStaff(role);
        }
        loadDashboardStats();
        loadRecentActivity();
      } else {
        showNotification(data.message || "Failed to add staff member", "error");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showNotification("Network error. Please try again.", "error");
    })
    .finally(() => {
      submitBtn.textContent = originalText;
      submitBtn.disabled = false;
    });
}

function submitExerciseForm() {
  const name = document.getElementById("exercise-name")?.value.trim();
  const description = document
    .getElementById("exercise-description")
    ?.value.trim();
  const difficulty = document.getElementById("exercise-difficulty")?.value;
  const muscle = document.getElementById("exercise-muscle")?.value.trim();
  const instructions = document
    .getElementById("exercise-instructions")
    ?.value.trim();
  const equipment = document.getElementById("exercise-equipment")?.value.trim();

  if (!name || !description || !muscle) {
    showNotification("Please fill in all required fields", "error");
    return;
  }

  const submitBtn = document.querySelector("#modal .action-btn");
  const originalText = submitBtn.textContent;
  submitBtn.innerHTML = "Adding...";
  submitBtn.disabled = true;

  const exerciseData = {
    adminId: 1,
    Name: name,
    Description: description,
    Difficultly: difficulty,
    Target_Muscle_Group: muscle,
    Instuctions: instructions,
    Equipment_Required: equipment,
  };

  fetch("api.php?action=addExercise", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(exerciseData),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showNotification(`Exercise "${name}" added successfully!`, "success");
        const modal = document.getElementById("modal");
        if (modal) {
          modal.classList.remove("active");
        }

        // Refresh exercises list if on workouts page
        if (document.querySelector(".card-grid")) {
          loadExercises();
        }
      } else {
        showNotification(data.message || "Failed to add exercise", "error");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showNotification("Network error. Please try again.", "error");
    })
    .finally(() => {
      submitBtn.textContent = originalText;
      submitBtn.disabled = false;
    });
}

// View/Edit/Delete Functions
function viewMember(id) {
  fetch(`api.php?action=getMember&id=${id}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        const member = data.data;
        const joinDate = new Date(member.Created_at);
        const dob = member.DOB
          ? new Date(member.DOB).toLocaleDateString()
          : "Not specified";

        const modalHTML = `
                    <div class="member-details">
                        <div class="profile-header" style="margin-bottom: 1.5rem;">
                            <div class="profile-avatar">${member.First_Name.charAt(
                              0
                            )}${member.Last_Name.charAt(0)}</div>
                            <div class="profile-info">
                                <h2>${member.First_Name} ${
          member.Last_Name
        }</h2>
                                <p>Member ID: ${member.User_ID}</p>
                            </div>
                        </div>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <strong>Email:</strong> ${member.Email}
                            </div>
                            <div class="detail-item">
                                <strong>Phone:</strong> ${
                                  member.Phone_Number || "N/A"
                                }
                            </div>
                            <div class="detail-item">
                                <strong>Date of Birth:</strong> ${dob}
                            </div>
                            <div class="detail-item">
                                <strong>Gender:</strong> ${
                                  member.Gender || "Not specified"
                                }
                            </div>
                            <div class="detail-item">
                                <strong>Status:</strong> <span class="status-badge ${
                                  member.Is_Active ? "active" : "inactive"
                                }">${
          member.Is_Active ? "Active" : "Inactive"
        }</span>
                            </div>
                            <div class="detail-item">
                                <strong>Join Date:</strong> ${joinDate.toLocaleDateString()}
                            </div>
                            <div class="detail-item">
                                <strong>Height:</strong> ${
                                  member.Height || 0
                                } cm
                            </div>
                            <div class="detail-item">
                                <strong>Weight:</strong> ${
                                  member.Weight || 0
                                } kg
                            </div>
                            <div class="detail-item">
                                <strong>Body Fat:</strong> ${
                                  member.Body_fat || 0
                                }%
                            </div>
                            <div class="detail-item">
                                <strong>BMI:</strong> ${member.BMI || 0}
                            </div>
                            <div class="detail-item full-width">
                                <strong>Experience Level:</strong> ${
                                  member.Experience_Level || "Beginner"
                                }
                            </div>
                            <div class="detail-item full-width">
                                <strong>Training Goals:</strong> ${
                                  member.Training_Goals || "Not specified"
                                }
                            </div>
                        </div>
                        
                        ${
                          member.workouts && member.workouts.length > 0
                            ? `
                        <div style="margin-top: 2rem;">
                            <h3 style="margin-bottom: 1rem;">Workout Programs (${
                              member.workouts.length
                            })</h3>
                            <div class="workout-list">
                                ${member.workouts
                                  .map(
                                    (workout) => `
                                    <div class="workout-item">
                                        <h4>${workout.Title}</h4>
                                        <p>${workout.Description}</p>
                                        <div class="workout-meta">
                                            <span>${
                                              workout.Weeks_Duration
                                            } weeks</span>
                                            <span class="status-badge ${workout.Status.toLowerCase()}">${
                                      workout.Status
                                    }</span>
                                        </div>
                                    </div>
                                `
                                  )
                                  .join("")}
                            </div>
                        </div>
                        `
                            : ""
                        }
                    </div>
                `;
        showModal("Member Details", modalHTML);
      } else {
        showNotification(
          data.message || "Failed to load member details",
          "error"
        );
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showNotification("Network error. Please try again.", "error");
    });
}

function editMember(id) {
  fetch(`api.php?action=getMember&id=${id}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        const member = data.data;
        showEditMemberModal(member);
      } else {
        showNotification(
          data.message || "Failed to load member details",
          "error"
        );
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showNotification("Network error. Please try again.", "error");
    });
}

function showEditMemberModal(member) {
  const modalHTML = `
        <div class="form-group">
            <label for="edit-first-name">First Name *</label>
            <input type="text" id="edit-first-name" class="form-control" value="${
              member.First_Name
            }" required>
        </div>
        <div class="form-group">
            <label for="edit-last-name">Last Name *</label>
            <input type="text" id="edit-last-name" class="form-control" value="${
              member.Last_Name
            }" required>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="edit-email">Email *</label>
                <input type="email" id="edit-email" class="form-control" value="${
                  member.Email
                }" required>
            </div>
            <div class="form-group">
                <label for="edit-phone">Phone</label>
                <input type="tel" id="edit-phone" class="form-control" value="${
                  member.Phone_Number || ""
                }">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="edit-height">Height (cm)</label>
                <input type="number" id="edit-height" class="form-control" value="${
                  member.Height || 0
                }">
            </div>
            <div class="form-group">
                <label for="edit-weight">Weight (kg)</label>
                <input type="number" id="edit-weight" class="form-control" value="${
                  member.Weight || 0
                }">
            </div>
        </div>
        <div class="form-group">
            <label for="edit-goals">Training Goals</label>
            <textarea id="edit-goals" class="form-control" rows="3">${
              member.Training_Goals || ""
            }</textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="edit-experience">Experience Level</label>
                <select id="edit-experience" class="form-control">
                    <option value="Beginner" ${
                      member.Experience_Level === "Beginner" ? "selected" : ""
                    }>Beginner</option>
                    <option value="Intermediate" ${
                      member.Experience_Level === "Intermediate"
                        ? "selected"
                        : ""
                    }>Intermediate</option>
                    <option value="Advanced" ${
                      member.Experience_Level === "Advanced" ? "selected" : ""
                    }>Advanced</option>
                </select>
            </div>
        </div>
        <input type="hidden" id="edit-member-id" value="${member.User_ID}">
        <button class="action-btn" style="width: 100%; margin-top: 1rem;" onclick="updateMember()">
            Update Member
        </button>
    `;

  showModal("Edit Member", modalHTML);
}

function updateMember() {
  const id = document.getElementById("edit-member-id")?.value;
  const firstName = document.getElementById("edit-first-name")?.value.trim();
  const lastName = document.getElementById("edit-last-name")?.value.trim();
  const email = document.getElementById("edit-email")?.value.trim();
  const phone = document.getElementById("edit-phone")?.value.trim();
  const height = document.getElementById("edit-height")?.value;
  const weight = document.getElementById("edit-weight")?.value;
  const goals = document.getElementById("edit-goals")?.value.trim();
  const experience = document.getElementById("edit-experience")?.value;

  if (!firstName || !lastName || !email) {
    showNotification("Please fill in all required fields", "error");
    return;
  }

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email)) {
    showNotification("Please enter a valid email address", "error");
    return;
  }

  const submitBtn = document.querySelector("#modal .action-btn");
  const originalText = submitBtn.textContent;
  submitBtn.innerHTML = "Updating...";
  submitBtn.disabled = true;

  const updateData = {
    adminId: 1,
    User_ID: parseInt(id),
    First_Name: firstName,
    Last_Name: lastName,
    Email: email,
    Phone_Number: phone,
    Height: parseInt(height) || 0,
    Weight: parseFloat(weight) || 0,
    Training_Goals: goals,
    Experience_Level: experience,
  };

  fetch("api.php?action=updateMember", {
    method: "PUT",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(updateData),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showNotification(
          `Member "${firstName} ${lastName}" updated successfully!`,
          "success"
        );
        const modal = document.getElementById("modal");
        if (modal) {
          modal.classList.remove("active");
        }
        loadMembers();
        loadDashboardStats();
      } else {
        showNotification(data.message || "Failed to update member", "error");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showNotification("Network error. Please try again.", "error");
    })
    .finally(() => {
      submitBtn.textContent = originalText;
      submitBtn.disabled = false;
    });
}

function deleteMember(id) {
  if (
    confirm(
      `Are you sure you want to delete member ID: ${id}? This action cannot be undone.`
    )
  ) {
    showNotification("Deleting member...", "info");

    fetch(`api.php?action=deleteMember&id=${id}&adminId=1`, {
      method: "DELETE",
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          showNotification(`Member ${id} deleted successfully`, "success");
          loadMembers();
          loadDashboardStats();
          loadRecentActivity();
        } else {
          showNotification(data.message || "Failed to delete member", "error");
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        showNotification("Network error. Please try again.", "error");
      });
  }
}

function viewStaff(id, role) {
  fetch(`api.php?action=getMember&id=${id}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        const staff = data.data;
        const modalHTML = `
                    <div class="member-details">
                        <div class="profile-header" style="margin-bottom: 1.5rem;">
                            <div class="profile-avatar">${staff.First_Name.charAt(
                              0
                            )}${staff.Last_Name.charAt(0)}</div>
                            <div class="profile-info">
                                <h2>${staff.First_Name} ${staff.Last_Name}</h2>
                                <p>${
                                  role.charAt(0).toUpperCase() + role.slice(1)
                                }</p>
                            </div>
                        </div>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <strong>Email:</strong> ${staff.Email}
                            </div>
                            <div class="detail-item">
                                <strong>Phone:</strong> ${
                                  staff.Phone_Number || "N/A"
                                }
                            </div>
                            <div class="detail-item">
                                <strong>Status:</strong> <span class="status-badge ${
                                  staff.Is_Active ? "active" : "inactive"
                                }">${
          staff.Is_Active ? "Active" : "Inactive"
        }</span>
                            </div>
                        </div>
                    </div>
                `;
        showModal(
          `${role.charAt(0).toUpperCase() + role.slice(1)} Details`,
          modalHTML
        );
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showNotification("Failed to load staff details", "error");
    });
}

function deleteStaff(id) {
  if (
    confirm(
      `Are you sure you want to delete this staff member? This action cannot be undone.`
    )
  ) {
    showNotification("Deleting staff member...", "info");

    fetch(`api.php?action=deleteMember&id=${id}`, {
      method: "DELETE",
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          showNotification(`Staff member deleted successfully`, "success");
          // Refresh current page
          const currentSection =
            document
              .querySelector("#sidebar li.active a")
              ?.getAttribute("data-section") || "dashboard";
          loadSection(currentSection);
          loadDashboardStats();
        } else {
          showNotification(
            data.message || "Failed to delete staff member",
            "error"
          );
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        showNotification("Network error. Please try again.", "error");
      });
  }
}

function showExerciseDetails(id) {
  fetch(`api.php?action=getWorkouts`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        const exercise = data.data.find((e) => e.Exercise_ID == id);
        if (exercise) {
          const modalHTML = `
                        <div class="workout-details">
                            <h3>${exercise.Name}</h3>
                            <p>${exercise.Description}</p>
                            <div class="workout-info">
                                <p><strong>Difficulty:</strong> <span class="difficulty-${
                                  exercise.Difficultly?.toLowerCase() ||
                                  "beginner"
                                }">${
            exercise.Difficultly || "Beginner"
          }</span></p>
                                <p><strong>Target Muscle:</strong> ${
                                  exercise.Target_Muscle_Group
                                }</p>
                                <p><strong>Secondary Muscles:</strong> ${
                                  exercise.Secondary_Muscles || "None"
                                }</p>
                                <p><strong>Equipment:</strong> ${
                                  exercise.Equipment_Required || "Bodyweight"
                                }</p>
                                ${
                                  exercise.Instuctions
                                    ? `<div style="margin-top: 1rem;"><strong>Instructions:</strong><p>${exercise.Instuctions}</p></div>`
                                    : ""
                                }
                            </div>
                        </div>
                    `;
          showModal("Exercise Details", modalHTML);
        }
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showNotification("Failed to load exercise details", "error");
    });
}

function showWorkoutProgramDetails(id) {
  fetch(`api.php?action=getWorkoutDetails&id=${id}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        const program = data.data;
        let exercisesHTML = "";

        if (program.exercises_by_day) {
          Object.keys(program.exercises_by_day).forEach((day) => {
            exercisesHTML += `<h4 style="margin-top: 1.5rem; color: var(--text-secondary-clr);">Day ${day}</h4>`;
            program.exercises_by_day[day].forEach((exercise) => {
              exercisesHTML += `
                                <div class="exercise-item" style="margin: 0.5rem 0; padding: 0.75rem; background: rgba(255,255,255,0.05); border-radius: 8px;">
                                    <strong>${exercise.Name}</strong>
                                    <p style="margin: 0.25rem 0; font-size: 0.9rem; opacity: 0.8;">${
                                      exercise.Sets
                                    } sets × ${exercise.Reps} reps • Rest: ${
                exercise.Rest_Time
              }</p>
                                    <p style="margin: 0; font-size: 0.85rem; opacity: 0.7;">${
                                      exercise.Target_Muscle_Group
                                    } • ${
                exercise.Equipment_Required || "Bodyweight"
              }</p>
                                </div>
                            `;
            });
          });
        }

        const modalHTML = `
                    <div class="workout-details">
                        <h3>${program.Title}</h3>
                        <p>${program.Description}</p>
                        <div class="workout-info">
                            <p><strong>Goal:</strong> ${program.Goal}</p>
                            <p><strong>Duration:</strong> ${
                              program.Weeks_Duration
                            } weeks</p>
                            <p><strong>Status:</strong> <span class="status-badge ${program.Status.toLowerCase()}">${
          program.Status
        }</span></p>
                            <p><strong>Start Date:</strong> ${new Date(
                              program.Start_Date
                            ).toLocaleDateString()}</p>
                            <p><strong>End Date:</strong> ${new Date(
                              program.End_Date
                            ).toLocaleDateString()}</p>
                        </div>
                        ${
                          exercisesHTML
                            ? `
                        <div style="margin-top: 1.5rem;">
                            <h4>Exercises</h4>
                            ${exercisesHTML}
                        </div>
                        `
                            : ""
                        }
                    </div>
                `;
        showModal("Workout Program Details", modalHTML);
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showNotification("Failed to load workout details", "error");
    });
}

// Notification function
function showNotification(message, type = "info") {
  const notification = document.createElement("div");
  notification.className = `notification ${type}`;
  notification.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" height="20" viewBox="0 -960 960 960" width="20" fill="currentColor">
            <path d="${
              type === "success"
                ? "M382-240 154-468l57-57 171 171 367-367 57 57-424 424Z"
                : type === "error"
                ? "M480-280q17 0 28.5-11.5T520-320q0-17-11.5-28.5T480-360q-17 0-28.5 11.5T440-320q0 17 11.5 28.5T480-280Zm-40-160h80v-240h-80v240Zm40 360q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z"
                : "M440-280h80v-240h-80v240Zm40-320q17 0 28.5-11.5T520-640q0-17-11.5-28.5T480-680q-17 0-28.5 11.5T440-640q0 17 11.5 28.5T480-600Zm0 520q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z"
            }"/>
        </svg>
        <span>${message}</span>
    `;

  document.body.appendChild(notification);

  setTimeout(() => {
    notification.classList.add("show");
  }, 10);

  setTimeout(() => {
    notification.classList.remove("show");
    setTimeout(() => {
      if (notification.parentNode) {
        notification.remove();
      }
    }, 300);
  }, 3000);
}

function initDashboard() {
  loadDashboardContent();
  setupNavigation();
  setupModal();
  setupQuickActions();
  loadDashboardStats();
  loadRecentActivity();
}
// Make functions globally available
window.viewMember = viewMember;
window.editMember = editMember;
window.deleteMember = deleteMember;
window.viewStaff = viewStaff;
window.deleteStaff = deleteStaff;
window.showExerciseDetails = showExerciseDetails;
window.showWorkoutProgramDetails = showWorkoutProgramDetails;
window.submitMemberForm = submitMemberForm;
window.submitStaffForm = submitStaffForm;
window.submitExerciseForm = submitExerciseForm;
window.viewContactMessage = viewContactMessage;
window.deleteContactMessage = deleteContactMessage;

// Update logo based on theme
function updateLogo(theme) {
  const logoImg = document.querySelector("#sidebar img");
  if (logoImg) {
    logoImg.src = theme === "light" ? "../Home Full/Image/logo-without-text.png" : "media/dark-logo-no-text.png";
  }
}
window.updateMember = updateMember;
