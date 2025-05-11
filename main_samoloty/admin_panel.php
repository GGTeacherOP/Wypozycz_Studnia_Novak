<?php
include 'db_config.php';
session_start();
if ($_SESSION['user'] !== 'admin') exit('Brak dostępu.');

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
if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<a href="logout.php">Wyloguj</a> | <a href="index.html">Dodaj samolot</a> | <a href="client_view.php">Widok klienta</a>
<h2>Panel administracyjny – Samoloty</h2>
<form method="get">
    <label>Producent:
        <input type="text" name="manufacturer" value="<?= htmlspecialchars($_GET['manufacturer'] ?? '') ?>">
    </label>
    <label>Maks. cena:
        <input type="number" step="0.01" name="max_price" value="<?= htmlspecialchars($_GET['max_price'] ?? '') ?>">
    </label>
    <button type="submit">Filtruj</button>
</form>
<hr>
<?php while ($row = $result->fetch_assoc()): ?>
    <div style="border:1px solid #333; padding:10px; margin-bottom:10px;">
        <h3><?= htmlspecialchars($row['name']) ?> (<?= htmlspecialchars($row['manufacturer']) ?>)</h3>
        <p>Cena: <?= htmlspecialchars($row['price']) ?></p>
        <p>Lokalizacja: <?= htmlspecialchars($row['location']) ?></p>
        <p><img src="<?= htmlspecialchars($row['image']) ?>" alt="obraz samolotu" style="max-width:300px;"></p>
        <p>Dodano: <?= $row['created_at'] ?></p>
        <a href="edit_aircraft.php?id=<?= $row['id'] ?>">Edytuj</a> | <a href="delete_aircraft.php?id=<?= $row['id'] ?>" onclick="return confirm('Na pewno usunąć?')">Usuń</a>
    </div>
<?php endwhile; ?>