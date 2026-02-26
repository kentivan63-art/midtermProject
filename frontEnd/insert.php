<?php
// 1. Establish the connection to your XAMPP MySQL
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "midtermProject"; // This matches your phpMyAdmin screenshot

$conn = new mysqli($servername, $username, $password, $dbname);

// 2. Check if the connection works
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 3. Capture the data from the form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['user_name']; // Matches the 'name' attribute in your HTML input
    
    // Since your table 'users' requires an email (based on your SQL error screenshot)
    // we will set a placeholder or you can add an email field to your form.
    $email = "user@example.com"; 

    // 4. Prepare the SQL command
    $sql = "INSERT INTO users (username, email) VALUES ('$user', '$email')";

    if ($conn->query($sql) === TRUE) {
        echo "Successfully saved to database! <br>";
        echo "<a href='index.php'>Return to Home</a>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>