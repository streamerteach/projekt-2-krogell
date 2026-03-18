<?php
require_once '../../includes/functions/handy_methods.php';
require_once '../../includes/functions/security.php';
require_once '../../includes/config/database.php';

requireLogin();

$database = new Database();
$db = $database->getConnection();

// get user data
$query = "SELECT u.*, 
          (SELECT COUNT(*) FROM messages WHERE to_user_id = u.id AND is_read = 0) as unread_messages,
          (SELECT COUNT(*) FROM likes WHERE to_user_id = u.id) as total_likes
          FROM users u WHERE u.id = :user_id";

$stmt = $db->prepare($query);
$stmt->execute([':user_id' => $_SESSION['user_id']]);
$user = $stmt->fetch();

// get user images
$img_query = "SELECT * FROM user_images WHERE user_id = :user_id ORDER BY uploaded_at DESC";
$img_stmt = $db->prepare($img_query);
$img_stmt->execute([':user_id' => $_SESSION['user_id']]);
$images = $img_stmt->fetchAll();

include '../../templates/header.php';
?>

<body>
    <?php include '../../templates/nav.php'; ?>

    <main>
        <section>
            <article class="profile-article">
                <div class="profile-container">
                    <h2>Profile for: <?php echo htmlspecialchars($_SESSION['username']); ?></h2>

                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="success-message"><?php echo htmlspecialchars($_SESSION['message']);
                                                        unset($_SESSION['message']); ?></div>
                    <?php endif; ?>

                    <!-- profile info -->
                    <div class="profile-info">
                        <div class="profile-stats">
                            <p><strong>Full name:</strong> <?php echo htmlspecialchars($user['full_name'] ?? 'Not set'); ?></p>
                            <p><strong>City:</strong> <?php echo htmlspecialchars($user['city'] ?? 'Not set'); ?></p>
                            <p><strong>Age:</strong> <?php echo htmlspecialchars($user['age'] ?? 'Not set'); ?></p>
                            <p><strong>Annual salary:</strong> <?php echo $user['annual_salary'] ? number_format($user['annual_salary']) . ' EUR' : 'Not set'; ?></p>
                            <p><strong>Looking for:</strong> <?php echo htmlspecialchars($user['preference'] ?? 'Not set'); ?></p>
                            <p><strong>Total likes:</strong> <?php echo $user['total_likes']; ?></p>
                            <p><strong>Unread messages:</strong> <?php echo $user['unread_messages']; ?></p>
                        </div>

                        <div class="profile-bio">
                            <h3>About me:</h3>
                            <p><?php echo nl2br(htmlspecialchars($user['bio'] ?? 'No bio yet.')); ?></p>
                        </div>
                    </div>

                    <!-- profile picture section -->
                    <div class="profile-image-section">
                        <h3>Profile Pictures</h3>

                        <div class="current-image">
                            <?php
                            $profile_image = array_filter($images, function ($img) {
                                return $img['is_profile'] == 1;
                            });
                            $profile_image = reset($profile_image);
                            ?>

                            <?php if ($profile_image): ?>
                                <img src="<?php echo imageUrl($profile_image['image_path']); ?>?v=<?php echo time(); ?>" alt="Profile picture">
                            <?php else: ?>
                                <img src="<?php echo imageUrl('assets/images/default-avatar.png'); ?>" alt="Default avatar">
                            <?php endif; ?>
                        </div>

                        <!-- upload form -->
                        <div class="upload-section">
                            <h4>Upload New Picture</h4>
                            <form id="uploadForm" enctype="multipart/form-data">
                                <div class="form-group">
                                    <input type="file" name="file" id="file" accept="image/*" required>
                                </div>
                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" name="set_as_profile"> Set as profile picture
                                    </label>
                                </div>
                                <button type="submit" class="submit-btn">Upload</button>
                            </form>
                            <div id="uploadMessage"></div>
                        </div>

                        <!-- gallery -->
                        <?php if (!empty($images)): ?>
                            <div class="gallery-section">
                                <h4>Your Gallery</h4>
                                <?php foreach ($images as $image): ?>
                                    <div class="gallery-item">
                                        <img src="<?php echo imageUrl($image['image_path']); ?>?v=<?php echo time(); ?>" alt="Gallery image">
                                        <?php if ($image['is_profile']): ?>
                                            <span class="profile-badge">Profile</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- profile comments section -->
                    <div class="profile-comments-section">
                        <h3>Profile Comments</h3>

                        <?php
                        // fetch comments for current profile (the logged-in user's profile)
                        $comment_query = "SELECT pc.*, u.username as commenter_name 
                      FROM profile_comments pc
                      JOIN users u ON pc.commenter_id = u.id
                      WHERE pc.profile_id = :profile_id AND pc.is_visible = 1
                      ORDER BY pc.created_at DESC";
                        $comment_stmt = $db->prepare($comment_query);
                        $comment_stmt->execute([':profile_id' => $_SESSION['user_id']]);
                        $profile_comments = $comment_stmt->fetchAll();
                        ?>

                        <?php if (empty($profile_comments)): ?>
                            <p>No comments yet.</p>
                        <?php else: ?>
                            <?php foreach ($profile_comments as $pc): ?>
                                <div class="comment-item">
                                    <div class="comment-header">
                                        <strong><?php echo htmlspecialchars($pc['commenter_name']); ?></strong>
                                        <span class="comment-time"><?php echo date('d-m-Y H:i', strtotime($pc['created_at'])); ?></span>

                                        <!-- report link for users (only if not your own comment) -->
                                        <?php if ($pc['commenter_id'] != $_SESSION['user_id']): ?>
                                            <a href="<?php echo url('pages/admin/report_comment.php?comment_id=' . $pc['id'] . '&from=profile'); ?>" class="report-link" onclick="return confirm('Report this comment as inappropriate?')">Report</a>
                                        <?php endif; ?>
                                    </div>
                                    <div class="comment-content">
                                        <?php echo nl2br(htmlspecialchars($pc['comment'])); ?>
                                    </div>

                                    <!-- admin/manager flag action -->
                                    <?php if (hasRole('admin') || hasRole('manager')): ?>
                                        <div class="comment-actions">
                                            <a href="<?php echo url('pages/admin/moderation.php?flag=' . $pc['id']); ?>" class="small-btn">Flag (admin)</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- link to guestbook -->
                    <div style="margin: 20px 0;">
                        <a href="<?php echo url('pages/guestbook/'); ?>" class="btn btn-secondary">Write a comment</a>
                    </div>

                    <!-- edit profile buttons -->
                    <div class="profile-actions">
                        <a href="<?php echo url('pages/profile/edit.php'); ?>" class="btn btn-primary">Edit Profile</a>
                        <a href="<?php echo url('pages/profile/delete.php'); ?>" class="btn btn-danger">Delete Account</a>
                    </div>
                </div>
            </article>
        </section>
    </main>

    <script>
        document.getElementById('uploadForm')?.addEventListener('submit', function(e) {
            e.preventDefault();

            let formData = new FormData(this);

            fetch(BASE_URL + '/api/upload.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('uploadMessage').innerHTML = '<div class="success">' + data.message + '</div>';
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        document.getElementById('uploadMessage').innerHTML = '<div class="error">' + data.message + '</div>';
                    }
                })
                .catch(error => {
                    document.getElementById('uploadMessage').innerHTML = '<div class="error">Upload failed</div>';
                });
        });
    </script>

    <?php include '../../templates/footer.php'; ?>
</body>

</html>