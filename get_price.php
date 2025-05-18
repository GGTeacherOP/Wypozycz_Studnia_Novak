<?php
require '../includes/db.php';

$carId = (int)$_GET['car_id'];
$stmt = $pdo->prepare("SELECT daily_price FROM cars WHERE car_id = ?");
$stmt->execute([$carId]);
$car = $stmt->fetch();

header('Content-Type: application/json');
echo json_encode(['price' => $car['daily_price']]);