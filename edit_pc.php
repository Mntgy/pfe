<?php
session_start();
require 'db.php';

// Fetch PC data to edit
if (isset($_GET['id'])) {
    $pc_id = $_GET['id'];
    $sql = "SELECT * FROM pcs WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $pc_id]);
    $pc = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if PC exists
    if (!$pc) {
        die('PC not found');
    }
}

// Handle form submission to update the PC
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $user = $_POST['user'];
    $status = $_POST['status'];

    $sql_update = "UPDATE pcs SET name = :name, user = :user, status = :status WHERE id = :id";
    $stmt = $pdo->prepare($sql_update);
    $stmt->execute(['name' => $name, 'user' => $user, 'status' => $status, 'id' => $pc_id]);

    header("Location: pc.php"); // Redirect after successful update
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le PC</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?> <!-- Include sidebar -->

    <!-- Main Content -->
    <div class="main-content">
        <header>
            <h1>Modifier le PC</h1>
        </header>
        <main>
            <form method="post" action="edit_pc.php?id=<?php echo $pc['id']; ?>">
                <label for="name">Nom du PC:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($pc['name']); ?>" required>

                <label for="user">Utilisateur:</label>
                <input type="text" id="user" name="user" value="<?php echo htmlspecialchars($pc['user']); ?>" required>

                <label for="status">Statut:</label>
                <select name="status" id="status" required>
                    <option value="En service" <?php echo ($pc['status'] == 'En service') ? 'selected' : ''; ?>>En service</option>
                    <option value="Hors service" <?php echo ($pc['status'] == 'Hors service') ? 'selected' : ''; ?>>Hors service</option>
                    <option value="En réparation" <?php echo ($pc['status'] == 'En réparation') ? 'selected' : ''; ?>>En réparation</option>
                </select>

                <button type="submit">Mettre à jour</button>
            </form>
        </main>
    </div>
</body>
</html>
