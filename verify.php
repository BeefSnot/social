<?php
session_start();
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

$verification_successful = false;
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
unset($_SESSION['message']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $verification_code = $_POST['verification_code'];
    $sql = "SELECT * FROM users WHERE verification_code = ? AND verification_expiration > NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $verification_code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $sql_update = "UPDATE users SET verified = 1 WHERE verification_code = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("s", $verification_code);
        
        if ($stmt_update->execute()) {
            $verification_successful = true;
            $_SESSION['message'] = "Your account has been successfully verified.";
            header('Location: landing.php');
            exit();
        } else {
            $message = "Error: " . $sql_update . "<br>" . $conn->error;
        }
        $stmt_update->close();
    } else {
        $message = "Invalid or expired verification code!";
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email</title>
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
            border-radius: 8px;
        }
        .message {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
        }
        .error {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Verify Your Email</h2>
        <?php if ($message): ?>
            <div class="<?php echo $verification_successful ? 'message' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <form action="verify.php" method="POST">
            <div class="form-group">
                <input type="text" name="verification_code" placeholder="Enter Verification Code" required>
            </div>
            <div class="form-group">
                <button type="submit">Verify</button>
            </div>
        </form>
    </div>
</body>
</html>