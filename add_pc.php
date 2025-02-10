<?php
session_start();
require 'db.php';

// Handling form submission to add a new PC
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $user = $_POST['user'];
    $status = $_POST['status'];

    // Insert new PC into the database
    $sql = "INSERT INTO pcs (name, user, status) VALUES (:name, :user, :status)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['name' => $name, 'user' => $user, 'status' => $status]);

    header("Location: pc.php"); // Redirect after successful insertion
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un PC</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?> <!-- Include sidebar -->

    <!-- Main Content -->
    <div class="main-content">
        <header>
            <h1>Ajouter un PC</h1>
        </header>
        <main>
            <form method="post" action="add_pc.php">
                <label for="name">Nom du PC:</label>
                <input type="text" id="name" name="name" required>

                <label for="user">Utilisateur:</label>
                <input type="text" id="user" name="user" required>

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
