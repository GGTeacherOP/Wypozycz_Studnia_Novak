<?php
session_start();
$users = [
  'admin' => 'admin123',
  'client' => 'client123'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = $_POST['username'];
  $password = $_POST['password'];
  if (isset($users[$username]) && $users[$username] === $password) {
    $_SESSION['user'] = $username;
    header('Location: ' . ($username === 'admin' ? 'admin_panel.php' : 'client_view.php'));
    exit;
  } else {
    echo "Nieprawidłowe dane logowania.";
  }
}
?>
<form method="post">
  <input name="username" placeholder="Login" required>
  <input name="password" type="password" placeholder="Hasło" required>
  <button type="submit">Zaloguj</button>
</form>