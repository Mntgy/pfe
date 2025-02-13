<?php
session_start();
require 'db.php';

// ✅ Allow both users and admins
if (!isset($_SESSION['admin_logged_in']) && !isset($_SESSION['user_logged_in'])) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'All fields are required.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New password and confirm password do not match.';
    } else {
        // ✅ Determine if user is an admin or a regular user
        if (isset($_SESSION['admin_logged_in'])) {
            $table = 'admin_users';
            $username = $_SESSION['admin_username'];
        } else {
            $table = 'users';
            $username = $_SESSION['user_username'];
        }

        // Fetch the current user details
        $sql = "SELECT * FROM $table WHERE username = :username";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify the current password
        if ($user && password_verify($current_password, $user['password'])) {
            // Hash the new password
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

            // Update password in the database
            $update_sql = "UPDATE $table SET password = :password WHERE username = :username";
            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->execute([
                'password' => $hashed_password,
                'username' => $username,
            ]);

            // ✅ Log password change if logging is enabled
            if (function_exists('logAction')) {
                logAction($pdo, $username, isset($_SESSION['admin_logged_in']) ? 'admin' : 'user', 'Password Change', 'User changed their password.');
            }

            $success = 'Password updated successfully!';
        } else {
            $error = 'Current password is incorrect.';
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
        /* 🌟 General Styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f8f9fc;
            display: flex;
        }

        /* 🌟 Main Content */
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

        h1 {
            font-size: 2rem;
            color: #007bff;
            margin-bottom: 20px;
            border-bottom: 3px solid #007bff;
            padding-bottom: 10px;
            text-align: center;
        }

        /* 🌟 Form Styling */
        .form-container {
            width: 100%;
            max-width: 500px;
            padding: 20px;
            background: white;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            text-align: center;
        }

        form input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        form button {
            width: 100%;
            background: #007bff;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
        }

        form button:hover {
            background: #0056b3;
        }

        /* 🌟 Message Styles */
        .message {
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            font-size: 14px;
            width: 100%;
            max-width: 500px;
        }

        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* 🌟 Responsive Design */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 240px;
                padding: 20px;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                margin-left: 220px;
                padding: 15px;
            }
        }
    </style>
</head>
<body>

    <!-- ✅ Include Sidebar -->
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <h1>Change Password</h1>

        <!-- ✅ Display Success or Error Messages -->
        <?php if (!empty($error)): ?>
            <p class="message error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <p class="message success"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>

        <!-- ✅ Password Change Form -->
        <div class="form-container">
            <form method="POST">
                <label for="current_password">Current Password:</label>
                <input type="password" id="current_password" name="current_password" required>

                <label for="new_password">New Password:</label>
                <input type="password" id="new_password" name="new_password" required>

                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>

                <button type="submit">Update Password</button>
            </form>
        </div>
    </div>

</body>
</html>
