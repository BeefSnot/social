<?php
include 'session_manager.php';
include 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Debugging: Log the received email and password
    error_log("Received email: " . $email);
    error_log("Received password: " . $password);

    // Authenticate user
    $user = authenticate($email, $password);

    if ($user) {
        // Store user ID, username, and email in session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];

        // Debugging: Log successful authentication
        error_log("Authentication successful for user ID: " . $user['id']);

        // Redirect to the landing page
        header("Location: landing.php");
        exit();
    } else {
        // Debugging: Log failed authentication
        error_log("Authentication failed for email: " . $email);
        $error_message = "Invalid email or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lumi Social</title>
    <style>
        body,
        html {
            height: 100%;
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #f0f2f5;
            overflow: hidden;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-box {
            background-color: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 360px;
            text-align: center;
        }

        .login-box h1 {
            color: #000;
            font-size: 28px;
            margin-bottom: 20px;
        }

        .login-box input[type="text"],
        .login-box input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #dddfe2;
            border-radius: 4px;
        }

        .login-box button {
            width: 100%;
            padding: 12px;
            background-color: #fe2c55;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
        }

        .login-box button:hover {
            background-color: #d81b45;
        }

        .login-box .forgot-password {
            display: block;
            text-align: center;
            margin-top: 10px;
            color: #fe2c55;
            text-decoration: none;
        }

        .login-box .forgot-password:hover {
            text-decoration: underline;
        }

        .login-box .register {
            margin-top: 20px;
            color: #000;
        }

        .login-box .register a {
            color: #fe2c55;
            text-decoration: none;
        }

        .login-box .register a:hover {
            text-decoration: underline;
        }

        .error-message {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="login-box">
            <h1>Lumi Social</h1>
            <?php if (isset($error_message)): ?>
                <p class="error-message"><?php echo $error_message; ?></p>
            <?php endif; ?>
            <form id="login-form" action="index.php" method="POST">
                <input type="text" name="email" placeholder="Email or Phone Number" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Log In</button>
            </form>
            <a href="#" class="forgot-password">Forgot Password?</a>
            <div class="register">
                <span>Don't have an account? </span>
                <a href="register.php">Register</a>
            </div>
        </div>
    </div>
</body>

</html>