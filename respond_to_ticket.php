<?php
session_start();
require 'db.php'; // Include the database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticket_id = $_POST['ticket_id'];
    $response = $_POST['response'];

    // Get the ticket details from the database
    $sql = "SELECT * FROM tickets WHERE id = :ticket_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['ticket_id' => $ticket_id]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($ticket) {
        // Prepare the email to respond to the user
        $to = $ticket['email'];
        $subject = "Response to your ticket: " . htmlspecialchars($ticket['subject']);
        $message = "Hello,\n\nHere is the response to your ticket:\n\n" . $response;
        $headers = "From: your_email@example.com";

        // Send the email
        if (mail($to, $subject, $message, $headers)) {
            // Optionally update the ticket status in the database
            $update_sql = "UPDATE tickets SET status = 'En cours' WHERE id = :ticket_id";
            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->execute(['ticket_id' => $ticket_id]);

            // Optional: Store the response in the database for auditing
            $response_sql = "INSERT INTO ticket_responses (ticket_id, response, created_at) VALUES (:ticket_id, :response, NOW())";
            $response_stmt = $pdo->prepare($response_sql);
            $response_stmt->execute(['ticket_id' => $ticket_id, 'response' => $response]);

            echo "Response sent successfully!";
        } else {
            echo "Failed to send response.";
        }
    } else {
        echo "Ticket not found.";
    }
}
?>
