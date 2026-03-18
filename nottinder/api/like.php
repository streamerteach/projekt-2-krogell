<?php
require_once '../includes/functions/handy_methods.php';
require_once '../includes/functions/security.php';
require_once '../includes/config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$profile_id = (int)($data['profile_id'] ?? 0);
$action = $data['action'] ?? ''; // like or unlike

if ($profile_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid profile ID']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

if ($action === 'like') {
    $query = "INSERT IGNORE INTO likes (from_user_id, to_user_id, created_at) VALUES (?, ?, NOW())";
    $stmt = $db->prepare($query);
    $stmt->execute([$_SESSION['user_id'], $profile_id]);
} elseif ($action === 'unlike') {
    $query = "DELETE FROM likes WHERE from_user_id = ? AND to_user_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_SESSION['user_id'], $profile_id]);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid action']);
    exit;
}

// get updated like count
$count_query = "SELECT COUNT(*) as count FROM likes WHERE to_user_id = ?";
$count_stmt = $db->prepare($count_query);
$count_stmt->execute([$profile_id]);
$count = $count_stmt->fetch()['count'];

// check if user has liked this profile
$check_query = "SELECT id FROM likes WHERE from_user_id = ? AND to_user_id = ?";
$check_stmt = $db->prepare($check_query);
$check_stmt->execute([$_SESSION['user_id'], $profile_id]);
$has_liked = $check_stmt->fetch() ? true : false;

echo json_encode([
    'success' => true,
    'likes' => $count,
    'has_liked' => $has_liked
]);