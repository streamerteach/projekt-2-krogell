<header>
    <h1>NotTinder</h1>
    <nav>
        <ul>
            <li><a href="/nottinder/">Home</a></li>
            <?php if (isLoggedIn()): ?>
                <li><a href="/nottinder/pages/browse/">Browse</a></li>
                <li><a href="/nottinder/pages/profile/">Profile</a></li>
                <li><a href="/nottinder/pages/messages/">Messages</a></li>
                <li><a href="/nottinder/pages/guestbook/">Guestbook</a></li>
                <?php if (isManager()): ?>
                    <li><a href="/nottinder/pages/admin/">Admin</a></li>
                <?php endif; ?>
                <li><a href="/nottinder/pages/logout.php" class="nav-btn">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a></li>
            <?php else: ?>
                <li><a href="/nottinder/pages/login/" class="nav-btn">Login</a></li>
                <li><a href="/nottinder/pages/register/" class="nav-btn">Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>