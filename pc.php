<?php
session_start();
require 'db.php';

// Fetch all PCs from the database
$sql = "SELECT * FROM pcs";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$pcList = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle delete
if (isset($_GET['delete_id'])) {
    $pc_id = $_GET['delete_id'];
    $sql_delete = "DELETE FROM pcs WHERE id = :id";
    $stmt = $pdo->prepare($sql_delete);
    $stmt->execute(['id' => $pc_id]);
    header("Location: pc.php"); // Redirect after deletion
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des PC</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?> <!-- Include sidebar -->

    <!-- Main Content -->
    <div class="main-content">
        <header>
            <h1>Gestion des PC</h1>
        </header>
        <main>
            <h2>Liste des PCs dans l'entreprise</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Utilisateur</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pcList as $pc): ?>
                        <tr>
                            <td><?php echo $pc['id']; ?></td>
                            <td><?php echo htmlspecialchars($pc['name']); ?></td>
                            <td><?php echo htmlspecialchars($pc['user']); ?></td>
                            <td><?php echo $pc['status']; ?></td>
                            <td>
                                <a href="edit_pc.php?id=<?php echo $pc['id']; ?>">Modifier</a>
                                <a href="pc.php?delete_id=<?php echo $pc['id']; ?>" onclick="return confirm('Are you sure you want to delete this PC?');">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Add PC button: visible for both admin and user -->
            <a href="add_pc.php" class="button">Ajouter un PC</a>
        </main>
    </div>
</body>
</html>
