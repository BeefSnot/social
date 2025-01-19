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
        /* styles */
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
                    <source src="http://99.148.48.236/webrtc-server/uploads/<?php echo htmlspecialchars(basename($row['file_path'])); ?>" type="video/mp4">
                </video>
                <p><?php echo htmlspecialchars($row['description']); ?></p>
                <p>Uploaded by: <?php echo htmlspecialchars($row['username']); ?></p>
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
            window.location.href = 'go_live.php';
        }

        function uploadVideo() {
            window.location.href = 'upload_video.php';
        }

        function manageAccount() {
            window.location.href = 'account_management.php';
        }

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
        setInterval(loadLiveStreams, 5000);
    </script>
</body>
</html>