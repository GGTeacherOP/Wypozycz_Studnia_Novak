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
        <a href="pages/manage_vehicles.php" class="btn btn-secondary">Anuluj</a>
    </form>
</div>

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

<style>
    :root {
        --primary-color: #1976d2;
        --primary-light: #e3f2fd;
        --success-color: #388e3c;
        --warning-color: #f57c00;
        --danger-color: #d32f2f;
        --text-color: #333;
        --light-gray: #f5f5f5;
        --medium-gray: #e0e0e0;
        --dark-gray: #616161;
        --border-radius: 8px;
        --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        --transition: all 0.3s ease;
    }

    body {
        font-family: 'Roboto', 'Segoe UI', sans-serif;
        background-color: #f8f9fa;
        color: var(--text-color);
        line-height: 1.6;
    }

    .container-fluid {
        max-width: 1200px;
        padding: 2rem;
    }

    h2 {
        color: var(--primary-color);
        font-weight: 600;
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        position: relative;
    }

    h2:after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 60px;
        height: 3px;
        background: var(--primary-color);
    }

    /* Alerty */
    .alert {
        padding: 1rem;
        border-radius: var(--border-radius);
        margin-bottom: 1.5rem;
    }

    .alert-danger {
        background-color: #ffebee;
        color: var(--danger-color);
        border-left: 4px solid var(--danger-color);
    }

    .alert-danger p {
        margin: 0.25rem 0;
    }

    /* Formularz */
    form {
        background: white;
        padding: 2rem;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
    }

    .form-label {
        font-weight: 500;
        color: var(--dark-gray);
        margin-bottom: 0.5rem;
        display: block;
    }

    .form-control, .form-select {
        border: 1px solid var(--medium-gray);
        border-radius: var(--border-radius);
        padding: 0.75rem;
        width: 100%;
        transition: var(--transition);
        font-size: 1rem;
        margin-bottom: 1rem;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        outline: none;
        box-shadow: 0 0 0 3px rgba(25, 118, 210, 0.1);
    }

    textarea.form-control {
        min-height: 120px;
        resize: vertical;
    }

    .form-check {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .form-check-input {
        width: 1.2em;
        height: 1.2em;
        margin-top: 0;
    }

    /* Przyciski */
    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: var(--border-radius);
        font-weight: 500;
        transition: var(--transition);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        margin-right: 1rem;
        margin-top: 1rem;
    }

    .btn-primary {
        background-color: var(--primary-color);
        color: white;
        border: none;
    }

    .btn-primary:hover {
        background-color: #1565c0;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(25, 118, 210, 0.3);
    }

    .btn-secondary {
        background-color: white;
        color: var(--primary-color);
        border: 1px solid var(--primary-color);
    }

    .btn-secondary:hover {
        background-color: var(--primary-light);
    }

    /* Responsywność */
    @media (max-width: 768px) {
        .container-fluid {
            padding: 1rem;
        }
        
        form {
            padding: 1.5rem;
        }
        
        .btn {
            width: 100%;
            margin-right: 0;
        }
    }

    /* Pola specyficzne dla typu pojazdu */
    .car-field, .plane-field {
        transition: var(--transition);
    }

    /* Animacja pojawiania się pól */
    [style*="display: block"] {
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Druk */
    @media print {
        .btn, .form-check {
            display: none !important;
        }
        
        form {
            box-shadow: none;
            padding: 0;
        }
    }
</style>