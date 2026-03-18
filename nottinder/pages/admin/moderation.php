<?php
require_once '../../includes/functions/handy_methods.php';
require_once '../../includes/functions/security.php';
require_once '../../includes/config/database.php';
requireManager(); // only managers and admins here, once again!

$database = new Database();
$db = $database->getConnection();

// handle actions
if (isset($_GET['action']) && isset($_GET['type']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $type = $_GET['type']; // 'message' or 'comment'
    
    if ($_GET['action'] === 'delete') {
        if ($type === 'message') {
            $stmt = $db->prepare("DELETE FROM messages WHERE id = ?");
            $stmt->execute([$id]);
        } elseif ($type === 'comment') {
            $stmt = $db->prepare("DELETE FROM profile_comments WHERE id = ?");
            $stmt->execute([$id]);
        }
        $_SESSION['message'] = ucfirst($type) . " deleted.";
    } elseif ($_GET['action'] === 'unflag') {
        if ($type === 'message') {
            $stmt = $db->prepare("UPDATE messages SET is_flagged = 0 WHERE id = ?");
            $stmt->execute([$id]);
        } elseif ($type === 'comment') {
            $stmt = $db->prepare("UPDATE profile_comments SET is_flagged = 0 WHERE id = ?");
            $stmt->execute([$id]);
        }
        $_SESSION['message'] = ucfirst($type) . " flag cleared.";
    }
    header("Location: moderation.php");
    exit;
}

// fetch flagged messages
$msg_query = "SELECT m.*, 
              u_from.username as from_username, 
              u_to.username as to_username 
              FROM messages m
              JOIN users u_from ON m.from_user_id = u_from.id
              JOIN users u_to ON m.to_user_id = u_to.id
              WHERE m.is_flagged = 1
              ORDER BY m.created_at DESC";
$flagged_msgs = $db->query($msg_query)->fetchAll();

// fetch flagged comments
$comm_query = "SELECT pc.*, 
               u_commenter.username as commenter_name,
               u_profile.username as profile_username
               FROM profile_comments pc
               JOIN users u_commenter ON pc.commenter_id = u_commenter.id
               JOIN users u_profile ON pc.profile_id = u_profile.id
               WHERE pc.is_flagged = 1
               ORDER BY pc.created_at DESC";
$flagged_comments = $db->query($comm_query)->fetchAll();

include '../../templates/header.php';
?>

<body>
    <?php include '../../templates/nav.php'; ?>
    
    <main>
        <section class="admin-section">
            <div class="admin-container">
                <h1>Moderation</h1>
                <p><a href="<?php echo url('pages/admin/index.php'); ?>" class="btn btn-secondary">← Back to Dashboard</a></p>
                
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="success-message"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
                <?php endif; ?>
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="error-message"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>
                
                <h2>Flagged Messages</h2>
                <?php if (empty($flagged_msgs)): ?>
                    <p>No flagged messages.</p>
                <?php else: ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Message</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($flagged_msgs as $msg): ?>
                            <tr>
                                <td><?php echo $msg['id']; ?></td>
                                <td><?php echo htmlspecialchars($msg['from_username']); ?></td>
                                <td><?php echo htmlspecialchars($msg['to_username']); ?></td>
                                <td><?php echo htmlspecialchars(substr($msg['message'], 0, 50)) . (strlen($msg['message']) > 50 ? '...' : ''); ?></td>
                                <td><?php echo date('Y-m-d H:i', strtotime($msg['created_at'])); ?></td>
                                <td>
                                    <a href="?action=delete&type=message&id=<?php echo $msg['id']; ?>" class="btn-small btn-danger" onclick="return confirm('Delete this message?')">Delete</a>
                                    <a href="?action=unflag&type=message&id=<?php echo $msg['id']; ?>" class="btn-small btn-success">Clear Flag</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
                
                <h2>Flagged Comments</h2>
                <?php if (empty($flagged_comments)): ?>
                    <p>No flagged comments.</p>
                <?php else: ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Commenter</th>
                                <th>On Profile</th>
                                <th>Comment</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($flagged_comments as $c): ?>
                            <tr>
                                <td><?php echo $c['id']; ?></td>
                                <td><?php echo htmlspecialchars($c['commenter_name']); ?></td>
                                <td><?php echo htmlspecialchars($c['profile_username']); ?></td>
                                <td><?php echo htmlspecialchars(substr($c['comment'], 0, 50)) . (strlen($c['comment']) > 50 ? '...' : ''); ?></td>
                                <td><?php echo date('Y-m-d H:i', strtotime($c['created_at'])); ?></td>
                                <td>
                                    <a href="?action=delete&type=comment&id=<?php echo $c['id']; ?>" class="btn-small btn-danger" onclick="return confirm('Delete this comment?')">Delete</a>
                                    <a href="?action=unflag&type=comment&id=<?php echo $c['id']; ?>" class="btn-small btn-success">Clear Flag</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </section>
    </main>
    
    <?php include '../../templates/footer.php'; ?>
</body>
</html>