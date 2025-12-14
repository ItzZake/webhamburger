<?php
    session_start();
    if (isset($_GET['logout'])) {
        session_unset();
        session_destroy();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
      }
    include "Nav.php";
?>
    <title>Contact Us | Power</title>

    <!-- Font Awesome -->
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    />

    <!-- Google Fonts -->
    <link
      href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
      rel="stylesheet"
    />

    <!-- CSS -->
    <link rel="stylesheet" href="contact.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a2d9d6a66a.js" crossorigin="anonymous"></script>
    <div class="orbs-background">
      <div class="blobs">
        <div class="blob-dodge"><div class="blob a"></div></div>
        <div class="blob-dodge"><div class="blob b"></div></div>
        <div class="blob-dodge"><div class="blob c"></div></div>
      </div>
    </div>
    <div class="container">
      <div class="contact-card">
        <div class="header">
          <h1>Contact Us</h1>
          <p>
            We'd love to hear from you. Send us a message and we'll respond as
            soon as possible.
          </p>
        </div>

        <div class="form-container">
          <form id="contactForm">
            <!-- Full Name Section -->
            <div class="form-group">
              <h2 class="required">Full Name</h2>
              <div class="input-group">
                <input
                  type="text"
                  id="fullName"
                  name="fullName"
                  placeholder="Enter your full name"
                  required
                />
              </div>
            </div>

            <!-- Email Section -->
            <div class="form-group">
              <h2 class="required">Email Address</h2>
              <div class="input-group">
                <input
                  type="email"
                  id="email"
                  name="email"
                  placeholder="Enter your email address"
                  required
                />
              </div>
            </div>

            <!-- Message Section -->
            <div class="form-group">
              <h2 class="required">Message</h2>
              <div class="input-group">
                <textarea
                  id="message"
                  name="message"
                  placeholder="Type your message here..."
                  required
                ></textarea>
              </div>
            </div>

            <!-- Privacy Policy Agreement -->
            <div class="checkbox-group">
              <input
                type="checkbox"
                id="privacyPolicy"
                name="privacyPolicy"
                required
              />
              <label for="privacyPolicy">I agree to the Privacy Policy.</label>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="submit-btn">Send Message</button>
          </form>
        </div>
      </div>
    </div>
    <script src="contact.js"></script>
    <?php include "Footer.php";?>