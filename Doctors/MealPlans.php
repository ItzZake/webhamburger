<?php
require_once __DIR__ . '/../DB.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Handle logout
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");
    header("Location: ../Home Full/Home.php");
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header("Location: ../Login/Loginsignup.php");
    exit;
}

// Check if user has a nutritionist assigned (check for ANY meal plan with Nutritionist_ID, regardless of status)
// Use the EXACT same query format as Doctors.php to ensure consistency
// If just assigned, wait a bit and retry the query
$hasNutritionist = null;
$retryCount = 0;
$maxRetries = isset($_GET['just_assigned']) ? 3 : 1;

while ($retryCount < $maxRetries && !$hasNutritionist) {
    // Check for ANY meal plan with a Nutritionist_ID (any status) - this means they're assigned
    $nutritionistCheck = $conn->prepare("SELECT Nutritionist_ID, Meal_Plan_ID, Status FROM mealplan 
                                         WHERE Member_Id = ? AND Nutritionist_ID IS NOT NULL AND is_deleted = 0 
                                         LIMIT 1");
    if (!$nutritionistCheck) {
        // If prepare fails, log error
        error_log("MealPlans.php: Failed to prepare nutritionist check query (attempt " . ($retryCount + 1) . "): " . $conn->error);
        $hasNutritionist = false;
    } else {
        $nutritionistCheck->bind_param("i", $user_id);
        if ($nutritionistCheck->execute()) {
            $nutritionistResult = $nutritionistCheck->get_result();
            $hasNutritionist = $nutritionistResult->fetch_assoc();
            $nutritionistCheck->close();
            
            if ($hasNutritionist) {
                error_log("MealPlans.php: User {$user_id} has nutritionist assigned (ID: {$hasNutritionist['Nutritionist_ID']}, Status: {$hasNutritionist['Status']})");
                break; // Found it, exit retry loop
            } else {
                error_log("MealPlans.php: User {$user_id} has no nutritionist assigned (attempt " . ($retryCount + 1) . ")");
                // If this is the last retry, do debug check
                if ($retryCount === $maxRetries - 1) {
                    // Also check if there's ANY meal plan (even with different status) for debugging
                    $debugCheck = $conn->query("SELECT Meal_Plan_ID, Status, Nutritionist_ID, is_deleted FROM mealplan WHERE Member_Id = {$user_id}");
                    if ($debugCheck) {
                        $debugRows = $debugCheck->fetch_all(MYSQLI_ASSOC);
                        error_log("MealPlans.php: Debug - All meal plans for user {$user_id}: " . json_encode($debugRows));
                    }
                }
            }
        } else {
            error_log("MealPlans.php: Failed to execute nutritionist check query (attempt " . ($retryCount + 1) . "): " . $nutritionistCheck->error);
            $nutritionistCheck->close();
            $hasNutritionist = false;
        }
    }
    
    $retryCount++;
    if ($retryCount < $maxRetries && !$hasNutritionist) {
        // Wait before retrying (only if just assigned)
        usleep(500000); // 0.5 seconds
    }
}

// If we still don't have a nutritionist after all checks, redirect (but prevent loops)
if (!$hasNutritionist && !isset($_GET['from_doctors'])) {
    header("Location: Doctors.php?skip_redirect=1");
    exit;
}

// Get nutritionist name for display (only if we have a nutritionist)
$nutritionistName = "Your Nutritionist";
if ($hasNutritionist && isset($hasNutritionist['Nutritionist_ID'])) {
    $nutritionistId = $hasNutritionist['Nutritionist_ID'];
    $nutStmt = $conn->prepare("SELECT u.First_Name, u.Last_Name FROM userprofile u WHERE u.User_ID = ?");
    if ($nutStmt) {
        $nutStmt->bind_param("i", $nutritionistId);
        if ($nutStmt->execute()) {
            $nutResult = $nutStmt->get_result();
            $nutritionist = $nutResult->fetch_assoc();
            if ($nutritionist) {
                $nutritionistName = trim($nutritionist['First_Name'] . ' ' . $nutritionist['Last_Name']);
            }
        }
        $nutStmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="Dashboard.css" />
    <link rel="stylesheet" href="MealPlans.css" />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
      rel="stylesheet"
    />
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet" />
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/toast.js" defer></script>
    <script src="MealPlans.js" defer></script>
    <title>Meal Plans - Power</title>
    <script>
      // Initialize theme immediately to prevent FOUC
      (function() {
        const savedTheme = localStorage.getItem("theme") || "dark";
        document.documentElement.setAttribute("data-theme", savedTheme);
      })();
    </script>
  </head>
  <body>
    <div id="sidebar-container"></div>
    <script src="dashboard_loader.js" defer></script>
    <script src="dashboard.js" defer></script>

    <main class="content">
      <div class="container">
        <!-- Blobs Background -->
        <div class="blobs">
          <div class="blob-dodge"><div class="blob a"></div></div>
          <div class="blob-dodge"><div class="blob b"></div></div>
          <div class="blob-dodge"><div class="blob c"></div></div>
        </div>

        <!-- Calorie Progress Bar -->
        <div id="calorie-bar-container" class="calorie-bar-container">
          <div class="calorie-bar-header">
            <h2>Daily Calorie Goal</h2>
            <div class="calorie-stats">
              <span id="calories-consumed">0</span>
              <span class="separator">/</span>
              <span id="calories-target">0</span>
              <span class="cal-unit">cal</span>
            </div>
          </div>
          <div class="calorie-progress-bar">
            <div id="calorie-progress" class="calorie-progress"></div>
          </div>
          <div class="calorie-macros">
            <div class="macro-item">
              <span class="macro-label">Carbs</span>
              <span id="carbs-consumed" class="macro-value">0g</span>
            </div>
            <div class="macro-item">
              <span class="macro-label">Protein</span>
              <span id="protein-consumed" class="macro-value">0g</span>
            </div>
            <div class="macro-item">
              <span class="macro-label">Fats</span>
              <span id="fats-consumed" class="macro-value">0g</span>
            </div>
          </div>
        </div>

      

        <!-- Page Header -->
        <div class="page-header">
          <h1 class="header-title" id="doctor-name"><?php echo htmlspecialchars($nutritionistName); ?>'s Meal Plans</h1>
          <p class="header-subtitle">
            Personalized nutrition plans tailored for your fitness goals
          </p>
        </div>

        <!-- Meal Categories -->
        <div class="meal-categories" id="meal-categories">
          <!-- Meals will be loaded dynamically -->
        </div>
      </div>
    </main>

    <!-- Modal for showing meal details -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-title">Meal Details</h2>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body" id="modal-body">
                <!-- Modal content will be loaded here -->
            </div>
        </div>
    </div>
  </body>
</html>

