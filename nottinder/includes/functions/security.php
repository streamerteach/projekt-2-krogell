<?php
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['error'] = "You must be logged in to access this page.";
        header('Location: /nottinder/pages/login/');
        exit;
    }
}

function hasRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

function requireRole($role) {
    if (!hasRole($role)) {
        $_SESSION['error'] = "You don't have permission to access this page.";
        header('Location: /nottinder/index.php');
        exit;
    }
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function isManager() {
    return isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'manager' || $_SESSION['user_role'] === 'admin');
}

function requireAdmin() {
    if (!isAdmin()) {
        $_SESSION['error'] = "You don't have permission to access this page.";
        header('Location: /nottinder/index.php');
        exit;
    }
}

function requireManager() {
    if (!isManager()) {
        $_SESSION['error'] = "You don't have permission to access this page.";
        header('Location: /nottinder/index.php');
        exit;
    }
}
?>