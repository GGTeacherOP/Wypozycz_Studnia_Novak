<?php
session_start();

// Zakończenie sesji
$_SESSION = array();

// Jeśli chcemy usunąć ciasteczko sesji
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Zniszczenie sesji
session_destroy();

// Przekierowanie
if(strpos($_SERVER['HTTP_REFERER'], 'admin') !== false) {
    header("Location: ../index.php");
} else {
    header("Location: index.php");
}
exit();
?>