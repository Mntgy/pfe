<?php
session_start(); // Start session
require 'db.php'; // Database connection

// ✅ Allow both admins & users
if (!isset($_SESSION['admin_logged_in']) && !isset($_SESSION['user_logged_in'])) {
    header("Location: login.php"); // Redirect if not logged in
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
    <style>
        /* 🌟 Layout */
        body {
            font-family: Arial, sans-serif;
            background: #f8f9fc;
            margin: 0;
            display: flex;
        }

        .main-content {
            flex-grow: 1;
            padding: 20px;
            background: white;
            min-height: 100vh;
            margin-left: 260px;
        }

        h1 {
            color: #007bff;
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px solid #007bff;
            padding-bottom: 10px;
        }

        /* 🌟 Table Styles */
        .table-container {
            max-width: 1000px;
            margin: auto;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #007bff;
            color: white;
        }

        tr:hover {
            background: #f1f1f1;
        }

        .no-alerts {
            text-align: center;
            font-size: 16px;
            color: #555;
            margin-top: 20px;
        }
    </style>
</head>
<body>

    <!-- Include Sidebar -->
    <?php include 'sidebar.php'; ?>

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
                                <td><?php echo nl2br(htmlspecialchars($alert['message'])); ?></td>
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

</body>
</html>
