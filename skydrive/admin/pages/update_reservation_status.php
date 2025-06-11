<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../admin/admin_functions.php';
checkAdminAuth();

if(!isset($_GET['reservation_id']) || !isset($_GET['status'])) {
    $_SESSION['error'] = "Brak wymaganych parametrów";
    header("Location: manage_reservations.php");
    exit();
}

$reservation_id = intval($_GET['reservation_id']);
$status = $_GET['status'];

// Dozwolone statusy
$allowed_statuses = ['pending', 'confirmed', 'cancelled', 'completed'];
if(!in_array($status, $allowed_statuses)) {
    $_SESSION['error'] = "Nieprawidłowy status";
    header("Location: manage_reservations.php");
    exit();
}

// Aktualizuj status
$stmt = $conn->prepare("UPDATE reservations SET status = ? WHERE reservation_id = ?");
$stmt->bind_param("si", $status, $reservation_id);
$stmt->execute();

if($stmt->affected_rows > 0) {
    $_SESSION['success'] = "Status rezerwacji został zaktualizowany";
} else {
    $_SESSION['error'] = "Nie udało się zaktualizować statusu rezerwacji";
}

header("Location: manage_reservations.php");
exit();
?>