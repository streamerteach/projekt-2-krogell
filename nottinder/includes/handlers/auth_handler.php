<?php
// add error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../functions/handy_methods.php';
require_once __DIR__ . '/../functions/security.php';

// log that the file was accessed
error_log("auth_handler.php accessed - " . date('Y-m-d H:i:s'));

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$action = $_POST['action'] ?? '';
error_log("Action: " . $action);

switch($action) {
    case 'login':
        handleLogin();
        break;
    case 'register':
        handleRegister();
        break;
    default:
        $response['message'] = 'Invalid action';
        echo json_encode($response);
}

function handleLogin() {
    global $response;
    
    error_log("Handling login for user: " . ($_POST['username'] ?? 'unknown'));
    
    // verify csrf token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $response['message'] = 'Invalid security token';
        error_log("CSRF token invalid");
        echo json_encode($response);
        exit;
    }
    
    $username = test_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $response['message'] = 'Please fill in all fields';
        echo json_encode($response);
        exit;
    }
    
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT id, username, password_hash, role, is_active FROM users WHERE username = :username AND is_active = 1";
        $stmt = $db->prepare($query);
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            
            $response['success'] = true;
            $response['message'] = 'Login successful!';
            $response['redirect'] = BASE_URL . '/pages/home/';
            error_log("Login successful for user: " . $username);
        } else {
            $response['message'] = 'Invalid username or password';
            error_log("Login failed for user: " . $username);
        }
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        $response['message'] = 'Database error occurred';
    }
    
    echo json_encode($response);
}

function handleRegister() {
    global $response;
    
    error_log("Handling registration for user: " . ($_POST['username'] ?? 'unknown'));
    
    // verify csrf token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $response['message'] = 'Invalid security token';
        echo json_encode($response);
        exit;
    }
    
    // validate all required fields
    $required_fields = ['username', 'email', 'full_name', 'city', 'age', 'bio', 'annual_salary', 'preference', 'password', 'confirm_password'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $response['message'] = 'All fields are required';
            echo json_encode($response);
            exit;
        }
    }
    
    $username = test_input($_POST['username']);
    $email = test_input($_POST['email']);
    $full_name = test_input($_POST['full_name']);
    $city = test_input($_POST['city']);
    $age = (int)$_POST['age'];
    $bio = test_input($_POST['bio']);
    $annual_salary = (float)$_POST['annual_salary'];
    $preference = test_input($_POST['preference']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid email format';
        echo json_encode($response);
        exit;
    }
    
    // validate age
    if ($age < 18 || $age > 120) {
        $response['message'] = 'Age must be between 18 and 120';
        echo json_encode($response);
        exit;
    }
    
    // validate password
    if (strlen($password) < 8) {
        $response['message'] = 'Password must be at least 8 characters';
        echo json_encode($response);
        exit;
    }
    
    if ($password !== $confirm_password) {
        $response['message'] = 'Passwords do not match';
        echo json_encode($response);
        exit;
    }
    
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        // check if username exists
        $check_query = "SELECT id FROM users WHERE username = :username OR email = :email";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->execute([':username' => $username, ':email' => $email]);
        
        if ($check_stmt->fetch()) {
            $response['message'] = 'Username or email already exists';
            echo json_encode($response);
            exit;
        }
        
        // hash password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // insert user
        $query = "INSERT INTO users (username, email, password_hash, full_name, city, age, bio, annual_salary, preference, created_at) 
                  VALUES (:username, :email, :password, :full_name, :city, :age, :bio, :salary, :preference, NOW())";
        
        $stmt = $db->prepare($query);
        $success = $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password' => $password_hash,
            ':full_name' => $full_name,
            ':city' => $city,
            ':age' => $age,
            ':bio' => $bio,
            ':salary' => $annual_salary,
            ':preference' => $preference
        ]);
        
        if ($success) {
            $response['success'] = true;
            $response['message'] = 'Registration successful! You can now log in.';
            $response['redirect'] = BASE_URL . '/pages/login/?form=login';
            error_log("Registration successful for user: " . $username);
        } else {
            $response['message'] = 'Registration failed. Please try again.';
            error_log("Registration failed for user: " . $username);
        }
    } catch (Exception $e) {
        error_log("Registration error: " . $e->getMessage());
        $response['message'] = 'Database error occurred';
    }
    
    echo json_encode($response);
}
?>