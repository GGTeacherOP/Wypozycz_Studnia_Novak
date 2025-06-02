<?php
include 'includes/config.php';

// Sprawdź czy admin już istnieje
$query = "SELECT * FROM users WHERE is_admin = 1";
$result = $conn->query($query);

if($result->num_rows > 0) {
    echo "Konto administratora już istnieje.";
    exit();
}

// Dane administratora
$firstName = 'Admin';
$lastName = 'SkyDrive';
$email = 'admin@skydrive.pl';
$password = 'Admin123!'; // W produkcji użyj silnego hasła!
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// Wstawienie administratora
$query = "INSERT INTO users (first_name, last_name, email, password_hash, is_admin) VALUES (?, ?, ?, ?, 1)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssss", $firstName, $lastName, $email, $passwordHash);

if($stmt->execute()) {
    echo "Konto administratora zostało utworzone:<br>";
    echo "Email: $email<br>";
    echo "Hasło: $password<br>";
    echo "Zaloguj się i zmień hasło!";
} else {
    echo "Błąd podczas tworzenia konta administratora: " . $conn->error;
}
?>