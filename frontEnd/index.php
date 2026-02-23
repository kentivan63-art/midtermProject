<!DOCTYPE html>
<html>
<head>
    <title>My PHP Website</title>
</head>
<body>
    <h1>Welcome to My Site</h1>

    <?php
    // You can write PHP right here in the middle of your HTML!
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "simple_web";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        echo "<p style='color:red;'>Database offline.</p>";
    } else {
        echo "<p style='color:green;'>Database connected successfully!</p>";
    }

    if (isset($_POST['user_name'])) {
    $user = $_POST['user_name'];
    $sql = "INSERT INTO users (username) VALUES ('$user')";
    
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color:blue;'>Saved: " . $user . "</p>";
    }
}
    ?>

    <form action="insert.php" method="POST">
        <input type="text" name="user_name" placeholder="Enter Name">
        <button type="submit">Submit</button>
    </form>
</body>
</html>