<?php
include 'session_manager.php';
include 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Authenticate user
    $user = authenticate($username, $password);

    if ($user) {
        // Store user ID, username, and email in session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];

        // Redirect to the page the user was trying to access
        $redirect_url = isset($_SESSION['redirect_url']) ? $_SESSION['redirect_url'] : 'landing.php';
        unset($_SESSION['redirect_url']);
        header("Location: " . $redirect_url);
        exit();
    } else {
        echo "Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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
        .forgot-password {
            color: #0073e6;
            cursor: pointer;
        }
        .forgot-password:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <form method="POST" action="login.php">
            <div class="form-group">
                <input type="text" name="username" placeholder="Username" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <div class="form-group">
                <button type="submit">Login</button>
            </div>
        </form>
        <p class="forgot-password" onclick="forgotPassword()">Forgot Password?</p>
    </div>

    <script>
        function forgotPassword() {
            // Redirect to forgot password page
            window.location.href = 'forgot_password.php';
        }
    </script>
</body>
</html>