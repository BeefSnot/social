<?php
session_start();
ob_start(); // Start output buffering

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

$reset_successful = false;
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $verification_code = $_POST['verification_code'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $message = "Passwords do not match!";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "SELECT * FROM users WHERE verification_code = ? AND verification_expiration > NOW()";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $verification_code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $sql_update = "UPDATE users SET password = ?, verification_code = NULL, verification_expiration = NULL WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("si", $hashed_password, $row['id']);
            if ($stmt_update->execute()) {
                $reset_successful = true;
                $message = "Password reset successful!";
            } else {
                $message = "Failed to reset password.";
            }
            $stmt_update->close();
        } else {
            $message = "Invalid or expired verification code!";
        }

        $stmt->close();
    }
}

$conn->close();
ob_end_flush(); // End output buffering and flush output
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
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
        <h2>Reset Password</h2>
        <?php if ($reset_successful): ?>
            <p><?php echo $message; ?></p>
            <button onclick="window.location.href='login.php'">Login</button>
        <?php else: ?>
            <form method="POST" action="reset_password.php">
                <div class="form-group">
                    <input type="hidden" name="verification_code" value="<?php echo isset($_GET['code']) ? htmlspecialchars($_GET['code']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <input type="password" name="new_password" placeholder="New Password" required>
                </div>
                <div class="form-group">
                    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                </div>
                <div class="form-group">
                    <button type="submit" name="reset_password">Reset Password</button>
                </div>
            </form>
            <p><?php echo $message; ?></p>
        <?php endif; ?>
    </div>
</body>
</html>