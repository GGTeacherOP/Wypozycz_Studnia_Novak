<?php 
require '../includes/db.php';
require '../includes/header.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['user_id'];
        header("Location: ../dashboard.php");
        exit();
    } else {
        $error = "Błędny login lub hasło";
    }
}
?>

<div class="auth-form">
    <h2>Logowanie</h2>
    <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Hasło" required>
        <button type="submit">Zaloguj</button>
    </form>
    <p>Nie masz konta? <a href="register.php">Zarejestruj się</a></p>
</div>

<?php require '../includes/footer.php'; ?>