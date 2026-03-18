<?php
require_once '../../includes/functions/handy_methods.php';
require_once '../../includes/functions/security.php';
require_once '../../includes/config/database.php';
include '../../templates/header.php';

$database = new Database();
$db = $database->getConnection();

// get filter parameters
$preference = $_GET['preference'] ?? 'any';
$min_salary = (float)($_GET['min_salary'] ?? 0);
$sort_by = $_GET['sort_by'] ?? 'newest';
$page = (int)($_GET['page'] ?? 1);
$limit = 5;
$offset = ($page - 1) * $limit;

// build base query
$query = "SELECT u.*, 
          (SELECT image_path FROM user_images WHERE user_id = u.id AND is_profile = 1 LIMIT 1) as profile_image,
          (SELECT COUNT(*) FROM likes WHERE to_user_id = u.id) as like_count";

// if logged in, add whether current user liked this profile
if (isLoggedIn()) {
    $query .= ", (SELECT COUNT(*) FROM likes WHERE to_user_id = u.id AND from_user_id = :current_user) as user_liked";
} else {
    $query .= ", 0 as user_liked";
}

$query .= " FROM users u WHERE u.is_active = 1";

$params = [];

// exclude current user if logged in
if (isLoggedIn()) {
    $query .= " AND u.id != :current_user";
    $params[':current_user'] = $_SESSION['user_id'];
}

// apply filters
if ($preference !== 'any') {
    $query .= " AND (u.preference = :preference OR u.preference = 'any')";
    $params[':preference'] = $preference;
}

if ($min_salary > 0) {
    $query .= " AND u.annual_salary >= :min_salary";
    $params[':min_salary'] = $min_salary;
}

// apply sorting
switch ($sort_by) {
    case 'salary_high':
        $query .= " ORDER BY u.annual_salary DESC";
        break;
    case 'salary_low':
        $query .= " ORDER BY u.annual_salary ASC";
        break;
    case 'likes':
        $query .= " ORDER BY like_count DESC";
        break;
    case 'newest':
    default:
        $query .= " ORDER BY u.created_at DESC";
}

// add pagination
$query .= " LIMIT :limit OFFSET :offset";

$stmt = $db->prepare($query);

// bind parameters
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$profiles = $stmt->fetchAll();

// get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM users u WHERE u.is_active = 1";
if (isLoggedIn()) {
    $count_query .= " AND u.id != " . $_SESSION['user_id'];
}
$count_stmt = $db->query($count_query);
$total_profiles = $count_stmt->fetch()['total'];
$total_pages = ceil($total_profiles / $limit);
?>

<body>
    <?php include '../../templates/nav.php'; ?>

    <main>
        <section class="browse-section">
            <div class="browse-container">
                <!-- filter sidebar -->
                <aside class="filter-sidebar">
                    <h3>Filter & Sorting</h3>

                    <form id="filter-form" method="GET" class="filter-form">
                        <div class="form-group">
                            <label for="preference">Interested in:</label>
                            <select name="preference" id="preference">
                                <option value="any" <?php echo $preference == 'any' ? 'selected' : ''; ?>>Any</option>
                                <option value="men" <?php echo $preference == 'men' ? 'selected' : ''; ?>>Men</option>
                                <option value="women" <?php echo $preference == 'women' ? 'selected' : ''; ?>>Women</option>
                                <option value="other" <?php echo $preference == 'other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="min_salary">Annual salary (EUR):</label>
                            <input type="number" name="min_salary" id="min_salary" step="10000" min="0"
                                value="<?php echo htmlspecialchars($min_salary); ?>">
                        </div>

                        <div class="form-group">
                            <label for="sort_by">Sort by:</label>
                            <select name="sort_by" id="sort_by">
                                <option value="newest" <?php echo $sort_by == 'newest' ? 'selected' : ''; ?>>Newest</option>
                                <option value="salary_high" <?php echo $sort_by == 'salary_high' ? 'selected' : ''; ?>>Highest salary</option>
                                <option value="salary_low" <?php echo $sort_by == 'salary_low' ? 'selected' : ''; ?>>Lowest salary</option>
                                <option value="likes" <?php echo $sort_by == 'likes' ? 'selected' : ''; ?>>Most liked</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">Apply</button>
                        <a href="?" class="btn btn-secondary">Reset</a>
                    </form>
                </aside>

                <!-- profiles container -->
                <main class="profiles-container" id="profiles-container">
                    <?php if (empty($profiles)): ?>
                        <div class="no-profiles">
                            <p>No profiles match your filters.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($profiles as $profile): ?>
                            <?php include '../../templates/profile_card.php'; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </main>

                <!-- loading indicator -->
                <div id="loading" style="display: none;" class="loading-indicator">
                    <div class="spinner"></div>
                    <p>Loading more profiles...</p>
                </div>

                <!-- pass filter parameters to js -->
                <script>
                    window.currentPage = <?php echo $page; ?>;
                    window.totalPages = <?php echo $total_pages; ?>;
                    window.filterParams = {
                        preference: '<?php echo htmlspecialchars($preference); ?>',
                        min_salary: <?php echo $min_salary; ?>,
                        sort_by: '<?php echo htmlspecialchars($sort_by); ?>'
                    };
                </script>
            </div>
        </section>
    </main>

    <?php include '../../templates/footer.php'; ?>
    <script src="<?php echo url('assets/js/main.js'); ?>"></script>
</body>

</html>