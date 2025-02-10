<?php
require '../db.php'; // Connexion à la base de données

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "DELETE FROM telephones WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);

    header('Location: telephones.php');
    exit;
}
?>
