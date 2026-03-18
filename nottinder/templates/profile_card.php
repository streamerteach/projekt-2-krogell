<?php
// profile card template for browse page
if (!isset($profile)) return;

$image = $profile['profile_image'] ?? '/nottinder/assets/images/default-avatar.png';
$show_salary = isLoggedIn(); // only show salary to logged in users
?>
<div class="profile-card" data-profile-id="<?php echo $profile['id']; ?>">
    <div class="profile-card-image">
        <img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($profile['full_name'] ?: $profile['username']); ?>">
    </div>

    <div class="profile-card-info">
        <h3><?php echo htmlspecialchars($profile['full_name'] ?: $profile['username']); ?>, <?php echo $profile['age'] ?? '?'; ?></h3>

        <p class="profile-city">📍 <?php echo htmlspecialchars($profile['city'] ?? 'Unknown city'); ?></p>

        <?php if ($show_salary && $profile['annual_salary'] > 0): ?>
            <p class="profile-salary">💰 <?php echo number_format($profile['annual_salary']); ?> EUR/year</p>
        <?php endif; ?>

        <p class="profile-bio"><?php echo htmlspecialchars(substr($profile['bio'] ?? '', 0, 100)) . '...'; ?></p>

        <div class="profile-stats">
            <span class="profile-likes">❤️ <?php echo $profile['like_count'] ?? 0; ?></span>
            <span class="profile-preference">🎯 <?php echo htmlspecialchars($profile['preference'] ?? 'Any'); ?></span>
        </div>

        <?php if (isLoggedIn() && $profile['id'] != $_SESSION['user_id']): ?>
            <?php
            $liked_class = !empty($profile['user_liked']) ? 'liked' : '';
            $button_text = !empty($profile['user_liked']) ? '💔 Unlike' : '❤️ Like';
            ?>
            <div class="profile-actions">
                <button class="btn-like <?php echo $liked_class; ?>" data-profile-id="<?php echo $profile['id']; ?>"><?php echo $button_text; ?></button>
                <a href="/nottinder/pages/messages/conversation.php?user=<?php echo $profile['id']; ?>" class="btn-message">💬 Message</a>
            </div>
        <?php endif; ?>
    </div>
</div>