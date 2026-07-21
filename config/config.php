<?php
/**
 * Centralized Configuration File
 * Loads environment variables and provides database connection
 */

// Load environment variables from .env file
function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse KEY=VALUE format
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes if present
            if (preg_match('/^(["\'])(.*)\1$/', $value, $matches)) {
                $value = $matches[2];
            }
            
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

// Load .env file
loadEnv(__DIR__ . '/../.env');

// Database configuration
function getDbConnection() {
    $servername = getenv('DB_HOST') ?: '127.0.0.1';
    $username = getenv('DB_USERNAME') ?: 'root';
    $password = getenv('DB_PASSWORD') ?: '';
    $dbname = getenv('DB_NAME') ?: 'midtermProject';
    
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        return null;
    }
    
    $conn->set_charset("utf8mb4");
    return $conn;
}

// Email configuration
function getMailConfig() {
    return [
        'host' => getenv('MAIL_HOST') ?: 'smtp.gmail.com',
        'port' => getenv('MAIL_PORT') ?: 465,
        'username' => getenv('MAIL_USERNAME') ?: '',
        'password' => getenv('MAIL_PASSWORD') ?: '',
        'from_name' => getenv('MAIL_FROM_NAME') ?: 'Groovify'
    ];
}

// Session configuration
function getSessionTimeout() {
    return (int)(getenv('SESSION_TIMEOUT') ?: 300);
}

// Environment check
function isDevelopment() {
    return (getenv('ENVIRONMENT') ?: 'development') === 'development';
}

// Error reporting based on environment
if (isDevelopment()) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
?>