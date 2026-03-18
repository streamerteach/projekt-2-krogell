<?php
require_once '../../includes/functions/handy_methods.php';
require_once '../../includes/functions/security.php';
include '../../templates/header.php';
?>

<body>
    <?php include '../../templates/nav.php'; ?>
    
    <main>
        <section class="home-layout">
            <?php 
            include '../swipe/index.php'; 
            ?>

            <article class="home-article">
                <?php if (isLoggedIn()): ?>
                    <!-- countdown to date -->
                    <div class="countdown-container">
                        <h3>Worst Date Countdown</h3>
                        
                        <?php
                        $visitorCount = getVisitorCount();
                        echo "<p><small>Unique visitors today: " . $visitorCount . "</small></p>";
                        ?>
                        
                        <form id="dateForm" class="countdown-form">
                            <p><strong>Set a date for your worst date ever:</strong></p>

                            <div class="form-group">
                                <label for="date">Date:</label>
                                <input type="date" id="date" name="date" required>
                            </div>

                            <div class="form-group">
                                <label for="time">Time:</label>
                                <input type="time" id="time" name="time" required>
                            </div>

                            <button type="submit" class="submit-btn">Start countdown</button>
                        </form>

                        <div class="countdown-display">
                            <p><strong>Your date is in:</strong></p>
                            <p id="dateCountdown" class="countdown-timer">--d --h --m --s</p>
                        </div>
                    </div>

                    <script src="../../assets/js/countdown.js"></script>
                <?php endif; ?>
                
                <div class="navigation-links">
                    <?php if (isLoggedIn()): ?>
                        <a href="../profile/" class="nav-link">Go to Profile</a>
                        <a href="../logout.php" class="nav-link">Logout</a>
                    <?php else: ?>
                        <a href="../login/" class="nav-link">Login here</a>
                        <a href="../register/" class="nav-link">Register</a>
                    <?php endif; ?>
                </div>
            </article>
        </section>
    </main>
    
    <?php include '../../templates/footer.php'; ?>
</body>
</html>