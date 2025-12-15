
   <?php
    include '../DB.php';
    session_start();
    if (isset($_GET['logout'])) {
        session_unset();
        session_destroy();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
    ?>
    <link rel="stylesheet" href="Home.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="Home.js" defer></script>
    <script src="HomeAnimations.js" defer></script>
    <script src="https://kit.fontawesome.com/a2d9d6a66a.js" crossorigin="anonymous"></script>
    <button id="open-sidebar-button" onclick="OpenSideBar()">
        <svg xmlns="http://www.w3.org/2000/svg" height="40px" viewBox="0 -960 960 960" width="40px" fill="#c9c9c9"><path d="M165.13-254.62q-10.68 0-17.9-7.26-7.23-7.26-7.23-18t7.23-17.86q7.22-7.13 17.9-7.13h629.74q10.68 0 17.9 7.26 7.23 7.26 7.23 18t-7.23 17.87q-7.22 7.12-17.9 7.12H165.13Zm0-200.25q-10.68 0-17.9-7.27-7.23-7.26-7.23-17.99 0-10.74 7.23-17.87 7.22-7.13 17.9-7.13h629.74q10.68 0 17.9 7.27 7.23 7.26 7.23 17.99 0 10.74-7.23 17.87-7.22 7.13-17.9 7.13H165.13Zm0-200.26q-10.68 0-17.9-7.26-7.23-7.26-7.23-18t7.23-17.87q7.22-7.12 17.9-7.12h629.74q10.68 0 17.9 7.26 7.23 7.26 7.23 18t-7.23 17.86q-7.22 7.13-17.9 7.13H165.13Z"/></svg>
    </button>
    <nav id="navbar">
        <ul>
            <li><button id="close-sidebar-button" onclick="CloseSideBar()"><svg xmlns="http://www.w3.org/2000/svg" height="40px" viewBox="0 -960 960 960" width="40px" fill="#c9c9c9"><path d="m480-444.62-209.69 209.7q-7.23 7.23-17.5 7.42-10.27.19-17.89-7.42-7.61-7.62-7.61-17.7 0-10.07 7.61-17.69L444.62-480l-209.7-209.69q-7.23-7.23-7.42-17.5-.19-10.27 7.42-17.89 7.62-7.61 17.7-7.61 10.07 0 17.69 7.61L480-515.38l209.69-209.7q7.23-7.23 17.5-7.42 10.27-.19 17.89 7.42 7.61 7.62 7.61 17.7 0 10.07-7.61 17.69L515.38-480l209.7 209.69q7.23 7.23 7.42 17.5.19 10.27-7.42 17.89-7.62 7.61-17.7 7.61-10.07 0-17.69-7.61L480-444.62Z"/></svg></button></li>
            <li class="Home-li"><a href="../Home Full/Home.php"><img id="logo" src="Image/dark-logo-no-text.png" alt=""></a> </li>
            <li><a href="Store.php">Store</a></li>
            <li><a href="../About us/Aboutus.php">About</a></li>
            <li><a href="../FAQ/FAQ.php">FAQ</a></li>
            <li><a href="../Contact us/contact.php">Contact Us</a></li>
           <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
            <!-- USER IS LOGGED IN -->
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <li><a href="adminprofile.php">Admin Panel</a></li>
            <?php endif; ?>

            <?php if ($_SESSION['role'] === 'member'): ?>
                <li><a href="memberprofile.php">My Profile</a></li>
            <?php endif; ?>

            <?php if ($_SESSION['role'] === 'coach'): ?>
                <li><a href="coachprofile.php">Coach Dashboard</a></li>
            <?php endif; ?>

            <?php if ($_SESSION['role'] === 'nutritionist'): ?>
                <li><a href="nutritionistprofile.php">Nutritionist Dashboard</a></li>
            <?php endif; ?>

            <li><a href="Home.php?logout=1">Logout</a></li>

        <?php else: ?>

            <!-- USER IS NOT LOGGED IN -->
            <li><a href="Login.php">Login</a></li>

        <?php endif; ?>
            <li>
                <label class="switch">
                    <input type="checkbox" id="switch" />
                    <span class="slider"></span>
                </label>
            </li>
            <li class="Cart"><a href="Cart"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M280-80q-33 0-56.5-23.5T200-160q0-33 23.5-56.5T280-240q33 0 56.5 23.5T360-160q0 33-23.5 56.5T280-80Zm400 0q-33 0-56.5-23.5T600-160q0-33 23.5-56.5T680-240q33 0 56.5 23.5T760-160q0 33-23.5 56.5T680-80ZM246-720l96 200h280l110-200H246Zm-38-80h590q23 0 35 20.5t1 41.5L692-482q-11 20-29.5 31T622-440H324l-44 80h480v80H280q-45 0-68-39.5t-2-78.5l54-98-144-304H40v-80h130l38 80Zm134 280h280-280Z"/></svg></a></li>
        </ul>
    </nav>
    <div id="overlay" onclick="CloseSideBar()"></div>
    <section>
            <div class="orbs-background">
                <div class="blobs">
                    <div class="blob-dodge"><div class="blob a"></div></div>
                    <div class="blob-dodge"><div class="blob b"></div></div>
                    <div class="blob-dodge"><div class="blob c"></div></div>
                </div>
            </div>
            <div class="container">
                <div class="Welcome-content">
                    <div class="text">
                     
                     <h1>
                            <div class="fade-right dur-1500 offset-300">
                             <span id="Heading"> Where better <br><span> training feels natural</span></span> <br>
                            </div>
                            <div class="Buttons">
                                    <a href="About.Html">
                                            <button class="super-button">
                                                <span>Learn More</span>
                                            </button>
                                    </a>
                                    <a href="Store.Html">
                                        
                                        <button class="super-button">
                                            <span>Visit our Store</span>
                                        </button>
                                    </a>
                            </div>
                    
                        </h1>
                        <div id="Welcome-Desc" class="fade-down dur-3000">
                            <p>Step into a fitness space that focuses on real progress, at your own pace, with guidance designed to help you grow every step of the way.</p>
                        </div>
                 
                    </div>
                <div class="Bottom">
                    <div class="fade-up dur-1000">
                        <h1 id="GYM Name">
                            <img id="logo" src="Image/logo-without-text.png" alt="">
                            <span>
                                 POWER
                            </span>
                        </h1>
                    </div>
                </div>  
                </div>
            </div>
            <div class="container">
                <div class="About-Content">
                    <div class="Content">
                        <div class="fade-up dur-1000">
                            <h1> About Us</h1>
                        </div>
                        <section>
                            <div class="Text">
                                <div class="zoom-in dur-1000 delay-100">
                                    <p> From our humble beginnings, Power Gym set out to redefine what a training space could be.
                                        Through dedication and continuous growth, we’ve transformed into a trusted fitness destination.
                                        Today, we stand as a space built for progress, confidence, and long-lasting results.
                                    </p>
                                </div>
                                <a href="About.Html">
                                    <button class="super-button">
                                        <span>Learn More</span>
                                    </button>
                                </a>

                            </div>
                            <div class="Image fade-left dur-1500">
                                <img src="Image/download.jpeg" alt="">        
                            </div>
                        </section>
                    </div>
                </div>
            </div>
    </section>
    <section class="orb-section">
        <div class="Membership-Content">
            <div class="orbs-background">
                <div class="blobs">
                    <div class="blob-dodge"><div class="blob a"></div></div>
                    <div class="blob-dodge"><div class="blob b"></div></div>
                    <div class="blob-dodge"><div class="blob c"></div></div>
                </div>
            </div>
            <div class="container">
                <div class="Membership">
                    <h1>
                        Be Sure To Join Us In Our Mission.
                    </h1>
                    <h1>
                        We Have Multiple Subscription Plans.
                    </h1>
                    <h1>
                        Become A Better Version Of Yourself.
                    </h1>
                    
                </div>
                <div class="scroll-shift cards-wrapper">

                    <!-- LEFT CARD -->
                    <div class="small-card">
                        <div class="nft">
    <div class='main'>
      <img class='tokenImage' src="Image/Silver membership.png" alt="NFT" />
      <h2>Silver</h2>
      <p class='description'>Start off light with our standard subscription.</p>
      <div class='tokenInfo'>
        <div class="price">
          <p>350 L.E<p>
        </div>
        <div class="duration">
          <ins>◷</ins>
          <p>1 Month</p>
        </div>
      </div>
      <hr/>
    </div>
  </div>
                    </div>

                    <!-- CENTER CARD (highlight) -->
  <div class="nft">
    <div class='main'>
      <img class='tokenImage' src="Image/diamond membership.png" alt="NFT" />
      <h2>Platinum</h2>
      <p class='description'>Enjoy all the perks with our expert coaches and your own nutritionist.</p>
      <div class='tokenInfo'>
        <div class="price">
          <p>1000 L.E<p>
        </div>
        <div class="duration">
          <ins>◷</ins>
          <p>1 Month</p>
        </div>
      </div>
      <hr/>
        <a href="../Membership Full/Membership.php">
            <button class="super-button">
                 <span>View our Options</span>
            </button>
        </a>
    </div>
  </div>

                    <!-- RIGHT CARD -->
                    <div class="small-card">
                        <div class="nft">
    <div class='main'>
      <img class='tokenImage' src="Image/gold membership.png" alt="NFT" />
      <h2>Gold</h2>
      <p class='description'>Enjoy private sessions with our well-esteemed coaches.</p>
      <div class='tokenInfo'>
          <div class="price">
              <p>600 L.E<p>
                  </div>
                  <div class="duration">
                      <ins>◷</ins>
                      <p>1 Month</p>
                    </div>
                </div>
                <hr/>
            </div>
        </div>
    </div>
</div>
<div class="Contact-Content">
    <h1> Get in Touch </h1>
    <a href="Contact.Html">
        <button class="super-button">
            <span>Send Us a Message</span>
        </button>
    </a>
</div>
</div>
</div>
</section>
<?php include "../ChatBot Full/ChatBot.Php"; ?>
<footer class="footer">
    <div class="footer-container">
        
        <!-- COLUMN 1 — QUICK LINKS -->
        <div class="footer-col">
            <h3>Quick Links</h3>
            <ul>
                <li><a href="../Home Full/Home.php">Home</a></li>
                <li><a href="Store.html">Store</a></li>
                <li><a href="../About us/Aboutus.php">About Us</a></li>
                <li><a href="FAQ.html">FAQ</a></li>
                <li><a href="../Contact us/contact.php">Contact Us</a></li>
            </ul>
        </div>

        <!-- COLUMN 2 — SOCIAL LINKS -->
        <div class="footer-col">
            <h3>Follow Us</h3>
            <div class="socials">
                <a href="#"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path d="M576 320C576 178.6 461.4 64 320 64C178.6 64 64 178.6 64 320C64 440 146.7 540.8 258.2 568.5L258.2 398.2L205.4 398.2L205.4 320L258.2 320L258.2 286.3C258.2 199.2 297.6 158.8 383.2 158.8C399.4 158.8 427.4 162 438.9 165.2L438.9 236C432.9 235.4 422.4 235 409.3 235C367.3 235 351.1 250.9 351.1 292.2L351.1 320L434.7 320L420.3 398.2L351 398.2L351 574.1C477.8 558.8 576 450.9 576 320z"/></svg></a>
                <a href="#"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path d="M320.3 205C256.8 204.8 205.2 256.2 205 319.7C204.8 383.2 256.2 434.8 319.7 435C383.2 435.2 434.8 383.8 435 320.3C435.2 256.8 383.8 205.2 320.3 205zM319.7 245.4C360.9 245.2 394.4 278.5 394.6 319.7C394.8 360.9 361.5 394.4 320.3 394.6C279.1 394.8 245.6 361.5 245.4 320.3C245.2 279.1 278.5 245.6 319.7 245.4zM413.1 200.3C413.1 185.5 425.1 173.5 439.9 173.5C454.7 173.5 466.7 185.5 466.7 200.3C466.7 215.1 454.7 227.1 439.9 227.1C425.1 227.1 413.1 215.1 413.1 200.3zM542.8 227.5C541.1 191.6 532.9 159.8 506.6 133.6C480.4 107.4 448.6 99.2 412.7 97.4C375.7 95.3 264.8 95.3 227.8 97.4C192 99.1 160.2 107.3 133.9 133.5C107.6 159.7 99.5 191.5 97.7 227.4C95.6 264.4 95.6 375.3 97.7 412.3C99.4 448.2 107.6 480 133.9 506.2C160.2 532.4 191.9 540.6 227.8 542.4C264.8 544.5 375.7 544.5 412.7 542.4C448.6 540.7 480.4 532.5 506.6 506.2C532.8 480 541 448.2 542.8 412.3C544.9 375.3 544.9 264.5 542.8 227.5zM495 452C487.2 471.6 472.1 486.7 452.4 494.6C422.9 506.3 352.9 503.6 320.3 503.6C287.7 503.6 217.6 506.2 188.2 494.6C168.6 486.8 153.5 471.7 145.6 452C133.9 422.5 136.6 352.5 136.6 319.9C136.6 287.3 134 217.2 145.6 187.8C153.4 168.2 168.5 153.1 188.2 145.2C217.7 133.5 287.7 136.2 320.3 136.2C352.9 136.2 423 133.6 452.4 145.2C472 153 487.1 168.1 495 187.8C506.7 217.3 504 287.3 504 319.9C504 352.5 506.7 422.6 495 452z"/></svg></a>
                <a href="#"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path d="M453.2 112L523.8 112L369.6 288.2L551 528L409 528L297.7 382.6L170.5 528L99.8 528L264.7 339.5L90.8 112L236.4 112L336.9 244.9L453.2 112zM428.4 485.8L467.5 485.8L215.1 152L173.1 152L428.4 485.8z"/></svg></a>
                <a href="#"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path d="M544.5 273.9C500.5 274 457.5 260.3 421.7 234.7L421.7 413.4C421.7 446.5 411.6 478.8 392.7 506C373.8 533.2 347.1 554 316.1 565.6C285.1 577.2 251.3 579.1 219.2 570.9C187.1 562.7 158.3 545 136.5 520.1C114.7 495.2 101.2 464.1 97.5 431.2C93.8 398.3 100.4 365.1 116.1 336C131.8 306.9 156.1 283.3 185.7 268.3C215.3 253.3 248.6 247.8 281.4 252.3L281.4 342.2C266.4 337.5 250.3 337.6 235.4 342.6C220.5 347.6 207.5 357.2 198.4 369.9C189.3 382.6 184.4 398 184.5 413.8C184.6 429.6 189.7 444.8 199 457.5C208.3 470.2 221.4 479.6 236.4 484.4C251.4 489.2 267.5 489.2 282.4 484.3C297.3 479.4 310.4 469.9 319.6 457.2C328.8 444.5 333.8 429.1 333.8 413.4L333.8 64L421.8 64C421.7 71.4 422.4 78.9 423.7 86.2C426.8 102.5 433.1 118.1 442.4 131.9C451.7 145.7 463.7 157.5 477.6 166.5C497.5 179.6 520.8 186.6 544.6 186.6L544.6 274z"/></svg></a>
                <a href="#"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path d="M581.7 188.1C575.5 164.4 556.9 145.8 533.4 139.5C490.9 128 320.1 128 320.1 128C320.1 128 149.3 128 106.7 139.5C83.2 145.8 64.7 164.4 58.4 188.1C47 231 47 320.4 47 320.4C47 320.4 47 409.8 58.4 452.7C64.7 476.3 83.2 494.2 106.7 500.5C149.3 512 320.1 512 320.1 512C320.1 512 490.9 512 533.5 500.5C557 494.2 575.5 476.3 581.8 452.7C593.2 409.8 593.2 320.4 593.2 320.4C593.2 320.4 593.2 231 581.8 188.1zM264.2 401.6L264.2 239.2L406.9 320.4L264.2 401.6z"/></svg></a>
            </div>
        </div>

        <!-- COLUMN 3 — LOCATIONS -->
        <div class="footer-col">
            <h3>Our Locations</h3>
            <ul>
                <li>Cairo — Nasr City</li>
                <li>Alexandria — Smouha</li>
                <li>Giza — Dokki</li>
                <li>New Capital — District 4</li>
            </ul>
        </div>

        <!-- COLUMN 4 — ACCOUNT -->
        <div class="footer-col">
            <h3>Account</h3>
            <ul>
               <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                <!-- USER IS LOGGED IN -->
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <li><a href="adminprofile.php">Admin Panel</a></li>
                <?php endif; ?>

                <?php if ($_SESSION['role'] === 'member'): ?>
                    <li><a href="memberprofile.php">My Profile</a></li>
                <?php endif; ?>

                <?php if ($_SESSION['role'] === 'coach'): ?>
                    <li><a href="coachprofile.php">Coach Dashboard</a></li>
                <?php endif; ?>

                <?php if ($_SESSION['role'] === 'nutritionist'): ?>
                    <li><a href="nutritionistprofile.php">Nutritionist Dashboard</a></li>
                <?php endif; ?>

                <li><a href="Home.php?logout=1">Logout</a></li>

            <?php else: ?>

                <!-- USER IS NOT LOGGED IN -->
                <li><a href="Login.php">Login</a></li>
                <li><a href="Signup.html">Sign Up</a></li>

            <?php endif; ?>
            </ul>
        </div>

    </div>

    <div class="footer-bottom">
        <p>© 2025 Power Gym — All Rights Reserved.</p>
    </div>
</footer>


