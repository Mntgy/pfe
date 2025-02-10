<?php
session_start();
require 'db.php'; // Include the database connection

// Manually include the necessary PHPMailer files
require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

// Handle status change submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $ticket_id = $_POST['ticket_id'];
    $status = $_POST['status'];

    // Update the ticket status in the database
    $update_sql = "UPDATE tickets SET status = :status WHERE id = :id";
    $update_stmt = $pdo->prepare($update_sql);
    $update_stmt->execute(['status' => $status, 'id' => $ticket_id]);

    // Redirect back to the assistance page
    header('Location: assistance.php');
    exit();
}

// Fetch all tickets from the database
$sql = "SELECT * FROM tickets ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission for ticket responses
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['response_message'])) {
    $ticket_id = $_POST['ticket_id'];
    $response_message = $_POST['response_message'];

    // Fetch the ticket details from the database
    $sql = "SELECT * FROM tickets WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $ticket_id]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

    // Ensure the ticket exists
    if ($ticket) {
        // Extract the email from the "name <email>" format
        $to = trim(preg_replace('/.*<(.*)>/', '$1', $ticket['email'])); // Extract only the email part

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            echo "Invalid email address: " . htmlspecialchars($to);
            exit();
        }

        // Prepare email content
        $subject = 'Response to your ticket - ' . $ticket['subject'];
        $body = 'Hello, <br><br>' . nl2br($response_message) . '<br><br>Best regards'; // Using HTML for the body

        // Set up PHPMailer to send the response
        $mail = new PHPMailer\PHPMailer\PHPMailer();
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Gmail SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'tickettourelle@gmail.com'; // Your Gmail address
        $mail->Password = 'ebfi dijo fbix zadn'; // Your Gmail password (use app password if 2FA enabled)
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('your-email@gmail.com', 'Support Team');
        $mail->addAddress($to); // Send to the user email
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->isHTML(true); // Set email format to HTML

        // Send the email
        if ($mail->send()) {
            // Update the ticket status to "Responded"
            $update_sql = "UPDATE tickets SET status = 'Responded' WHERE id = :id";
            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->execute(['id' => $ticket_id]);

            // Redirect back to the assistance page
            header('Location: assistance.php');
            exit();
        } else {
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        }
    } else {
        echo 'Ticket not found';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assistance - Tickets</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="layout-container">
        <?php include 'sidebar.php'; ?> <!-- Sidebar -->

        <div class="main-content">
            <h1>Liste des Tickets</h1>

            <!-- Scrollable table container -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Email</th>
                            <th>Objet</th>
                            <th>Message</th>
                            <th>Date</th>
                            <th>Statut</th>
                            <th>Response</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $ticket): ?>
                            <tr>
                                <td><?php echo $ticket['id']; ?></td>
                                <td><?php echo htmlspecialchars($ticket['email']); ?></td>
                                <td><?php echo htmlspecialchars(mb_decode_mimeheader($ticket['subject'])); ?></td>
                                <td class="message-column"><?php echo nl2br(htmlspecialchars($ticket['message'])); ?></td>
                                <td><?php echo $ticket['created_at']; ?></td>
                                <td>
                                    <!-- Status update form -->
                                    <form method="post" action="assistance.php">
                                        <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                        <select name="status" onchange="this.form.submit()">
                                            <option value="Nouveau" <?php echo ($ticket['status'] === 'Nouveau') ? 'selected' : ''; ?>>Nouveau</option>
                                            <option value="En cours" <?php echo ($ticket['status'] === 'En cours') ? 'selected' : ''; ?>>En cours</option>
                                            <option value="Résolu" <?php echo ($ticket['status'] === 'Résolu') ? 'selected' : ''; ?>>Résolu</option>
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <!-- Response form for admin -->
                                    <form method="POST" action="assistance.php">
                                        <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                        <textarea name="response_message" rows="4" placeholder="Write your response here..." required></textarea>
                                        <button type="submit">Respond</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
