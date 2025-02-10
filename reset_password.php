<?php
session_start();
require 'db.php'; // Connexion à la base de données

// Vérifier si l'admin est connecté
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

// Gestion du changement de mot de passe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $user_id = $_POST['user_id'];
    $new_password = trim($_POST['new_password']);
    
    if (empty($new_password)) {
        $error = "Le nouveau mot de passe est requis.";
    } else {
        // Hacher le mot de passe
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        
        // Mettre à jour le mot de passe dans la base de données
        $sql = "UPDATE users SET password = :password WHERE id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'password' => $hashed_password,
            'user_id' => $user_id
        ]);
        
        $success = "Le mot de passe a été réinitialisé avec succès.";
    }
}

// Récupérer tous les utilisateurs
$stmt = $pdo->query('SELECT id, username FROM users');
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialiser le mot de passe</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php include 'sidebar.php'; ?> <!-- Include sidebar -->
    <h1>Réinitialiser le mot de passe</h1>

    <!-- Afficher les messages de succès ou d'erreur -->
    <?php if (!empty($error)): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <p class="success"><?php echo $success; ?></p>
    <?php endif; ?>

    <!-- Formulaire pour réinitialiser le mot de passe -->
    <form method="POST">
        <label for="user_id">Sélectionner l'utilisateur :</label>
        <select name="user_id" required>
            <?php foreach ($users as $user): ?>
                <option value="<?php echo $user['id']; ?>"><?php echo $user['username']; ?></option>
            <?php endforeach; ?>
        </select>

        <label for="new_password">Nouveau mot de passe :</label>
        <input type="password" id="new_password" name="new_password" required>

        <button type="submit" name="reset_password">Réinitialiser le mot de passe</button>
    </form>
</body>
</html>
