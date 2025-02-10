<?php
session_start();
require 'db.php'; // Include the database connection

// Fetch all telephones from the database
$sql = "SELECT * FROM telephones";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$telephones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle delete action
if (isset($_GET['delete_id'])) {
    $telephone_id = $_GET['delete_id'];
    $sql_delete = "DELETE FROM telephones WHERE id = :id";
    $stmt = $pdo->prepare($sql_delete);
    $stmt->execute(['id' => $telephone_id]);
    header("Location: telephones.php"); // Redirect after deletion
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Téléphones</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to CSS -->
</head>
<body>
    <?php include 'sidebar.php'; ?> <!-- Include sidebar -->

    <div class="main-content">
        <header>
            <h1>Liste des Téléphones</h1>
        </header>
        <main>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Marque</th>
                            <th>Modèle</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($telephones as $telephone): ?>
                            <tr>
                                <td><?php echo $telephone['id']; ?></td>
                                <td><?php echo htmlspecialchars($telephone['brand']); ?></td>
                                <td><?php echo htmlspecialchars($telephone['model']); ?></td>
                                <td><?php echo $telephone['status']; ?></td>
                                <td>
                                    <a href="edit_telephone.php?id=<?php echo $telephone['id']; ?>">Modifier</a>
                                    <a href="telephones.php?delete_id=<?php echo $telephone['id']; ?>" onclick="return confirm('Are you sure you want to delete this phone?');">Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- Add phone button visible for both admin and user -->
            <a href="add_telephone.php" class="button">Ajouter un Téléphone</a>
        </main>
    </div>
</body>
</html>
