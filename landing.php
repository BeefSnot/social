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

$sql_videos = "SELECT v.*, u.username FROM videos v JOIN users u ON v.user_id = u.id ORDER BY v.created_at DESC";
$result_videos = $conn->query($sql_videos);

$sql_live = "SELECT l.*, u.username FROM live_streams l JOIN users u ON l.user_id = u.id WHERE l.is_live = TRUE";
$result_live = $conn->query($sql_live);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lumi Social - For You</title>
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: #f4f4f4;
            color: #333;
        }
        .header {
            width: 100%;
            padding: 20px;
            background: #007bff;
            color: #fff;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .header h1 {
            margin: 0;
        }
        .profile {
            text-align: right;
            padding: 10px;
            background: #007bff;
            color: #fff;
            width: 100%;
            box-sizing: border-box;
            border-radius: 0 0 8px 8px;
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
        .video, .live-stream {
            background: #fff;
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .video video, .live-stream video {
            width: 100%;
            border-radius: 8px;
            autoplay: true;
            muted: true;
            playsinline: true;
        }
        .footer {
            width: 100%;
            padding: 20px;
            background: #007bff;
            text-align: center;
            border-radius: 0 0 8px 8px;
        }
        .footer button {
            padding: 10px 20px;
            margin: 0 10px;
            background: #005bb5;
            border: none;
            border-radius: 5px;
            color: #fff;
            cursor: pointer;
        }
        .footer button:hover {
            background: #004494;
        }
        .messaging {
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .messages {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 20px;
        }
        .message {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 8px;
            background: #f4f4f4;
        }
        .message.sent {
            background: #007bff;
            color: #fff;
        }
        .message.received {
            background: #e4e4e4;
        }
        .notifications {
            position: fixed;
            top: 10px;
            right: 10px;
            background: #007bff;
            color: #fff;
            padding: 10px;
            border-radius: 8px;
            display: none;
        }
        .search-button {
            padding: 10px 20px;
            background: #005bb5;
            border: none;
            border-radius: 5px;
            color: #fff;
            cursor: pointer;
            margin-top: 10px;
        }

        .search-button:hover {
            background: #004494;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>For You</h1>
        <button onclick="window.location.href='search.php'" class="search-button">Search Users</button>
    </div>
    <div class="profile">
        <?php if (isset($_SESSION['username'])): ?>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
        <?php else: ?>
            <p><a href="index.html" style="color: #fff;">Login</a></p>
        <?php endif; ?>
    </div>
    <div class="video-feed" id="video-feed">
        <h2>Live Streams</h2>
        <?php while ($row = $result_live->fetch_assoc()): ?>
            <div class="live-stream">
                <video id="liveVideo_<?php echo $row['id']; ?>" autoplay muted playsinline></video>
                <p>Live by: <?php echo htmlspecialchars($row['username']); ?></p>
            </div>
        <?php endwhile; ?>
        <h2>Uploaded Videos</h2>
        <?php while ($row = $result_videos->fetch_assoc()): ?>
            <div class="video">
                <video autoplay muted playsinline>
                    <source src="http://your.webrtc.server.ip/path/to/webrtc/uploads/<?php echo htmlspecialchars(basename($row['file_path'])); ?>" type="video/mp4">
                </video>
                <p><?php echo htmlspecialchars($row['description']); ?></p>
                <p>Uploaded by: <?php echo htmlspecialchars($row['username']); ?></p>
            </div>
        <?php endwhile; ?>
    </div>
    <div class="messaging">
        <h2>Direct Messaging</h2>
        <div class="messages" id="messages"></div>
        <form id="message-form">
            <input type="hidden" name="receiver_id" value="2"> <!-- Replace with dynamic receiver ID -->
            <textarea name="message" placeholder="Type your message..." required></textarea>
            <button type="submit">Send</button>
        </form>
    </div>
    <div class="footer">
        <button onclick="goLive()">Go Live</button>
        <button onclick="uploadVideo()">Upload Video</button>
        <button onclick="manageAccount()">Account Management</button>
    </div>
    <div class="notifications" id="notifications">You have a new message!</div>

    <script>
        function goLive() {
            // Redirect to go live page
            window.location.href = 'go_live.php';
        }

        function uploadVideo() {
            // Redirect to upload video page
            window.location.href = 'upload_video.php';
        }

        function manageAccount() {
            // Redirect to account management page
            window.location.href = 'account_management.php';
        }

        document.getElementById('message-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('send_message.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                console.log(data);
                loadMessages();
                this.reset();
            });
        });

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
                        messageElement.classList.add(message.sender_id == <?php echo $_SESSION['user_id']; ?> ? 'sent' : 'received');
                        messageElement.textContent = message.message;
                        messagesContainer.appendChild(messageElement);
                    });
                });
        }

        function checkNotifications() {
            fetch('check_notifications.php')
                .then(response => response.json())
                .then(data => {
                    if (data.new_message) {
                        const notifications = document.getElementById('notifications');
                        notifications.style.display = 'block';
                        setTimeout(() => {
                            notifications.style.display = 'none';
                        }, 5000);
                    }
                });
        }

        loadMessages();
        setInterval(loadMessages, 5000); // Refresh messages every 5 seconds
        setInterval(checkNotifications, 5000); // Check for notifications every 5 seconds

        // Load live streams
        function loadLiveStreams() {
            fetch('get_live_streams.php')
                .then(response => response.json())
                .then(data => {
                    data.forEach(stream => {
                        const videoElement = document.getElementById(`liveVideo_${stream.id}`);
                        if (videoElement) {
                            const peerConnection = new RTCPeerConnection({
                                iceServers: [{ urls: 'stun:stun.l.google.com:19302' }]
                            });

                            peerConnection.ontrack = event => {
                                videoElement.srcObject = event.streams[0];
                            };

                            if (stream.offer) {
                                peerConnection.setRemoteDescription(new RTCSessionDescription(stream.offer))
                                    .catch(error => console.error('Failed to set remote description:', error));
                            }

                            if (stream.candidate) {
                                peerConnection.addIceCandidate(new RTCIceCandidate(stream.candidate))
                                    .catch(error => console.error('Failed to add ICE candidate:', error));
                            }
                        }
                    });
                })
                .catch(error => console.error('Failed to load live streams:', error));
        }

        loadLiveStreams();
        setInterval(loadLiveStreams, 5000); // Refresh live streams every 5 seconds
    </script>
</body>
</html>