<?php
include 'session_manager.php';

$servername = "localhost";
$username = "dynastyhosting_social"; // Change this to your MySQL username
$password = "d9Au7MmbqBJh5ucSz2kq"; // Change this to your MySQL password
$dbname = "dynastyhosting_social";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_info = [];
$videos = [];

$username = $_SESSION['username'];
$sql = "SELECT username, email, display_name, profile_picture FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $user_info = $result->fetch_assoc();
}
$stmt->close();

$sql_videos = "SELECT * FROM videos WHERE username = ? ORDER BY created_at DESC";
$stmt_videos = $conn->prepare($sql_videos);
$stmt_videos->bind_param("s", $username);
$stmt_videos->execute();
$result_videos = $stmt_videos->get_result();
while ($row = $result_videos->fetch_assoc()) {
    $videos[] = $row;
}
$stmt_videos->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Management</title>
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            background: #000;
            color: #fff;
        }
        .header {
            width: 100%;
            padding: 20px;
            background: #333;
            text-align: center;
        }
        .header h1 {
            margin: 0;
        }
        .profile {
            width: 100%;
            max-width: 600px;
            padding: 20px;
            box-sizing: border-box;
            text-align: center;
        }
        .profile img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-bottom: 20px;
        }
        .profile h2 {
            margin: 0;
            margin-bottom: 20px;
        }
        .profile p {
            margin: 0;
            margin-bottom: 20px;
        }
        .profile button {
            padding: 10px 20px;
            background: #0073e6;
            border: none;
            border-radius: 5px;
            color: #fff;
            cursor: pointer;
        }
        .profile button:hover {
            background: #005bb5;
        }
        .video-feed {
            flex: 1;
            width: 100%;
            max-width: 600px;
            overflow-y: auto;
            padding: 20px;
            box-sizing: border-box;
        }
        .video {
            background: #222;
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 10px;
        }
        .video video {
            width: 100%;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Account Management</h1>
    </div>
    <div class="profile">
        <img src="<?php echo isset($user_info['profile_picture']) ? htmlspecialchars($user_info['profile_picture']) : 'default_profile_picture.jpg'; ?>" alt="Profile Picture">
        <h2><?php echo isset($user_info['display_name']) ? htmlspecialchars($user_info['display_name']) : 'Display Name'; ?></h2>
        <p>Username: <?php echo isset($user_info['username']) ? htmlspecialchars($user_info['username']) : 'user123'; ?></p>
        <p>Email: <?php echo isset($user_info['email']) ? htmlspecialchars($user_info['email']) : 'user@example.com'; ?></p>
        <button onclick="editProfile()">Edit Profile</button>
        <button onclick="goHome()">Home</button>
    </div>
    <div class="video-feed" id="video-feed">
        <h2>Your Uploaded Videos</h2>
        <?php foreach ($videos as $video): ?>
            <div class="video">
                <video controls>
                    <source src="<?php echo htmlspecialchars($video['file_path']); ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
                <p><?php echo htmlspecialchars($video['title']); ?></p>
                <p><?php echo htmlspecialchars($video['description']); ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        function editProfile() {
            // Redirect to edit profile page
            window.location.href = 'edit_profile.php';
        }

        function goHome() {
            // Redirect to landing page
            window.location.href = 'landing.php';
        }
    </script>
</body>
</html>