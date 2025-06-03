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
<style>
/* Główny kontener */
.container-fluid {
    padding: 2.5rem;
    max-width: 1200px;
    margin: 0 auto;
    background: #f8fafc;
    min-height: 100vh;
}

/* Nagłówek */
.container-fluid h2 {
    color: #1e293b;
    font-weight: 700;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e2e8f0;
    font-size: 1.8rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.container-fluid h2::before {
    content: "🚗";
    font-size: 1.5rem;
}

/* Alert błędów */
.alert-danger {
    background-color: #fef2f2;
    color: #b91c1c;
    border-left: 4px solid #ef4444;
    padding: 1.25rem 1.5rem;
    border-radius: 8px;
    margin-bottom: 2rem;
}

.alert-danger p {
    margin-bottom: 0.5rem;
}

.alert-danger p:last-child {
    margin-bottom: 0;
}

/* Formularz */
form {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}

/* Wiersze formularza */
.row {
    margin-bottom: 1.5rem;
}

/* Pola formularza */
.mb-3 {
    margin-bottom: 1.5rem !important;
}

.form-label {
    font-weight: 600;
    color: #475569;
    margin-bottom: 0.75rem;
    display: block;
    font-size: 0.95rem;
}

.form-control, .form-select {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    transition: all 0.3s ease;
    width: 100%;
    font-size: 0.95rem;
    background-color: #f8fafc;
}

.form-control:focus, .form-select:focus {
    border-color: #8b5cf6;
    box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2);
    background-color: white;
    outline: none;
}

textarea.form-control {
    min-height: 120px;
    resize: vertical;
}

/* Checkbox */
.form-check {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.form-check-input {
    width: 1.25rem;
    height: 1.25rem;
    margin-top: 0;
    border: 1px solid #e2e8f0;
    transition: all 0.2s ease;
}

.form-check-input:checked {
    background-color: #8b5cf6;
    border-color: #8b5cf6;
}

.form-check-input:focus {
    box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2);
}

.form-check-label {
    font-weight: 500;
    color: #475569;
    cursor: pointer;
}

/* Przyciski */
.btn {
    border-radius: 8px;
    padding: 0.75rem 1.75rem;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    font-size: 1rem;
    cursor: pointer;
}

.btn-primary {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
}

.btn-secondary {
    background: linear-gradient(135deg, #64748b 0%, #475569 100%);
    color: white;
    margin-left: 1rem;
}

.btn-secondary:hover {
    background: linear-gradient(135deg, #475569 0%, #334155 100%);
}

/* Ikony w nagłówkach sekcji */
.section-icon {
    font-size: 1.25rem;
    margin-right: 0.75rem;
    color: #8b5cf6;
}

/* Responsywność */
@media (max-width: 768px) {
    .container-fluid {
        padding: 1.5rem;
    }
    
    form {
        padding: 1.5rem;
    }
    
    .row {
        flex-direction: column;
    }
    
    .col-md-6 {
        width: 100%;
    }
    
    .btn {
        width: 100%;
        margin-bottom: 1rem;
    }
    
    .btn-secondary {
        margin-left: 0;
    }
}

/* Animacje */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.mb-3 {
    animation: fadeIn 0.3s ease forwards;
}

/* Pola specyficzne dla typu pojazdu */
.plane-field, .car-field {
    opacity: 0;
    height: 0;
    overflow: hidden;
    transition: all 0.4s ease;
}

.plane-field.show, .car-field.show {
    opacity: 1;
    height: auto;
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
}
</style>

<script>
document.getElementById('vehicleType').addEventListener('change', function() {
    const type = this.value;
    document.querySelectorAll('.car-field').forEach(el => el.style.display = type === 'car' ? 'block' : 'none');
    document.querySelectorAll('.plane-field').forEach(el => el.style.display = type === 'plane' ? 'block' : 'none');
});
</script>

<?php require_once __DIR__ . '/../admin/includes/admin_footer.php'; ?>