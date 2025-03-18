<?php
session_start();
require 'db.php'; // Include database connection

$error = ''; // To store error messages

// Handle OTP verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_otp'])) {
    $user_entered_otp = $_POST['otp'];

    // Check if the user is an admin or regular user
    if (isset($_SESSION['admin_id_for_otp'])) {
        $user_id = $_SESSION['admin_id_for_otp'];
        $redirect = 'index.php'; // Redirect to admin dashboard
    } elseif (isset($_SESSION['user_id_for_otp'])) {
        $user_id = $_SESSION['user_id_for_otp'];
        $redirect = 'index.php'; // Redirect to user dashboard
    } else {
        $error = "Invalid session. Please log in again.";
    }

    // Verify the OTP
    if (empty($error)) {
        $stmt = $pdo->prepare("SELECT otp_code, expires_at FROM otp_store WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$user_id]);
        $otp_data = $stmt->fetch();

        if ($otp_data && $user_entered_otp === $otp_data['otp_code'] && time() <= strtotime($otp_data['expires_at'])) {
            // OTP is valid, log the user in
            if (isset($_SESSION['admin_id_for_otp'])) {
                $_SESSION['admin_logged_in'] = true;
                unset($_SESSION['admin_id_for_otp']);
            } else {
                $_SESSION['user_logged_in'] = true;
                unset($_SESSION['user_id_for_otp']);
            }

            header("Location: $redirect");
            exit();
        } else {
            $error = "Invalid or expired OTP.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
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

        /* Container for the OTP form */
        .login-container {
            background: #ffffff; /* White background for the form */
            padding: 30px;
            border-radius: 10px; /* Rounded corners */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Subtle shadow */
            width: 300px;
            text-align: center;
        }

        /* OTP header */
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

        /* Input field for OTP */
        .login-container input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 14px;
        }

        /* Focus effect for input field */
        .login-container input[type="text"]:focus {
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

        /* Resend OTP link styling */
        .login-container .resend-otp {
            margin-top: 10px;
            font-size: 14px;
            color: #007bff;
            text-decoration: none;
            cursor: pointer;
        }

        .login-container .resend-otp:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Verify OTP</h1>
        <?php if (!empty($error)): ?>
            <p class="error-message"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="post" action="verify_otp.php">
            <label for="otp">Enter OTP:</label>
            <input type="text" id="otp" name="otp" required>
            <button type="submit" name="verify_otp">Verify OTP</button>
        </form>
        <a href="#" class="resend-otp">Resend OTP</a>
    </div>
</body>
</html>