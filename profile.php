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

$user_id = $_GET['id'];
$current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Get user info
$sql = "SELECT username FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    die("User not found");
}

// Get follower count
$sql = "SELECT COUNT(*) as count FROM followers WHERE followed_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$followers = $result->fetch_assoc()['count'];
$stmt->close();

// Check if current user is following
$is_following = false;
if ($current_user_id) {
    $sql = "SELECT * FROM followers WHERE follower_id = ? AND followed_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $current_user_id, $user_id);
    $stmt->execute();
    $is_following = $stmt->get_result()->num_rows > 0;
    $stmt->close();
}

// Get user's videos
$sql = "SELECT * FROM videos WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$videos = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user['username']); ?>'s Profile</title>
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .profile-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .follow-button, .message-button {
            padding: 10px 20px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 5px;
        }
        .follow-button:hover, .message-button:hover {
            background: #0056b3;
        }
        .following {
            background: #28a745;
        }
        .video-grid {
            display: grid;
            gap: 20px;
            margin-top: 20px;
        }
        .video-item {
            background: #fff;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }
        .video-item video {
            width: 100%;
            border-radius: 4px;
        }
        .messaging {
            margin-top: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .messages {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 10px;
        }
        .message {
            padding: 10px;
            margin: 5px 0;
            border-radius: 4px;
        }
        .message.sent {
            background: #007bff;
            color: #fff;
            margin-left: 20%;
        }
        .message.received {
            background: #e9ecef;
            margin-right: 20%;
        }
        .message .sender {
            font-weight: bold;
        }
        .message .timestamp {
            font-size: 0.8em;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="profile-header">
            <h1><?php echo htmlspecialchars($user['username']); ?></h1>
            <p>Followers: <?php echo $followers; ?></p>
            <?php if ($current_user_id && $current_user_id != $user_id): ?>
                <button 
                    onclick="toggleFollow(<?php echo $user_id; ?>)" 
                    class="follow-button <?php echo $is_following ? 'following' : ''; ?>"
                >
                    <?php echo $is_following ? 'Following' : 'Follow'; ?>
                </button>
                <button 
                    onclick="showMessageForm()" 
                    class="message-button"
                >
                    Message
                </button>
            <?php elseif (!$current_user_id): ?>
                <p><a href="login.php">Log in to follow or message</a></p>
            <?php endif; ?>
        </div>

        <div class="video-grid">
            <?php while ($video = $videos->fetch_assoc()): ?>
                <div class="video-item">
                    <video controls>
                        <source src="<?php echo htmlspecialchars($video['file_path']); ?>" type="video/mp4">
                    </video>
                    <p><?php echo htmlspecialchars($video['description']); ?></p>
                </div>
            <?php endwhile; ?>
        </div>

        <?php if ($current_user_id && $current_user_id != $user_id): ?>
            <div class="messaging" id="messaging" style="display: none;">
                <h3>Messages</h3>
                <div class="messages" id="messages"></div>
                <form id="message-form">
                    <input type="hidden" name="receiver_id" value="<?php echo $user_id; ?>">
                    <textarea name="message" required></textarea>
                    <button type="submit">Send</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function toggleFollow(userId) {
            const isFollowing = document.querySelector('.follow-button').classList.contains('following');
            const url = isFollowing ? 'unfollow_user.php' : 'follow_user.php';
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'user_id=' + userId
            })
            .then(response => response.text())
            .then(() => {
                location.reload();
            });
        }

        function showMessageForm() {
            document.getElementById('messaging').style.display = 'block';
        }

        function loadMessages() {
            const receiverId = document.querySelector('input[name="receiver_id"]').value;
            fetch(`get_messages.php?receiver_id=${receiverId}`)
                .then(response => response.json())
                .then(data => {
                    const messagesContainer = document.getElementById('messages');
                    messagesContainer.innerHTML = '';
                    data.forEach(message => {
                        const messageElement = document.createElement('div');
                        messageElement.classList.add('message');
                        messageElement.classList.add(message.sender_id == <?php echo $current_user_id; ?> ? 'sent' : 'received');
                        messageElement.innerHTML = `
                            <div class="sender">${message.sender_name}</div>
                            <div class="timestamp">${new Date(message.timestamp).toLocaleString()}</div>
                            <div class="content">${message.message}</div>
                        `;
                        messagesContainer.appendChild(messageElement);
                    });
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                });
        }

        if (document.getElementById('message-form')) {
            document.getElementById('message-form').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                fetch('send_message.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(() => {
                    this.reset();
                    loadMessages();
                });
            });

            loadMessages();
            setInterval(loadMessages, 5000);
        }
    </script>
</body>
</html>