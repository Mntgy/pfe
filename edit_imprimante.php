<?php
session_start();
require 'db.php';

// Fetch printer data to edit
if (isset($_GET['id'])) {
    $imprimante_id = $_GET['id'];
    $sql = "SELECT * FROM imprimantes WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $imprimante_id]);
    $imprimante = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if printer exists
    if (!$imprimante) {
        die('Imprimante not found');
    }
}

// Handle form submission to update the printer
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $model = $_POST['model'];
    $status = $_POST['status'];

    $sql_update = "UPDATE imprimantes SET name = :name, model = :model, status = :status WHERE id = :id";
    $stmt = $pdo->prepare($sql_update);
    $stmt->execute(['name' => $name, 'model' => $model, 'status' => $status, 'id' => $imprimante_id]);

    header("Location: imprimantes.php"); // Redirect after successful update
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier l'Imprimante</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?> <!-- Include sidebar -->

    <!-- Main Content -->
    <div class="main-content">
        <header>
            <h1>Modifier l'Imprimante</h1>
        </header>
        <main>
            <form method="post" action="edit_imprimante.php?id=<?php echo $imprimante['id']; ?>">
                <label for="name">Nom de l'Imprimante:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($imprimante['name']); ?>" required>

                <label for="model">Modèle:</label>
                <input type="text" id="model" name="model" value="<?php echo htmlspecialchars($imprimante['model']); ?>" required>

                <label for="status">Statut:</label>
                <select name="status" id="status" required>
                    <option value="En service" <?php echo ($imprimante['status'] == 'En service') ? 'selected' : ''; ?>>En service</option>
                    <option value="Hors service" <?php echo ($imprimante['status'] == 'Hors service') ? 'selected' : ''; ?>>Hors service</option>
                    <option value="En réparation" <?php echo ($imprimante['status'] == 'En réparation') ? 'selected' : ''; ?>>En réparation</option>
                </select>

                <button type="submit">Mettre à jour</button>
            </form>
        </main>
    </div>
</body>
</html>
