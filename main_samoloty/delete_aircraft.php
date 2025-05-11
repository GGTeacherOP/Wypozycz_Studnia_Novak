<?php
include 'db_config.php';
session_start();
if ($_SESSION['user'] !== 'admin') exit('Brak dostępu.');
$id = $_GET['id'];
$conn->query("DELETE FROM aircrafts WHERE id = " . intval($id));
header('Location: admin_panel.php');
?>