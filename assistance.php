<?php
session_start();
require 'db.php'; // Include the database connection

// Manually include the necessary PHPMailer files
require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

// ✅ Handle status change submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $ticket_id = $_POST['ticket_id'];
    $status = $_POST['status'];

    // Update ticket status in the database
    $update_sql = "UPDATE tickets SET status = :status WHERE id = :id";
    $update_stmt = $pdo->prepare($update_sql);
    $update_stmt->execute(['status' => $status, 'id' => $ticket_id]);

    header('Location: assistance.php'); // Redirect after update
    exit();
}

// ✅ Fetch all tickets
$sql = "SELECT * FROM tickets ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ Handle response submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['response_message'])) {
    $ticket_id = $_POST['ticket_id'];
    $response_message = trim($_POST['response_message']);

    // Fetch ticket details
    $sql = "SELECT * FROM tickets WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $ticket_id]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($ticket) {
        // Extract email from "name <email>" format
        $to = filter_var(trim(preg_replace('/.*<(.*)>/', '$1', $ticket['email'])), FILTER_VALIDATE_EMAIL);
        if (!$to) {
            die("Invalid email address: " . htmlspecialchars($to));
        }

        // Prepare email
        $mail = new PHPMailer\PHPMailer\PHPMailer();
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'tickettourelle@gmail.com'; // Change this
        $mail->Password = 'ebfi dijo fbix zadn '; // Use App Password if 2FA is enabled
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('your-email@gmail.com', 'Support Team');
        $mail->addAddress($to);
        $mail->Subject = 'Response to your ticket - ' . htmlspecialchars($ticket['subject']);
        $mail->Body = nl2br($response_message);
        $mail->isHTML(true);

        if ($mail->send()) {
            // Update ticket status to "Responded"
            $update_sql = "UPDATE tickets SET status = 'Responded' WHERE id = :id";
            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->execute(['id' => $ticket_id]);

            header('Location: assistance.php'); // Redirect
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
    <style>
        /* 🌟 Table Layout */
        .table-container {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #007bff;
            color: white;
        }

        tr:hover {
            background: #f1f1f1;
        }

        /* 🌟 Response Button */
        .respond-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 8px 12px;
            cursor: pointer;
            border-radius: 4px;
        }

        .respond-btn:hover {
            background: #218838;
        }

        /* 🌟 Response Form */
        .response-form {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .response-form textarea {
            width: 100%;
            height: 60px;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            resize: none;
        }

        /* Ensure everything looks good on small screens */
        @media (max-width: 768px) {
            th, td {
                font-size: 14px;
            }
            .respond-btn {
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="layout-container">
        <?php include 'sidebar.php'; ?> <!-- Include Sidebar -->

        <div class="main-content">
            <h1>Liste des Tickets</h1>

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
                                <td><?php echo nl2br(htmlspecialchars($ticket['message'])); ?></td>
                                <td><?php echo $ticket['created_at']; ?></td>
                                <td>
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
                                    <form class="response-form" method="POST" action="assistance.php">
                                        <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                        <textarea name="response_message" placeholder="Write your response..." required></textarea>
                                        <button type="submit" class="respond-btn">Respond</button>
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
