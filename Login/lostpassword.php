<?php
    include 'Nav.php';
?>

<title>Reset Password</title>
<link href="https://fonts.googleapis.com/css2?family=Epunda+Slab:ital,wght@0,300..900;1,300..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="lostpassword.css">
<link rel="stylesheet" href="loginsignup.css">

<!-- Full-screen decorative overlay (covers viewport) -->
  <div id="lp-full-overlay" aria-hidden="true"></div>

  <!-- Single centered card (overlay and blobs removed for clarity) -->
  <div class="container" id="container">
    <div class="form-container sign-in-container" style="position:relative;left:0;width:100%;">
      <form id="lpForm" class="lp-form" novalidate style="background:transparent;padding:40px;">
        <h1>Reset Password</h1>
        <span>Enter your email or gym code and set a new password</span>
        <input id="lp_identifier" name="identifier" type="text" placeholder="you@example.com or GYM123" autocomplete="username" required>
        <input id="lp_new" name="newpass" type="password" placeholder="New password" autocomplete="new-password" required>
        <input id="lp_confirm" name="confirmpass" type="password" placeholder="Confirm password" autocomplete="new-password" required>
        <div id="lpMessage" class="lp-message" role="status" aria-live="polite"></div>
        <button id="lpSubmit" type="submit">Reset password</button>
        <a href="loginsignup.php">Back to sign in</a>
      </form>
    </div>
  </div>
    <script src="loginsignup.js" defer></script>
    <script src="lostpassword.js" defer></script>