<?php
session_start();
require 'db.php'; // Include database connection

// Ensure only admin users can access this page
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Initialize variables for messages
$success = '';
$error = '';

// Handle user creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    $username = htmlspecialchars(trim($_POST['username']));
    $password = htmlspecialchars(trim($_POST['password']));
    $role = htmlspecialchars(trim($_POST['role']));

    if (empty($username) || empty($password) || empty($role)) {
        $error = 'All fields are required.';
    } elseif (!in_array($role, ['admin', 'limited'])) {
        $error = 'Invalid role.';
    } else {
        // Check if the username already exists
        if ($role == 'admin') {
            // Check for existing admin username
            $stmt = $pdo->prepare('SELECT id FROM admin_users WHERE username = :username');
            $stmt->execute(['username' => $username]);
            if ($stmt->fetch()) {
                $error = 'Admin Username already exists.';
            } else {
                // Hash the password for admin
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                // Insert the new admin into the admin_users table
                $stmt = $pdo->prepare('INSERT INTO admin_users (username, password) VALUES (:username, :password)');
                $stmt->execute([
                    'username' => $username,
                    'password' => $hashed_password,
                ]);
                $success = 'Admin created successfully.';
            }
        } else {
            // Check for existing regular user username
            $stmt = $pdo->prepare('SELECT id FROM users WHERE username = :username');
            $stmt->execute(['username' => $username]);
            if ($stmt->fetch()) {
                $error = 'Username already exists.';
            } else {
                // Hash the password for regular user
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                // Insert the new user into the users table
                $stmt = $pdo->prepare('INSERT INTO users (username, password, role) VALUES (:username, :password, :role)');
                $stmt->execute([
                    'username' => $username,
                    'password' => $hashed_password,
                    'role' => $role,
                ]);
                $success = 'User created successfully.';
            }
        }
    }
}

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $user_id = (int) $_POST['user_id'];

    // Prevent admins from deleting themselves
    if ($user_id === $_SESSION['admin_id']) {
        $error = 'You cannot delete yourself.';
    } else {
        // Check if the user is in admin_users table first
        $stmt = $pdo->prepare('DELETE FROM admin_users WHERE id = :id');
        $stmt->execute(['id' => $user_id]);

        // Check if it was in users table
        if ($stmt->rowCount() === 0) {
            // If not found in admin_users, check in users table
            $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
            $stmt->execute(['id' => $user_id]);
        }

        $success = 'User deleted successfully.';
    }
}

// Fetch all users and admins
$stmt = $pdo->query('SELECT id, username, role, created_at FROM users');
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query('SELECT id, username, created_at FROM admin_users');
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="container">
        <h1>Manage Users</h1>

        <!-- Display success or error messages -->
        <?php if (!empty($error)): ?>
            <p class="message error"><?php echo $error; ?></p>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <p class="message success"><?php echo $success; ?></p>
        <?php endif; ?>

        <!-- User Creation Form -->
        <div class="form-container">
            <h2>Create a User</h2>
            <form method="post" class="user-form">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>

                <label for="role">Role:</label>
                <select id="role" name="role" required>
                    <option value="limited">Limited User</option>
                    <option value="admin">Admin</option>
                </select>

                <button type="submit" name="create_user">Create</button>
            </form>
        </div>

        <!-- User List -->
        <h2>User List</h2>
        <table class="user-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Creation Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo $user['username']; ?></td>
                        <td><?php echo $user['role']; ?></td>
                        <td><?php echo $user['created_at']; ?></td>
                        <td>
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit" name="delete_user" class="delete-button">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Admin List -->
        <h2>Admin List</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Creation Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($admins as $admin): ?>
                    <tr>
                        <td><?php echo $admin['id']; ?></td>
                        <td><?php echo $admin['username']; ?></td>
                        <td><?php echo $admin['created_at']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
