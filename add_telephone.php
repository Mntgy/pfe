<?php
session_start();
require 'db.php'; // Include the database connection

// Handle form submission to add a new phone
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brand = $_POST['brand'];
    $model = $_POST['model'];
    $status = $_POST['status'];

    // Insert new phone into the database
    $sql = "INSERT INTO telephones (brand, model, status) VALUES (:brand, :model, :status)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['brand' => $brand, 'model' => $model, 'status' => $status]);

    header("Location: telephones.php"); // Redirect after successful insertion
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Téléphone</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to CSS -->
</head>
<body>
    <?php include 'sidebar.php'; ?> <!-- Include sidebar -->

    <div class="main-content">
        <header>
            <h1>Ajouter un Téléphone</h1>
        </header>
        <main>
            <form method="post" action="add_telephone.php">
                <label for="brand">Marque:</label>
                <input type="text" id="brand" name="brand" required>

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
