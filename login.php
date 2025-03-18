<?php
session_start();
require 'db.php'; // Include database connection
require 'email.php'; // Include the email sending functions

$error = ''; // To store error messages

// Check if the form has been submitted (POST method)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Trim the username and password to avoid spaces
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // First, check the admin_users table
    $sql = "SELECT * FROM admin_users WHERE username = :username";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['username' => $username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin['password'])) {
        // Login successful for admin
        // Step 2: Generate and send OTP
        $otp = generateOTP(); // Use the OTP generation function
        storeOTP($admin['id'], $otp); // Store the OTP in the database
        sendOTP($admin['email'], $otp); // Send the OTP via email

        // Store admin ID in session for OTP verification
        $_SESSION['admin_id_for_otp'] = $admin['id'];

        // Log admin login action
        $action = "Admin Login";
        $details = "Admin logged in successfully.";
        $ip_address = $_SERVER['REMOTE_ADDR']; // Get the admin's IP address

        $stmt = $pdo->prepare("INSERT INTO logs (username, action, details, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $action, $details, $ip_address]);

        // Redirect to OTP verification page
        header('Location: verify_otp.php');
        exit();
    } else {
        // If no match in admin_users, check the users table
        $sql = "SELECT * FROM users WHERE username = :username";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if (password_verify($password, $user['password'])) {
                // Login successful for regular user
                // Step 2: Generate and send OTP
                $otp = generateOTP(); // Use the OTP generation function
                storeOTP($user['id'], $otp); // Store the OTP in the database
                sendOTP($user['email'], $otp); // Send the OTP via email

                // Store user ID in session for OTP verification
                $_SESSION['user_id_for_otp'] = $user['id'];

                // Redirect to OTP verification page
                header('Location: verify_otp.php');
                exit();
            } else {
                // Password doesn't match, show error
                $error = "Invalid password for user.";
            }
        } else {
            // If no user found in both tables
            $error = "Invalid username or password.";
        }
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