<?php
require_once '../../includes/functions/handy_methods.php';
require_once '../../includes/functions/security.php';
require_once '../../includes/config/database.php';
requireLogin();

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

// get other uid from query string
$other_id = isset($_GET['user']) ? (int)$_GET['user'] : 0;
if ($other_id <= 0) {
    header('Location: ' . url('pages/messages/index.php'));
    exit;
}

// handle report
if (isset($_GET['action']) && $_GET['action'] === 'report' && isset($_GET['msg_id'])) {
    $msg_id = (int)$_GET['msg_id'];
    // verify the message exists and is not already flagged
    $check = $db->prepare("SELECT id FROM messages WHERE id = ? AND is_flagged = 0");
    $check->execute([$msg_id]);
    if ($check->fetch()) {
        $update = $db->prepare("UPDATE messages SET is_flagged = 1 WHERE id = ?");
        $update->execute([$msg_id]);
        $_SESSION['message'] = "Message reported to moderators.";
    } else {
        $_SESSION['error'] = "Message already reported or not found.";
    }
    // redirect back to conversation
    header("Location: " . url('pages/messages/conversation.php?user=' . $other_id));
    exit;
}

// verify that other user exists and is active
$query = "SELECT u.id, u.username, u.full_name, 
                 (SELECT image_path FROM user_images WHERE user_id = u.id AND is_profile = 1 LIMIT 1) as profile_image
          FROM users u 
          WHERE u.id = ? AND u.is_active = 1";
$stmt = $db->prepare($query);
$stmt->execute([$other_id]);
$other = $stmt->fetch();
if (!$other) {
    $_SESSION['error'] = "User doesn't exist.";
    header('Location: ' . url('pages/messages/index.php'));
    exit;
}

// handle sending a new message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        // find existing conversation ID
        $conv_query = "SELECT DISTINCT conversation_id FROM messages 
                       WHERE (from_user_id = :uid AND to_user_id = :oid) 
                          OR (from_user_id = :oid AND to_user_id = :uid) 
                       LIMIT 1";
        $conv_stmt = $db->prepare($conv_query);
        $conv_stmt->execute([':uid' => $user_id, ':oid' => $other_id]);
        $conv_result = $conv_stmt->fetch();
        if ($conv_result) {
            $conversation_id = $conv_result['conversation_id'];
        } else {
            // new convo
            $conversation_id = uniqid('conv_', true);
        }

        $insert = "INSERT INTO messages (conversation_id, from_user_id, to_user_id, message, created_at) 
                   VALUES (:conv_id, :from_id, :to_id, :msg, NOW())";
        $ins_stmt = $db->prepare($insert);
        $ins_stmt->execute([
            ':conv_id' => $conversation_id,
            ':from_id' => $user_id,
            ':to_id' => $other_id,
            ':msg' => $message
        ]);
    }
    // redirect to avoid resubmission
    header("Location: " . url('pages/messages/conversation.php?user=' . $other_id));
    exit;
}

// mark messages as read (those sent to current user from other)
$mark_read = "UPDATE messages SET is_read = 1 WHERE to_user_id = :uid AND from_user_id = :oid AND is_read = 0";
$mark_stmt = $db->prepare($mark_read);
$mark_stmt->execute([':uid' => $user_id, ':oid' => $other_id]);

// fetch all messages between the two users
$msg_query = "SELECT m.*, 
              u_from.username as from_username,
              u_from.full_name as from_fullname
              FROM messages m
              JOIN users u_from ON m.from_user_id = u_from.id
              WHERE (m.from_user_id = :uid AND m.to_user_id = :oid) 
                 OR (m.from_user_id = :oid AND m.to_user_id = :uid)
              ORDER BY m.created_at ASC";
$msg_stmt = $db->prepare($msg_query);
$msg_stmt->execute([':uid' => $user_id, ':oid' => $other_id]);
$messages = $msg_stmt->fetchAll();

include '../../templates/header.php';
?>

<body>
    <?php include '../../templates/nav.php'; ?>

    <main>
        <section class="conversation-section">
            <div class="conversation-container">
                <div class="conversation-header">
                    <a href="<?php echo url('pages/messages/index.php'); ?>" class="back-link">← Back</a>
                    <h2>
                        <img src="<?php echo imageUrl($other['profile_image'] ?? 'assets/images/default-avatar.png'); ?>" alt="" class="small-avatar">
                        <?php echo htmlspecialchars($other['full_name'] ?: $other['username']); ?>
                    </h2>
                </div>

                <div class="message-thread" id="message-thread">
                    <?php foreach ($messages as $msg):
                        $is_mine = $msg['from_user_id'] == $user_id;
                        $class = $is_mine ? 'message-mine' : 'message-theirs';
                    ?>
                        <div class="message <?php echo $class; ?>">
                            <div class="message-bubble">
                                <?php if (!$is_mine): ?>
                                    <a href="?action=report&msg_id=<?php echo $msg['id']; ?>&user=<?php echo $other_id; ?>" class="report-link" onclick="return confirm('Report this message as inappropriate?')">🚩 Report</a>
                                <?php endif; ?>
                                <div class="message-text"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></div>
                                <div class="message-time"><?php echo date('d M H:i', strtotime($msg['created_at'])); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="message-form">
                    <form method="POST">
                        <textarea name="message" placeholder="Your message..." required></textarea>
                        <button type="submit" class="send-btn">Send</button>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <script>
        // scroll to bottom automatically
        const thread = document.getElementById('message-thread');
        thread.scrollTop = thread.scrollHeight;
    </script>

    <?php include '../../templates/footer.php'; ?>
</body>

</html>