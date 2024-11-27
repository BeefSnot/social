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
    error_log("Connection failed: " . $conn->connect_error);
    die("Connection failed: " . $conn->connect_error);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    if ($action === 'register') {
        $confirm_password = $_POST['confirm_password'];
        if ($password !== $confirm_password) {
            $_SESSION['error'] = "Passwords do not match!";
            header("Location: index.php");
            exit;
        }
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $verification_code = rand(100000, 999999); // Generate a 6-digit code
        $expiration_time = date('Y-m-d H:i:s', strtotime('+2 hours')); // Set expiration time to 2 hours from now
        $sql = "INSERT INTO users (username, email, password, verification_code, verification_expiration, verified) VALUES (?, ?, ?, ?, ?, 0)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $username, $email, $hashed_password, $verification_code, $expiration_time);
        if ($stmt->execute()) {
            // Send verification email
            $to = $email;
            $subject = "Verify Your Email for KELP Social";
            $body = "Please enter the following 6-digit code on the verification page to verify your email address:\n\n";
            $body .= "Verification Code: $verification_code\n\n";
            $body .= "Go to https://social.jameshamby.me/verify.php to enter your code.";
            if (sendEmail($to, $subject, $body)) {
                // Redirect to verification page
                header("Location: verify.php");
                exit();
            } else {
                error_log("Failed to send verification email.");
                $_SESSION['error'] = "Registration successful! Failed to send verification email.";
                header("Location: index.php");
                exit;
            }
        } else {
            error_log("Error: " . $sql . " - " . $conn->error);
            $_SESSION['error'] = "Error: " . $sql . "<br>" . $conn->error;
            header("Location: index.php");
            exit;
        }
        $stmt->close();
    } elseif ($action === 'login') {
        $sql = "SELECT * FROM users WHERE username = ? AND email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row['verified'] == 0) {
                $_SESSION['error'] = "Please verify your email address before logging in. Check your email for the verification link.";
                header("Location: index.php");
                exit;
            }
            if (password_verify($password, $row['password'])) {
                // Store username and email in session
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
                // Send login notification email
                $to = $email;
                $subject = "Login Notification";
                $body = "You have successfully logged in to TikTok Clone.";
                if (sendEmail($to, $subject, $body)) {
                    // Redirect to landing page
                    header("Location: landing.php");
                    exit();
                } else {
                    error_log("Failed to send login notification email.");
                    $_SESSION['error'] = "Login successful! Failed to send login notification email.";
                    header("Location: index.php");
                    exit;
                }
            } else {
                $_SESSION['error'] = "Invalid password!";
                header("Location: index.php");
                exit;
            }
        } else {
            $_SESSION['error'] = "No user found with that username and email!";
            header("Location: index.php");
            exit;
        }
        $stmt->close();
    }
}
$conn->close();
ob_end_flush(); // End output buffering and flush output
?>