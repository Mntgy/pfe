<?php
require 'db.php';

// Admin credentials
$username = 'admin'; // Desired username
$password = password_hash('admin123', PASSWORD_BCRYPT); // Desired password (hashed for security)

try {
    // Insert the admin user into the database
    $sql = "INSERT INTO admin_users (username, password) VALUES (:username, :password)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'username' => $username,
        'password' => $password,
    ]);

    echo "Admin user created successfully!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage(); // Display detailed error message
}
?>
