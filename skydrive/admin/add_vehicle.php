<?php
require_once __DIR__ . '/admin_functions.php';
checkAdminAuth();

require_once __DIR__ . '/../includes/config.php';

$errors = [];
$success = '';

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
            INSERT INTO vehicles (
                type, make, model, year, registration_number, capacity, 
                fuel_type, engine_power, max_speed, `range`, 
                daily_rate, hourly_rate, available, location_id, 
                image_path, description
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->bind_param(
            "sssisissssddiiss",
            $type, $make, $model, $year, $registration_number, $capacity,
            $fuel_type, $engine_power, $max_speed, $range,
            $daily_rate, $hourly_rate, $available, $location_id,
            $image_path, $description
        );
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Pojazd został pomyślnie dodany";
            header("Location: pages/manage_vehicles.php");
            exit();
        } else {
            $errors[] = "Błąd podczas dodawania pojazdu: " . $conn->error;
        }
    }
}

// Pobierz lokalizacje dla selecta
$locations = $conn->query("SELECT * FROM locations ORDER BY city");

require_once __DIR__ . '/../admin/includes/admin_header.php';
?>

<div class="container-fluid">
    <h2>Dodaj nowy pojazd</h2>
    
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
                        <option value="car" selected>Samochód</option>
                        <option value="plane">Samolot</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Marka</label>
                    <input type="text" class="form-control" name="make" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Model</label>
                    <input type="text" class="form-control" name="model" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Rok produkcji</label>
                    <input type="number" class="form-control" name="year" min="1900" max="<?= date('Y') + 1 ?>" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Numer rejestracyjny</label>
                    <input type="text" class="form-control" name="registration_number">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Liczba miejsc</label>
                    <input type="number" class="form-control" name="capacity" min="1" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Rodzaj paliwa</label>
                    <input type="text" class="form-control" name="fuel_type">
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Moc silnika</label>
                    <input type="text" class="form-control" name="engine_power">
                </div>
                
                <div class="mb-3 plane-field">
                    <label class="form-label">Maksymalna prędkość</label>
                    <input type="text" class="form-control" name="max_speed">
                </div>
                
                <div class="mb-3 plane-field">
                    <label class="form-label">Zasięg</label>
                    <input type="text" class="form-control" name="range">
                </div>
                
                <div class="mb-3 car-field">
                    <label class="form-label">Cena dzienna (PLN)</label>
                    <input type="number" step="0.01" class="form-control" name="daily_rate" min="0">
                </div>
                
                <div class="mb-3 plane-field">
                    <label class="form-label">Cena godzinowa (PLN)</label>
                    <input type="number" step="0.01" class="form-control" name="hourly_rate" min="0">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Lokalizacja</label>
                    <select class="form-select" name="location_id" required>
                        <option value="">Wybierz lokalizację</option>
                        <?php while ($location = $locations->fetch_assoc()): ?>
                            <option value="<?= $location['location_id'] ?>">
                                <?= htmlspecialchars($location['city']) ?> - <?= htmlspecialchars($location['address']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Link do zdjęcia</label>
                    <input type="url" class="form-control" name="image_path" required>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" name="available" id="available" checked>
                    <label class="form-check-label" for="available">Dostępny</label>
                </div>
            </div>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Opis</label>
            <textarea class="form-control" name="description" rows="3"></textarea>
        </div>
        
        <button type="submit" class="btn btn-primary">Dodaj pojazd</button>
        <a href="manage_vehicles.php" class="btn btn-secondary">Anuluj</a>
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
    animation: fadeIn 0.4s ease-out;
}

.alert-danger p {
    margin-bottom: 0.5rem;
    position: relative;
    padding-left: 1.5rem;
}

.alert-danger p::before {
    content: "•";
    position: absolute;
    left: 0.5rem;
    font-weight: bold;
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
    animation: slideUp 0.5s ease-out;
}

/* Wiersze formularza */
.row {
    margin-bottom: 1.5rem;
}

/* Pola formularza */
.mb-3 {
    margin-bottom: 1.5rem !important;
    position: relative;
}

.form-label {
    font-weight: 600;
    color: #475569;
    margin-bottom: 0.75rem;
    display: block;
    font-size: 0.95rem;
    transition: all 0.3s ease;
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
    transition: height 0.3s ease;
}

/* Checkbox */
.form-check {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.5rem 0;
}

.form-check-input {
    width: 1.25rem;
    height: 1.25rem;
    margin-top: 0;
    border: 1px solid #e2e8f0;
    transition: all 0.2s ease;
    cursor: pointer;
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
    transition: color 0.2s ease;
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
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
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

/* Grupa przycisków */
.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
}

/* Pola specyficzne dla typu pojazdu */
.plane-field {
    display: none;
    opacity: 0;
    height: 0;
    overflow: hidden;
    transition: all 0.4s ease;
}

.car-field {
    display: block;
    opacity: 1;
    height: auto;
    transition: all 0.4s ease;
}

/* Animacje */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.mb-3 {
    animation: fadeIn 0.3s ease forwards;
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
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        margin-bottom: 1rem;
        justify-content: center;
    }
    
    .btn-secondary {
        margin-left: 0;
    }
}

/* Efekty hover dla pól */
.form-group:hover .form-label {
    color: #7c3aed;
}

/* Podpowiedzi dla pól */
.form-text {
    font-size: 0.8rem;
    color: #64748b;
    margin-top: 0.25rem;
    display: block;
}
</style>


<script>
document.getElementById('vehicleType').addEventListener('change', function() {
    const type = this.value;
    document.querySelectorAll('.car-field').forEach(el => el.style.display = type === 'car' ? 'block' : 'none');
    document.querySelectorAll('.plane-field').forEach(el => el.style.display = type === 'plane' ? 'block' : 'none');
});

// Ukryj odpowiednie pola na starcie
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.plane-field').forEach(el => el.style.display = 'none');
});
</script>

<?php require_once __DIR__ . '/../admin/includes/admin_footer.php'; ?>