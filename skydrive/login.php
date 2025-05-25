<?php
session_start();
include 'includes/config.php';

$error = '';

if(isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Sprawdź czy użytkownik istnieje
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Sprawdź hasło
        if(password_verify($password, $user['password_hash'])) {
            // Zaloguj użytkownika
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['is_admin'] = $user['is_admin'];
            
            // Przekieruj w zależności od typu użytkownika
            if($user['is_admin'] == 1) {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $error = "Nieprawidłowe hasło";
        }
    } else {
        $error = "Nie znaleziono użytkownika o podanym adresie email";
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Logowanie - SkyDrive Rentals</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="login-form">
        <h1>Logowanie</h1>
        
        <?php if($error): ?>
            <div class="alert error"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="post">
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Hasło:</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" name="login" class="btn btn-primary">Zaloguj</button>
            <p>Nie masz konta? <a href="register.php">Zarejestruj się</a></p>
        </form>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>