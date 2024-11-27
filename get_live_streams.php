<?php
include 'session_manager.php';

$servername = "localhost";
$username = "dynastyhosting_social";
$password = "d9Au7MmbqBJh5ucSz2kq";
$dbname = "dynastyhosting_social";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch live streams from the database
$sql = "SELECT l.*, u.username FROM live_streams l JOIN users u ON l.user_id = u.id WHERE l.is_live = TRUE";
$result = $conn->query($sql);

$live_streams = [];
while ($row = $result->fetch_assoc()) {
    $live_streams[] = $row;
}

// Close the database connection
$conn->close();

// Provide the necessary information for the client to connect to the WebRTC server
foreach ($live_streams as &$stream) {
    $stream['webrtc_server'] = 'ws://192.99.9.164:3000'; // Replace with your WebRTC server address
}

header('Content-Type: application/json');
echo json_encode($live_streams);
?>