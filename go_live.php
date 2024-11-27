<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

$sql = "SELECT * FROM videos ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KELP Social - For You</title>
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
            text-align: right;
            padding: 10px;
            background: #333;
            width: 100%;
            box-sizing: border-box;
        }
        .profile p {
            margin: 0;
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
        .footer {
            width: 100%;
            padding: 20px;
            background: #333;
            text-align: center;
        }
        .footer button {
            padding: 10px 20px;
            margin: 0 10px;
            background: #0073e6;
            border: none;
            border-radius: 5px;
            color: #fff;
            cursor: pointer;
        }
        .footer button:hover {
            background: #005bb5;
        }
        .like-comment {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }
        .like-comment button {
            background: #0073e6;
            border: none;
            border-radius: 5px;
            color: #fff;
            cursor: pointer;
            padding: 5px 10px;
        }
        .like-comment button:hover {
            background: #005bb5;
        }
        .comments {
            margin-top: 10px;
        }
        .comments p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>For You</h1>
    </div>
    <div class="profile">
        <?php if (isset($_SESSION['username'])): ?>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
            <p>Email: <?php echo htmlspecialchars($_SESSION['email']); ?></p>
        <?php else: ?>
            <p><a href="index.html">Login</a></p>
        <?php endif; ?>
    </div>
    <div class="video-feed" id="video-feed">
        <?php while($row = $result->fetch_assoc()): ?>
            <div class="video">
                <video controls>
                    <source src="<?php echo htmlspecialchars($row['file_path']); ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
                <p><?php echo htmlspecialchars($row['title']); ?></p>
                <p><?php echo htmlspecialchars($row['description']); ?></p>
                <div class="like-comment">
                    <button onclick="likeVideo(<?php echo $row['id']; ?>)">Like</button>
                    <button onclick="showCommentBox(<?php echo $row['id']; ?>)">Comment</button>
                </div>
                <div class="comments" id="comments-<?php echo $row['id']; ?>">
                    <!-- Placeholder for comments -->
                </div>
                <div class="form-group" id="comment-box-<?php echo $row['id']; ?>" style="display: none;">
                    <input type="text" id="comment-input-<?php echo $row['id']; ?>" placeholder="Add a comment">
                    <button onclick="postComment(<?php echo $row['id']; ?>)">Post</button>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
    <div class="footer">
        <button onclick="goLive()">Go Live</button>
        <button onclick="uploadVideo()">Upload Video</button>
        <button onclick="manageAccount()">Account Management</button>
    </div>

    <script>
        function goLive() {
            // Redirect to go live page
            window.location.href = 'go_live.html';
        }

        function uploadVideo() {
            // Redirect to upload video page
            window.location.href = 'upload_video.html';
        }

        function manageAccount() {
            // Redirect to account management page
            window.location.href = 'account_management.html';
        }

        function likeVideo(videoId) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'like_video.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    alert('Video liked!');
                } else {
                    alert('An error occurred while liking the video.');
                }
            };
            xhr.send(`video_id=${videoId}`);
        }

        function showCommentBox(videoId) {
            const commentBox = document.getElementById(`comment-box-${videoId}`);
            commentBox.style.display = 'block';
        }

        function postComment(videoId) {
            const commentInput = document.getElementById(`comment-input-${videoId}`);
            const comment = commentInput.value;
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'post_comment.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    const commentsDiv = document.getElementById(`comments-${videoId}`);
                    const newComment = document.createElement('p');
                    newComment.textContent = `${xhr.responseText}: ${comment}`;
                    commentsDiv.appendChild(newComment);
                    commentInput.value = '';
                } else {
                    alert('An error occurred while posting the comment.');
                }
            };
            xhr.send(`video_id=${videoId}&comment=${encodeURIComponent(comment)}`);
        }
    </script>
</body>
</html>