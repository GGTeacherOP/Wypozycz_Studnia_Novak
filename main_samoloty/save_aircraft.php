<?php
include 'db_config.php';

$name = $_POST['name'];
$manufacturer = $_POST['manufacturer'];
$price = $_POST['price'] . ' ' . $_POST['currency'];
$location = $_POST['location'];
$image = $_POST['image'];

$stmt = $conn->prepare("INSERT INTO aircrafts (name, manufacturer, price, location, image) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $name, $manufacturer, $price, $location, $image);

if ($stmt->execute()) {
    echo "Dodano samolot!";
} else {
    echo "Błąd: " . $conn->error;
}
?>