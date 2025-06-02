<?php
require_once __DIR__ . '../admin_functions.php';
checkAdminAuth();

require_once __DIR__ . '/../includes/config.php';

if (!isset($_GET['id'])) {
    header("Location: manage_vehicles.php");
    exit();
}

$vehicle_id = intval($_GET['id']);
$errors = [];

// Pobierz dane pojazdu
$stmt = $conn->prepare("SELECT * FROM vehicles WHERE vehicle_id = ?");
$stmt->bind_param("i", $vehicle_id);
$stmt->execute();
$vehicle = $stmt->get_result()->fetch_assoc();

if (!$vehicle) {
    $_SESSION['error'] = "Pojazd nie został znaleziony";
    header("Location: manage_vehicles.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    $make = trim($_POST['make'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $year = intval($_POST['year'] ?? 0);
    $registration_number = trim($_POST['registration_number'] ?? '');
    $capacity = intval($_POST['capacity'] ?? 0);
    $fuel_type = trim($_POST['fuel_type'] ?? '');
    $engine_power = trim($_POST['engine_power'] ?? '');
    $max_speed = trim($_POST['max_speed'] ?? '');
    $range = trim($_POST['range'] ?? '');
    $daily_rate = floatval($_POST['daily_rate'] ?? 0);
    $hourly_rate = floatval($_POST['hourly_rate'] ?? 0);
    $location_id = intval($_POST['location_id'] ?? 0);
    $image_path = trim($_POST['image_path'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $available = isset($_POST['available']) ? 1 : 0;

    // Walidacja
    if (empty($make)) $errors[] = 'Marka jest wymagana';
    if (empty($model)) $errors[] = 'Model jest wymagany';
    if ($year < 1900 || $year > date('Y') + 1) $errors[] = 'Nieprawidłowy rok';
    if ($capacity <= 0) $errors[] = 'Pojemność musi być większa niż 0';
    if ($type === 'car' && $daily_rate <= 0) $errors[] = 'Cena dzienna musi być większa niż 0';
    if ($type === 'plane' && $hourly_rate <= 0) $errors[] = 'Cena godzinowa musi być większa niż 0';
    if (empty($image_path)) $errors[] = 'Link do zdjęcia jest wymagany';
    if (!filter_var($image_path, FILTER_VALIDATE_URL)) $errors[] = 'Nieprawidłowy format linku do zdjęcia';

    if (empty($errors)) {
        $stmt = $conn->prepare("
            UPDATE vehicles SET
                type = ?, make = ?, model = ?, year = ?, registration_number = ?, capacity = ?, 
                fuel_type = ?, engine_power = ?, max_speed = ?, `range` = ?, 
                daily_rate = ?, hourly_rate = ?, available = ?, location_id = ?, 
                image_path = ?, description = ?
            WHERE vehicle_id = ?
        ");
        
        $stmt->bind_param(
            "sssisissssddiissi",
            $type, $make, $model, $year, $registration_number, $capacity,
            $fuel_type, $engine_power, $max_speed, $range,
            $daily_rate, $hourly_rate, $available, $location_id,
            $image_path, $description, $vehicle_id
        );
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Pojazd został pomyślnie zaktualizowany";
            header("Location: manage_vehicles.php");
            exit();
        } else {
            $errors[] = "Błąd podczas aktualizacji pojazdu: " . $conn->error;
        }
    }
}

// Pobierz lokalizacje dla selecta
$locations = $conn->query("SELECT * FROM locations ORDER BY city");

require_once __DIR__ . '/../admin/includes/admin_header.php';
?>

<div class="container-fluid">
    <h2>Edytuj pojazd</h2>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Typ pojazdu</label>
                    <select class="form-select" name="type" id="vehicleType" required>
                        <option value="car" <?= $vehicle['type'] === 'car' ? 'selected' : '' ?>>Samochód</option>
                        <option value="plane" <?= $vehicle['type'] === 'plane' ? 'selected' : '' ?>>Samolot</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Marka</label>
                    <input type="text" class="form-control" name="make" value="<?= htmlspecialchars($vehicle['make']) ?>" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Model</label>
                    <input type="text" class="form-control" name="model" value="<?= htmlspecialchars($vehicle['model']) ?>" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Rok produkcji</label>
                    <input type="number" class="form-control" name="year" min="1900" max="<?= date('Y') + 1 ?>" 
                           value="<?= $vehicle['year'] ?>" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Numer rejestracyjny</label>
                    <input type="text" class="form-control" name="registration_number" 
                           value="<?= htmlspecialchars($vehicle['registration_number'] ?? '') ?>">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Liczba miejsc</label>
                    <input type="number" class="form-control" name="capacity" min="1" 
                           value="<?= $vehicle['capacity'] ?>" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Rodzaj paliwa</label>
                    <input type="text" class="form-control" name="fuel_type" 
                           value="<?= htmlspecialchars($vehicle['fuel_type'] ?? '') ?>">
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Moc silnika</label>
                    <input type="text" class="form-control" name="engine_power" 
                           value="<?= htmlspecialchars($vehicle['engine_power'] ?? '') ?>">
                </div>
                
                <div class="mb-3 plane-field" style="display: <?= $vehicle['type'] === 'plane' ? 'block' : 'none' ?>">
                    <label class="form-label">Maksymalna prędkość</label>
                    <input type="text" class="form-control" name="max_speed" 
                           value="<?= htmlspecialchars($vehicle['max_speed'] ?? '') ?>">
                </div>
                
                <div class="mb-3 plane-field" style="display: <?= $vehicle['type'] === 'plane' ? 'block' : 'none' ?>">
                    <label class="form-label">Zasięg</label>
                    <input type="text" class="form-control" name="range" 
                           value="<?= htmlspecialchars($vehicle['range'] ?? '') ?>">
                </div>
                
                <div class="mb-3 car-field" style="display: <?= $vehicle['type'] === 'car' ? 'block' : 'none' ?>">
                    <label class="form-label">Cena dzienna (PLN)</label>
                    <input type="number" step="0.01" class="form-control" name="daily_rate" min="0" 
                           value="<?= $vehicle['daily_rate'] ?>">
                </div>
                
                <div class="mb-3 plane-field" style="display: <?= $vehicle['type'] === 'plane' ? 'block' : 'none' ?>">
                    <label class="form-label">Cena godzinowa (PLN)</label>
                    <input type="number" step="0.01" class="form-control" name="hourly_rate" min="0" 
                           value="<?= $vehicle['hourly_rate'] ?>">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Lokalizacja</label>
                    <select class="form-select" name="location_id" required>
                        <option value="">Wybierz lokalizację</option>
                        <?php while ($location = $locations->fetch_assoc()): ?>
                            <option value="<?= $location['location_id'] ?>" 
                                <?= $location['location_id'] == $vehicle['location_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($location['city']) ?> - <?= htmlspecialchars($location['address']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Link do zdjęcia</label>
                    <input type="url" class="form-control" name="image_path" 
                           value="<?= htmlspecialchars($vehicle['image_path']) ?>" required>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" name="available" id="available" 
                           <?= $vehicle['available'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="available">Dostępny</label>
                </div>
            </div>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Opis</label>
            <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($vehicle['description'] ?? '') ?></textarea>
        </div>
        
        <button type="submit" class="btn btn-primary">Zapisz zmiany</button>
        <a href="pages/manage_vehicles.php" class="btn btn-secondary">Anuluj</a>
    </form>
</div>

<script>
document.getElementById('vehicleType').addEventListener('change', function() {
    const type = this.value;
    document.querySelectorAll('.car-field').forEach(el => el.style.display = type === 'car' ? 'block' : 'none');
    document.querySelectorAll('.plane-field').forEach(el => el.style.display = type === 'plane' ? 'block' : 'none');
});
</script>

<?php require_once __DIR__ . '/../admin/includes/admin_footer.php'; ?>