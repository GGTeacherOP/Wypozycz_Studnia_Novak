<?php
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "skydriverentals";

// Utwórz połączenie
$conn = new mysqli($servername, $username, $password, $dbname);

// Sprawdź połączenie
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ustaw kodowanie
$conn->set_charset("utf8mb4");
?>