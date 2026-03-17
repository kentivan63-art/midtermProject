<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("../config/db.php");

if (!isset($_SESSION["user_id"])) {
    return;
}

$userID = $_SESSION["user_id"];

$sql = "
SELECT songs.title, songs.artist
FROM listeninghistory
JOIN songs ON listeninghistory.songID = songs.id
WHERE listeninghistory.userID = ?
ORDER BY listeninghistory.timestamp 
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
?>

<br>
<section class="panel">
<div class="panel-title">Recently Played</div>
<br>

<?php
if ($result->num_rows > 0) {
    $num = 1;

    while ($row = $result->fetch_assoc()) {
        echo $num . ". " . htmlspecialchars($row["title"]) . " — " . htmlspecialchars($row["artist"]) . "<br>";
        $num++;
    }
} else {
    echo "No recently played songs yet.";
}
?>

</section>