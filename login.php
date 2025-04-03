<?php
// Start session with enhanced configuration
session_start([
    'cookie_lifetime' => 86400, // 1 day
    'cookie_secure'   => isset($_SERVER['HTTPS']), // Secure if HTTPS
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict',
    'use_strict_mode' => true
]);

require 'db.php';
require 'email.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Check admin_users table first
    $sql = "SELECT * FROM admin_users WHERE username = :username";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['username' => $username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin['password'])) {
        // Set complete admin session
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        $_SESSION['last_activity'] = time();
        
        // Generate and send OTP
        $otp = generateOTP();
        storeOTP($admin['id'], $otp);
        sendOTP($admin['email'], $otp);
        
        $_SESSION['admin_id_for_otp'] = $admin['id'];
        
        // Log admin login action
        $action = "Admin Login Attempt";
        $details = "Admin login initiated, OTP sent";
        $stmt = $pdo->prepare("INSERT INTO logs (username, action, details, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $action, $details, $_SERVER['REMOTE_ADDR']]);

        // Regenerate session ID for security
        session_regenerate_id(true);
        
        header('Location: verify_otp.php');
        exit();
    } else {
        // Check regular users table
        $sql = "SELECT * FROM users WHERE username = :username";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Set complete user session
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_username'] = $user['username'];
            $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
            $_SESSION['last_activity'] = time();
            
            // Generate and send OTP
            $otp = generateOTP();
            storeOTP($user['id'], $otp);
            sendOTP($user['email'], $otp);
            
            $_SESSION['user_id_for_otp'] = $user['id'];
            
            // Regenerate session ID for security
            session_regenerate_id(true);
            
            header('Location: verify_otp.php');
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>

<!-- Rest of your HTML remains the same -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        /* General body styling */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f4f4f9; /* Light background color */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        /* Container for the login form */
        .login-container {
            background: #ffffff; /* White background for the login box */
            padding: 30px;
            border-radius: 10px; /* Rounded corners */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Subtle shadow */
            width: 300px;
            text-align: center;
        }

        /* Login header */
        .login-container h1 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333333;
        }

        /* Label styling */
        .login-container label {
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
            color: #555555;
            text-align: left;
        }

        /* Input fields */
        .login-container input[type="text"],
        .login-container input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 14px;
        }

        /* Focus effect for input fields */
        .login-container input[type="text"]:focus,
        .login-container input[type="password"]:focus {
            border-color: #007bff; /* Blue outline on focus */
            outline: none;
            box-shadow: 0 0 3px rgba(0, 123, 255, 0.5);
        }

        /* Button styling */
        .login-container button {
            background: #007bff; /* Blue background */
            color: #ffffff; /* White text */
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            width: 100%;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        /* Button hover effect */
        .login-container button:hover {
            background: #0056b3; /* Darker blue */
        }

        /* Error message styling */
        .login-container .error-message {
            color: red;
            font-size: 14px;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Login</h1>
        <?php if (!empty($error)): ?>
            <p class="error-message"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="post" action="login.php">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>