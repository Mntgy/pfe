<?php
require 'session_utils.php';
startSecureSession();
require 'db.php';

// Check database connection
if (!isset($pdo) || !($pdo instanceof PDO)) {
    die("Database connection error. Please try again later.");
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_entered_otp = trim($_POST['otp'] ?? '');

    // Input validation
    if (empty($user_entered_otp)) {
        $error = "Please enter the OTP code.";
    } elseif (!preg_match('/^\d{6}$/', $user_entered_otp)) {
        $error = "OTP must be 6 digits.";
    } else {
        // Determine user type and get user ID from session
        if (isset($_SESSION['admin_id_for_otp'])) {
            $user_id = $_SESSION['admin_id_for_otp'];
            $is_admin = true;
            $redirect = 'index.php';
        } elseif (isset($_SESSION['user_id_for_otp'])) {
            $user_id = $_SESSION['user_id_for_otp'];
            $is_admin = false;
            $redirect = 'index.php';
        } else {
            endSession();
            header('Location: login.php?error=session_expired');
            exit();
        }

        try {
            // Verify the OTP - using your current table structure
            $stmt = $pdo->prepare("SELECT otp_code, expires_at FROM otp_store 
                                  WHERE user_id = ? 
                                  ORDER BY created_at DESC LIMIT 1");
            $stmt->execute([$user_id]);
            $otp_data = $stmt->fetch();

            if (!$otp_data) {
                throw new Exception("No OTP found. Please request a new one.");
            }

            if ($user_entered_otp !== $otp_data['otp_code']) {
                throw new Exception("Invalid OTP code.");
            }

            if (time() > strtotime($otp_data['expires_at'])) {
                throw new Exception("OTP has expired. Please request a new one.");
            }

            // OTP is valid - complete login
            if ($is_admin) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $user_id;
                unset($_SESSION['admin_id_for_otp']);
                
                // Get admin details
                $stmt = $pdo->prepare("SELECT username FROM admin_users WHERE id = ?");
                $stmt->execute([$user_id]);
                $admin = $stmt->fetch();
                $_SESSION['admin_username'] = $admin['username'];
            } else {
                $_SESSION['user_logged_in'] = true;
                $_SESSION['user_id'] = $user_id;
                unset($_SESSION['user_id_for_otp']);
                
                // Get user details
                $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch();
                $_SESSION['user_username'] = $user['username'];
            }

            // Set session security parameters
            $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
            $_SESSION['last_activity'] = time();

            // Delete used OTP
            $pdo->prepare("DELETE FROM otp_store WHERE user_id = ?")
                ->execute([$user_id]);

            // Regenerate session ID
            session_regenerate_id(true);

            // Log successful login
            $log_action = "Successful Login";
            $log_details = "User successfully completed OTP verification";
            $log_stmt = $pdo->prepare("INSERT INTO logs (username, action, details, ip_address) VALUES (?, ?, ?, ?)");
            $log_stmt->execute([
                $_SESSION[$is_admin ? 'admin_username' : 'user_username'],
                $log_action,
                $log_details,
                $_SERVER['REMOTE_ADDR']
            ]);

            header("Location: $redirect");
            exit();

        } catch (Exception $e) {
            $error = $e->getMessage();
            error_log("OTP Verification Error: " . $error);
        }
    }
}

// HTML remains the same as in your previous version
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        
        .otp-container {
            background: #ffffff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        
        h1 {
            color: #2c3e50;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }
        
        .otp-form {
            margin-bottom: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.25rem;
            text-align: left;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #495057;
        }
        
        input[type="text"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        input[type="text"]:focus {
            border-color: #4dabf7;
            outline: none;
            box-shadow: 0 0 0 3px rgba(77, 171, 247, 0.2);
        }
        
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background-color: #4dabf7;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%;
        }
        
        .btn:hover {
            background-color: #339af0;
        }
        
        .btn-resend {
            background: none;
            color: #4dabf7;
            text-decoration: underline;
            padding: 0;
            border: none;
            cursor: pointer;
            font-size: 0.875rem;
        }
        
        .btn-resend:hover {
            color: #228be6;
        }
        
        .message {
            padding: 0.75rem;
            margin-bottom: 1rem;
            border-radius: 4px;
            font-size: 0.875rem;
        }
        
        .error {
            background-color: #fff3bf;
            color: #e67700;
        }
        
        .success {
            background-color: #d3f9d8;
            color: #2b8a3e;
        }
        
        .otp-instructions {
            font-size: 0.875rem;
            color: #868e96;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="otp-container">
        <h1>Verify Your Identity</h1>
        <p class="otp-instructions">Please enter the 6-digit verification code sent to your email.</p>
        
        <?php if (!empty($error)): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="message success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="post" class="otp-form">
            <div class="form-group">
                <label for="otp">Verification Code</label>
                <input type="text" id="otp" name="otp" inputmode="numeric" pattern="\d{6}" 
                       maxlength="6" required autocomplete="off" autofocus>
            </div>
            <button type="submit" name="verify_otp" class="btn">Verify</button>
        </form>
        
        <form method="post">
            <p>Didn't receive a code? <button type="submit" name="resend_otp" class="btn-resend">Resend OTP</button></p>
        </form>
    </div>

    <script>
        // Auto-focus OTP input and move to next on input
        document.getElementById('otp').focus();
        
        // Prevent form resubmission on refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>