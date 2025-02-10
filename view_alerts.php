<?php
require 'db.php'; // Connection to the database

// Fetch alerts from the alerts table
$sql = "SELECT * FROM alerts ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Debugging: Check if data is being fetched from the database
if (!$alerts) {
    echo "No alerts found in the database.\n"; // Debugging line
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Alerts</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="layout-container">
        <?php include 'sidebar.php'; ?> <!-- Sidebar -->

        <div class="main-content">
            <h1>Liste des Alertes</h1>

            <!-- Scrollable table container -->
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
                        <?php if (!empty($alerts)): ?>
                            <?php foreach ($alerts as $alert): ?>
                                <tr>
                                    <td><?php echo $alert['id']; ?></td>
                                    <td><?php echo htmlspecialchars($alert['email']); ?></td>
                                    <td><?php echo htmlspecialchars($alert['subject']); ?></td>
                                    <td class="message-column"><?php echo nl2br(htmlspecialchars($alert['message'])); ?></td>
                                    <td><?php echo $alert['created_at']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">No alerts available.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
