<?php
require_once '../../includes/functions/handy_methods.php';
require_once '../../includes/functions/security.php';
require_once '../../includes/config/database.php';

requireLogin();

$database = new Database();
$db = $database->getConnection();

// determine which user to edit
$target_user_id = $_SESSION['user_id']; // default to self

// if user is manager/admin and a user_id is provided in URL, allow editing that user
if (isManager() && isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
    $target_user_id = (int)$_GET['user_id'];
}

// fetch the target user's data
$query = "SELECT * FROM users WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->execute([':id' => $target_user_id]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['error'] = "User not found.";
    header('Location: ' . url('pages/profile/'));
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "Invalid security token.";
    } else {
        $full_name = test_input($_POST['full_name'] ?? '');
        $city = test_input($_POST['city'] ?? '');
        $age = (int)($_POST['age'] ?? 0);
        $bio = test_input($_POST['bio'] ?? '');
        $annual_salary = (float)($_POST['annual_salary'] ?? 0);
        $preference = test_input($_POST['preference'] ?? 'any');

        if ($age < 18) {
            $error = "You must be at least 18 years old.";
        } elseif (empty($full_name) || empty($city) || empty($bio)) {
            $error = "Please fill in all fields.";
        } else {
            $update_query = "UPDATE users SET 
                full_name = :full_name,
                city = :city,
                age = :age,
                bio = :bio,
                annual_salary = :salary,
                preference = :preference,
                updated_at = NOW()
                WHERE id = :id";

            $update_stmt = $db->prepare($update_query);
            $success = $update_stmt->execute([
                ':full_name' => $full_name,
                ':city' => $city,
                ':age' => $age,
                ':bio' => $bio,
                ':salary' => $annual_salary,
                ':preference' => $preference,
                ':id' => $target_user_id
            ]);

            if ($success) {
                $_SESSION['message'] = "Profile updated successfully!";
                // if editing another user (e.g. from admin panel), go back to user list
                if (isset($_GET['user_id'])) {
                    header("Location: " . url('pages/admin/users.php'));
                } else {
                    header("Location: " . url('pages/profile/'));
                }
                exit;
            } else {
                $error = "Update failed. Please try again.";
            }
        }
    }
}

include '../../templates/header.php';
?>

<body>
    <?php include '../../templates/nav.php'; ?>

    <main>
        <section>
            <article class="profile-article">
                <div class="profile-edit-container">
                    <h2>Edit Profile <?php echo ($target_user_id != $_SESSION['user_id']) ? 'for ' . htmlspecialchars($user['username']) : ''; ?></h2>

                    <?php if ($error): ?>
                        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form method="POST" class="profile-form" action="<?php echo isset($_GET['user_id']) ? '?user_id=' . $_GET['user_id'] : ''; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

                        <div class="form-group">
                            <label for="full_name">Full Name:</label>
                            <input type="text" id="full_name" name="full_name"
                                value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="city">City:</label>
                            <input type="text" id="city" name="city"
                                value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="age">Age:</label>
                            <input type="number" id="age" name="age" min="18" max="120"
                                value="<?php echo htmlspecialchars($user['age'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="bio">About Me:</label>
                            <textarea id="bio" name="bio" rows="5" required><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                        </div>

                        <?php
                        $salary = $user['annual_salary'] ?? 50000;
                        $selected_15000 = ($salary <= 30000) ? 'selected' : '';
                        $selected_40000 = ($salary > 30000 && $salary <= 50000) ? 'selected' : '';
                        $selected_75000 = ($salary > 50000 && $salary <= 100000) ? 'selected' : '';
                        $selected_150000 = ($salary > 100000) ? 'selected' : '';
                        ?>
                        <div class="form-group">
                            <label for="annual_salary">Annual salary:</label>
                            <select id="annual_salary" name="annual_salary" required>
                                <option value="">Select salary range...</option>
                                <option value="15000" <?php echo $selected_15000; ?>>Under 30 000 €</option>
                                <option value="40000" <?php echo $selected_40000; ?>>30 000 – 50 000 €</option>
                                <option value="75000" <?php echo $selected_75000; ?>>50 000 – 100 000 €</option>
                                <option value="150000" <?php echo $selected_150000; ?>>Över 100 000 €</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="preference">Looking for:</label>
                            <select id="preference" name="preference" required>
                                <option value="men" <?php echo ($user['preference'] ?? '') == 'men' ? 'selected' : ''; ?>>Men</option>
                                <option value="women" <?php echo ($user['preference'] ?? '') == 'women' ? 'selected' : ''; ?>>Women</option>
                                <option value="other" <?php echo ($user['preference'] ?? '') == 'other' ? 'selected' : ''; ?>>Other</option>
                                <option value="any" <?php echo ($user['preference'] ?? '') == 'any' ? 'selected' : ''; ?>>Any</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">Update Profile</button>
                        <a href="<?php echo isset($_GET['user_id']) ? url('pages/admin/users.php') : url('pages/profile/'); ?>" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </article>
        </section>
    </main>

    <?php include '../../templates/footer.php'; ?>
</body>

</html>