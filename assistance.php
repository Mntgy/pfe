<?php
session_start();

// Enhanced error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// Database connection with improved error handling
try {
    require 'db.php';
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("<div class='error-message'>System temporarily unavailable. Please try again later.</div>");
}

// Include PHPMailer files
require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['status'])) {
        // Status change handling
        handleStatusChange($pdo);
    } elseif (isset($_POST['response_message'])) {
        // Ticket response handling
        handleTicketResponse($pdo);
    }
}

// Fetch tickets with pagination
$tickets = fetchTickets($pdo);
$totalTickets = countTickets($pdo);

// Function to handle status changes
function handleStatusChange($pdo) {
    try {
        $update_sql = "UPDATE tickets SET status = :status, updated_at = NOW() WHERE id = :id";
        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->execute([
            'status' => $_POST['status'],
            'id' => $_POST['ticket_id']
        ]);
        
        $_SESSION['flash_message'] = "Ticket status updated successfully";
        header('Location: assistance.php');
        exit();
    } catch (PDOException $e) {
        error_log("Status update failed: " . $e->getMessage());
        $_SESSION['error'] = "Failed to update ticket status";
    }
}

// Function to handle ticket responses
function handleTicketResponse($pdo) {
    try {
        // Get ticket details
        $ticket = getTicketById($pdo, $_POST['ticket_id']);
        if (!$ticket) {
            $_SESSION['error'] = 'Ticket not found';
            return;
        }

        // Send email response
        if (sendEmailResponse($ticket, $_POST['response_message'])) {
            // Update ticket status
            $update_sql = "UPDATE tickets SET status = 'Responded', updated_at = NOW() WHERE id = :id";
            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->execute(['id' => $ticket['id']]);
            
            $_SESSION['flash_message'] = "Response sent successfully";
            header('Location: assistance.php');
            exit();
        }
    } catch (Exception $e) {
        error_log("Response failed: " . $e->getMessage());
        $_SESSION['error'] = "Failed to send response";
    }
}

// Function to fetch tickets with optional pagination
function fetchTickets($pdo, $limit = 20, $offset = 0) {
    try {
        $sql = "SELECT * FROM tickets ORDER BY 
                CASE WHEN status = 'Nouveau' THEN 0 
                     WHEN status = 'En cours' THEN 1
                     WHEN status = 'Responded' THEN 2
                     ELSE 3 END, created_at DESC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Failed to fetch tickets: " . $e->getMessage());
        return [];
    }
}

// Function to count total tickets
function countTickets($pdo) {
    try {
        return $pdo->query("SELECT COUNT(*) FROM tickets")->fetchColumn();
    } catch (PDOException $e) {
        error_log("Failed to count tickets: " . $e->getMessage());
        return 0;
    }
}

// Function to get single ticket by ID
function getTicketById($pdo, $id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM tickets WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Failed to fetch ticket: " . $e->getMessage());
        return false;
    }
}

// Function to send email response
function sendEmailResponse($ticket, $message) {
    $to = filter_var(trim(preg_replace('/.*<(.*)>/', '$1', $ticket['email'])), FILTER_VALIDATE_EMAIL);
    if (!$to) {
        $_SESSION['error'] = "Invalid email address";
        return false;
    }

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'tickettourelle@gmail.com';
        $mail->Password = 'ebfi dijo fbix zadn ';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('support@example.com', 'Support Team');
        $mail->addAddress($to);
        $mail->Subject = 'Re: ' . htmlspecialchars($ticket['subject']);
        
        // Build professional email body
        $mail->Body = buildEmailBody($ticket, $message);
        $mail->isHTML(true);
        
        return $mail->send();
    } catch (Exception $e) {
        error_log("Mailer Error: " . $e->getMessage());
        $_SESSION['error'] = 'Failed to send email: ' . $e->getMessage();
        return false;
    }
}

// Function to build professional email body
function buildEmailBody($ticket, $response) {
    return "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .header { color: #007bff; border-bottom: 1px solid #eee; padding-bottom: 10px; }
            .ticket-info { background: #f9f9f9; padding: 15px; border-left: 3px solid #007bff; margin: 10px 0; }
            .response { margin: 20px 0; padding: 15px; background: #f0f8ff; border-left: 3px solid #4CAF50; }
            .footer { margin-top: 20px; font-size: 0.9em; color: #777; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h2>Re: " . htmlspecialchars($ticket['subject']) . "</h2>
        </div>
        
        <div class='ticket-info'>
            <p><strong>Original Message:</strong></p>
            <p>" . nl2br(htmlspecialchars($ticket['message'])) . "</p>
        </div>
        
        <div class='response'>
            <p><strong>Our Response:</strong></p>
            <p>" . nl2br(htmlspecialchars($response)) . "</p>
        </div>
        
        <div class='footer'>
            <p>Ticket ID: " . htmlspecialchars($ticket['id']) . "</p>
            <p>Please don't reply to this email. If you need further assistance, 
            reference the ticket ID above in any correspondence.</p>
        </div>
    </body>
    </html>
    ";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Management System</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        :root {
            --primary: #007bff;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --light: #f8f9fa;
            --dark: #343a40;
            --gray: #6c757d;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f7fa;
        }
        
        .layout-container {
            display: flex;
            min-height: 100vh;
        }
        
        .main-content {
            flex: 1;
            padding: 20px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        /* Notification styles */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            position: relative;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .close-alert {
            position: absolute;
            right: 15px;
            top: 15px;
            cursor: pointer;
        }
        
        /* Table styles */
        .table-container {
            width: 100%;
            overflow-x: auto;
            margin-top: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        
        th {
            background-color: var(--primary);
            color: white;
            position: sticky;
            top: 0;
            font-weight: 500;
        }
        
        /* Ticket status styling */
        .ticket-new {
            background-color: rgba(255, 193, 7, 0.1);
            border-left: 4px solid var(--warning);
        }
        
        .ticket-responded {
            background-color: rgba(40, 167, 69, 0.1);
        }
        
        .ticket-resolved {
            background-color: rgba(13, 110, 253, 0.1);
            color: var(--gray);
        }
        
        .ticket-in-progress {
            background-color: rgba(23, 162, 184, 0.1);
        }
        
        tr:hover {
            background-color: rgba(0, 123, 255, 0.05) !important;
        }
        
        /* Status indicators */
        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }
        
        .status-new { background-color: var(--warning); }
        .status-responded { background-color: var(--success); }
        .status-resolved { background-color: var(--primary); }
        .status-in-progress { background-color: #17a2b8; }
        
        /* Buttons and forms */
        .btn {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            font-weight: 500;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #0069d9;
        }
        
        .btn-success {
            background-color: var(--success);
            color: white;
        }
        
        .btn-success:hover {
            background-color: #218838;
        }
        
        .response-form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .response-form textarea {
            width: 100%;
            min-height: 100px;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            resize: vertical;
            transition: border-color 0.3s;
        }
        
        .response-form textarea:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
        }
        
        /* Status dropdown */
        select {
            padding: 8px 12px;
            border-radius: 4px;
            border: 1px solid #ced4da;
            background-color: white;
            cursor: pointer;
        }
        
        select:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
        }
        
        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 5px;
        }
        
        .page-item {
            list-style: none;
        }
        
        .page-link {
            display: block;
            padding: 8px 12px;
            border: 1px solid #dee2e6;
            color: var(--primary);
            text-decoration: none;
            border-radius: 4px;
        }
        
        .page-link:hover {
            background-color: #e9ecef;
        }
        
        .page-link.active {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        /* Responsive adjustments */
        @media (max-width: 992px) {
            .layout-container {
                flex-direction: column;
            }
        }
        
        @media (max-width: 768px) {
            th, td {
                padding: 8px 10px;
                font-size: 14px;
            }
            
            .btn {
                padding: 6px 12px;
                font-size: 14px;
            }
        }
        
        /* Animation for new tickets */
        @keyframes highlight {
            0% { background-color: rgba(255, 193, 7, 0.3); }
            100% { background-color: rgba(255, 193, 7, 0.1); }
        }
        
        .highlight-new {
            animation: highlight 2s ease-out;
        }
        
        /* Badge for ticket count */
        .badge {
            display: inline-block;
            padding: 3px 7px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: bold;
            margin-left: 5px;
        }
        
        .badge-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .badge-warning {
            background-color: var(--warning);
            color: #212529;
        }
    </style>
</head>
<body>
    <div class="layout-container">
        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <h1>Ticket Management 
                <span class="badge badge-primary"><?php echo $totalTickets; ?> total</span>
                <?php if ($newCount = count(array_filter($tickets, fn($t) => $t['status'] === 'Nouveau'))): ?>
                    <span class="badge badge-warning"><?php echo $newCount; ?> new</span>
                <?php endif; ?>
            </h1>
            
            <!-- Flash messages -->
            <?php if (isset($_SESSION['flash_message'])): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($_SESSION['flash_message']); ?>
                    <span class="close-alert">&times;</span>
                </div>
                <?php unset($_SESSION['flash_message']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($_SESSION['error']); ?>
                    <span class="close-alert">&times;</span>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <?php if (empty($tickets)): ?>
                <div class="alert alert-info">No tickets found in the system.</div>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Email</th>
                                <th>Subject</th>
                                <th>Message</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tickets as $ticket): 
                                $ticketDate = new DateTime($ticket['created_at']);
                                $now = new DateTime();
                                $ageInDays = $now->diff($ticketDate)->days;
                                
                                // Determine styling based on status and age
                                $rowClass = '';
                                $statusClass = '';
                                
                                switch ($ticket['status']) {
                                    case 'Nouveau':
                                        $rowClass = 'ticket-new' . ($ageInDays <= 1 ? ' highlight-new' : '');
                                        $statusClass = 'status-new';
                                        break;
                                    case 'Responded':
                                        $rowClass = 'ticket-responded';
                                        $statusClass = 'status-responded';
                                        break;
                                    case 'Résolu':
                                        $rowClass = 'ticket-resolved';
                                        $statusClass = 'status-resolved';
                                        break;
                                    case 'En cours':
                                        $rowClass = 'ticket-in-progress';
                                        $statusClass = 'status-in-progress';
                                        break;
                                    default:
                                        $rowClass = '';
                                        $statusClass = '';
                                }
                            ?>
                                <tr class="<?php echo $rowClass; ?>">
                                    <td><?php echo htmlspecialchars($ticket['id']); ?></td>
                                    <td>
                                        <?php 
                                            $email = htmlspecialchars($ticket['email']);
                                            if (preg_match('/<(.*?)>/', $email, $matches)) {
                                                echo htmlspecialchars(trim(preg_replace('/<.*?>/', '', $email))) . 
                                                     " &lt;" . htmlspecialchars($matches[1]) . "&gt;";
                                            } else {
                                                echo $email;
                                            }
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                                    <td>
                                        <div class="message-preview">
                                            <?php 
                                                $message = htmlspecialchars($ticket['message']);
                                                echo strlen($message) > 50 ? 
                                                    substr($message, 0, 50) . '...' : 
                                                    $message;
                                            ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span title="<?php echo htmlspecialchars($ticket['created_at']); ?>">
                                            <?php echo $ticketDate->format('M j, Y'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-indicator <?php echo $statusClass; ?>"></span>
                                        <form method="post" action="assistance.php" class="status-form">
                                            <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                            <select name="status" onchange="this.form.submit()">
                                                <option value="Nouveau" <?php echo ($ticket['status'] === 'Nouveau') ? 'selected' : ''; ?>>New</option>
                                                <option value="En cours" <?php echo ($ticket['status'] === 'En cours') ? 'selected' : ''; ?>>In Progress</option>
                                                <option value="Responded" <?php echo ($ticket['status'] === 'Responded') ? 'selected' : ''; ?>>Responded</option>
                                                <option value="Résolu" <?php echo ($ticket['status'] === 'Résolu') ? 'selected' : ''; ?>>Resolved</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td>
                                        <form class="response-form" method="POST" action="assistance.php">
                                            <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                            <textarea name="response_message" placeholder="Type your response here..." required></textarea>
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-reply"></i> Respond
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Simple pagination -->
                <div class="pagination">
                    <li class="page-item"><a href="?page=1" class="page-link">First</a></li>
                    <li class="page-item"><a href="?page=1" class="page-link">1</a></li>
                    <li class="page-item"><a href="?page=2" class="page-link">2</a></li>
                    <li class="page-item"><a href="?page=3" class="page-link">3</a></li>
                    <li class="page-item"><a href="?page=1" class="page-link">Last</a></li>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Close alert messages
        document.querySelectorAll('.close-alert').forEach(btn => {
            btn.addEventListener('click', function() {
                this.parentElement.style.display = 'none';
            });
        });
        
        // Auto-focus the first response textarea
        document.querySelector('textarea[name="response_message"]')?.focus();
    </script>
</body>
</html>