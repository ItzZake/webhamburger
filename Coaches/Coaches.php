<?php
require_once __DIR__ . '/../DB.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$has_coach_access = false;
$subscription_info = null;
$has_coach_assigned = false;
$assigned_coach_id = null;

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
            $has_coach_access = (int)$row['Coach_Access'] === 1;
            $subscription_info = [
                'name' => $row['Name'],
                'tier' => $row['Tier']
            ];
        }
        $stmt->close();
    }
    
    // Check if user already has a coach assigned (active or pending workout program)
    $coachCheckSql = "SELECT Coach_ID FROM workoutprogram 
                       WHERE Member_Id = ? AND (Status = 'Active' OR Status = 'Pending') AND is_deleted = 0 
                       LIMIT 1";
    $coachCheckStmt = $conn->prepare($coachCheckSql);
    if ($coachCheckStmt) {
        $coachCheckStmt->bind_param('i', $user_id);
        $coachCheckStmt->execute();
        $coachResult = $coachCheckStmt->get_result();
        if ($coachRow = $coachResult->fetch_assoc()) {
            $has_coach_assigned = true;
            $assigned_coach_id = $coachRow['Coach_ID'];
            // Redirect to workouts page if coach is already assigned
            header("Location: ../Workouts Full/workouts.php");
            exit;
        }
        $coachCheckStmt->close();
    }
}
?>

<link rel="stylesheet" href="Coaches.css" />
<link rel="stylesheet" href="../Workouts Full/dashboard.css" />
<link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
      rel="stylesheet"
    />
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet" />
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="Coaches.js" defer></script>
    <title>Our Coaches - Power</title>
<link rel="stylesheet" href="Nav.css">

<button id="open-sidebar-button" onclick="OpenSideBar()">
    <svg xmlns="http://www.w3.org/2000/svg" height="40px" viewBox="0 -960 960 960" width="40px" fill="#c9c9c9"><path d="M165.13-254.62q-10.68 0-17.9-7.26-7.23-7.26-7.23-18t7.23-17.86q7.22-7.13 17.9-7.13h629.74q10.68 0 17.9 7.26 7.23 7.26 7.23 18t-7.23 17.87q-7.22 7.12-17.9 7.12H165.13Zm0-200.25q-10.68 0-17.9-7.27-7.23-7.26-7.23-17.99 0-10.74 7.23-17.87 7.22-7.13 17.9-7.13h629.74q10.68 0 17.9 7.27 7.23 7.26 7.23 17.99 0 10.74-7.23 17.87-7.22 7.13-17.9 7.13H165.13Zm0-200.26q-10.68 0-17.9-7.26-7.23-7.26-7.23-18t7.23-17.87q7.22-7.12 17.9-7.12h629.74q10.68 0 17.9 7.26 7.23 7.26 7.23 18t-7.23 17.86q-7.22 7.13-17.9 7.13H165.13Z"/></svg>
</button>
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
                            <li><a href="nutritionistprofile.php">Nutritionist Dashboard</a></li>
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
                        <script src="Nav.js" defer></script>
                        
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
                                    <h1 class="header-title">Meet Our Coaches</h1>
                                    <p class="header-subtitle">
                                        Professional coaches ready to guide you through your fitness journey
                                    </p>
                                </div>
                                
                                <!-- Subscription Access Check -->
                                <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && !$has_coach_access): ?>
                                    <div style="text-align: center; padding: 4rem 2rem; background: var(--card-bg, rgba(255,255,255,0.05)); border-radius: 18px; margin: 2rem 0; position: relative; z-index: 10;">
                                        <h2 style="color: var(--text-primary, #fff); margin-bottom: 1rem;">Coach Access Required</h2>
                                        <p style="color: var(--text-secondary, #ccc); margin-bottom: 2rem; max-width: 600px; margin-left: auto; margin-right: auto;">
                                            <?php if ($subscription_info): ?>
                                                Your current subscription (<strong><?php echo htmlspecialchars($subscription_info['name']); ?></strong>) does not include access to coaches.
                                            <?php else: ?>
                                                You need an active subscription with coach access to view and book coaches.
                                            <?php endif; ?>
                                        </p>
                                        <p style="color: var(--text-secondary, #ccc); margin-bottom: 2rem;">
                                            Upgrade to <strong>Gold</strong> or <strong>Platinum</strong> membership to access our professional coaches.
                                        </p>
                                        <a href="../Membership Full/Membership.php" style="display: inline-block; padding: 12px 32px; background: linear-gradient(135deg, #a66fff, #5c4e9c); color: white; text-decoration: none; border-radius: 8px; font-weight: 600; transition: transform 0.2s;">
                                            View Membership Plans
                                        </a>
                                    </div>
                                <?php else: ?>
                                <!-- Coaches Grid -->
                                <div class="coaches-grid">
                                    <!-- Coach 1 - Strength & Conditioning -->
                                    <div class="coach-card" data-aos="fade-up">
                                        <div class="coach-image">
                                            <div
                                            class="avatar"
                                            style="background: linear-gradient(135deg, #a66fff, #5c4e9c)"
                                            >
                                            CO
                                        </div>
                                    </div>
                                    <div class="coach-info">
                                        <h3 class="coach-name">John Anderson</h3>
                                        <p class="coach-specialty">Strength & Conditioning</p>
                                        <p class="coach-description">
                Certified strength coach with 12 years of experience.
                Specializes in powerlifting, strongman training, and building
                explosive strength for all fitness levels.
              </p>
            </div>
            <div class="coach-actions">
              <button class="btn-book-schedule">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  height="20px"
                  viewBox="0 -960 960 960"
                  width="20px"
                  fill="currentColor"
                >
                  <path
                    d="M200-80q-33 0-56.5-23.5T120-160v-560q0-33 23.5-56.5T200-800h40v-40q0-17 11.5-28.5T280-880q17 0 28.5 11.5T320-840v40h320v-40q0-17 11.5-28.5T680-880q17 0 28.5 11.5T720-840v40h40q33 0 56.5 23.5T840-720v560q0 33-23.5 56.5T760-80H200Zm0-80h560v-360H200v360Zm0-440h560v-120H200v120Zm280 220q-17 0-28.5-11.5T440-420q0-17 11.5-28.5T480-460q17 0 28.5 11.5T520-420q0 17-11.5 28.5T480-380Zm-160 0q-17 0-28.5-11.5T280-420q0-17 11.5-28.5T320-460q17 0 28.5 11.5T360-420q0 17-11.5 28.5T320-380Zm320 0q-17 0-28.5-11.5T600-420q0-17 11.5-28.5T640-460q17 0 28.5 11.5T680-420q0 17-11.5 28.5T640-380Z"
                  />
                </svg>
                Pick Coach
              </button>
              <div class="booking-dropdown">
                <label>Select Date</label>
                <input type="date" class="booking-date" />
                <label>Select Time</label>
                <input type="time" class="booking-time" />
                <div class="booking-actions">
                  <button class="booking-confirm">Confirm</button>
                  <button class="booking-cancel">Cancel</button>
                </div>
              </div>
              <button class="btn-generated-workout">
                Generated Workout Plan
              </button>
            </div>
          </div>

          <!-- Coach 2 - Bodybuilding & Hypertrophy -->
          <div class="coach-card" data-aos="fade-up" data-aos-delay="100">
            <div class="coach-image">
              <div
                class="avatar"
                style="background: linear-gradient(135deg, #7c5dff, #4a528e)"
              >
                CO
              </div>
            </div>
            <div class="coach-info">
              <h3 class="coach-name">Marcus Wilson</h3>
              <p class="coach-specialty">Bodybuilding & Hypertrophy</p>
              <p class="coach-description">
                IFBB Pro coach dedicated to muscle development and aesthetic
                training. Creates personalized workout programs to maximize
                muscle growth and definition.
              </p>
            </div>
            <div class="coach-actions">
              <button class="btn-book-schedule">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  height="20px"
                  viewBox="0 -960 960 960"
                  width="20px"
                  fill="currentColor"
                >
                  <path
                    d="M200-80q-33 0-56.5-23.5T120-160v-560q0-33 23.5-56.5T200-800h40v-40q0-17 11.5-28.5T280-880q17 0 28.5 11.5T320-840v40h320v-40q0-17 11.5-28.5T680-880q17 0 28.5 11.5T720-840v40h40q33 0 56.5 23.5T840-720v560q0 33-23.5 56.5T760-80H200Zm0-80h560v-360H200v360Zm0-440h560v-120H200v120Zm280 220q-17 0-28.5-11.5T440-420q0-17 11.5-28.5T480-460q17 0 28.5 11.5T520-420q0 17-11.5 28.5T480-380Zm-160 0q-17 0-28.5-11.5T280-420q0-17 11.5-28.5T320-460q17 0 28.5 11.5T360-420q0 17-11.5 28.5T320-380Zm320 0q-17 0-28.5-11.5T600-420q0-17 11.5-28.5T640-460q17 0 28.5 11.5T680-420q0 17-11.5 28.5T640-380Z"
                  />
                </svg>
                Pick Coach
              </button>
              <div class="booking-dropdown">
                <label>Select Date</label>
                <input type="date" class="booking-date" />
                <label>Select Time</label>
                <input type="time" class="booking-time" />
                <div class="booking-actions">
                  <button class="booking-confirm">Confirm</button>
                  <button class="booking-cancel">Cancel</button>
                </div>
              </div>
              <button class="btn-generated-workout">
                Generated Workout Plan
              </button>
            </div>
          </div>

          <!-- Coach 3 - Cardio & Endurance -->
          <div class="coach-card" data-aos="fade-up" data-aos-delay="200">
            <div class="coach-image">
              <div
                class="avatar"
                style="background: linear-gradient(135deg, #c49aff, #917fe6)"
              >
                CO
              </div>
            </div>
            <div class="coach-info">
              <h3 class="coach-name">Lisa Chen</h3>
              <p class="coach-specialty">Cardio & Endurance</p>
              <p class="coach-description">
                Marathon runner and CrossFit Level 1 coach. Expert in
                high-intensity interval training, cardio conditioning, and
                building athletic endurance and stamina.
              </p>
            </div>
            <div class="coach-actions">
              <button class="btn-book-schedule">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  height="20px"
                  viewBox="0 -960 960 960"
                  width="20px"
                  fill="currentColor"
                >
                  <path
                    d="M200-80q-33 0-56.5-23.5T120-160v-560q0-33 23.5-56.5T200-800h40v-40q0-17 11.5-28.5T280-880q17 0 28.5 11.5T320-840v40h320v-40q0-17 11.5-28.5T680-880q17 0 28.5 11.5T720-840v40h40q33 0 56.5 23.5T840-720v560q0 33-23.5 56.5T760-80H200Zm0-80h560v-360H200v360Zm0-440h560v-120H200v120Zm280 220q-17 0-28.5-11.5T440-420q0-17 11.5-28.5T480-460q17 0 28.5 11.5T520-420q0 17-11.5 28.5T480-380Zm-160 0q-17 0-28.5-11.5T280-420q0-17 11.5-28.5T320-460q17 0 28.5 11.5T360-420q0 17-11.5 28.5T320-380Zm320 0q-17 0-28.5-11.5T600-420q0-17 11.5-28.5T640-460q17 0 28.5 11.5T680-420q0 17-11.5 28.5T640-380Z"
                  />
                </svg>
                Pick Coach
              </button>
              <div class="booking-dropdown">
                <label>Select Date</label>
                <input type="date" class="booking-date" />
                <label>Select Time</label>
                <input type="time" class="booking-time" />
                <div class="booking-actions">
                  <button class="booking-confirm">Confirm</button>
                  <button class="booking-cancel">Cancel</button>
                </div>
              </div>
              <button class="btn-generated-workout">
                Generated Workout Plan
              </button>
            </div>
          </div>

          <!-- Coach 4 - Functional Fitness -->
          <div class="coach-card" data-aos="fade-up" data-aos-delay="300">
            <div class="coach-image">
              <div
                class="avatar"
                style="background: linear-gradient(135deg, #9370db, #6d5aa6)"
              >
                CO
              </div>
            </div>
            <div class="coach-info">
              <h3 class="coach-name">David Thompson</h3>
              <p class="coach-specialty">Functional Fitness</p>
              <p class="coach-description">
                CrossFit certified coach with specialization in functional
                movement patterns. Designs programs for overall fitness,
                mobility, and real-world strength application.
              </p>
            </div>
            <div class="coach-actions">
              <button class="btn-book-schedule">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  height="20px"
                  viewBox="0 -960 960 960"
                  width="20px"
                  fill="currentColor"
                >
                  <path
                    d="M200-80q-33 0-56.5-23.5T120-160v-560q0-33 23.5-56.5T200-800h40v-40q0-17 11.5-28.5T280-880q17 0 28.5 11.5T320-840v40h320v-40q0-17 11.5-28.5T680-880q17 0 28.5 11.5T720-840v40h40q33 0 56.5 23.5T840-720v560q0 33-23.5 56.5T760-80H200Zm0-80h560v-360H200v360Zm0-440h560v-120H200v120Zm280 220q-17 0-28.5-11.5T440-420q0-17 11.5-28.5T480-460q17 0 28.5 11.5T520-420q0 17-11.5 28.5T480-380Zm-160 0q-17 0-28.5-11.5T280-420q0-17 11.5-28.5T320-460q17 0 28.5 11.5T360-420q0 17-11.5 28.5T320-380Zm320 0q-17 0-28.5-11.5T600-420q0-17 11.5-28.5T640-460q17 0 28.5 11.5T680-420q0 17-11.5 28.5T640-380Z"
                  />
                </svg>
                Pick Coach
              </button>
              <div class="booking-dropdown">
                <label>Select Date</label>
                <input type="date" class="booking-date" />
                <label>Select Time</label>
                <input type="time" class="booking-time" />
                <div class="booking-actions">
                  <button class="booking-confirm">Confirm</button>
                  <button class="booking-cancel">Cancel</button>
                </div>
              </div>
              <button class="btn-generated-workout">
                Generated Workout Plan
              </button>
            </div>
          </div>

          <!-- Coach 5 - Personal Training -->
          <div class="coach-card" data-aos="fade-up" data-aos-delay="400">
            <div class="coach-image">
              <div
                class="avatar"
                style="background: linear-gradient(135deg, #b8a0ff, #8d5bff)"
              >
                CO
              </div>
            </div>
            <div class="coach-info">
              <h3 class="coach-name">Emma Rodriguez</h3>
              <p class="coach-specialty">Personal Training</p>
              <p class="coach-description">
                Certified personal trainer specializing in one-on-one coaching
                and beginner transformations. Builds custom programs tailored to
                your goals and fitness level.
              </p>
            </div>
            <div class="coach-actions">
              <button class="btn-book-schedule">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  height="20px"
                  viewBox="0 -960 960 960"
                  width="20px"
                  fill="currentColor"
                >
                  <path
                    d="M200-80q-33 0-56.5-23.5T120-160v-560q0-33 23.5-56.5T200-800h40v-40q0-17 11.5-28.5T280-880q17 0 28.5 11.5T320-840v40h320v-40q0-17 11.5-28.5T680-880q17 0 28.5 11.5T720-840v40h40q33 0 56.5 23.5T840-720v560q0 33-23.5 56.5T760-80H200Zm0-80h560v-360H200v360Zm0-440h560v-120H200v120Zm280 220q-17 0-28.5-11.5T440-420q0-17 11.5-28.5T480-460q17 0 28.5 11.5T520-420q0 17-11.5 28.5T480-380Zm-160 0q-17 0-28.5-11.5T280-420q0-17 11.5-28.5T320-460q17 0 28.5 11.5T360-420q0 17-11.5 28.5T320-380Zm320 0q-17 0-28.5-11.5T600-420q0-17 11.5-28.5T640-460q17 0 28.5 11.5T680-420q0 17-11.5 28.5T640-380Z"
                  />
                </svg>
                Pick Coach
              </button>
              <div class="booking-dropdown">
                <label>Select Date</label>
                <input type="date" class="booking-date" />
                <label>Select Time</label>
                <input type="time" class="booking-time" />
                <div class="booking-actions">
                  <button class="booking-confirm">Confirm</button>
                  <button class="booking-cancel">Cancel</button>
                </div>
              </div>
              <button class="btn-generated-workout">
                Generated Workout Plan
              </button>
            </div>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </main>