<?php
/**
 * Centralized Session Management
 * Handles session initialization, timeout, and security
 */

require_once __DIR__ . '/config.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Configure session security settings
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
    
    session_start();
}

// Session timeout handling
$timeout = getSessionTimeout();

if (isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > $timeout) {
        // Session expired
        session_unset();
        session_destroy();
        header("Location: login.php?error=timeout");
        exit;
    }
}

// Update last activity time
$_SESSION['last_activity'] = time();

// Check if user is logged in
function requireLogin() {
    if (!isset($_SESSION["userID"])) {
        header("Location: login.php");
        exit;
    }
}

// Get current user ID
function getCurrentUserID() {
    return $_SESSION["userID"] ?? null;
}

// Regenerate session ID (call after login)
function regenerateSession() {
    session_regenerate_id(true);
}

// Set session variables after successful login
function setLoginSession($userID, $fullname, $email) {
    $_SESSION["userID"] = $userID;
    $_SESSION["fullname"] = $fullname;
    $_SESSION["email"] = $email;
    $_SESSION['last_activity'] = time();
    regenerateSession();
}

// Destroy session (logout)
function destroySession() {
    session_unset();
    session_destroy();
    
    // Delete session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
}
?>