<?php
session_start();


include '../DB.php';

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

$user_id = $_SESSION['user_id'];

// First check if user has a coach assigned (any workout program, even pending)
$coachCheckStmt = $conn->prepare("SELECT Coach_ID, Status FROM workoutprogram 
                                   WHERE Member_Id = ? AND (Status = 'Active' OR Status = 'Pending') AND is_deleted = 0 
                                   LIMIT 1");
$coachCheckStmt->bind_param("i", $user_id);
$coachCheckStmt->execute();
$coachResult = $coachCheckStmt->get_result();
$coachAssignment = $coachResult->fetch_assoc();
$coachCheckStmt->close();

// If no coach assigned, redirect to coaches page
if (!$coachAssignment) {
    header("Location: ../Coaches/Coaches.php");
    exit;
}

$has_workout_plan = false;
$workout_program = null;
$workouts = [];
$program_status = $coachAssignment['Status'];

// Get the active workout program for the user
$stmt = $conn->prepare("SELECT * FROM workoutprogram WHERE Member_Id = ? AND (Status = 'Active' OR Status = 'Pending') AND is_deleted = 0 LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$workout_program = $result->fetch_assoc();
$stmt->close();

if ($workout_program && $workout_program['Status'] === 'Active') {
    $workout_id = $workout_program['Workout_ID'];
    
    // Get workout exercises ordered by day and sequence
    $stmt = $conn->prepare("SELECT * FROM WorkoutExercise WHERE Workout_ID = ? ORDER BY Day_Number, Sequence_Order");
    $stmt->bind_param("i", $workout_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $workout_exercises = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    // Only set has_workout_plan if there are actual exercises
    if (!empty($workout_exercises)) {
        $has_workout_plan = true;
        
        // Group exercises by day first
        $workouts_by_day = [];
        foreach ($workout_exercises as $we) {
            $day = $we['Day_Number'];
            if (!isset($workouts_by_day[$day])) {
                $workouts_by_day[$day] = [];
            }
            // Get exercise details
            $stmt = $conn->prepare("SELECT * FROM Exercise WHERE Exercise_ID = ?");
            $stmt->bind_param("i", $we['Exercise_ID']);
            $stmt->execute();
            $result = $stmt->get_result();
            $exercise = $result->fetch_assoc();
            $stmt->close();
            if ($exercise) {
                $exercise['sets'] = $we['Sets'];
                $exercise['reps'] = $we['Reps'];
                $exercise['rest_time'] = $we['Rest_Time'];
                $exercise['notes'] = $we['Notes'];
                $workouts_by_day[$day][] = $exercise;
            }
        }
        
        // Now group days into weeks (5 days per week)
        $workouts_by_week = [];
        $days_per_week = 5;
        $week_number = 1;
        $current_week_days = [];
        
        // Get max day number
        $max_day = !empty($workouts_by_day) ? max(array_keys($workouts_by_day)) : 0;
        $total_weeks = ceil($max_day / $days_per_week);
        
        for ($day = 1; $day <= $max_day; $day++) {
            $current_week_days[$day] = isset($workouts_by_day[$day]) ? $workouts_by_day[$day] : [];
            
            // When we reach 5 days or the last day, create a week
            if (count($current_week_days) >= $days_per_week || $day == $max_day) {
                $workouts_by_week[$week_number] = $current_week_days;
                $week_number++;
                $current_week_days = [];
            }
        }
        
        $workouts = $workouts_by_day; // Keep for backward compatibility
        $workouts_by_week_data = $workouts_by_week; // New week-based structure
    }
} else {
    // Status is 'Pending' - coach hasn't assigned workout plan yet
    $has_workout_plan = false;
}

// Initialize workouts_by_week_data if not set
if (!isset($workouts_by_week_data)) {
    $workouts_by_week_data = [];
}

// Get coach name for empty state message
$coachName = "Your Coach";
if ($coachAssignment) {
    $coachId = $coachAssignment['Coach_ID'];
    $coachStmt = $conn->prepare("SELECT u.First_Name, u.Last_Name FROM userprofile u WHERE u.User_ID = ?");
    $coachStmt->bind_param("i", $coachId);
    $coachStmt->execute();
    $coachResult = $coachStmt->get_result();
    if ($coachRow = $coachResult->fetch_assoc()) {
        $coachName = trim($coachRow['First_Name'] . ' ' . $coachRow['Last_Name']);
    }
    $coachStmt->close();
}

// Initialize workouts_by_week_data if not set
if (!isset($workouts_by_week_data)) {
    $workouts_by_week_data = [];
}

// $has_workout_plan and $workouts are now set correctly above

// Function to get primary muscle group for a day (if needed)
function get_day_muscle_group($day_exercises) {
    if (empty($day_exercises)) return 'REST';
    $muscles = array_unique(array_column($day_exercises, 'Target_Muscle_Group'));
    return implode(', ', $muscles);
}
?>

<link rel="stylesheet" href="style.css">
<script>
  // Initialize theme immediately to prevent FOUC
  (function() {
    const savedTheme = localStorage.getItem("theme") || "dark";
    document.documentElement.setAttribute("data-theme", savedTheme);
  })();
</script>
<style>
.empty-workout-state {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: radial-gradient(ellipse 100% 100% at center, var(--base-clr) 0%, var(--secondary-base-clr) 100%);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    color: var(--text-clr);
    text-align: center;
    padding: 2rem;
    transition: background 0.3s ease, color 0.3s ease;
}

.empty-workout-state h1 {
    font-size: 3rem;
    margin-bottom: 1rem;
	color: var(--accent-secondary);
}

.empty-workout-state p {
    font-size: 1.2rem;
    margin-bottom: 0.5rem;
    color: var(--text-secondary-clr);
    max-width: 600px;
}

.empty-workout-state .coach-name {
    color: var(--accent-secondary);
    font-weight: 600;
}

.empty-workout-state .spinner {
    width: 60px;
    height: 60px;
    border: 4px solid var(--border-color);
    border-top-color: var(--accent-secondary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 2rem 0;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.empty-workout-state .info-box {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 1.5rem;
    margin-top: 2rem;
    max-width: 500px;
    box-shadow: 0 4px 20px var(--shadow-color);
}

.empty-workout-state .info-box p {
    margin: 0.5rem 0;
    font-size: 1rem;
}
</style>
	<div id="sidebar-container"></div>

	<main class="content">
<?php if (!$has_workout_plan): ?>
		<div class="empty-workout-state">
			<div class="spinner"></div>
			<h1>Waiting for Your Workout Plan</h1>
			<p>You've successfully picked <span class="coach-name"><?php echo htmlspecialchars($coachName); ?></span> as your coach!</p>
			<p>Your coach is currently preparing a personalized workout plan just for you.</p>
			<div class="info-box">
				<p><strong>What happens next?</strong></p>
				<p>• Your coach will review your profile and goals</p>
				<p>• A customized workout plan will be created</p>
				<p>• You'll be notified when it's ready</p>
				<p>• Check back here to see your plan!</p>
			</div>
			<p style="margin-top: 2rem; font-size: 0.9rem; color: var(--text-secondary-clr);">
				This page will automatically update when your workout plan is ready.
			</p>
		</div>
<?php endif; ?>
		<!-- Week Selection View -->
		<div id="weeks-selection" style="<?php echo !$has_workout_plan ? 'display: none;' : ''; ?>">
			<div class="weeks-container">
				<h1 class="weeks-title">Training Program</h1>
				<p class="weeks-subtitle">Select a week to view your workouts</p>
				<div class="weeks-grid" id="weeks-grid">
					<!-- Weeks will be dynamically generated by JavaScript -->
				</div>
			</div>
		</div>

		<div id="image-track" style="display: none;">

			<div id="bg-images">
				<img class="image" src="media/chest.png" draggable="false" />
				<img class="image" src="media/back.png" draggable="false" />
				<img class="image" src="media/arms.png" draggable="false" />
				<img class="image" src="media/shoulders.png" draggable="false" />
				<img class="image" src="media/legs.png" draggable="false" />
			</div>

			<div id="workout-texts-main">
				<div id="day-texts-container">
					<div class="dw-tape-content">
<?php for ($i = 1; $i <= 5; $i++): ?>
						<h1>DAY <?php echo $i; ?></h1>
<?php endfor; ?>
					</div>
				</div>

				<div id="workouts-texts-container">
					<div class="dw-tape-content">
<?php for ($i = 1; $i <= 5; $i++): ?>
						<h2><?php echo isset($workouts[$i]) ? get_day_muscle_group($workouts[$i]) : 'REST'; ?></h2>
<?php endfor; ?>
					</div>
				</div>
			</div>

			<div id="index-text">
				<div class="tape-content">
<?php for ($i = 1; $i <= 5; $i++): ?>
					<h1><?php echo $i; ?></h1>
<?php endfor; ?>
				</div>
				<h1>—</h1>
				<h1>5</h1>
			</div>

			<div id="prevButton">
				<img class="cycleButton" src="icons/fakeCursor.png" draggable="false" />
			</div>
			<div id="nextButton">
				<img class="cycleButton" src="icons/fakeCursor.png" draggable="false" />
			</div>
		</div>

		<div id="workout-info" style="<?php echo !$has_workout_plan ? 'display: none;' : ''; ?>">

			<div class="progress-container">
				<div class="progress-bar" id="progressBar"></div>
			</div>
			<div id="exit-workout">
				<img id="exit-workout-img" src="icons/close.png" draggable="false">
			</div>
			<div id="take-me-up-yabo-3amo">
				<img id="take-me-up-yabo-3amo-img" src="icons/arrow.png" draggable="false">
			</div>

			<div id="workout-info-track">
<?php for ($day = 1; $day <= 5; $day++): ?>
				<div class="exercises" data-day="<?php echo $day; ?>">
<?php if (isset($workouts[$day]) && !empty($workouts[$day])): ?>
<?php foreach ($workouts[$day] as $index => $exercise): ?>
					<div class="card <?php echo $index == 0 ? 'first-card' : ''; ?>">

						<div class="left">
							<img class="workout-visual" alt="Not Found" onerror="this.src='icons/no-visual.png';" src="workout-visuals/<?php echo strtolower(str_replace(' ', '-', $exercise['Name'])); ?>.gif">
						</div>

						<div class="right">

							<div class="sets-reps">
								<p><?php echo $exercise['sets']; ?>x<?php echo $exercise['reps']; ?></p>
							</div>

							<div class="workout-name">
								<p><?php echo strtoupper($exercise['Name']); ?></p>
							</div>

							<div class="target-muscles">
								<p>Primary: <?php echo $exercise['Target_Muscle_Group']; ?></p>
								<p>Secondary: <?php echo $exercise['Secondary_Muscles']; ?>.</p>
							</div>

							<div class="tips-hints">
								<div>
									<p><b>SETUP</b></p>
									<p><?php echo $exercise['Instuctions']; ?></p>
									<p><b>EXECUTION</b></p>
									<p></p>
									<p><b>STRENGTH/SAFETY</b></p>
									<p><?php echo $exercise['notes']; ?></p>
								</div>
							</div>

						</div>

					</div>
<?php endforeach; ?>
<?php else: ?>
					<div class="card first-card">
						<div class="left">
							<!-- Empty for rest day -->
						</div>
						<div class="right">
							<div class="workout-name">
								<p>REST DAY</p>
							</div>
						</div>
					</div>
<?php endif; ?>
				</div>
<?php endfor; ?>
			</div>
		</div>

	</main>

	<script>
		// Pass workout data to JavaScript
		window.workoutData = {
			workoutsByDay: <?php echo json_encode($workouts ?? []); ?>,
			workoutsByWeek: <?php echo json_encode($workouts_by_week_data ?? []); ?>,
			hasWorkoutPlan: <?php echo $has_workout_plan ? 'true' : 'false'; ?>,
			workoutPlanId: <?php echo isset($workout_program['Workout_ID']) ? $workout_program['Workout_ID'] : 'null'; ?>
		};
	</script>
	<script src="../assets/js/toast.js"></script>
	<script src="dashboard_loader.js"></script>
	<script src="dashboard.js"></script>
	<script src="https://unpkg.com/lenis@1.3.15/dist/lenis.min.js"></script>
	<script src="weeks.js"></script>
	<script src="script.js"></script>
