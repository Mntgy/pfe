<?php
session_start();
require 'db.php';

// Ensure the user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Initialize error and success messages
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate the form inputs
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'All fields are required.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New password and confirm password do not match.';
    } else {
        // Fetch the current admin details
        $sql = "SELECT * FROM admin_users WHERE username = :username";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['username' => $_SESSION['admin_username']]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify the current password
        if ($admin && password_verify($current_password, $admin['password'])) {
            // Hash the new password
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

            // Update the password in the database
            $update_sql = "UPDATE admin_users SET password = :password WHERE username = :username";
            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->execute([
                'password' => $hashed_password,
                'username' => $_SESSION['admin_username'],
            ]);

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
    <style>
        /* Styling for the change password form */
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .change-password-container {
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 300px;
        }

        .change-password-container h1 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333333;
            text-align: center;
        }

        .change-password-container form label {
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
            color: #555555;
        }

        .change-password-container form input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .change-password-container form button {
            background: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
        }

        .change-password-container form button:hover {
            background: #0056b3;
        }

        .change-password-container .message {
            font-size: 14px;
            color: red;
            text-align: center;
            margin-bottom: 10px;
        }

        .change-password-container .success {
            color: green;
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?> <!-- Include sidebar -->
    <div class="change-password-container">
        
        <h1>Change Password</h1>
        <?php if (!empty($error)): ?>
            <div class="message"><?php echo $error; ?></div>
        <?php elseif (!empty($success)): ?>
            <div class="message success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="post" action="change_password.php">
            <label for="current_password">Current Password:</label>
            <input type="password" id="current_password" name="current_password" required>

            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" required>

            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <button type="submit">Update Password</button>
        </form>
    </div>
</body>
</html>