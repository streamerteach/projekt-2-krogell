<?php
require_once '../../includes/functions/handy_methods.php';
require_once '../../includes/functions/security.php';
requireManager(); // only managers and admins can access this page (duh)

include '../../templates/header.php';
?>

<body>
    <?php include '../../templates/nav.php'; ?>
    
    <main>
        <section class="admin-section">
            <div class="admin-container">
                <h1>Admin Dashboard</h1>
                <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> (Role: <?php echo $_SESSION['user_role']; ?>)</p>
                
                <div class="admin-cards">
                    <div class="admin-card">
                        <h3>User Management</h3>
                        <p>View, edit, or delete user accounts.</p>
                        <a href="users.php" class="btn btn-primary">Manage Users</a>
                    </div>
                    
                    <div class="admin-card">
                        <h3>Moderation</h3>
                        <p>Review flagged messages and comments.</p>
                        <a href="moderation.php" class="btn btn-primary">Moderate Content</a>
                    </div>
                </div>
            </div>
        </section>
    </main>
    
    <?php include '../../templates/footer.php'; ?>
</body>
</html>