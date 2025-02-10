<?php
session_start(); // Start the session
session_regenerate_id(true); // Regenerate session ID for security

// Check if the user is logged in and determine the role
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    // Admin is logged in, allow access to admin section
    $role = 'admin';
} elseif (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) { // Change from 'logged_in' to 'user_logged_in'
    // Regular user is logged in, allow access to user section
    $role = 'user';
} else {
    // Redirect to login page if not logged in
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PME</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to your updated CSS -->
</head>
<body>
    <?php include 'sidebar.php'; ?> <!-- Include sidebar -->

    <div class="main-content">
        <h1>Bienvenue</h1>
        
        <?php if ($role === 'admin'): ?>
            <p>Bienvenue, Admin ! Sélectionnez une option dans le menu pour continuer.</p>
        <?php elseif ($role === 'user'): ?>
            <p>Bienvenue, Utilisateur ! Sélectionnez une option dans le menu pour continuer.</p>
        <?php endif; ?>
    </div>
</body>
</html>
