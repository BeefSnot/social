<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
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

function authenticate($email, $password) {
    global $conn;
    if ($conn->connect_error) {
        error_log("Connection failed: " . $conn->connect_error);
        return false;
    }
    $sql = "SELECT id, username, password FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($id, $username, $hashed_password);
    $stmt->fetch();
    $stmt->close();

    // Debugging: Log the fetched user details
    error_log("Fetched user ID: " . $id);
    error_log("Fetched username: " . $username);
    error_log("Fetched hashed password: " . $hashed_password);

    if ($hashed_password && password_verify($password, $hashed_password)) {
        return ['id' => $id, 'username' => $username];
    } else {
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if ($action === 'register') {
        $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
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
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            $_SESSION['error'] = "Failed to register user.";
            header("Location: index.php");
            exit();
        }
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
        $sql = "SELECT id, username, password FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            $_SESSION['error'] = "Failed to login.";
            header("Location: index.php");
            exit();
        }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if ($user['password'] && password_verify($password, $user['password'])) {
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
            $_SESSION['error'] = "No user found with that email.";
            header("Location: index.php");
            exit();
        }
    }
}

// Do not close the connection here to avoid closing it prematurely
// $conn->close();
ob_end_flush(); // End output buffering and flush output
?>