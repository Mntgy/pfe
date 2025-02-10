<?php
require 'db.php'; // Database connection

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $status = $_POST['status'];

    // Log the incoming POST data for debugging
    error_log("POST data received: ID = $id, Status = $status");

    try {
        // Validate the status before updating
        $validStatuses = ['Nouveau', 'En cours', 'Résolu'];
        if (!in_array($status, $validStatuses)) {
            throw new Exception("Status invalide.");
        }

        // Prepare and execute the SQL statement to update the status
        $sql = "UPDATE tickets SET status = :status WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'status' => $status,
            'id' => $id,
        ]);

        // Check if the update was successful
        if ($stmt->rowCount() > 0) {
            error_log("Status updated successfully for ticket ID $id");
        } else {
            error_log("No rows were updated. The ticket ID might not exist or the status was already the same.");
        }

        // Redirect back to the ticket list (assistance.php) with the updated data
        header('Location: assistance.php'); // Ensure the path is correct
        exit();

    } catch (Exception $e) {
        // Log the error message for debugging
        error_log("Error: " . $e->getMessage());
        echo "Erreur : " . $e->getMessage();
        exit(); // Ensure no further code runs
    }
} else {
    // Log if the request method is not POST
    error_log("Request method is not POST. It is " . $_SERVER['REQUEST_METHOD']);
}
?>
