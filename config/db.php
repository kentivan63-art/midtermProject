<?php

$servername = "127.0.0.1";   // better than localhost in Windows
$username   = "root";
$password   = "";
$dbname     = "midtermProject";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    exit("Database connection failed.");
}

?>