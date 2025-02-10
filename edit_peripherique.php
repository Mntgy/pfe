<?php
session_start();
require 'db.php';

// Fetch peripheral data to edit
if (isset($_GET['id'])) {
    $peripherique_id = $_GET['id'];
    $sql = "SELECT * FROM peripheriques WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $peripherique_id]);
    $peripherique = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if peripheral exists
    if (!$peripherique) {
        die('Peripheral not found');
    }
}

// Handle form submission to update the peripheral
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $type = $_POST['type'];
    $status = $_POST['status'];

    $sql_update = "UPDATE peripheriques SET name = :name, type = :type, status = :status WHERE id = :id";
    $stmt = $pdo->prepare($sql_update);
    $stmt->execute(['name' => $name, 'type' => $type, 'status' => $status, 'id' => $peripherique_id]);

    header("Location: peripheriques.php"); // Redirect after successful update
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le Périphérique</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?> <!-- Include sidebar -->

    <!-- Main Content -->
    <div class="main-content">
        <header>
            <h1>Modifier le Périphérique</h1>
        </header>
        <main>
            <form method="post" action="edit_peripherique.php?id=<?php echo $peripherique['id']; ?>">
                <label for="name">Nom du Périphérique:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($peripherique['name']); ?>" required>

                <label for="type">Type:</label>
                <input type="text" id="type" name="type" value="<?php echo htmlspecialchars($peripherique['type']); ?>" required>

                <label for="status">Statut:</label>
                <select name="status" id="status" required>
                    <option value="En service" <?php echo ($peripherique['status'] == 'En service') ? 'selected' : ''; ?>>En service</option>
                    <option value="Hors service" <?php echo ($peripherique['status'] == 'Hors service') ? 'selected' : ''; ?>>Hors service</option>
                    <option value="En réparation" <?php echo ($peripherique['status'] == 'En réparation') ? 'selected' : ''; ?>>En réparation</option>
                </select>

                <button type="submit">Mettre à jour</button>
            </form>
        </main>
    </div>
</body>
</html>
