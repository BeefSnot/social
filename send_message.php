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
    $sender_id = $_SESSION['user_id'];
    $receiver_id = $_POST['receiver_id'];
    $message = $_POST['message'];
    $timestamp = date('Y-m-d H:i:s');

    $sql = "INSERT INTO messages (sender_id, receiver_id, message, timestamp) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiss", $sender_id, $receiver_id, $message, $timestamp);

    if ($stmt->execute()) {
        echo "Message sent successfully";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>