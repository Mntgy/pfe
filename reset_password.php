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
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialiser le mot de passe</title>
    <style>
        /* 🌟 Global Styles */
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fc;
            color: #333;
            display: flex;
        }

        /* 🌟 Sidebar Styles (Improved Dropdown Formatting) */
        .sidebar {
            width: 260px;
            height: 100vh;
            background: #2c3e50;
            color: white;
            padding: 20px;
            position: fixed;
            left: 0;
            top: 0;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.2);
            overflow-y: auto;
            z-index: 1000;
        }

        .sidebar h2 {
            text-align: center;
            font-size: 1.6rem;
            margin-bottom: 20px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.3);
            padding-bottom: 10px;
        }

        /* 🌟 Sidebar Menu Styles */
        .sidebar ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        .sidebar ul li {
            position: relative;
            margin-bottom: 8px;
        }

        .sidebar ul li a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 10px;
            background: #3a4a5f;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .sidebar ul li a:hover {
            background: #556377;
        }

        /* 🌟 Dropdown Styling */
        .sidebar ul li ul {
            list-style: none;
            padding-left: 15px;
            display: none;
        }

        .sidebar ul li:hover ul {
            display: block;
        }

        /* 🌟 Main Content */
        .main-content {
            margin-left: 280px;
            padding: 40px;
            flex-grow: 1;
            background-color: white;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
        }

        h1 {
            font-size: 2rem;
            color: #007bff;
            margin-bottom: 20px;
            border-bottom: 3px solid #007bff;
            padding-bottom: 10px;
            text-align: center;
        }

        /* 🌟 Form Styling */
        .form-container {
            width: 100%;
            max-width: 500px;
            padding: 20px;
            background: white;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            text-align: center;
        }

        form input, form select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        form button {
            width: 100%;
            background: #28a745;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
        }

        form button:hover {
            background: #218838;
        }

        /* 🌟 Message Styles */
        .message {
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            font-size: 14px;
            width: 100%;
            max-width: 500px;
        }

        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* 🌟 Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                width: 220px;
            }
            .main-content {
                margin-left: 240px;
                padding: 20px;
            }
        }

        @media (max-width: 480px) {
            .sidebar {
                width: 200px;
                padding: 10px;
            }
            .main-content {
                margin-left: 220px;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Menu</h2>
        <ul>
            <li><a href="#">Stock ▼</a>
                <ul>
                    <li><a href="#">PC</a></li>
                    <li><a href="#">Périphériques</a></li>
                    <li><a href="#">Imprimantes</a></li>
                    <li><a href="#">Téléphones</a></li>
                </ul>
            </li>
            <li><a href="#">Assistance ▼</a>
                <ul>
                    <li><a href="#">Tickets</a></li>
                </ul>
            </li>
            <li><a href="#">Alert ▼</a>
                <ul>
                    <li><a href="#">View Alerts</a></li>
                </ul>
            </li>
            <li><a href="#">Admin ▼</a>
                <ul>
                    <li><a href="#">Gérer les utilisateurs</a></li>
                    <li><a href="#">Réinitialiser mot de passe utilisateurs</a></li>
                    <li><a href="#">Edit Password</a></li>
                    <li><a href="#">Déconnexion</a></li>
                </ul>
            </li>
        </ul>
    </div>

    <div class="main-content">
        <h1>Réinitialiser le mot de passe</h1>
        <div class="form-container">
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
        </div>
    </div>
</body>
</html>
