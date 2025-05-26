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
    <style>
        .login-form {
            max-width: 400px;
            margin: 80px auto;
            padding: 30px 25px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .login-form h1 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
            font-size: 26px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #444;
        }

        .form-group input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
        }

        .form-group input:focus {
            outline: none;
            border-color: #3f87a6;
            box-shadow: 0 0 5px rgba(63, 135, 166, 0.3);
        }

        .btn {
            width: 100%;
            padding: 12px;
            background-color: #3f87a6;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #336c86;
        }

        .alert.error {
            background-color: #ffe6e6;
            border-left: 4px solid #f44336;
            color: #b30000;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 6px;
        }

        .login-form p {
            text-align: center;
            margin-top: 16px;
        }

        .login-form a {
            color: #3f87a6;
            text-decoration: none;
        }

        .login-form a:hover {
            text-decoration: underline;
        }
    </style>
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
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div class="form-group">
                <label for="password">Hasło:</label>
                <input type="password" name="password" id="password" required>
            </div>
            <button type="submit" name="login" class="btn">Zaloguj</button>
            <p>Nie masz konta? <a href="register.php">Zarejestruj się</a></p>
        </form>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
