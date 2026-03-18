<?php
require_once __DIR__ . '/../includes/functions/handy_methods.php';
require_once __DIR__ . '/../includes/functions/security.php';

// clear all session data
$_SESSION = array();

// destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// destroy the session
session_destroy();

// redirect to home page
header('Location: /nottinder/');
exit;
?>