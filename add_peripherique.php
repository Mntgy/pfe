<?php
session_start();
require 'db.php';

// Handling form submission to add a new peripheral
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $type = $_POST['type'];
    $status = $_POST['status'];

    // Insert new peripheral into the database
    $sql = "INSERT INTO peripheriques (name, type, status) VALUES (:name, :type, :status)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['name' => $name, 'type' => $type, 'status' => $status]);

    header("Location: peripheriques.php"); // Redirect after successful insertion
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Périphérique</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?> <!-- Include sidebar -->

    <!-- Main Content -->
    <div class="main-content">
        <header>
            <h1>Ajouter un Périphérique</h1>
        </header>
        <main>
            <form method="post" action="add_peripherique.php">
                <label for="name">Nom du Périphérique:</label>
                <input type="text" id="name" name="name" required>

                <label for="type">Type:</label>
                <input type="text" id="type" name="type" required>

                <label for="status">Statut:</label>
                <select name="status" id="status" required>
                    <option value="En service">En service</option>
                    <option value="Hors service">Hors service</option>
                    <option value="En réparation">En réparation</option>
                </select>

                <button type="submit">Ajouter</button>
            </form>
        </main>
    </div>
</body>
</html>
