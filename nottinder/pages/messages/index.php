<?php
require_once '../../includes/functions/handy_methods.php';
require_once '../../includes/functions/security.php';
require_once '../../includes/config/database.php';
requireLogin();

include '../../templates/header.php';

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

// get all conversations
$query = "SELECT 
            other.id as other_id,
            other.username,
            other.full_name,
            (SELECT image_path FROM user_images WHERE user_id = other.id AND is_profile = 1 LIMIT 1) as profile_image,
            (SELECT message FROM messages 
             WHERE (from_user_id = :user_id AND to_user_id = other.id) 
                OR (from_user_id = other.id AND to_user_id = :user_id)
             ORDER BY created_at DESC LIMIT 1) as last_message,
            (SELECT created_at FROM messages 
             WHERE (from_user_id = :user_id AND to_user_id = other.id) 
                OR (from_user_id = other.id AND to_user_id = :user_id)
             ORDER BY created_at DESC LIMIT 1) as last_time,
            (SELECT COUNT(*) FROM messages 
             WHERE to_user_id = :user_id AND from_user_id = other.id AND is_read = 0) as unread
          FROM users other
          WHERE other.id IN (
            SELECT DISTINCT from_user_id FROM messages WHERE to_user_id = :user_id
            UNION
            SELECT DISTINCT to_user_id FROM messages WHERE from_user_id = :user_id
          )
          ORDER BY last_time DESC";

$stmt = $db->prepare($query);
$stmt->execute([':user_id' => $user_id]);
$conversations = $stmt->fetchAll();
?>

<body>
    <?php include '../../templates/nav.php'; ?>
    
    <main>
        <section class="messages-section">
            <div class="messages-container">
                <h2>Messages</h2>
                
                <?php if (empty($conversations)): ?>
                    <p>You have no messages yet. <br><a href="../browse/">Browse </a> for other users to message someone.</p>
                <?php else: ?>
                    <div class="conversation-list">
                        <?php foreach ($conversations as $conv): 
                            $avatar = $conv['profile_image'] ?? '/nottinder/assets/images/default-avatar.png';
                            $name = htmlspecialchars($conv['full_name'] ?: $conv['username']);
                            $lastMsg = htmlspecialchars(substr($conv['last_message'] ?? '', 0, 50)) . (strlen($conv['last_message'] ?? '') > 50 ? '...' : '');
                            $time = $conv['last_time'] ? date('Y-m-d H:i', strtotime($conv['last_time'])) : '';
                            $unread = $conv['unread'] > 0 ? '<span class="unread-badge">' . $conv['unread'] . '</span>' : '';
                        ?>
                        <a href="conversation.php?user=<?php echo $conv['other_id']; ?>" class="conversation-item <?php echo $conv['unread'] > 0 ? 'unread' : ''; ?>">
                            <div class="conv-avatar">
                                <img src="<?php echo htmlspecialchars($avatar); ?>" alt="<?php echo $name; ?>">
                            </div>
                            <div class="conv-info">
                                <span class="conv-name"><?php echo $name; ?></span>
                                <span class="conv-last"><?php echo $lastMsg; ?></span>
                            </div>
                            <div class="conv-meta">
                                <span class="conv-time"><?php echo $time; ?></span>
                                <?php echo $unread; ?>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>
    
    <?php include '../../templates/footer.php'; ?>
</body>
</html>