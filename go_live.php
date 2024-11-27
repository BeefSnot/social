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

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    if ($action === 'start') {
        $stream_key = bin2hex(random_bytes(16));
        $sql = "INSERT INTO live_streams (user_id, stream_key, is_live) VALUES (?, ?, TRUE)
                ON DUPLICATE KEY UPDATE stream_key = VALUES(stream_key), is_live = TRUE";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $user_id, $stream_key);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['stream_key' => $stream_key]);
        exit();
    } elseif ($action === 'stop') {
        $sql = "UPDATE live_streams SET is_live = FALSE WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Go Live</title>
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
        .container {
            text-align: center;
            width: 100%;
            max-width: 800px;
            margin: 20px;
        }
        .video-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
        }
        video {
            width: 45%;
            margin: 0 10px;
            border-radius: 10px;
        }
        .controls button {
            padding: 10px 20px;
            margin: 0 10px;
            background: #0073e6;
            border: none;
            border-radius: 5px;
            color: #fff;
            cursor: pointer;
        }
        .controls button:hover {
            background: #005bb5;
        }
        .chat-container {
            margin-top: 20px;
            width: 100%;
        }
        .chat-messages {
            height: 200px;
            overflow-y: auto;
            margin-bottom: 10px;
            border: 1px solid #333;
            padding: 10px;
            border-radius: 10px;
            background: #222;
        }
        .chat-messages p {
            margin: 5px 0;
        }
        .chat-container input {
            width: 80%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            margin-right: 10px;
        }
        .chat-container button {
            padding: 10px;
            background: #0073e6;
            border: none;
            border-radius: 5px;
            color: #fff;
            cursor: pointer;
        }
        .chat-container button:hover {
            background: #005bb5;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Go Live</h1>
        <div class="video-container">
            <video id="localVideo" autoplay muted></video>
            <video id="remoteVideo" autoplay></video>
        </div>
        <div class="controls">
            <button id="startButton">Start Live</button>
            <button id="stopButton">Stop Live</button>
        </div>
        <div class="chat-container">
            <div class="chat-messages" id="chatMessages"></div>
            <input type="text" id="chatInput" placeholder="Type a message">
            <button onclick="sendMessage()">Send</button>
        </div>
    </div>
    <script src="https://cdn.socket.io/4.0.0/socket.io.min.js"></script>
    <script>
        const socket = io('http://your.server.ip.address:3000');

        const localVideo = document.getElementById('localVideo');
        const remoteVideo = document.getElementById('remoteVideo');
        const startButton = document.getElementById('startButton');
        const stopButton = document.getElementById('stopButton');
        const chatMessages = document.getElementById('chatMessages');
        const chatInput = document.getElementById('chatInput');

        let localStream;
        let peerConnection;
        let streamKey;

        const config = {
            iceServers: [
                {
                    urls: 'stun:stun.l.google.com:19302'
                }
            ]
        };

        startButton.addEventListener('click', startLive);
        stopButton.addEventListener('click', stopLive);

        async function startLive() {
            try {
                localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
                localVideo.srcObject = localStream;

                peerConnection = new RTCPeerConnection(config);
                peerConnection.addStream(localStream);

                peerConnection.onicecandidate = (event) => {
                    if (event.candidate) {
                        socket.emit('candidate', event.candidate);
                    }
                };

                peerConnection.onaddstream = (event) => {
                    remoteVideo.srcObject = event.stream;
                };

                const offer = await peerConnection.createOffer();
                await peerConnection.setLocalDescription(offer);
                socket.emit('offer', offer);

                const response = await fetch('go_live.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'action=start'
                });
                const data = await response.json();
                streamKey = data.stream_key;
            } catch (error) {
                console.error('Error accessing media devices.', error);
            }
        }

        async function stopLive() {
            if (localStream) {
                localStream.getTracks().forEach(track => track.stop());
                localVideo.srcObject = null;
                remoteVideo.srcObject = null;
                peerConnection.close();
                peerConnection = null;

                await fetch('go_live.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'action=stop'
                });
            }
        }

        socket.on('offer', async (offer) => {
            if (!peerConnection) {
                peerConnection = new RTCPeerConnection(config);
                peerConnection.addStream(localStream);

                peerConnection.onicecandidate = (event) => {
                    if (event.candidate) {
                        socket.emit('candidate', event.candidate);
                    }
                };

                peerConnection.onaddstream = (event) => {
                    remoteVideo.srcObject = event.stream;
                };
            }

            await peerConnection.setRemoteDescription(new RTCSessionDescription(offer));
            const answer = await peerConnection.createAnswer();
            await peerConnection.setLocalDescription(answer);
            socket.emit('answer', answer);
        });

        socket.on('answer', (answer) => {
            peerConnection.setRemoteDescription(new RTCSessionDescription(answer));
        });

        socket.on('candidate', (candidate) => {
            peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
        });

        socket.on('message', (data) => {
            const messageElement = document.createElement('p');
            messageElement.textContent = `${data.username}: ${data.message}`;
            chatMessages.appendChild(messageElement);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        });

        function sendMessage() {
            const message = chatInput.value;
            if (message) {
                const data = {
                    username: '<?php echo $_SESSION['username']; ?>', // Use the actual username from the session
                    message: message
                };
                socket.emit('message', data);
                chatInput.value = '';
            }
        }
    </script>
</body>
</html>