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
                $_SESSION['error'] = "Failed to send verification email.";
                header("Location: index.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Failed to register user.";
            header("Location: index.php");
            exit();
        }
    }

    if ($action === 'login') {
        $sql = "SELECT id, username, password FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                // Redirect to landing page or the page the user was trying to access
                $redirect_url = isset($_SESSION['redirect_url']) ? $_SESSION['redirect_url'] : 'landing.php';
                unset($_SESSION['redirect_url']);
                header("Location: " . $redirect_url);
                exit();
            } else {
                $_SESSION['error'] = "Invalid password.";
                header("Location: index.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "No user found with that username.";
            header("Location: index.php");
            exit();
        }
    }
}

$conn->close();
ob_end_flush(); // End output buffering and flush output
?>