<?php
session_start();
require 'db.php'; // Include database connection

// Ensure only admins can access this page
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Fetch logs from the database
$stmt = $pdo->query("SELECT * FROM logs ORDER BY created_at DESC");
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Logs</title>
    <style>
        /* General body styling */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f4f4f9; /* Light background color */
        }

        /* Container for the logs */
        .logs-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #ffffff; /* White background */
            border-radius: 10px; /* Rounded corners */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Subtle shadow */
        }

        /* Table styling */
        .logs-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .logs-table th,
        .logs-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .logs-table th {
            background: #007bff; /* Blue header */
            color: #ffffff; /* White text */
        }

        .logs-table tr:hover {
            background: #f1f1f1; /* Hover effect */
        }

        /* Button styling */
        .back-button {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 15px;
            background: #007bff; /* Blue background */
            color: #ffffff; /* White text */
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s ease;
        }

        .back-button:hover {
            background: #0056b3; /* Darker blue */
        }
    </style>
</head>
<body>

    <div class="logs-container">
        <a href="index.php" class="back-button">Back to Dashboard</a>
        <h1>Admin Logs</h1>
        <table class="logs-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Action</th>
                    <th>Details</th>
                    <th>IP Address</th>
                    <th>Date & Time</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?php echo $log['id']; ?></td>
                        <td><?php echo htmlspecialchars($log['username']); ?></td>
                        <td><?php echo htmlspecialchars($log['action']); ?></td>
                        <td><?php echo htmlspecialchars($log['details']); ?></td>
                        <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                        <td><?php echo htmlspecialchars($log['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>