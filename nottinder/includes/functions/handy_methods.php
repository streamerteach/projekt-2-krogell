<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/paths.php'; 

/**
 * trying to fix my noob mistake
 * return a full web path for the given relative path.
 * url('assets/css/style.css') -> /~krogellh/nottinder/assets/css/style.css
 */
function url($path = '') {
    return BASE_URL . '/' . ltrim($path, '/');
}

/**
 * return a full web path for an image stored in the database.
 * fix old paths that start with '/nottinder/'.
 */
function imageUrl($db_path) {
    // Remove legacy /nottinder/ prefix if present
    if (strpos($db_path, '/nottinder/') === 0) {
        $db_path = substr($db_path, strlen('/nottinder'));
    }
    return url($db_path);
}

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function getVisitorCount() {
    require_once ROOT_PATH . '/includes/config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    // get unique visitors from database
    $query = "SELECT COUNT(DISTINCT 
              CASE WHEN username IS NOT NULL THEN username ELSE ip_address END) as count 
              FROM visitors 
              WHERE visit_time > DATE_SUB(NOW(), INTERVAL 24 HOUR)";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch();
    
    return $result['count'] ?? 0;
}

function logVisitor() {
    require_once ROOT_PATH . '/includes/config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    $username = $_SESSION['username'] ?? null;
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $query = "INSERT INTO visitors (username, ip_address, user_agent, visit_time) 
              VALUES (:username, :ip, :user_agent, NOW())";
    
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':username' => $username,
        ':ip' => $ip,
        ':user_agent' => $user_agent
    ]);
}

logVisitor();