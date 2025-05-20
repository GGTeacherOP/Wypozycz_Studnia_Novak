<?php
require_once __DIR__ . '/../admin_functions.php';
checkAdminAuth();

require_once __DIR__ . '/../../includes/config.php';

// Akcje na pojazdach
if (isset($_GET['delete'])) {
    $vehicle_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM vehicles WHERE vehicle_id = ?");
    $stmt->bind_param("i", $vehicle_id);
    $stmt->execute();
    $_SESSION['success'] = "Pojazd został usunięty";
    header("Location: manage_vehicles.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vehicle_id = isset($_POST['vehicle_id']) ? intval($_POST['vehicle_id']) : 0;
    $available = isset($_POST['available']) ? 1 : 0;
    
    $stmt = $conn->prepare("UPDATE vehicles SET available = ? WHERE vehicle_id = ?");
    $stmt->bind_param("ii", $available, $vehicle_id);
    $stmt->execute();
    $_SESSION['success'] = "Status pojazdu został zaktualizowany";
    header("Location: manage_vehicles.php");
    exit();
}

// Pobierz listę pojazdów
$vehicles = $conn->query("
    SELECT v.*, l.city 
    FROM vehicles v 
    LEFT JOIN locations l ON v.location_id = l.location_id
    ORDER BY v.type, v.make, v.model
");

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="container-fluid">
    <h2>Zarządzanie pojazdami</h2>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Typ</th>
                    <th>Marka i model</th>
                    <th>Rok</th>
                    <th>Lokalizacja</th>
                    <th>Cena</th>
                    <th>Dostępny</th>
                    <th>Akcje</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($vehicle = $vehicles->fetch_assoc()): ?>
                <tr>
                    <td><?= $vehicle['vehicle_id'] ?></td>
                    <td><?= $vehicle['type'] == 'car' ? 'Samochód' : 'Samolot' ?></td>
                    <td><?= htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']) ?></td>
                    <td><?= $vehicle['year'] ?></td>
                    <td><?= htmlspecialchars($vehicle['city'] ?? 'Brak') ?></td>
                    <td>
                        <?= $vehicle['type'] == 'car' ? 
                            number_format($vehicle['daily_rate'], 2) . ' PLN/dzień' : 
                            number_format($vehicle['hourly_rate'], 2) . ' PLN/godz' ?>
                    </td>
                    <td>
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="vehicle_id" value="<?= $vehicle['vehicle_id'] ?>">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="available" 
                                    <?= $vehicle['available'] ? 'checked' : '' ?> 
                                    onchange="this.form.submit()">
                            </div>
                        </form>
                    </td>
                    <td>
                        <a href="../edit_vehicle.php?id=<?= $vehicle['vehicle_id'] ?>" class="btn btn-primary btn-sm">Edytuj</a>
                        <a href="?delete=<?= $vehicle['vehicle_id'] ?>" class="btn btn-danger btn-sm"
                           onclick="return confirm('Czy na pewno chcesz usunąć ten pojazd?')">
                            Usuń
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <a href="../add_vehicle.php" class="btn btn-success mt-3">Dodaj nowy pojazd</a>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>