<?php
session_start();
require 'db.php'; // Include the database connection

// Fetch phone data to edit
if (isset($_GET['id'])) {
    $telephone_id = $_GET['id'];
    $sql = "SELECT * FROM telephones WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $telephone_id]);
    $telephone = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if phone exists
    if (!$telephone) {
        die('Téléphone not found');
    }
}

// Handle form submission to update the phone
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brand = $_POST['brand'];
    $model = $_POST['model'];
    $status = $_POST['status'];

    $sql_update = "UPDATE telephones SET brand = :brand, model = :model, status = :status WHERE id = :id";
    $stmt = $pdo->prepare($sql_update);
    $stmt->execute(['brand' => $brand, 'model' => $model, 'status' => $status, 'id' => $telephone_id]);

    header("Location: telephones.php"); // Redirect after successful update
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un Téléphone</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to CSS -->
</head>
<body>
    <?php include 'sidebar.php'; ?> <!-- Include sidebar -->

    <div class="main-content">
        <header>
            <h1>Modifier un Téléphone</h1>
        </header>
        <main>
            <form method="post" action="edit_telephone.php?id=<?php echo $telephone['id']; ?>">
                <label for="brand">Marque:</label>
                <input type="text" id="brand" name="brand" value="<?php echo htmlspecialchars($telephone['brand']); ?>" required>

                <label for="model">Modèle:</label>
                <input type="text" id="model" name="model" value="<?php echo htmlspecialchars($telephone['model']); ?>" required>

                <label for="status">Statut:</label>
                <select name="status" id="status" required>
                    <option value="En service" <?php echo ($telephone['status'] == 'En service') ? 'selected' : ''; ?>>En service</option>
                    <option value="Hors service" <?php echo ($telephone['status'] == 'Hors service') ? 'selected' : ''; ?>>Hors service</option>
                    <option value="En réparation" <?php echo ($telephone['status'] == 'En réparation') ? 'selected' : ''; ?>>En réparation</option>
                </select>

                <button type="submit">Mettre à jour</button>
            </form>
        </main>
    </div>
</body>
</html>
