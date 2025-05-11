<?php
$host = 'localhost';
$db = 'baza_operacyjna';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die("Błąd połączenia: " . $conn->connect_error);
}
?>