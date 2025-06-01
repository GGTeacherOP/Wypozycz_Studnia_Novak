<?php
function checkAdminAuth() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['admin_logged_in'])) {
        header("Location: index.php");
        exit();
    }
    
    // Regeneracja ID sesji dla bezpieczeństwa
    if (!isset($_SESSION['admin_last_regeneration'])) {
        session_regenerate_id(true);
        $_SESSION['admin_last_regeneration'] = time();
    } elseif (time() - $_SESSION['admin_last_regeneration'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['admin_last_regeneration'] = time();
    }
}

function adminLogout() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Usuń wszystkie zmienne sesji
    $_SESSION = array();
    
    // Usuń ciasteczko sesji
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Zniszcz sesję
    session_destroy();
    
    header("Location: index.php");
    exit();
}
?>