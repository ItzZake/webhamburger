<?php
require_once __DIR__ . '/../api/helpers/auth.php';
require_role(['doctor','nutritionist','admin']);

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: ../Home Full/Home.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nutritionist Dashboard - Power Gym</title>
    <link rel="stylesheet" href="../Admin/admin.css">
    <style>
      .status-badge.completed {
        background: rgba(16, 185, 129, 0.1);
        color: var(--success-color);
        border: 1px solid rgba(16, 185, 129, 0.2);
      }
      .status-badge.paused {
        background: rgba(245, 158, 11, 0.1);
        color: var(--warning-color);
        border: 1px solid rgba(245, 158, 11, 0.2);
      }
      .meal-category-section {
        margin-bottom: 30px;
        padding: 20px;
        background: var(--card-bg);
        border-radius: 12px;
        border: 1px solid var(--border-color);
      }
      .meal-category-section h3 {
        margin: 0 0 15px 0;
        color: var(--accent-secondary);
        font-size: 1.2rem;
      }
      .food-items-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 15px;
        margin-top: 15px;
        max-height: 400px;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 10px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        background: var(--card-bg);
      }
      .food-items-grid::-webkit-scrollbar {
        width: 8px;
      }
      .food-items-grid::-webkit-scrollbar-track {
        background: var(--base-clr);
        border-radius: 4px;
      }
      .food-items-grid::-webkit-scrollbar-thumb {
        background: var(--accent-secondary);
        border-radius: 4px;
      }
      .food-items-grid::-webkit-scrollbar-thumb:hover {
        background: var(--accent-primary);
      }
      .food-item-card {
        padding: 15px;
        background: var(--base-clr);
        border-radius: 8px;
        border: 2px solid var(--border-color);
        cursor: pointer;
        transition: all 0.3s ease;
      }
      .food-item-card:hover {
        border-color: var(--accent-secondary);
        transform: translateY(-2px);
      }
      .food-item-card.selected {
        border-color: var(--accent-secondary);
        background: rgba(166, 111, 255, 0.1);
      }
      .food-item-card h4 {
        margin: 0 0 8px 0;
        font-size: 1rem;
      }
      .food-item-card .nutrition-info {
        font-size: 0.85rem;
        color: var(--text-secondary-clr);
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
      }
      .nutrition-badge {
        padding: 4px 8px;
        background: rgba(166, 111, 255, 0.1);
        border-radius: 4px;
        font-size: 0.8rem;
      }
    </style>
    <script>
      // Set theme immediately to prevent flash
      (function() {
        const savedTheme = localStorage.getItem("theme") || "dark";
        document.documentElement.setAttribute("data-theme", savedTheme);
      })();
    </script>
    <script src="../assets/js/toast.js" defer></script>
    <script src="../Admin/admin.js" defer></script>
    <script src="DoctorDashboard.js" defer></script>
</head>
<body>
    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn">
        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor">
            <path d="M120-240v-80h720v80H120Zm0-200v-80h720v80H120Zm0-200v-80h720v80H120Z"/>
        </svg>
    </button>

    <nav id="sidebar" class="close">
        <ul>
            <li>
                <span class="logo">Nutritionist</span>
                <button id="toggle-btn" onclick="window.location.href='../Home Full/Home.php'">
                    <img id="logo" src="../Admin/media/dark-logo-no-text.png" alt="">
                </button>
            </li>
            <li class="active">
                <a href="#" data-section="dashboard">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3">
                        <path d="M520-600v-240h320v240H520ZM120-440v-400h320v400H120Zm400 320v-400h320v400H520Zm-400 0v-240h320v240H120Zm80-400h160v-240H200v240Zm400 320h160v-240H600v240Zm0-480h160v-80H600v80ZM200-200h160v-80H200v80Zm160-320Zm240-160Zm0 240ZM360-280Z"/>
                    </svg>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="#" data-section="members">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3">
                        <path d="M240-200h120v-240h240v240h120v-360L480-740 240-560v360Zm-80 80v-480l320-240 320 240v480H520v-240h-80v240H160Zm320-350Z"/>
                    </svg>
                    <span>My Members</span>
                </a>
            </li>
            <li>
                <a href="#" data-section="mealplans">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3">
                        <path d="M280-200q-33 0-56.5-23.5T200-280v-400q0-33 23.5-56.5T280-760h480q33 0 56.5 23.5T840-680v400q0 33-23.5 56.5T760-200H280Zm0-80h480v-400H280v400Zm100-80h80v-80h-80v80Zm120 0h80v-80h-80v80Zm120 0h80v-80h-80v80Z"/>
                    </svg>
                    <span>Meal Plans</span>
                </a>
            </li>
            <li>
                <a href="#" data-section="create">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3">
                        <path d="M440-280h80v-240h-80v240Zm40-320q17 0 28.5-11.5T520-640q0-17-11.5-28.5T480-680q-17 0-28.5 11.5T440-640q0 17 11.5 28.5T480-600Zm0 520q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z"/>
                    </svg>
                    <span>Create Meal Plan</span>
                </a>
            </li>
            <li>
                <a href="#" data-section="profile">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3">
                        <path d="M480-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM160-160v-112q0-34 17.5-62.5T224-378q62-31 126-46.5T480-440q66 0 130 15.5T736-378q29 15 46.5 43.5T800-272v112H160Zm80-80h480v-32q0-11-5.5-20T700-306q-54-27-109-40.5T480-360q-56 0-111 13.5T260-306q-9 5-14.5 14t-5.5 20v32Zm240-320q33 0 56.5-23.5T560-640q0-33-23.5-56.5T480-720q-33 0-56.5 23.5T400-640q0 33 23.5 56.5T480-560Zm0-80Zm0 400Z"/>
                    </svg>
                    <span>Profile</span>
                </a>
            </li>
            <li>
                <a href="#" class="action-btn" id="light-mode">
                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px">
                        <path d="M480-360q50 0 85-35t35-85q0-50-35-85t-85-35q-50 0-85 35t-35 85q0 50 35 85t85 35Zm0 80q-83 0-141.5-58.5T280-480q0-83 58.5-141.5T480-680q83 0 141.5 58.5T680-480q0 83-58.5 141.5T480-280ZM200-440H40v-80h160v80Zm720 0H760v-80h160v80ZM440-760v-160h80v160h-80Zm0 720v-160h80v160h-80ZM256-650l-101-97 57-59 96 100-52 56Zm492 496-97-101 53-55 101 97-57 59Zm-98-550 97-101 59 57-100 96-56-52ZM154-212l101-97 55 53-97 101-59-57Zm326-268Z"/>
                    </svg>
                    Light Mode
                </a>
            </li>
            <li>
                <a href="?logout=1">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3">
                        <path d="M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h280v80H200v560h280v80H200Zm440-160-55-58 102-102H360v-80h327L585-622l55-58 200 200-200 200Z"/>
                    </svg>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </nav>

    <main>
        <div id="content-area">
            <!-- Default Dashboard Content will be loaded here -->
          </div>
    </main>

    <!-- Modal for showing details -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-title">Details</h2>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body" id="modal-body">
                <!-- Modal content will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Meal Plan Preview Modal -->
    <div id="mealplan-preview-modal" class="modal">
        <div class="modal-content" style="max-width: 900px; max-height: 90vh; overflow-y: auto;">
            <div class="modal-header">
                <h2 id="preview-title">Meal Plan Preview</h2>
                <button class="modal-close" onclick="closeMealPlanPreview()">&times;</button>
            </div>
            <div class="modal-body" id="mealplan-preview-body">
                <!-- Preview content -->
            </div>
            <div class="modal-footer" style="padding: 20px; border-top: 1px solid var(--border-color); display: flex; gap: 10px; justify-content: flex-end;">
                <button class="action-btn secondary" onclick="closeMealPlanPreview()">Cancel</button>
                <button class="action-btn" id="confirm-mealplan-btn">Confirm & Save</button>
            </div>
        </div>
    </div>
</body>
</html>
