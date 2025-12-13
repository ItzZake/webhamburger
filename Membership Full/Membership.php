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

<link rel="stylesheet" href="Membership.Css">
<script src="Membership.js" defer></script>
<title>Membership</title>

<section>
     <div class="orbs-background">
                <div class="blobs">
                    <div class="blob-dodge"><div class="blob a"></div></div>
                    <div class="blob-dodge"><div class="blob b"></div></div>
                    <div class="blob-dodge"><div class="blob c"></div></div>
                </div>
            </div>
      <div class="MembershipOptions">
        <h1>Choose Your Membership</h1>
        <p>Select the plan that best fits your fitness goals and lifestyle. Whether you're just starting out or looking to take your training to the next level, we have a membership option for you.</p>
        <div class="Buttons">
          <button class="super-button">
              <span>1 Month</span>
          </button>
          <button class="super-button">
              <span>1 Year</span>
          </button>
          <button class="super-button">
              <span>3 Months</span>
          </button>
        </div>
      </div>
      <div class="container">
            <div class="carousel-wrapper">
              <div class="carousel">
                <div class="card small-card">
                    <div class="nft">
                        <div class='main'>
                            <img class='tokenImage' src="Image/Silver membership.png" alt="NFT" />
                            <h2>Silver</h2>
                            <p class='description'>Start off light with our standard subscription.</p>
                          <div class='tokenInfo'>
                            <div class="price">
                              <p class="Amount">350 L.E<p>
                            </div>
                            <div class="duration">
                                <ins>◷</ins>
                                <p class="Time">1 Month</p>
                            </div>
                          </div>
                          <hr/>
                          <a href="Membership.php">
                            <button class="super-button">
                              <span>Select Option</span>
                            </button>
                          </a>
                        </div>
                    </div>
                </div>
                <!-- CENTER CARD (highlight) -->
                <div class="card active">
                    <div class="nft">
                      <div class='main'>
                          <img class='tokenImage' src="Image/diamond membership.png" alt="NFT" />
                          <h2>Platinum</h2>
                          <p class='description'>Enjoy all the perks with our expert coaches and your own nutritionist.</p>
                          <div class='tokenInfo'>
                              <div class="price">
                                  <p class="Amount">1000 L.E<p>
                              </div>
                              <div class="duration">
                                  <ins>◷</ins>
                                  <p class="Time">1 Month</p>
                              </div>
                          </div>
                          <hr/>
                            <a href="Membership.php">
                              <button class="super-button">
                                <span>Select Option</span>
                              </button>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- RIGHT CARD -->
                <div class="card small-card">
                    <div class="nft">
                        <div class='main'>
                          <img class='tokenImage' src="Image/gold membership.png" alt="NFT" />
                              <h2>Gold</h2>
                              <p class='description'>Enjoy private sessions with our well-esteemed coaches.</p>
                              <div class='tokenInfo'>
                                  <div class="price">
                                      <p class="Amount">600 L.E<p>
                                  </div>
                                  <div class="duration">
                                      <ins>◷</ins>
                                      <p class="Time">1 Month</p>
                                  </div>
                              </div>
                              <hr/>
                              <a href="Membership.php">
                                  <button class="super-button">
                                      <span>Select Option</span>
                                  </button>
                              </a>
                        </div>
                    </div>
                </div>
              </div>
            </div>
            
</section>
<?php include "Footer.php"; ?> <!-- NEEDS TO BE AT THE BOTTOM -->
