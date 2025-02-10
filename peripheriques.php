<?php
session_start();
require 'db.php';

// Fetch all peripherals from the database
$sql = "SELECT * FROM peripheriques";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$peripheriques = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle delete
if (isset($_GET['delete_id'])) {
    $peripherique_id = $_GET['delete_id'];
    $sql_delete = "DELETE FROM peripheriques WHERE id = :id";
    $stmt = $pdo->prepare($sql_delete);
    $stmt->execute(['id' => $peripherique_id]);
    header("Location: peripheriques.php"); // Redirect after deletion
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Périphériques</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?> <!-- Include sidebar -->

    <!-- Main Content -->
    <div class="main-content">
        <header>
            <h1>Gestion des Périphériques</h1>
        </header>
        <main>
            <h2>Liste des Périphériques</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Type</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($peripheriques as $p): ?>
                        <tr>
                            <td><?php echo $p['id']; ?></td>
                            <td><?php echo htmlspecialchars($p['name']); ?></td>
                            <td><?php echo $p['type']; ?></td>
                            <td><?php echo $p['status']; ?></td>
                            <td>
                                <a href="edit_peripherique.php?id=<?php echo $p['id']; ?>">Modifier</a>
                                <a href="peripheriques.php?delete_id=<?php echo $p['id']; ?>" onclick="return confirm('Are you sure you want to delete this peripheral?');">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <!-- Add Peripheral button visible for both admin and user -->
            <a href="add_peripherique.php" class="button">Ajouter un Périphérique</a>
        </main>
    </div>
</body>
</html>
