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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $follower_id = $_SESSION['user_id'];
    $followed_id = $_POST['user_id'];

    $sql = "INSERT INTO followers (follower_id, followed_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $follower_id, $followed_id);

    if ($stmt->execute()) {
        echo "You are now following this user!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>