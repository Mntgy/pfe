<?php
require 'db.php'; // Connexion à la base de données

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Supprimer l'imprimante
    $sql = "DELETE FROM imprimantes WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);

    // Redirection vers la liste des imprimantes
    header('Location: imprimantes.php');
    exit;
}
?>
