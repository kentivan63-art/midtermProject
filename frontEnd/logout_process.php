<?php
session_start();

// CLEAR ALL SESSION VARIABLES
$_SESSION = [];

// DESTROY THE SESSION
session_destroy();

// Optional: DELETE THE SESSION COOKIE
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// REDIRECT TO HOMEPAGE OR LOGIN PAGE
header("Location: index.php");
exit;