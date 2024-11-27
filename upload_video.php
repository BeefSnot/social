<?php
include 'session_manager.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debugging: Check if form data is received
    error_log("POST data: " . print_r($_POST, true));
    error_log("FILES data: " . print_r($_FILES, true));

    // Validate form data
    if (empty($_FILES['video'])) {
        die("Video file is missing.");
    }

    $user_id = $_SESSION['user_id'];
    $description = isset($_POST['description']) ? $_POST['description'] : '';
    $video = $_FILES['video'];

    // WebRTC server details
    $webrtc_server = 'http://192.99.9.164:3000/upload';

    // Use cURL to send the video file to the WebRTC server
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $webrtc_server);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'video' => new CURLFile($video['tmp_name'], $video['type'], $video['name']),
        'description' => $description,
        'user_id' => $user_id
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    // Debugging: Log cURL response and error
    error_log("cURL response: " . $response);
    error_log("cURL error: " . $curl_error);
    error_log("HTTP code: " . $http_code);

    if ($http_code != 200) {
        die("Failed to upload video to WebRTC server. HTTP code: " . $http_code . ". cURL error: " . $curl_error);
    }

    // Assuming the WebRTC server returns the path to the uploaded file
    $response_data = json_decode($response, true);
    if (!isset($response_data['file_path'])) {
        die("Invalid response from WebRTC server.");
    }
    $file_path = $response_data['file_path'];

    $servername = "localhost";
    $username = "dynastyhosting_social";
    $password = "d9Au7MmbqBJh5ucSz2kq";
    $dbname = "dynastyhosting_social";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "INSERT INTO videos (user_id, file_path, description) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $user_id, $file_path, $description);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    header("Location: landing.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Video</title>
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f4f4f4;
            color: #333;
        }
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        input, textarea, button {
            margin: 10px 0;
            padding: 10px;
            width: 100%;
            max-width: 300px;
        }
        button {
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <form action="upload_video.php" method="POST" enctype="multipart/form-data">
        <input type="file" name="video" accept="video/*" required>
        <textarea name="description" placeholder="Description"></textarea>
        <button type="submit">Upload</button>
    </form>
</body>
</html>