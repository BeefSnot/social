<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
    $query = $_POST['query'];
    
    // Search for users with similar usernames
    $sql = "SELECT id, username FROM users WHERE username LIKE ? AND verified = 1";
    $stmt = $conn->prepare($sql);
    $searchTerm = "%" . $query . "%";
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $users = [];
    while ($row = $result->fetch_assoc()) {
        // Get follower count for each user
        $followerSql = "SELECT COUNT(*) as follower_count FROM followers WHERE followed_id = ?";
        $followerStmt = $conn->prepare($followerSql);
        $followerStmt->bind_param("i", $row['id']);
        $followerStmt->execute();
        $followerResult = $followerStmt->get_result();
        $followerCount = $followerResult->fetch_assoc()['follower_count'];
        
        $users[] = [
            'id' => $row['id'],
            'username' => $row['username'],
            'followers' => $followerCount
        ];
        
        $followerStmt->close();
    }
    
    $stmt->close();
    
    // Return results as JSON
    header('Content-Type: application/json');
    echo json_encode($users);
}

$conn->close();
?>