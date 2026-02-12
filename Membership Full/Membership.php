<?php
    session_start();
    if (isset($_GET['logout'])) {
        session_unset();
        session_destroy();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
    include "../DB.php";

    // Insert membership plans if not exist
    $plans = [
        [1, 'Silver', 'Basic', 350, 30, 0, 0, 1, 0, 1, 3, 1],
        [2, 'Gold', 'Standard', 600, 30, 1, 0, 1, 0, 5, 7, 2],
        [3, 'Platinum', 'Premium', 1000, 30, 1, 1, 1, 5, 10, 14, 3]
    ];

    foreach ($plans as $plan) {
        $stmt = $conn->prepare("INSERT IGNORE INTO MembershipPlan (Plan_ID, Name, Tier, Price, Duration, Coach_Access, Nutritionist_Access, Is_Active, Max_Nutritionist_Session, Max_Coach_Sessions, Max_Freeze_Length_days, Max_Freezes_Allowed, Created_at, Updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->bind_param("issiiiiiiiii", $plan[0], $plan[1], $plan[2], $plan[3], $plan[4], $plan[5], $plan[6], $plan[7], $plan[8], $plan[9], $plan[10], $plan[11]);
        $stmt->execute();
        $stmt->close();
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
                          <button class="super-button add-to-cart-btn" data-plan="Silver" data-price="350" data-duration="1 Month">
                              <span>Add to Cart</span>
                          </button>
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
                          <button class="super-button add-to-cart-btn" data-plan="Platinum" data-price="1000" data-duration="1 Month">
                              <span>Add to Cart</span>
                          </button>
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
                              <button class="super-button add-to-cart-btn" data-plan="Gold" data-price="600" data-duration="1 Month">
                                  <span>Add to Cart</span>
                              </button>
                        </div>
                    </div>
                </div>
              </div>
            </div>
            
</section>
<?php include "../ChatBot Full/ChatBot.Php"; ?>
<?php include "Footer.php"; ?>