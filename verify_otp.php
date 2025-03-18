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
        /* Add your CSS styles here */
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
    </div>
</body>
</html>