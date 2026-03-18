<?php
require_once '../../includes/functions/handy_methods.php';
require_once '../../includes/functions/security.php';
require_once '../../includes/config/database.php';

requireLogin();

if (!isset($_GET['comment_id']) || !is_numeric($_GET['comment_id'])) {
    header('Location: /nottinder/index.php');
    exit;
}

$comment_id = (int)$_GET['comment_id'];
$from = $_GET['from'] ?? 'profile'; // to redirect back

$database = new Database();
$db = $database->getConnection();

// check if comment exists and is not already flagged
$check = $db->prepare("SELECT id, commenter_id FROM profile_comments WHERE id = ? AND is_flagged = 0");
$check->execute([$comment_id]);
$comment = $check->fetch();

if ($comment) {
    // don't allow reporting your own comment
    if ($comment['commenter_id'] == $_SESSION['user_id']) {
        $_SESSION['error'] = "You cannot report your own comment.";
    } else {
        $update = $db->prepare("UPDATE profile_comments SET is_flagged = 1 WHERE id = ?");
        $update->execute([$comment_id]);
        $_SESSION['message'] = "Comment reported to moderators.";
    }
} else {
    $_SESSION['error'] = "Comment not found or already reported.";
}

// redirect back
if ($from === 'guestbook') {
    header('Location: /nottinder/pages/guestbook/');
} else {
    header('Location: /nottinder/pages/profile/');
}
exit;