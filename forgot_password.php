<?php
session_start();
ob_start(); // Start output buffering

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'send_email.php';

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

$verification_sent = false;
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_reset'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];

    $sql = "SELECT * FROM users WHERE username = ? AND email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $verification_code = bin2hex(random_bytes(16));
        $expiration_time = date('Y-m-d H:i:s', strtotime('+1 hour')); // Set expiration time to 1 hour from now
        $sql_update = "UPDATE users SET verification_code = ?, verification_expiration = ? WHERE username = ? AND email = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("ssss", $verification_code, $expiration_time, $username, $email);
        if ($stmt_update->execute()) {
            // Send verification email
            $to = $email;
            $subject = "Password Reset for KELP Social";
            $body = "Please click the link below to reset your password:\n\n";
            $body .= "https://social.jameshamby.me/reset_password.php?code=$verification_code";
            if (sendEmail($to, $subject, $body)) {
                $verification_sent = true;
                $message = "A verification email has been sent to your email address.";
            } else {
                $message = "Failed to send verification email.";
            }
        } else {
            $message = "Failed to update verification code.";
        }
        $stmt_update->close();
    } else {
        $message = "No user found with that username and email.";
    }

    $stmt->close();
}

$conn->close();
ob_end_flush(); // End output buffering and flush output
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #000;
            color: #fff;
        }
        .container {
            text-align: center;
            background-color: rgba(0, 0, 0, 0.7);
            padding: 20px;
            border-radius: 10px;
            width: 300px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
        }
        .form-group button {
            width: 100%;
            padding: 10px;
            background-color: #0073e6;
            border: none;
            border-radius: 5px;
            color: #fff;
            cursor: pointer;
        }
        .form-group button:hover {
            background-color: #005bb5;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Forgot Password</h2>
        <?php if ($verification_sent): ?>
            <p><?php echo $message; ?></p>
        <?php else: ?>
            <form method="POST" action="forgot_password.php">
                <div class="form-group">
                    <input type="text" name="username" placeholder="Username" required>
                </div>
                <div class="form-group">
                    <input type="email" name="email" placeholder="Email" required>
                </div>
                <div class="form-group">
                    <button type="submit" name="request_reset">Send Verification Email</button>
                </div>
            </form>
            <p><?php echo $message; ?></p>
        <?php endif; ?>
    </div>
</body>
</html>