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

    <title>About Us | Power</title>

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
    <link rel="stylesheet" href="Nav.css"/>
    <link rel="stylesheet" href="About us.css"/>
  </head>
  <body>
    <div class="orbs-background">
      <div class="blobs">
        <div class="blob-dodge"><div class="blob a"></div></div>
        <div class="blob-dodge"><div class="blob b"></div></div>
        <div class="blob-dodge"><div class="blob c"></div></div>
      </div>
    </div>
    

    <section class="about-hero">
      <div class="hero-content">
        <h2>About <span>Us</span></h2>
        <p>
          At <strong>Power Gym</strong>, we help you transform your body and
          mind. Track your health, improve your performance, and train with
          expert coaches who guide you every step of the way.
        </p>
        <a href="#mission">
          <button class="super-button" id="learnMoreBtn">
            <span>Learn More</span>
          </button>
        </a>
      </div>
      <div class="hero-image">
        <img src="media/download (1).jpg" alt="Gym workout" />
      </div>
    </section>

    <!-- MISSION -->
    <section class="mission" id="mission">
      <h3>Our Mission</h3>
      <p>
        Our goal is to create a smarter fitness experience — one that combines
        technology, motivation, and real coaching to help you achieve results
        that last.
      </p>
    </section>

    <!-- COACHES -->
    <section class="team">
      <h3>Meet Your Coaches</h3>
      <div class="team-cards">
        <div class="card">
          <img
            src="media/1.png"
            alt="Alex Johnson - Strength Trainer"
          />
          <div class="card-content">
            <h4>Alex Johnson</h4>
            <p>Certified Strength Trainer</p>
          </div>
        </div>
        <div class="card">
          <img
            src="media/2.png"
            alt="Sophia Lee - Nutrition Expert"
          />
          <div class="card-content">
            <h4>Sophia Lee</h4>
            <p>Nutrition & Health Expert</p>
          </div>
        </div>
        <div class="card">
          <img src="media/3.png" alt="Chris Evans - Fitness Coach" />
          <div class="card-content">
            <h4>Chris Evans</h4>
            <p>Personal Fitness Coach</p>
          </div>
        </div>
      </div>
    </section>

    <!-- DEVELOPERS -->
    <section class="developers">
      <h3>Meet the Developers</h3>
      <div class="developer-cards">
        <!-- Developer Flip Cards -->
        <div class="flip-card">
          <div class="flip-card-inner">
            <div class="flip-card-front">
              <img src="media/Adel.png" alt="Adel Ehab" />
              <div class="title">
                <h3>Adel Ehab</h3>
                <p>Frontend Developer</p>
              </div>
            </div>
            <div class="flip-card-back">
              <h3>Adel Ehab</h3>
              <p>
                Brings ideas to life through clean, responsive, and creative
                front-end design.
              </p>
              <div class="social-icons">
                <a href="https://github.com/eldola-coder"
                  ><i class="fab fa-github"></i
                ></a>
                <a href="https://www.instagram.com/__dola__.exe/"
                  ><i class="fab fa-instagram"></i
                ></a>
                <a href="https://www.linkedin.com/in/adel-ehab-327369358/"
                  ><i class="fab fa-linkedin"></i
                ></a>
              </div>
            </div>
          </div>
        </div>

        <div class="flip-card">
          <div class="flip-card-inner">
            <div class="flip-card-front">
              <img src="media/Maher.png" alt="Ahmed Maher" />
              <div class="title">
                <h3>Ahmed Maher</h3>
                <p>Backend Developer</p>
              </div>
            </div>
            <div class="flip-card-back">
              <h3>Ahmed Maher</h3>
              <p>
                Ensures everything runs smoothly behind the scenes with
                efficient backend systems.
              </p>
              <div class="social-icons">
                <a href="#"><i class="fab fa-github"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-linkedin"></i></a>
              </div>
            </div>
          </div>
        </div>

        <div class="flip-card">
          <div class="flip-card-inner">
            <div class="flip-card-front">
              <img src="media/Mostafa.png" alt="Mohamed Mostafa" />
              <div class="title">
                <h3>Mohamed Mostafa</h3>
                <p>Full Stack Developer</p>
              </div>
            </div>
            <div class="flip-card-back">
              <h3>Mohamed Mostafa</h3>
              <p>
                Builds seamless connections between front and back ends with
                precision and passion.
              </p>
              <div class="social-icons">
                <a href="#"><i class="fab fa-github"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-linkedin"></i></a>
              </div>
            </div>
          </div>
        </div>

        <div class="flip-card">
          <div class="flip-card-inner">
            <div class="flip-card-front">
              <img
                src="media/istockphoto-1142192548-612x612.jpg"
                alt="Mohamed Sami"
              />
              <div class="title">
                <h3>Mohamed Sami</h3>
                <p>UI/UX Designer</p>
              </div>
            </div>
            <div class="flip-card-back">
              <h3>Mohamed Sami</h3>
              <p>
                Designs intuitive and visually appealing interfaces that enhance
                user experience.
              </p>
              <div class="social-icons">
                <a href="#"><i class="fab fa-github"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-linkedin"></i></a>
              </div>
            </div>
          </div>
        </div>

        <div class="flip-card">
          <div class="flip-card-inner">
            <div class="flip-card-front">
              <img
                src="media/istockphoto-1142192548-612x612.jpg"
                alt="Adham Ahmed"
              />
              <div class="title">
                <h3>Adham Ahmed</h3>
                <p>Database Manager</p>
              </div>
            </div>
            <div class="flip-card-back">
              <h3>Adham Ahmed</h3>
              <p>
                Organizes and optimizes data to keep the FitTrack experience
                running smoothly.
              </p>
              <div class="social-icons">
                <a href="#"><i class="fab fa-github"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-linkedin"></i></a>
              </div>
            </div>
          </div>
        </div>

        <div class="flip-card">
          <div class="flip-card-inner">
            <div class="flip-card-front">
              <img
                src="media/istockphoto-1142192548-612x612.jpg"
                alt="Ahmed Hassan"
              />
              <div class="title">
                <h3>Ahmed Hassan</h3>
                <p>Project Lead</p>
              </div>
            </div>
            <div class="flip-card-back">
              <h3>Ahmed Hassan</h3>
              <p>
                Leads the team with innovation and ensures FitTrack's vision
                stays on track.
              </p>
              <div class="social-icons">
                <a href="#"><i class="fab fa-github"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-linkedin"></i></a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <?php include "Footer.php"; ?>
    <!-- Fixed JavaScript references -->
    <script src="Nav.js"></script>
    <script src="About us.js"></script>