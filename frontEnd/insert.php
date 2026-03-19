<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "midtermProject";

$conn = new mysqli($servername, $username, $password, $dbname);

// CHECK IF CONNECTION WAS SUCCESSFUL
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// CHECK IF THE FORM WAS SUBMITTED
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['user_name']; // MATCHES THE NAME ATTRIBUTE IN YOUR FORM INPUT

    // WE WILL USE A FIXED EMAIL FOR DEMONSTRATION PURPOSES, AS THE FORM ONLY HAS A USERNAME FIELD
    $email = "user@example.com"; 

    // PREPARE THE SQL COMMAND
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