<?php
require '../includes/db.php';

header('Content-Type: application/json');

$carId = (int)$_GET['car_id'];
$start = $_GET['start'];
$end = $_GET['end'];

$stmt = $pdo->prepare("
    SELECT 
        date AS start, 
        CASE 
            WHEN status = 'dostępny' THEN 'green'
            WHEN status = 'zarezerwowany' THEN 'red'
            ELSE 'gray'
        END AS backgroundColor,
        status AS title
    FROM car_availability 
    WHERE car_id = ? AND date BETWEEN ? AND ?
");

$stmt->execute([$carId, $start, $end]);
echo json_encode($stmt->fetchAll());