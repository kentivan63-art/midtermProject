<?php
/**
 * Database Connection
 * Uses centralized configuration
 */

require_once __DIR__ . '/config.php';

$conn = getDbConnection();

if ($conn === null) {
    if (isDevelopment()) {
        exit("Database connection failed. Check your .env configuration.");
    } else {
        exit("Database connection failed.");
    }
}
?>