<?php
session_start();
session_regenerate_id(true);

// Check user role and login status
if (isset($_SESSION['admin_logged_in'])) {
    $role = 'admin';
} elseif (isset($_SESSION['user_logged_in'])) {
    $role = 'user';
} else {
    header('Location: login.php');
    exit();
}

// Database connection and stats retrieval
require 'db.php';

// Get the last checked time from session or set to now
if (!isset($_SESSION['last_checked'])) {
    $_SESSION['last_checked'] = date('Y-m-d H:i:s');
}

try {
    // Count new tickets
    $newTickets = $pdo->query("SELECT COUNT(*) FROM tickets WHERE status = 'Nouveau'")->fetchColumn();
    
    // Count unread emails since last check
    $unreadEmails = $pdo->prepare("SELECT COUNT(*) FROM emails WHERE is_read = 0 AND created_at > :last_checked");
    $unreadEmails->execute(['last_checked' => $_SESSION['last_checked']]);
    $unreadEmails = $unreadEmails->fetchColumn();
    
    // Count recent activity
    $recentActivity = $pdo->query("SELECT COUNT(*) FROM logs WHERE created_at > NOW() - INTERVAL 1 DAY")->fetchColumn();
    
    // Update last checked time
    $_SESSION['last_checked'] = date('Y-m-d H:i:s');
    
} catch (PDOException $e) {
    $newTickets = $unreadEmails = $recentActivity = 0;
    error_log("Dashboard error: " . $e->getMessage());
}

// Check if there are new emails to show a message
if ($unreadEmails > 0) {
    $newEmailMessage = "<div class='alert alert-success'>Vous avez $unreadEmails nouveau(x) email(s)!</div>";
} else {
    $newEmailMessage = "";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - PME</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Dashboard specific styles */
        .dashboard {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .dashboard-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-top: 4px solid;
            position: relative;
        }
        
        .card-tickets { border-color: #ffc107; }
        .card-emails { border-color: #17a2b8; }
        .card-activity { border-color: #28a745; }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .card-title {
            font-size: 1.1rem;
            color: #495057;
            margin: 0;
        }
        
        .card-icon {
            font-size: 1.5rem;
            color: inherit;
        }
        
        .card-value {
            font-size: 2rem;
            font-weight: bold;
            margin: 10px 0;
            color: #212529;
        }
        
        .card-footer {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background: #007bff;
            color: white;
            border-radius: 4px;
            text-decoration: none;
            margin-top: 10px;
            font-size: 0.9rem;
        }
        
        .welcome-message {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .new-email-badge {
            position: absolute;
            top: -10px;
            right: -10px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        @media (max-width: 768px) {
            .dashboard {
                grid-template-columns: 1fr;
            }
        }
        
        /* Auto-refresh the page every 30 seconds */
        meta[http-equiv="refresh"] {
            display: none;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="welcome-message">
            <h1>Bienvenue <?php echo $role === 'admin' ? 'Administrateur' : 'Utilisateur'; ?></h1>
            <p>Voici votre tableau de bord avec les dernières activités.</p>
            <?php echo $newEmailMessage; ?>
        </div>
        
        <div class="dashboard">
            <!-- Tickets Card -->
            <div class="dashboard-card card-tickets">
                <div class="card-header">
                    <h2 class="card-title">Nouveaux Tickets</h2>
                    <i class="fas fa-ticket-alt card-icon"></i>
                </div>
                <div class="card-value"><?php echo $newTickets; ?></div>
                <div class="card-footer">
                    Tickets nécessitant votre attention
                </div>
                <a href="assistance.php" class="btn">Voir les tickets</a>
            </div>
            
            <!-- Emails Card -->
            <div class="dashboard-card card-emails">
                <?php if ($unreadEmails > 0): ?>
                    <div class="new-email-badge"><?php echo $unreadEmails; ?></div>
                <?php endif; ?>
                <div class="card-header">
                    <h2 class="card-title">Nouveaux Emails</h2>
                    <i class="fas fa-envelope card-icon"></i>
                </div>
                <div class="card-value"><?php echo $unreadEmails; ?></div>
                <div class="card-footer">
                    Emails non lus
                </div>
                <a href="view_emails.php" class="btn">Voir les emails</a>
            </div>
            
            <!-- Activity Card -->
            <div class="dashboard-card card-activity">
                <div class="card-header">
                    <h2 class="card-title">Activité Récente</h2>
                    <i class="fas fa-history card-icon"></i>
                </div>
                <div class="card-value"><?php echo $recentActivity; ?></div>
                <div class="card-footer">
                    Événements aujourd'hui
                </div>
                <a href="admin_logs.php" class="btn">Voir l'activité</a>
            </div>
        </div>
        
        <!-- Auto-refresh the page every 30 seconds -->
        <meta http-equiv="refresh" content="30">
    </div>
</body>
</html>