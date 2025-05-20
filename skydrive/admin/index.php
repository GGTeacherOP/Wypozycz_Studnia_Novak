<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/config.php';

// Jeśli użytkownik jest już zalogowany jako admin - przekieruj do dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Walidacja danych
    if (empty($email) || empty($password)) {
        $error = "Proszę wprowadzić email i hasło";
    } else {
        $stmt = $conn->prepare("SELECT user_id, first_name, last_name, password_hash FROM users WHERE email = ? AND is_admin = 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password_hash'])) {
                // Ustawienia sesji admina
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $user['user_id'];
                $_SESSION['admin_name'] = $user['first_name'] . ' ' . $user['last_name'];
                
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Nieprawidłowe hasło";
            }
        } else {
            $error = "Nie znaleziono konta administratora";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logowanie administratora | SkyDrive</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-container {
            max-width: 400px;
            width: 100%;
            margin: 0 auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .logo-admin {
            text-align: center;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="logo-admin">
                <h2>SkyDrive Admin</h2>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="email" class="form-label">Email administratora</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Hasło</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" name="login" class="btn btn-primary w-100">Zaloguj</button>
                
            </form><br>
            <a href="../index.php"><button type="submit" name="login" class="btn btn-secondary w-100">Powrot na strone glowna</button></a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>