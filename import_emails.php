<?php
require 'db.php'; // Connexion à la base de données

// Configuration IMAP
$hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
$username = 'tickettourelle@gmail.com';
$password = 'ebfi dijo fbix zadn'; // Utilisez un mot de passe d'application pour Gmail

// Open IMAP connection
function openImapConnection($hostname, $username, $password) {
    $inbox = imap_open($hostname, $username, $password);
    if (!$inbox) {
        throw new Exception("Unable to connect to Gmail: " . imap_last_error());
    }
    return $inbox;
}

// Decode email message properly
function decodeMessage($message, $encoding) {
    switch ($encoding) {
        case 4: // Base64
            return mb_convert_encoding(base64_decode($message), 'UTF-8', 'auto');
        case 3: // Quoted-printable
            return mb_convert_encoding(quoted_printable_decode($message), 'UTF-8', 'auto');
        default: // Plain text
            return mb_convert_encoding($message, 'UTF-8', 'auto');
    }
}

// Start infinite loop to constantly check for new emails
while (true) {
    try {
        $inbox = openImapConnection($hostname, $username, $password); // Open IMAP connection

        // Search for unread emails
        $emails = imap_search($inbox, 'UNSEEN');

        if ($emails) {
            rsort($emails); // Sort emails by descending order (most recent first)

            foreach ($emails as $email_number) {
                $overview = imap_fetch_overview($inbox, $email_number, 0)[0];
                $raw_message = imap_fetchbody($inbox, $email_number, 1);
                
                // Decode email details
                $from = isset($overview->from) ? trim($overview->from) : 'Unknown sender';
                $subject = isset($overview->subject) ? mb_decode_mimeheader($overview->subject) : 'No subject';
                
                // Get encoding if available
                $encoding = isset($overview->encoding) ? $overview->encoding : 0;
                $message = decodeMessage($raw_message, $encoding);

                // Debugging output
                echo "Processing Email - From: $from, Subject: $subject \n";

                // Insert into Alerts if from "tickettourelle"
                if (strpos($from, 'tickettourelle') !== false) {
                    echo "Inserting Alert: $from, $subject \n";

                    $sql = "INSERT INTO alerts (email, subject, message, created_at) VALUES (:email, :subject, :message, NOW())";
                    $stmt = $pdo->prepare($sql);
                    if (!$stmt->execute([
                        'email' => $from,
                        'subject' => $subject,
                        'message' => $message
                    ])) {
                        echo "Error inserting into alerts: " . implode(" ", $stmt->errorInfo()) . "\n";
                    }
                } else {
                    // Insert into Tickets otherwise
                    echo "Inserting Ticket: $from, $subject \n";

                    $sql = "INSERT INTO tickets (email, subject, message) VALUES (:email, :subject, :message)";
                    $stmt = $pdo->prepare($sql);
                    if (!$stmt->execute([
                        'email' => $from,
                        'subject' => $subject,
                        'message' => $message,
                    ])) {
                        echo "Error inserting into tickets: " . implode(" ", $stmt->errorInfo()) . "\n";
                    }
                }

                // Mark email as read
                imap_setflag_full($inbox, $email_number, "\\Seen");
            }

            echo "New emails imported successfully.\n";
        } else {
            echo "No new emails found.\n";
        }

        // Close IMAP connection
        imap_close($inbox);
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";

        // Retry after a short time if connection fails
        echo "Retrying in 10 seconds...\n";
        sleep(10);
    }

    sleep(3); // Wait 3 seconds before checking for new emails again
}
?>
