<?php
session_start();  // Ensure session starts before any output
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
        try {
            if ($role === 'admin') {
                $stmt = $pdo->prepare('SELECT id FROM admin_users WHERE username = :username');
                $stmt->execute(['username' => $username]);

                if ($stmt->fetch()) {
                    $error = 'Admin username already exists.';
                } else {
                    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare('INSERT INTO admin_users (username, password) VALUES (:username, :password)');
                    $stmt->execute(['username' => $username, 'password' => $hashed_password]);
                    $success = 'Admin created successfully.';
                }
            } else {
                $stmt = $pdo->prepare('SELECT id FROM users WHERE username = :username');
                $stmt->execute(['username' => $username]);

                if ($stmt->fetch()) {
                    $error = 'Username already exists.';
                } else {
                    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare('INSERT INTO users (username, password, role) VALUES (:username, :password, :role)');
                    $stmt->execute([
                        'username' => $username,
                        'password' => $hashed_password,
                        'role' => $role,
                    ]);
                    $success = 'User created successfully.';
                }
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $user_id = (int) $_POST['user_id'];

    // Ensure $_SESSION['admin_id'] is set before comparison
    if (isset($_SESSION['admin_id']) && $user_id === $_SESSION['admin_id']) {
        $error = 'You cannot delete yourself.';
    } else {
        try {
            // Check if user exists in users table
            $stmt = $pdo->prepare('SELECT id FROM users WHERE id = :id');
            $stmt->execute(['id' => $user_id]);
            $userExists = $stmt->fetch();

            // Check if user exists in admin_users table
            $stmt = $pdo->prepare('SELECT id FROM admin_users WHERE id = :id');
            $stmt->execute(['id' => $user_id]);
            $adminExists = $stmt->fetch();

            // Delete only from the correct table
            if ($userExists) {
                $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
                $stmt->execute(['id' => $user_id]);
                $success = 'User deleted successfully.';
            } elseif ($adminExists) {
                $stmt = $pdo->prepare('DELETE FROM admin_users WHERE id = :id');
                $stmt->execute(['id' => $user_id]);
                $success = 'Admin deleted successfully.';
            } else {
                $error = 'User not found.';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Fetch all users and admins
$stmt = $pdo->query('SELECT id, username, role, created_at FROM users ORDER BY created_at DESC');
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query('SELECT id, username, created_at FROM admin_users ORDER BY created_at DESC');
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
            <p class="message error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <p class="message success"><?php echo htmlspecialchars($success); ?></p>
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
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                        <td><?php echo htmlspecialchars($user['created_at']); ?></td>
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
                        <td><?php echo htmlspecialchars($admin['username']); ?></td>
                        <td><?php echo htmlspecialchars($admin['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
