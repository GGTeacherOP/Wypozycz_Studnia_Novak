<?php
include 'db_config.php';
session_start();
if ($_SESSION['user'] !== 'admin') exit('Brak dostępu.');

$id = $_GET['id'];
$result = $conn->query("SELECT * FROM aircrafts WHERE id = " . intval($id));
$row = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'];
  $manufacturer = $_POST['manufacturer'];
  $price = $_POST['price'];
  $location = $_POST['location'];
  $image = $_POST['image'];
  $stmt = $conn->prepare("UPDATE aircrafts SET name=?, manufacturer=?, price=?, location=?, image=? WHERE id=?");
  $stmt->bind_param("sssssi", $name, $manufacturer, $price, $location, $image, $id);
  $stmt->execute();
  header("Location: admin_panel.php");
  exit;
}
?>
<form method="post">
  <input name="name" value="<?= htmlspecialchars($row['name']) ?>"><br>
  <input name="manufacturer" value="<?= htmlspecialchars($row['manufacturer']) ?>"><br>
  <input name="price" value="<?= htmlspecialchars($row['price']) ?>"><br>
  <input name="location" value="<?= htmlspecialchars($row['location']) ?>"><br>
  <input name="image" value="<?= htmlspecialchars($row['image']) ?>"><br>
  <button type="submit">Zapisz</button>
</form>