<header>
    <h1>NotTinder</h1>
    <nav>
        <ul>
            <li><a href="<?php echo url(''); ?>">Home</a></li>
            <?php if (isLoggedIn()): ?>
                <li><a href="<?php echo url('pages/browse/'); ?>">Browse</a></li>
                <li><a href="<?php echo url('pages/messages/'); ?>">Messages</a></li>
                <li><a href="<?php echo url('pages/guestbook/'); ?>">Guestbook</a></li>
                <li><a href="<?php echo url('pages/profile/'); ?>">Profile</a></li>
                <?php if (isManager()): ?>
                    <li><a href="<?php echo url('pages/admin/'); ?>">Admin</a></li>
                <?php endif; ?>
                <li><a href="<?php echo url('logout.php'); ?>" class="nav-btn">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a></li>
            <?php else: ?>
                <li><a href="<?php echo url('pages/login/'); ?>" class="nav-btn">Login</a></li>
                <li><a href="<?php echo url('pages/register/'); ?>" class="nav-btn">Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>