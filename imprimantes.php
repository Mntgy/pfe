<?php
session_start();
require 'db.php';

// Fetch all printers from the database
$sql = "SELECT * FROM imprimantes";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$imprimantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle delete
if (isset($_GET['delete_id'])) {
    $imprimante_id = $_GET['delete_id'];
    $sql_delete = "DELETE FROM imprimantes WHERE id = :id";
    $stmt = $pdo->prepare($sql_delete);
    $stmt->execute(['id' => $imprimante_id]);
    header("Location: imprimantes.php"); // Redirect after deletion
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Imprimantes</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?> <!-- Include sidebar -->

    <!-- Main Content -->
    <div class="main-content">
        <header>
            <h1>Liste des Imprimantes</h1>
        </header>
        <main>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Modèle</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($imprimantes as $imprimante): ?>
                        <tr>
                            <td><?php echo $imprimante['id']; ?></td>
                            <td><?php echo htmlspecialchars($imprimante['name']); ?></td>
                            <td><?php echo htmlspecialchars($imprimante['model']); ?></td>
                            <td><?php echo $imprimante['status']; ?></td>
                            <td>
                                <a href="edit_imprimante.php?id=<?php echo $imprimante['id']; ?>">Modifier</a>
                                <a href="imprimantes.php?delete_id=<?php echo $imprimante['id']; ?>" onclick="return confirm('Are you sure you want to delete this printer?');">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <!-- Add Printer button visible for both admin and user -->
            <a href="add_imprimante.php" class="button">Ajouter une Imprimante</a>
        </main>
    </div>
</body>
</html>
