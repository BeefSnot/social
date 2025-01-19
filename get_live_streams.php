<?php
include 'session_manager.php';

$servername = "localhost";
$username = "dynastyhosting_social";
$password = "d9Au7MmbqBJh5ucSz2kq";
$dbname = "dynastyhosting_social";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT l.*, u.username FROM live_streams l JOIN users u ON l.user_id = u.id WHERE l.is_live = TRUE";
$result = $conn->query($sql);

$live_streams = [];
while ($row = $result->fetch_assoc()) {
    $live_streams[] = $row;
}

$conn->close();

foreach ($live_streams as &$stream) {
    $stream['webrtc_server'] = 'ws://99.148.48.236:3000'; // Replace with your WebRTC server address
}

header('Content-Type: application/json');
echo json_encode($live_streams);
?>