<?php
require_once __DIR__ . '/../DB.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Check if user already has a nutritionist assigned - if so, redirect to meal plans
// Check for ANY meal plan with a Nutritionist_ID (regardless of status) - this means they're assigned
// Only redirect if we're not already being redirected (prevent loops)
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // Check if there's a redirect parameter to prevent loops
    if (!isset($_GET['skip_redirect'])) {
        // Check for ANY meal plan with a Nutritionist_ID (any status, even if no plan is created yet)
        $existingCheck = $conn->prepare("SELECT Nutritionist_ID, Meal_Plan_ID, Status FROM mealplan 
                                        WHERE Member_Id = ? AND Nutritionist_ID IS NOT NULL AND is_deleted = 0 
                                        LIMIT 1");
        if ($existingCheck) {
            $existingCheck->bind_param('i', $user_id);
            if ($existingCheck->execute()) {
                $existingResult = $existingCheck->get_result();
                if ($existingResult->num_rows > 0) {
                    $mealPlanData = $existingResult->fetch_assoc();
                    $existingCheck->close();
                    // Debug: log redirect
                    error_log("Doctors.php: User {$user_id} has nutritionist assigned (ID: {$mealPlanData['Nutritionist_ID']}, Status: {$mealPlanData['Status']}), redirecting to MealPlans.php");
                    header("Location: MealPlans.php");
                    exit;
                } else {
                    error_log("Doctors.php: User {$user_id} has no nutritionist assigned (num_rows = 0)");
                }
            } else {
                error_log("Doctors.php: Failed to execute existing check query: " . $existingCheck->error);
            }
            $existingCheck->close();
        } else {
            error_log("Doctors.php: Failed to prepare existing check query: " . $conn->error);
        }
    }
}

$has_nutritionist_access = false;
$subscription_info = null;

// Check subscription access if user is logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // Get current active subscription
    $sql = "SELECT ms.Plan_ID, mp.Name, mp.Tier, mp.Coach_Access, mp.Nutritionist_Access
            FROM MembershipSubscription ms
            JOIN MembershipPlan mp ON ms.Plan_ID = mp.Plan_ID
            WHERE ms.Member_Id = ? AND ms.Status = 1 AND ms.is_deleted = 0
            ORDER BY ms.Created_at DESC
            LIMIT 1";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $has_nutritionist_access = (int)$row['Nutritionist_Access'] === 1;
            $subscription_info = [
                'name' => $row['Name'],
                'tier' => $row['Tier']
            ];
        }
        $stmt->close();
    }
}
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<link rel="stylesheet" href="Nav.css">
<script src="Nav.js" defer></script>
<link rel="stylesheet" href="Doctors.css" />
<script src="Doctors.js" defer></script>
<title>Our Doctors - Power</title>
 <nav id="navbar">
        <ul>
            <li><button id="close-sidebar-button" onclick="CloseSideBar()"><svg xmlns="http://www.w3.org/2000/svg" height="40px" viewBox="0 -960 960 960" width="40px" fill="#c9c9c9"><path d="m480-444.62-209.69 209.7q-7.23 7.23-17.5 7.42-10.27.19-17.89-7.42-7.61-7.62-7.61-17.7 0-10.07 7.61-17.69L444.62-480l-209.7-209.69q-7.23-7.23-7.42-17.5-.19-10.27 7.42-17.89 7.62-7.61 17.7-7.61 10.07 0 17.69 7.61L480-515.38l209.69-209.7q7.23-7.23 17.5-7.42 10.27-.19 17.89 7.42 7.61 7.62 7.61 17.7 0 10.07-7.61 17.69L515.38-480l209.7 209.69q7.23 7.23 7.42 17.5.19 10.27-7.42 17.89-7.62 7.61-17.7 7.61-10.07 0-17.69-7.61L480-444.62Z"/></svg></button></li>
            <li class="Home-li"><a href="../Home Full/Home.php"><img id="logo" src="media/dark-logo-no-text.png" alt=""></a> </li>
            <li><a href="../Store/Store/Store.php">Store</a></li>
            <li><a href="../About us/Aboutus.php">About</a></li>
            <li><a href="../FAQ/FAQ.php">FAQ</a></li>
            <li><a href="../Contact us/contact.php">Contact Us</a></li>
           <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
            <!-- USER IS LOGGED IN -->
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <li><a href="../Admin/admin.php">Admin Panel</a></li>
            <?php endif; ?>

            <?php if ($_SESSION['role'] === 'member'): ?>
                <li><a href="../Workouts Full/workouts.php">Member Dashboard</a></li>
                <li><a href="../UserProfile/userprofile.php">My Profile</a></li>
            <?php endif; ?>

            <?php if ($_SESSION['role'] === 'coach'): ?>
                <li><a href="coachprofile.php">Coach Dashboard</a></li>
            <?php endif; ?>

            <?php if ($_SESSION['role'] === 'nutritionist'): ?>
                <li><a href="../Doctors/DoctorDashboard.php">Nutritionist Dashboard</a></li>
            <?php endif; ?>

            <li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?logout=1">Logout</a></li>

        <?php else: ?>

            <!-- USER IS NOT LOGGED IN -->
            <li><a href="../Login/Loginsignup.php">Login</a></li>

        <?php endif; ?>
            <li>
                <label class="switch">
                    <input type="checkbox" id="switch" />
                    <span class="slider"></span>
                </label>
            </li>
            <li class="Cart"><a href="../Cart/Cart/Cart.php"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M280-80q-33 0-56.5-23.5T200-160q0-33 23.5-56.5T280-240q33 0 56.5 23.5T360-160q0 33-23.5 56.5T280-80Zm400 0q-33 0-56.5-23.5T600-160q0-33 23.5-56.5T680-240q33 0 56.5 23.5T760-160q0 33-23.5 56.5T680-80ZM246-720l96 200h280l110-200H246Zm-38-80h590q23 0 35 20.5t1 41.5L692-482q-11 20-29.5 31T622-440H324l-44 80h480v80H280q-45 0-68-39.5t-2-78.5l54-98-144-304H40v-80h130l38 80Zm134 280h280-280Z"/></svg></a></li>
        </ul>
    </nav>
    <div id="overlay" onclick="CloseSideBar()"></div>
    <main id="Main-Content">
      <div class="container">
        <!-- Blobs Background -->
        <div class="blobs">
          <div class="blob-dodge"><div class="blob a"></div></div>
          <div class="blob-dodge"><div class="blob b"></div></div>
          <div class="blob-dodge"><div class="blob c"></div></div>
        </div>

        <!-- Page Header -->
        <div class="page-header">
          <h1 class="header-title">Meet Our Doctors</h1>
          <p class="header-subtitle">
            Expert medical professionals dedicated to your health and
            performance
          </p>
        </div>

        <!-- Subscription Access Check -->
        <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && !$has_nutritionist_access): ?>
          <div style="text-align: center; padding: 4rem 2rem; background: var(--card-bg, rgba(255,255,255,0.05)); border-radius: 18px; margin: 2rem 0; position: relative; z-index: 10;">
            <h2 style="color: var(--text-primary, #fff); margin-bottom: 1rem;">Nutritionist Access Required</h2>
            <p style="color: var(--text-secondary, #ccc); margin-bottom: 2rem; max-width: 600px; margin-left: auto; margin-right: auto;">
              <?php if ($subscription_info): ?>
                Your current subscription (<strong><?php echo htmlspecialchars($subscription_info['name']); ?></strong>) does not include access to nutritionists.
              <?php else: ?>
                You need an active <strong>Platinum</strong> subscription to access our nutritionists.
              <?php endif; ?>
            </p>
            <p style="color: var(--text-secondary, #ccc); margin-bottom: 2rem;">
              Upgrade to <strong>Platinum</strong> membership to access our expert nutritionists and meal planning services.
            </p>
            <a href="../Membership Full/Membership.php" style="display: inline-block; padding: 12px 32px; background: linear-gradient(135deg, #a66fff, #5c4e9c); color: white; text-decoration: none; border-radius: 8px; font-weight: 600; transition: transform 0.2s;">
              View Membership Plans
            </a>
          </div>
        <?php else: ?>
        <!-- Doctors Grid -->
        <div class="doctors-grid">
          <!-- Doctor 1 - Athletes Specialist -->
          <div class="doctor-card" data-aos="fade-up">
            <div class="doctor-image">
              <div
                class="avatar"
                style="background: linear-gradient(135deg, #a66fff, #5c4e9c)"
              >
                DR
              </div>
            </div>
            <div class="doctor-info">
              <h3 class="doctor-name">Dr. Ahmed Hassan</h3>
              <p class="doctor-specialty">Athletes Specialist</p>
              <p class="doctor-description">
                Specializes in sports medicine and athletic performance
                optimization. With over 15 years of experience, Dr. Hassan helps
                athletes achieve peak physical condition.
              </p>
            </div>
            <div class="doctor-actions">
              <button class="btn-pick-nutritionist">Pick Nutritionist</button>
            </div>
          </div>

          <!-- Doctor 2 - Bodybuilding Specialist -->
          <div class="doctor-card" data-aos="fade-up" data-aos-delay="100">
            <div class="doctor-image">
              <div
                class="avatar"
                style="background: linear-gradient(135deg, #7c5dff, #4a528e)"
              >
                DR
              </div>
            </div>
            <div class="doctor-info">
              <h3 class="doctor-name">Dr. Sarah Williams</h3>
              <p class="doctor-specialty">Bodybuilding Specialist</p>
              <p class="doctor-description">
                Expert in muscle development and nutrition for bodybuilding. Dr.
                Williams combines scientific research with practical training
                methods to maximize your gains.
              </p>
            </div>
            <div class="doctor-actions">
              <button class="btn-pick-nutritionist">Pick Nutritionist</button>
            </div>
          </div>

          <!-- Doctor 3 - Recovery Specialist -->
          <div class="doctor-card" data-aos="fade-up" data-aos-delay="200">
            <div class="doctor-image">
              <div
                class="avatar"
                style="background: linear-gradient(135deg, #c49aff, #917fe6)"
              >
                DR
              </div>
            </div>
            <div class="doctor-info">
              <h3 class="doctor-name">Dr. Marcus Reid</h3>
              <p class="doctor-specialty">Recovery Specialist</p>
              <p class="doctor-description">
                Focused on injury prevention and recovery protocols. Dr. Reid
                develops personalized rehabilitation plans to keep you healthy
                and training consistently.
              </p>
            </div>
            <div class="doctor-actions">
              <button class="btn-pick-nutritionist">Pick Nutritionist</button>
            </div>
          </div>

          <!-- Doctor 4 - Nutrition Expert -->
          <div class="doctor-card" data-aos="fade-up" data-aos-delay="300">
            <div class="doctor-image">
              <div
                class="avatar"
                style="background: linear-gradient(135deg, #9370db, #6d5aa6)"
              >
                DR
              </div>
            </div>
            <div class="doctor-info">
              <h3 class="doctor-name">Dr. Elena Martinez</h3>
              <p class="doctor-specialty">Nutrition Expert</p>
              <p class="doctor-description">
                Specializes in sports nutrition and dietary optimization. Dr.
                Martinez creates customized meal plans that support your fitness
                goals and overall wellness.
              </p>
            </div>
            <div class="doctor-actions">
              <button class="btn-pick-nutritionist">Pick Nutritionist</button>
            </div>
          </div>
         <?php endif; ?>