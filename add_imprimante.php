<?php
session_start();
require 'db.php';

// Handling form submission to add a new printer
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $model = $_POST['model'];
    $status = $_POST['status'];

    // Insert new printer into the database
    $sql = "INSERT INTO imprimantes (name, model, status) VALUES (:name, :model, :status)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['name' => $name, 'model' => $model, 'status' => $status]);

    header("Location: imprimantes.php"); // Redirect after successful insertion
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une Imprimante</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?> <!-- Include sidebar -->

    <!-- Main Content -->
    <div class="main-content">
        <header>
            <h1>Ajouter une Imprimante</h1>
        </header>
        <main>
            <form method="post" action="add_imprimante.php">
                <label for="name">Nom de l'Imprimante:</label>
                <input type="text" id="name" name="name" required>

                <label for="model">Modèle:</label>
                <input type="text" id="model" name="model" required>

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
