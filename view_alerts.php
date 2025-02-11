<?php
session_start(); // Ensure session is started

require 'db.php'; // Database connection

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php"); // Redirect to login page if not admin
    exit();
}

// Fetch alerts from the database
$sql = "SELECT * FROM alerts ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Alertes</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="layout-container">
        <?php include 'sidebar.php'; ?> <!-- Sidebar -->

        <div class="main-content">
            <h1>Liste des Alertes</h1>

            <?php if (!empty($alerts)): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Email</th>
                                <th>Objet</th>
                                <th>Message</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alerts as $alert): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($alert['id']); ?></td>
                                    <td><?php echo htmlspecialchars($alert['email']); ?></td>
                                    <td><?php echo htmlspecialchars($alert['subject']); ?></td>
                                    <td class="message-column"><?php echo nl2br(htmlspecialchars($alert['message'])); ?></td>
                                    <td><?php echo htmlspecialchars($alert['created_at']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="no-alerts">Aucune alerte disponible.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
