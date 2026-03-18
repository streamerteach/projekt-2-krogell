<?php
require_once '../../includes/functions/handy_methods.php';
require_once '../../includes/functions/security.php';
require_once '../../includes/config/database.php';

requireLogin();

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "Invalid security token.";
    } else {
        $password = $_POST['password'] ?? '';
        
        // verify password
        $query = "SELECT password_hash FROM users WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->execute([':id' => $user_id]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // soft delete - set is_active to false
            $delete_query = "UPDATE users SET is_active = 0, username = CONCAT(username, '_deleted_', id) WHERE id = :id";
            $delete_stmt = $db->prepare($delete_query);
            $delete_stmt->execute([':id' => $user_id]);
            
            // log out user
            session_destroy();
            
            $_SESSION['message'] = "Your account has been deleted.";
            header("Location: " . BASE_URL . "/");
            exit;
        } else {
            $error = "Incorrect password.";
        }
    }
}

include '../../templates/header.php';
?>

<body>
    <?php include '../../templates/nav.php'; ?>
    
    <main>
        <section>
            <article class="profile-article">
                <div class="delete-container">
                    <h2>Delete Account</h2>
                    
                    <div class="warning-message">
                        <p><strong>Warning:</strong> This action cannot be undone!</p>
                        <p>All your profile data, messages, and images will be permanently deleted.</p>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" class="delete-form" onsubmit="return confirm('Are you absolutely sure you want to delete your account? This cannot be undone.');">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        
                        <div class="form-group">
                            <label for="password">Enter your password to confirm:</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        
                        <button type="submit" class="btn btn-danger">Permanently Delete My Account</button>
                        <a href="<?php echo url('pages/profile/'); ?>" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </article>
        </section>
    </main>
    
    <?php include '../../templates/footer.php'; ?>
</body>
</html>