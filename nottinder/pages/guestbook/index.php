<?php
require_once '../../includes/functions/handy_methods.php';
require_once '../../includes/functions/security.php';
require_once '../../includes/config/database.php';
include '../../templates/header.php';

$database = new Database();
$db = $database->getConnection();

// handle new comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment']) && isLoggedIn()) {
    $comment = trim($_POST['comment']);
    $profile_id = (int)($_POST['profile_id'] ?? 0);

    if (!empty($comment) && $profile_id > 0) {
        $query = "INSERT INTO profile_comments (profile_id, commenter_id, comment) 
                  VALUES (:profile_id, :commenter_id, :comment)";
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':profile_id' => $profile_id,
            ':commenter_id' => $_SESSION['user_id'],
            ':comment' => $comment
        ]);
        header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
        exit;
    }
}

// fetch all visible comments, newest first
$query = "SELECT pc.*, 
          u_from.username as commenter_name,
          u_to.username as profile_username,
          u_to.full_name as profile_fullname
          FROM profile_comments pc
          JOIN users u_from ON pc.commenter_id = u_from.id
          JOIN users u_to ON pc.profile_id = u_to.id
          WHERE pc.is_visible = 1
          ORDER BY pc.created_at DESC
          LIMIT 50";
$stmt = $db->query($query);
$comments = $stmt->fetchAll();

// get list of profiles for the dropdown (only active users)
$profiles_query = "SELECT id, username, full_name FROM users WHERE is_active = 1 ORDER BY username";
$profiles_stmt = $db->query($profiles_query);
$profiles = $profiles_stmt->fetchAll();
?>

<body>
    <?php include '../../templates/nav.php'; ?>
    
    <section>
        <article class="guest-article">
            <h1>Profile Comments</h1>

            <?php if (isset($_GET['success'])): ?>
                <div class="success-message">Comment sent!</div>
            <?php endif; ?>

            <?php if (isLoggedIn()): ?>
                <div class="comment-form-container">
                    <h2>Leave a comment</h2>
                    
                    <form method="POST" class="comment-form">
                        <div class="form-group">
                            <label for="profile_id">Select any user:</label>
                            <select name="profile_id" id="profile_id" required>
                                <option value="">Users...</option>
                                <?php foreach ($profiles as $profile): ?>
                                    <option value="<?php echo $profile['id']; ?>">
                                        <?php echo htmlspecialchars($profile['full_name'] ?: $profile['username']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="comment">Your comment:</label>
                            <textarea id="comment" name="comment" rows="4" required placeholder="Write your comment here..."></textarea>
                        </div>
                        
                        <p>You're leaving the comment as: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></p>
                        <input type="submit" class="submit-btn" value="Send comment">
                    </form>
                </div>
            <?php else: ?>
                <p>You must be logged in to write in the guestbook. <a href="<?php echo url('pages/login/'); ?>">Log in</a></p>
            <?php endif; ?>

            <div class="comments-container">
                <h2>Newest comments (<?php echo count($comments); ?>)</h2>

                <?php if (empty($comments)): ?>
                    <p>No comments yet.</p>
                <?php else: ?>
                    <?php foreach ($comments as $c): ?>
                        <div class="comment-item">
                            <div class="comment-header">
                                <strong><?php echo htmlspecialchars($c['commenter_name']); ?></strong>
                                <span class="comment-to">→ <?php echo htmlspecialchars($c['profile_fullname'] ?: $c['profile_username']); ?></span>
                                <span class="comment-time"><?php echo date('d-m-Y H:i', strtotime($c['created_at'])); ?></span>
                            </div>
                            <div class="comment-content">
                                <?php echo nl2br(htmlspecialchars($c['comment'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </article>
    </section>
    
    <?php include '../../templates/footer.php'; ?>
</body>
</html>