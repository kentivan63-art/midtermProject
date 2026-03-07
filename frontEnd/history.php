<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once("../config/db.php");

$userID = $_SESSION["user_id"];

$stmt = $conn->prepare("
    SELECT s.title, s.artist, lh.timestamp
    FROM listeninghistory lh
    JOIN songs s ON lh.songID = s.id
    WHERE lh.userID = ?
    ORDER BY lh.timestamp DESC
    LIMIT 50
");
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
?>

<h2>Your Listening History</h2>
<table>
    <tr>
        <th>Song</th>
        <th>Artist</th>
        <th>Played At</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?php echo htmlspecialchars($row['title']); ?></td>
        <td><?php echo htmlspecialchars($row['artist']); ?></td>
        <td><?php echo $row['timestamp']; ?></td>
    </tr>
    <?php endwhile; ?>
</table>