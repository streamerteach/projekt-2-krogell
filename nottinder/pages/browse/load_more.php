<?php
require_once '../../includes/functions/handy_methods.php';
require_once '../../includes/functions/security.php';
require_once '../../includes/config/database.php';

header('Content-Type: application/json');

// get parameters
$page = (int)($_GET['page'] ?? 2);
$preference = $_GET['preference'] ?? 'any';
$min_salary = (float)($_GET['min_salary'] ?? 0);
$sort_by = $_GET['sort_by'] ?? 'newest';
$limit = 5;
$offset = ($page - 1) * $limit;

$database = new Database();
$db = $database->getConnection();

$query = "SELECT u.*, 
          (SELECT image_path FROM user_images WHERE user_id = u.id AND is_profile = 1 LIMIT 1) as profile_image,
          (SELECT COUNT(*) FROM likes WHERE to_user_id = u.id) as like_count";

if (isLoggedIn()) {
    $query .= ", (SELECT COUNT(*) FROM likes WHERE to_user_id = u.id AND from_user_id = :current_user) as user_liked";
} else {
    $query .= ", 0 as user_liked";
}

$query .= " FROM users u WHERE u.is_active = 1";

$params = [];

if (isLoggedIn()) {
    $query .= " AND u.id != :current_user";
    $params[':current_user'] = $_SESSION['user_id'];
}

if ($preference !== 'any') {
    $query .= " AND (u.preference = :preference OR u.preference = 'any')";
    $params[':preference'] = $preference;
}

if ($min_salary > 0) {
    $query .= " AND u.annual_salary >= :min_salary";
    $params[':min_salary'] = $min_salary;
}

switch($sort_by) {
    case 'salary_high': $query .= " ORDER BY u.annual_salary DESC"; break;
    case 'salary_low':  $query .= " ORDER BY u.annual_salary ASC"; break;
    case 'likes':       $query .= " ORDER BY like_count DESC"; break;
    case 'newest':
    default:            $query .= " ORDER BY u.created_at DESC";
}

$query .= " LIMIT :limit OFFSET :offset";

$stmt = $db->prepare($query);
foreach($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$profiles = $stmt->fetchAll();

// if no more profiles to see, return empty array
if (empty($profiles)) {
    echo json_encode([]);
    exit;
}

// generate html for each profile using profile_card template
ob_start();
foreach ($profiles as $profile) {
    include '../../templates/profile_card.php';
}
$html = ob_get_clean();

echo json_encode([
    'html' => $html,
    'next_page' => $page + 1,
    'has_more' => count($profiles) === $limit
]);