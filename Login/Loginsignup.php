<?php
    include 'Nav.php';
    include '../DB.php';
    session_start();

    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['signup'])) {
            // Signup validation
            $first_name = trim($_POST['first_name'] ?? '');
            $last_name = trim($_POST['last_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            // Server-side validation
            if (empty($first_name) || strlen($first_name) < 2) {
                $errors['first_name'] = 'First name must be at least 2 characters.';
            }
            if (empty($last_name) || strlen($last_name) < 2) {
                $errors['last_name'] = 'Last name must be at least 2 characters.';
            }
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Please enter a valid email address.';
            }
            if (empty($password) || strlen($password) < 6) {
                $errors['password'] = 'Password must be at least 6 characters.';
            }

            // Check if email already exists
            if (empty($errors['email'])) {
                $stmt = $conn->prepare("SELECT User_ID FROM UserProfile WHERE Email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $errors['email'] = 'Email already registered.';
                }
                $stmt->close();
            }

            if (empty($errors)) {
                // Insert new user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $role = 'Member'; // Default role
                $stmt = $conn->prepare("INSERT INTO UserProfile (Email, Password, Last_Login, First_Name, Last_Name, Phone_Number, DOB, Role, Gender, Is_Active, Profile_pic_url, Created_at, Updated_at) VALUES (?, ?, NOW(), ?, ?, '', '2000-01-01', ?, 'Male', 1, '', NOW(), NOW())");
                $stmt->bind_param("sssss", $email, $hashed_password, $first_name, $last_name, $role);
                if ($stmt->execute()) {
                    // Get the new user ID
                    $new_user_id = $conn->insert_id;
                    
                    // Start session
                    $_SESSION['user_id'] = $new_user_id;
                    $_SESSION['logged_in'] = true;
                    $_SESSION['role'] = $role;
                    // New users don't have a subscription yet — send them to membership purchase
                    header("Location: ../Membership Full/Membership.php");
                    exit;
                } else {
                    $errors['general'] = 'Registration failed. Please try again.';
                }
                $stmt->close();
            }
        } elseif (isset($_POST['signin'])) {
            // Login validation
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Please enter a valid email address.';
            }
            if (empty($password)) {
                $errors['password'] = 'Please enter your password.';
            }

            if (empty($errors)) {
                // Check credentials
                $stmt = $conn->prepare("SELECT User_ID, Password, Role FROM UserProfile WHERE Email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                  $stored = $row['Password'];
                  $credentials_ok = false;
                  // Prefer secure password verification
                  if (!empty($stored) && password_verify($password, $stored)) {
                    $credentials_ok = true;
                  } else {
                    // Fallback: some users may have plaintext passwords in DB (legacy).
                    if ($password === $stored) {
                      $credentials_ok = true;
                      // Re-hash password and update DB with secure hash
                      $newhash = password_hash($password, PASSWORD_DEFAULT);
                      $rehashStmt = $conn->prepare("UPDATE UserProfile SET Password = ? WHERE User_ID = ?");
                      if ($rehashStmt) {
                        $rehashStmt->bind_param("si", $newhash, $row['User_ID']);
                        $rehashStmt->execute();
                        $rehashStmt->close();
                      }
                    }
                  }

                  if ($credentials_ok) {
                    // Login successful
                    $_SESSION['user_id'] = $row['User_ID'];
                    $_SESSION['logged_in'] = true;
                    $roleNormalized = strtolower(trim((string)($row['Role'] ?? '')));
                    $_SESSION['role'] = $roleNormalized;
                        // Update last login
                        $stmt2 = $conn->prepare("UPDATE UserProfile SET Last_Login = NOW() WHERE User_ID = ?");
                        $stmt2->bind_param("i", $row['User_ID']);
                        $stmt2->execute();
                        $stmt2->close();
                        // Redirect based on role. For members, check active subscription.
                        if ($row['Role'] === 'Admin') {
                          header("Location: ../Admin/admin.php");
                        } elseif ($row['Role'] === 'Member') {
                          // Check for active subscription
                          $subStmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM MembershipSubscription WHERE Member_Id = ? AND Status = 1");
                          if ($subStmt) {
                            $subStmt->bind_param("i", $row['User_ID']);
                            $subStmt->execute();
                            $res = $subStmt->get_result();
                            $hasSub = false;
                            if ($r = $res->fetch_assoc()) {
                              $hasSub = intval($r['cnt']) > 0;
                            }
                            $subStmt->close();
                          }
                          if (!empty($hasSub)) {
                            header("Location: ../UserProfile/userprofile.php");
                          } else {
                            header("Location: ../Membership Full/Membership.php");
                          }
                        } else {
                          header("Location: ../Home Full/Home.php");
                        }
                        exit;
                    } else {
                      error_log('Login failed for email: ' . $email . ' (password mismatch)');
                      $errors['general'] = 'Invalid email or password.';
                    }
                } else {
                    error_log('Login failed: no user found for email: ' . $email);
                    $errors['general'] = 'Invalid email or password.';
                }
                $stmt->close();
            }
        }
    }
?>

<title>Sign in/up Form</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css?family=Montserrat:400,800" rel="stylesheet">
  <link rel="stylesheet" href="loginsignup.css">
  <script src="loginsignup.js" defer></script>

<div class="blobs">
    <div class="blob-dodge"><div class="blob"></div></div>
    <div class="blob-dodge"><div class="blob"></div></div>
    <div class="blob-dodge"><div class="blob"></div></div>
  </div>

  <div class="container" id="container">
    <div class="form-container sign-up-container">
      <form method="post" action="">
        <h1>Create Account</h1>
        <div class="social-container">
          <a href="#" class="social"><i class="fab fa-facebook-f"></i></a>
          <a href="#" class="social"><i class="fab fa-google-plus-g"></i></a>
          <a href="#" class="social"><i class="fab fa-linkedin-in"></i></a>
        </div>
        <span>or use your email for registration</span>
        <?php if (isset($errors['first_name'])) echo "<p class='error'>{$errors['first_name']}</p>"; ?>
        <input type="text" name="first_name" placeholder="First Name" value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" />
        <?php if (isset($errors['last_name'])) echo "<p class='error'>{$errors['last_name']}</p>"; ?>
        <input type="text" name="last_name" placeholder="Last Name" value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" />
        <?php if (isset($errors['email'])) echo "<p class='error'>{$errors['email']}</p>"; ?>
        <input type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" />
        <?php if (isset($errors['password'])) echo "<p class='error'>{$errors['password']}</p>"; ?>
        <input type="password" name="password" placeholder="Password" />
        <?php if (isset($errors['general'])) echo "<p class='error'>{$errors['general']}</p>"; ?>
        <button type="submit" name="signup">Sign Up</button>
      </form>
    </div>

    <div class="form-container sign-in-container">
      <form method="post" action="">
        <h1>Sign in</h1>
        <div class="social-container">
          <a href="#" class="social"><i class="fab fa-facebook-f"></i></a>
          <a href="#" class="social"><i class="fab fa-google-plus-g"></i></a>
          <a href="#" class="social"><i class="fab fa-linkedin-in"></i></a>
        </div>
        <span>or use your account</span>
        <?php if (isset($errors['email'])) echo "<p class='error'>{$errors['email']}</p>"; ?>
        <input type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" />
        <?php if (isset($errors['password'])) echo "<p class='error'>{$errors['password']}</p>"; ?>
        <input type="password" name="password" placeholder="Password" />
        <a href="lostpassword.php">Forgot your password?</a>
        <?php if (isset($errors['general'])) echo "<p class='error'>{$errors['general']}</p>"; ?>
        <button type="submit" name="signin">Sign In</button>
      </form>
    </div>

    <div class="overlay-container">
      <div class="overlay">
        <div class="overlay-panel overlay-left">
          <h1>Welcome Back!</h1>
          <p>To keep connected with us please login with your personal info</p>
          <button class="ghost" id="signIn">Sign In</button>
        </div>
        <div class="overlay-panel overlay-right">
          <h1>Hello, Friend!</h1>
          <p>Enter your personal details and start your journey with us</p>
          <button class="ghost" id="signUp">Sign Up</button>
        </div>
      </div>
    </div>
  </div>

  <script src="loginsignup.js"></script>