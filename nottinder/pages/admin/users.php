<?php
require_once '../../includes/functions/handy_methods.php';
require_once '../../includes/functions/security.php';
require_once '../../includes/config/database.php';
requireManager();

$database = new Database();
$db = $database->getConnection();

// handle actions
if (isset($_GET['action']) && isset($_GET['user_id'])) {
    $user_id = (int)$_GET['user_id'];
    
    if ($_GET['action'] === 'delete' && isAdmin()) {
        // soft delete
        $stmt = $db->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
        $stmt->execute([$user_id]);
        $_SESSION['message'] = "User deactivated.";
    } elseif ($_GET['action'] === 'activate' && isAdmin()) {
        $stmt = $db->prepare("UPDATE users SET is_active = 1 WHERE id = ?");
        $stmt->execute([$user_id]);
        $_SESSION['message'] = "User activated.";
    }
    header("Location: users.php");
    exit;
}

// get all users
$query = "SELECT id, username, email, full_name, role, is_active, created_at FROM users ORDER BY id DESC";
$users = $db->query($query)->fetchAll();

include '../../templates/header.php';
?>

<body>
    <?php include '../../templates/nav.php'; ?>
    
    <main>
        <section class="admin-section">
            <div class="admin-container">
                <h1>User Management</h1>
                <p><a href="<?php echo url('pages/admin/index.php'); ?>" class="btn btn-secondary">← Back to Dashboard</a></p>
                
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="success-message"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
                <?php endif; ?>
                
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Full Name</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td><?php echo $user['role']; ?></td>
                            <td><?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?></td>
                            <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                            <td>
                                <?php if (isAdmin()): ?>
                                    <?php if ($user['is_active']): ?>
                                        <a href="?action=delete&user_id=<?php echo $user['id']; ?>" class="btn-small btn-danger" onclick="return confirm('Deactivate this user?')">Deactivate</a>
                                    <?php else: ?>
                                        <a href="?action=activate&user_id=<?php echo $user['id']; ?>" class="btn-small btn-success">Activate</a>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <!-- managers can edit profile (redirect to edit page with user ID) -->
                                <a href="<?php echo url('pages/profile/edit.php?user_id=' . $user['id']); ?>" class="btn-small btn-primary">Edit</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
    
    <?php include '../../templates/footer.php'; ?>
</body>
</html>