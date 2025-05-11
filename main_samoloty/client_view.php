<?php
include 'db_config.php';
session_start();
if ($_SESSION['user'] !== 'client') exit('Brak dostępu.');

$where = [];
$params = [];
$types = '';

if (!empty($_GET['manufacturer'])) {
    $where[] = "manufacturer = ?";
    $types .= 's';
    $params[] = $_GET['manufacturer'];
}
if (!empty($_GET['max_price'])) {
    $where[] = "CAST(SUBSTRING_INDEX(price, ' ', 1) AS DECIMAL(10,2)) <= ?";
    $types .= 'd';
    $params[] = $_GET['max_price'];
}

$sql = "SELECT * FROM aircrafts";
if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>
<a href="logout.php">Wyloguj</a>
<h2>Dostępne samoloty</h2>
<form method="get">
  <input name="manufacturer" placeholder="Producent" value="<?= htmlspecialchars($_GET['manufacturer'] ?? '') ?>">
  <input type="number" step="0.01" name="max_price" placeholder="Maks cena" value="<?= htmlspecialchars($_GET['max_price'] ?? '') ?>">
  <button type="submit">Filtruj</button>
</form><hr>
<?php while ($row = $result->fetch_assoc()): ?>
  <div style="border:1px solid #ccc;padding:10px;margin:10px;">
    <h3><?= htmlspecialchars($row['name']) ?> (<?= htmlspecialchars($row['manufacturer']) ?>)</h3>
    <p>Cena: <?= htmlspecialchars($row['price']) ?></p>
    <p>Lokalizacja: <?= htmlspecialchars($row['location']) ?></p>
    <img src="<?= htmlspecialchars($row['image']) ?>" style="max-width:300px;">
  </div>
<?php endwhile; ?>