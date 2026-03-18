<?php
require_once 'includes/functions/handy_methods.php';
require_once 'includes/functions/security.php';
include 'templates/header.php';
?>

<body>
    <?php include 'templates/nav.php'; ?>

    <main>
        <section class="landing-page">
            <article class="welcome-article">
                <?php 
                if (isLoggedIn()) {
                    echo "<h2>Welcome back " . htmlspecialchars($_SESSION['username']) . "!</h2>";
                }
                
                print("<p>Today: " . date("l, d F Y") . ", week " . date("W") . "</p>");
                ?>
                
                <h1>Welcome to NotTinder!</h1>
                <h2>Want to find the worst date ever? Look no further!</h2>
                <p>Sign up now to start your journey towards unforgettable (and perhaps regrettable) dating experiences!</p>
                
                <?php if (!isLoggedIn()): ?>
                    <a href="pages/register/"><button class="nav-btn">Sign Up</button></a>
                <?php else: ?>
                    <a href="pages/home/"><button class="nav-btn">Start Swiping</button></a>
                <?php endif; ?>
                
                <!-- visitor count -->
                <h3>Visitors today: <strong><?php echo getVisitorCount(); ?></strong></h3>
            </article>
            
            <!-- preview of profiles -->
            <div class="preview-profiles">
                <h3>Some of our awful members:</h3>
                <?php
                require_once 'includes/config/database.php';
                $database = new Database();
                $db = $database->getConnection();
                
                $preview_query = "SELECT u.username, u.full_name, u.age, 
                                  (SELECT image_path FROM user_images WHERE user_id = u.id AND is_profile = 1 LIMIT 1) as profile_image
                                  FROM users u 
                                  WHERE u.is_active = 1 
                                  ORDER BY RAND() 
                                  LIMIT 3";
                $preview_stmt = $db->query($preview_query);
                $previews = $preview_stmt->fetchAll();
                
                foreach ($previews as $preview): ?>
                    <div class="preview-card">
                        <img src="<?php echo htmlspecialchars($preview['profile_image'] ?? 'assets/images/default-avatar.png'); ?>" 
                             alt="<?php echo htmlspecialchars($preview['full_name'] ?: $preview['username']); ?>">
                        <p><?php echo htmlspecialchars($preview['full_name'] ?: $preview['username']); ?>, <?php echo $preview['age'] ?? '?'; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
    
    <?php include 'templates/footer.php'; ?>