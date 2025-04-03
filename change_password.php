<?php
require 'session_utils.php';
startSecureSession();
require 'db.php';

// Verify database connection
if (!isset($pdo) || !($pdo instanceof PDO)) {
    die("Database connection failed. Please try again later.");
}

// Validate session and get user info
if (isset($_SESSION['admin_logged_in'])) {
    validateAdminSession();
    $table = 'admin_users';
    $username = $_SESSION['admin_username'];
    $id = $_SESSION['admin_id'];
} elseif (isset($_SESSION['user_logged_in'])) {
    validateUserSession();
    $table = 'users';
    $username = $_SESSION['user_username'];
    $id = $_SESSION['user_id'];
} else {
    endSession();
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = trim($_POST['current_password'] ?? '');
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // Validation
    if (empty($current_password)) {
        $error = 'Current password is required.';
    } elseif (empty($new_password)) {
        $error = 'New password is required.';
    } elseif (strlen($new_password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif (empty($confirm_password)) {
        $error = 'Please confirm your new password.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New password and confirmation do not match.';
    } else {
        try {
            // Verify current password
            $stmt = $pdo->prepare("SELECT * FROM $table WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch();

            if (!$user) {
                throw new Exception('User account not found.');
            }

            if (!password_verify($current_password, $user['password'])) {
                throw new Exception('Current password is incorrect.');
            }

            if (password_verify($new_password, $user['password'])) {
                throw new Exception('New password must be different from current password.');
            }

            // Hash new password
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            if (!$hashed_password) {
                throw new Exception('Failed to secure new password.');
            }

            // Update password
            $pdo->beginTransaction();
            $update_stmt = $pdo->prepare("UPDATE $table SET password = ? WHERE id = ?");
            $update_stmt->execute([$hashed_password, $id]);

            // Log the action
            $log_action = "Password Change";
            $log_details = "User changed their password";
            $log_stmt = $pdo->prepare("INSERT INTO logs (username, action, details, ip_address) VALUES (?, ?, ?, ?)");
            $log_stmt->execute([$username, $log_action, $log_details, $_SERVER['REMOTE_ADDR']]);

            $pdo->commit();
            
            // Regenerate session
            session_regenerate_id(true);
            $_SESSION['last_activity'] = time();
            
            $success = 'Password updated successfully!';
            
        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = $e->getMessage();
            error_log("Password change error: " . $error);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Consistent with other pages */
        .main-content {
            margin-left: 260px;
            padding: 40px;
            flex-grow: 1;
            background-color: white;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
        }

        .form-container {
            width: 100%;
            max-width: 500px;
            padding: 30px;
            background: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .password-hint {
            font-size: 13px;
            color: #666;
            margin-top: 5px;
        }

        button[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button[type="submit"]:hover {
            background-color: #0069d9;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <h1>Change Password</h1>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="form-container">
            <form method="POST" autocomplete="off">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required minlength="8">
                    <p class="password-hint">Must be at least 8 characters long</p>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
                </div>
                
                <button type="submit">Update Password</button>
            </form>
        </div>
    </div>
</body>
</html>