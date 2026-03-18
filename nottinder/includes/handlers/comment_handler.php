<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../functions/handy_methods.php';
require_once __DIR__ . '/../functions/security.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'You must be logged in to comment']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$profile_id = $data['profile_id'] ?? 0;
$message = trim($data['message'] ?? '');

if (empty($profile_id) || empty($message)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

// create or get conversation id
$conversation_id = getConversationId($db, $_SESSION['user_id'], $profile_id);

// save message
$query = "INSERT INTO messages (conversation_id, from_user_id, to_user_id, message, created_at) 
          VALUES (:conv_id, :from_id, :to_id, :message, NOW())";

$stmt = $db->prepare($query);
$success = $stmt->execute([
    ':conv_id' => $conversation_id,
    ':from_id' => $_SESSION['user_id'],
    ':to_id' => $profile_id,
    ':message' => $message
]);

if ($success) {
    echo json_encode([
        'success' => true,
        'message' => 'Comment posted successfully',
        'conversation_id' => $conversation_id
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save comment']);
}

function getConversationId($db, $user1, $user2) {
    // check if conversation exists
    $query = "SELECT DISTINCT conversation_id FROM messages 
              WHERE (from_user_id = :user1 AND to_user_id = :user2) 
                 OR (from_user_id = :user2 AND to_user_id = :user1)
              LIMIT 1";
    
    $stmt = $db->prepare($query);
    $stmt->execute([':user1' => $user1, ':user2' => $user2]);
    $result = $stmt->fetch();
    
    if ($result) {
        return $result['conversation_id'];
    }
    
    // create new conversation ID
    return uniqid('conv_', true);
}
?>