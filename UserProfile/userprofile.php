<?php
include("../DB.php");
session_start();

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");
    header("Location: ../Home Full/Home.php");
    exit;
}

// Assume user is logged in with session['user_id']
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header("Location: ../Login/Loginsignup.php");
    exit;
}

$membership = 'none'; // default
if ($user_id) {
    $stmt = $conn->prepare("SELECT mp.Name FROM MembershipSubscription ms JOIN MembershipPlan mp ON ms.Plan_ID = mp.Plan_ID WHERE ms.Member_Id = ? AND ms.Status = 1 ORDER BY ms.Created_at DESC LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $membership = strtolower($row['Name']);
    }
    $stmt->close();
}

$showWizard = false;
if ($user_id) {
    // Check if user has medical info
    $stmt = $conn->prepare("SELECT Member_Id FROM MedicalRecord WHERE Member_Id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 0) {
        $showWizard = true;
    }
    $stmt->close();
}
?>

<title>Gym Member Profile</title>

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Epunda+Slab:ital,wght@0,300..900;1,300..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

  <!-- Styles -->
  <link rel="stylesheet" href="userprofile.css" />




  <div id="medicalWizard" class="medical-wizard" style="display:<?php echo $showWizard ? 'block' : 'none'; ?>">
    <div class="wizard-modal">
      <h2>Quick Health Check</h2>
      <p class="wizard-subtitle">Please provide a few basic details so we can keep you safe.</p>

      <!-- Step 0: Age + Weight (first screen) -->
      <div data-wizard-step="ageweight">
        <label for="wizard-age">Age</label>
        <input id="wizard-age" type="number" min="0" step="1" placeholder="e.g. 28" autofocus>

        <label for="wizard-weight">Weight (kg)</label>
        <input id="wizard-weight" type="number" step="0.1" min="0" placeholder="e.g. 78.5">
        <label for="wizard-height">Height (cm)</label>
        <input id="wizard-height" type="number" step="0.1" min="0" placeholder="e.g. 170">

        <label for="wizard-experience">Experience Level</label>
        <select id="wizard-experience" required>
          <option value="">Select your experience level</option>
          <option value="Beginner">Beginner</option>
          <option value="Intermediate">Intermediate</option>
          <option value="Advanced">Advanced</option>
        </select>

        <div class="wizard-actions">
          <button type="button" onclick="nextWizardStep()" class="btn primary">Next</button>
        </div>
      </div>

      <!-- Step 1: Conditions checklist part 1 (5 conditions) -->
      <div data-wizard-step="conditions1">
        <label>Do you have any of the following conditions? Tick any that apply.</label>
        <div class="conditions-list">
          <label class="condition-item"><input type="checkbox" name="wizard-condition" id="cond_heart"> Heart disease / chest pain</label>
          <label class="condition-item"><input type="checkbox" name="wizard-condition" id="cond_diabetes"> Diabetes</label>
          <label class="condition-item"><input type="checkbox" name="wizard-condition" id="cond_asthma"> Asthma or breathing issues</label>
          <label class="condition-item"><input type="checkbox" name="wizard-condition" id="cond_thyroid"> Thyroid disorder</label>
          <label class="condition-item"><input type="checkbox" name="wizard-condition" id="cond_cholesterol"> High cholesterol</label>
        </div>

        <div class="wizard-actions">
          <button type="button" onclick="skipWizardField()" class="btn ghost">Skip</button>
          <button type="button" onclick="nextWizardStep()" class="btn primary">Next</button>
        </div>
      </div>

      <!-- Step 2: Conditions checklist part 2 (remaining + notes) -->
      <div data-wizard-step="conditions2">
        <label>Do you have any of the following conditions? Tick any that apply.</label>
        <div class="conditions-list">
          <label class="condition-item"><input type="checkbox" name="wizard-condition" id="cond_back"> Back injury</label>
          <label class="condition-item"><input type="checkbox" name="wizard-condition" id="cond_neck"> Neck injury</label>
          <label class="condition-item"><input type="checkbox" name="wizard-condition" id="cond_lactose"> Lactose intolerance</label>
          <label class="condition-item"><input type="checkbox" name="wizard-condition" id="cond_gluten"> Gluten intolerance</label>
          <label class="condition-item"><input type="checkbox" name="wizard-condition" id="cond_nut"> Nut allergy</label>
          <label class="condition-item"><input type="checkbox" name="wizard-condition" id="cond_egg"> Egg allergy</label>
          <label class="condition-item"><input type="checkbox" name="wizard-condition" id="cond_surgeries"> Recent surgery</label>
        </div>

        <label for="wizard-notes">Other important notes (optional)</label>
        <textarea id="wizard-notes" rows="3" placeholder="Other details we should know"></textarea>

        <div class="wizard-actions">
          <button type="button" onclick="skipWizardField()" class="btn ghost">Skip</button>
          <button type="button" onclick="nextWizardStep()" class="btn primary">Complete</button>
        </div>
      </div>
    </div>
  </div>
  <?php include("Nav.php"); ?>

  <!-- PROFILE CONTENT -->
  <main class="profile-wrap">

    <section class="profile-grid">
      <div class="left-col">
        <div class="card your-details">
          <h2>YOUR DETAILS</h2>
          <ul class="details-list">
            <li><strong>Gym code:</strong> <span id="gymCode">252113</span></li>
            <li><strong>Name:</strong> <span id="name">John Pork</span></li>
            <li><strong>Email:</strong> <span id="email">muaythaichampchanco@example.com</span></li>
            <li><strong>Phone:</strong> <span id="phone">+1 234 567 890</span></li>
            <li>
              <strong>Saved addresses:</strong>
              <ul id="addresses" class="addresses"></ul>
            </li>
          </ul>
        </div>

        <div class="card subscription">
          <h2>SUBSCRIPTION DETAILS</h2>
          <div class="sub-row">
            <div class="sub-type">
              <div class="badge <?php echo $membership; ?>" aria-hidden="true"><span class="type-text <?php echo $membership; ?>" id="subType"><?php echo strtoupper($membership); ?></span></div>
              <div class="radial-label"><?php echo ucfirst($membership); ?> Membership</div>
            </div>

            <div class="time-left">
              <div class="time-card">
                <div class="time-ring" id="timeRing" role="img" aria-label="Time left progress">
                  <div class="time-inner">
                    <div class="time-value" id="timeValue">--</div>
                    <div class="time-unit">days</div>
                  </div>
                </div>
              </div>
              <div class="time-meta" id="subDates">Subscribed: — to —</div>
            </div>
          </div>

          <div class="actions">
            <button id="printReceipt" class="btn">Print Receipt</button>
            <button id="toggleFreeze" class="btn ghost">Freeze</button>
          </div>
          <div id="frozenNotice" class="frozen-notice hidden">Subscription is frozen</div>
        </div>
      </div>

      <!-- Freeze Subscription Modal -->
      <div id="freezeModal" class="freeze-modal hidden">
        <div class="freeze-modal-content">
          <div class="freeze-modal-header">
            <h2>Freeze Subscription</h2>
            <button id="closeFreezeModal" class="freeze-modal-close">&times;</button>
          </div>
          <form id="freezeForm" class="freeze-form">
            <label for="freezeStartDate">Start Date</label>
            <input type="date" id="freezeStartDate" name="start_date" required>
            
            <label for="freezeEndDate">End Date</label>
            <input type="date" id="freezeEndDate" name="end_date" required>
            
            <label for="freezeReason">Reason</label>
            <textarea id="freezeReason" name="reason" rows="4" placeholder="Please provide a reason for freezing your subscription..." required></textarea>
            
            <div class="freeze-form-actions">
              <button type="submit" class="btn primary">Confirm Freeze</button>
              <button type="button" id="cancelFreeze" class="btn ghost">Cancel</button>
            </div>
          </form>
        </div>
      </div>
        </div>
      </div>

      <aside class="right-col">
        <div class="card profile-box">
          <label class="picture-area" for="avatarInput">
            <input id="avatarInput" type="file" accept="image/*" hidden>
            <img id="avatar" src="https://via.placeholder.com/160x160.png?text=Avatar" alt="Profile photo">
            <button id="uploadBtn" class="small ghost" type="button">Change</button>
          </label>

          <div class="qr-wrap">
            <img id="qrImg" src="Image/qr-code.png" alt="Profile QR code" />
          </div>

          <!-- NEW: Medical profile quick button -->
          <div class="medical-section">
            <button id="openMedical" class="small">Medical profile</button>
          </div>
        </div>

        <!-- Expanded medical panel (hidden by default) -->
        <div id="medicalPanel" class="card medical-panel hidden" aria-hidden="true">
          <div class="medical-header">
            <h3>Medical Profile</h3>
            <button id="closeMedical" class="small ghost">Close</button>
          </div>

          <form id="medicalForm" class="medical-form" novalidate>
            <label for="weight">Weight (kg)</label>
            <input id="weight" name="weight" type="number" step="0.1" placeholder="e.g. 78.5">

            <label for="height">Height (cm)</label>
            <input id="height" name="height" type="number" step="0.1" placeholder="e.g. 180">

              <label for="age">Age</label>
              <input id="age" name="age" type="number" min="0" step="1" placeholder="e.g. 28">

              <label>Conditions (tick any that apply)</label>
              <div class="conditions-list medical-inline">
                <label class="condition-item"><input type="checkbox" id="m_cond_heart"> Heart disease / chest pain</label>
                <input class="condition-note" id="m_note_heart" type="text" placeholder="Details (optional)" style="display:none">
                <label class="condition-item"><input type="checkbox" id="m_cond_diabetes"> Diabetes</label>
                <input class="condition-note" id="m_note_diabetes" type="text" placeholder="Details (optional)" style="display:none">
                <label class="condition-item"><input type="checkbox" id="m_cond_asthma"> Asthma</label>
                <input class="condition-note" id="m_note_asthma" type="text" placeholder="Details (optional)" style="display:none">
                <label class="condition-item"><input type="checkbox" id="m_cond_thyroid"> Thyroid disorder</label>
                <input class="condition-note" id="m_note_thyroid" type="text" placeholder="Details (optional)" style="display:none">
                <label class="condition-item"><input type="checkbox" id="m_cond_cholesterol"> High cholesterol</label>
                <input class="condition-note" id="m_note_cholesterol" type="text" placeholder="Details (optional)" style="display:none">
                <label class="condition-item"><input type="checkbox" id="m_cond_back"> Back injury</label>
                <input class="condition-note" id="m_note_back" type="text" placeholder="Details (optional)" style="display:none">
                <label class="condition-item"><input type="checkbox" id="m_cond_neck"> Neck injury</label>
                <input class="condition-note" id="m_note_neck" type="text" placeholder="Details (optional)" style="display:none">
                <label class="condition-item"><input type="checkbox" id="m_cond_lactose"> Lactose intolerance</label>
                <input class="condition-note" id="m_note_lactose" type="text" placeholder="Details (optional)" style="display:none">
                <label class="condition-item"><input type="checkbox" id="m_cond_gluten"> Gluten intolerance</label>
                <input class="condition-note" id="m_note_gluten" type="text" placeholder="Details (optional)" style="display:none">
                <label class="condition-item"><input type="checkbox" id="m_cond_nut"> Nut allergy</label>
                <input class="condition-note" id="m_note_nut" type="text" placeholder="Details (optional)" style="display:none">
                <label class="condition-item"><input type="checkbox" id="m_cond_egg"> Egg allergy</label>
                <input class="condition-note" id="m_note_egg" type="text" placeholder="Details (optional)" style="display:none">
                <label class="condition-item"><input type="checkbox" id="m_cond_surgeries"> Recent surgery</label>
                <input class="condition-note" id="m_note_surgeries" type="text" placeholder="Details (optional)" style="display:none">
              </div>
              

            <div class="medical-actions">
              <button type="button" id="saveMedical" class="btn primary">Save</button>
              <button type="button" id="cancelMedical" class="btn ghost">Cancel</button>
            </div>
          </form>
        </div>

      </aside>
    </section>
  </main>

  <!-- Scripts -->
  <script src="userprofile.js" defer></script>