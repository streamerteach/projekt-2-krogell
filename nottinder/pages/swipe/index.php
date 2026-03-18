<?php
require_once __DIR__ . '/../../includes/functions/handy_methods.php';
require_once __DIR__ . '/../../includes/functions/security.php';
require_once __DIR__ . '/../../includes/config/database.php';

$database = new Database();
$db = $database->getConnection();

// get random profiles for swiping
$query = "SELECT u.*, 
          (SELECT image_path FROM user_images WHERE user_id = u.id AND is_profile = 1 LIMIT 1) as profile_image,
          (SELECT COUNT(*) FROM likes WHERE to_user_id = u.id) as like_count
          FROM users u 
          WHERE u.is_active = 1";

// if logged in, exclude own profile and already liked profiles
if (isLoggedIn()) {
    $query .= " AND u.id != :user_id 
                AND u.id NOT IN (SELECT to_user_id FROM likes WHERE from_user_id = :user_id)";
}

$query .= " ORDER BY RAND() LIMIT 1";

$stmt = $db->prepare($query);

if (isLoggedIn()) {
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
} else {
    $stmt->execute();
}

$profile = $stmt->fetch();

// handle swipe action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['swipe']) && isLoggedIn()) {
    $profile_id = (int)($_POST['profile_id'] ?? 0);
    $action = $_POST['swipe'];
    
    if ($action === 'right' && $profile_id > 0) {
        // like the profile
        $like_query = "INSERT IGNORE INTO likes (from_user_id, to_user_id, created_at) 
                       VALUES (:from_id, :to_id, NOW())";
        $like_stmt = $db->prepare($like_query);
        $like_stmt->execute([
            ':from_id' => $_SESSION['user_id'],
            ':to_id' => $profile_id
        ]);
    }
    
    // refresh to get new profile
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>

<?php if ($profile): ?>
    <div class="card">
        <?php 
        $image_path = $profile['profile_image'] ?? 'assets/images/default-avatar.png';
        ?>
        <img src="<?php echo imageUrl($image_path); ?>" alt="<?php echo htmlspecialchars($profile['full_name'] ?: $profile['username']); ?>">
        
        <h2><?php echo htmlspecialchars($profile['full_name'] ?: $profile['username']); ?>, <?php echo $profile['age'] ?? '?'; ?></h2>
        
        <?php if (isLoggedIn()): ?>
            <p><strong>City:</strong> <?php echo htmlspecialchars($profile['city'] ?? 'Unknown'); ?></p>
            <p><strong>Looking for:</strong> <?php echo htmlspecialchars($profile['preference'] ?? 'Any'); ?></p>
            <p><strong>Likes:</strong> <?php echo $profile['like_count']; ?></p>
            <p><strong>Salary:</strong> <?php echo $profile['annual_salary'] ? number_format($profile['annual_salary']) . ' EUR' : 'Secret'; ?></p>
        <?php endif; ?>
        
        <p class="bio"><?php echo nl2br(htmlspecialchars($profile['bio'] ?? 'No bio yet.')); ?></p>

        <?php if (isLoggedIn()): ?>
            <form method="POST" class="swipe-form">
                <input type="hidden" name="profile_id" value="<?php echo $profile['id']; ?>">
                <button type="submit" name="swipe" value="left" class="swipe-left">❌</button>
                <button type="submit" name="swipe" value="right" class="swipe-right">❤️</button>
            </form>
        <?php else: ?>
            <p><a href="<?php echo url('pages/login/'); ?>">Log in</a> to like profiles and see more info.</p>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="card">
        <p>No more available profiles at the moment.</p>
        <?php if (isLoggedIn()): ?>
            <p><a href="<?php echo url('pages/browse/'); ?>">Browse for profiles</a> instead.</p>
        <?php endif; ?>
    </div>
<?php endif; ?>