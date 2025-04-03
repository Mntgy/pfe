<?php
function startSecureSession() {
    session_start([
        'cookie_lifetime' => 86400,
        'cookie_path' => '/',
        'cookie_domain' => $_SERVER['HTTP_HOST'],
        'cookie_secure' => isset($_SERVER['HTTPS']),
        'cookie_httponly' => true,
        'cookie_samesite' => 'Strict',
        'use_strict_mode' => true
    ]);
}

function validateAdminSession() {
    if (empty($_SESSION['admin_logged_in']) || 
        $_SESSION['admin_logged_in'] !== true || 
        empty($_SESSION['admin_id']) ||
        empty($_SESSION['admin_username']) ||
        $_SESSION['user_ip'] !== $_SERVER['REMOTE_ADDR'] ||
        $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
        
        endSession();
        header('Location: login.php?error=invalid_session');
        exit();
    }
    
    checkSessionTimeout();
}

function validateUserSession() {
    if (empty($_SESSION['user_logged_in']) || 
        $_SESSION['user_logged_in'] !== true || 
        empty($_SESSION['user_id']) ||
        empty($_SESSION['user_username']) ||
        $_SESSION['user_ip'] !== $_SERVER['REMOTE_ADDR'] ||
        $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
        
        endSession();
        header('Location: login.php?error=invalid_session');
        exit();
    }
    
    checkSessionTimeout();
}

function checkSessionTimeout() {
    $timeout = 1800; // 30 minutes
    
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
        endSession();
        header('Location: login.php?error=session_timeout');
        exit();
    }
    
    $_SESSION['last_activity'] = time();
}

function endSession() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}
?>