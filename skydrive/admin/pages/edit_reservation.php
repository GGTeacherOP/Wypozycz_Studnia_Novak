<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../admin/admin_functions.php';
checkAdminAuth();

$reservation_id = isset($_GET['reservation_id']) ? intval($_GET['reservation_id']) : 0;

// Pobierz dane rezerwacji
$query = "SELECT r.*, v.make, v.model, v.type, v.daily_rate, v.hourly_rate,
          v.available, v.location_id as vehicle_location,
          CONCAT(u.first_name, ' ', u.last_name) as customer_name
          FROM reservations r
          JOIN vehicles v ON r.vehicle_id = v.vehicle_id
          JOIN users u ON r.user_id = u.user_id
          WHERE r.reservation_id = ?";
          
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $reservation_id);
$stmt->execute();
$reservation = $stmt->get_result()->fetch_assoc();

if(!$reservation) {
    $_SESSION['error'] = "Rezerwacja nie istnieje";
    header("Location: manage_reservations.php");
    exit();
}

// Sprawdź czy można edytować rezerwację
$can_edit = $reservation['status'] == 'pending' || $reservation['status'] == 'confirmed';

if(!$can_edit) {
    $_SESSION['error'] = "Nie można edytować zakończonej lub anulowanej rezerwacji";
    header("Location: manage_reservations.php");
    exit();
}

// Pobierz dostępne lokalizacje
$locations = $conn->query("SELECT * FROM locations ORDER BY city");

// Pobierz wyposażenie
$equipment = $conn->query("SELECT * FROM equipment ORDER BY name");
$reserved_equipment = [];
$eq_stmt = $conn->prepare("SELECT equipment_id, quantity FROM reservationequipment WHERE reservation_id = ?");
$eq_stmt->bind_param("i", $reservation_id);
$eq_stmt->execute();
$result = $eq_stmt->get_result();
while($row = $result->fetch_assoc()) {
    $reserved_equipment[$row['equipment_id']] = $row['quantity'];
}

// Przetwarzanie formularza
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $pickup_date = $_POST['pickup_date'];
    $return_date = $_POST['return_date'];
    $pickup_location = intval($_POST['pickup_location']);
    $return_location = intval($_POST['return_location']);
    $status = $_POST['status'];
    $selected_equipment = isset($_POST['equipment']) ? $_POST['equipment'] : [];
    
    // Walidacja dat
    if(strtotime($pickup_date) >= strtotime($return_date)) {
        $error = "Data zwrotu musi być późniejsza niż data odbioru";
    } else {
        // Sprawdź dostępność pojazdu w nowym terminie (z wyłączeniem bieżącej rezerwacji)
        $check_query = "SELECT * FROM reservations
                       WHERE vehicle_id = ? AND reservation_id != ?
                       AND status IN ('pending', 'confirmed')
                       AND (
                           (pickup_date BETWEEN ? AND ?)
                           OR (return_date BETWEEN ? AND ?)
                           OR (? BETWEEN pickup_date AND return_date)
                           OR (? BETWEEN pickup_date AND return_date)
                       )";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("iissssss", $reservation['vehicle_id'], $reservation_id,
                         $pickup_date, $return_date, $pickup_date, $return_date,
                         $pickup_date, $return_date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0) {
            $error = "Pojazd jest już zarezerwowany w wybranym terminie";
        } else {
            // Oblicz nowy koszt
            $start = new DateTime($pickup_date);
            $end = new DateTime($return_date);
            $interval = $start->diff($end);
            
            if($reservation['type'] == 'car') {
                $days = $interval->days;
                $total_cost = $days * $reservation['daily_rate'];
            } else {
                $hours = $interval->h + ($interval->days * 24);
                $total_cost = $hours * $reservation['hourly_rate'];
            }
            
            // Dodaj koszt wyposażenia
            $equipment_cost = 0;
            if(!empty($selected_equipment)) {
                $equipment_ids = implode(",", array_map('intval', array_keys($selected_equipment)));
                $equipment_query = $conn->query("SELECT * FROM equipment WHERE equipment_id IN ($equipment_ids)");
                while($eq = $equipment_query->fetch_assoc()) {
                    $qty = intval($selected_equipment[$eq['equipment_id']]);
                    $equipment_cost += $eq['daily_cost'] * $qty * ($reservation['type'] == 'car' ? $days : $hours);
                }
            }
            
            $total_cost += $equipment_cost;
            
            // Rozpocznij transakcję
            $conn->begin_transaction();
            
            try {
                // Aktualizuj rezerwację
                $update_query = "UPDATE reservations SET
                                pickup_date = ?, return_date = ?,
                                pickup_location_id = ?, return_location_id = ?,
                                status = ?, total_cost = ?
                                WHERE reservation_id = ?";
                $stmt = $conn->prepare($update_query);
                $stmt->bind_param("ssiisdi", $pickup_date, $return_date,
                                 $pickup_location, $return_location,
                                 $status, $total_cost, $reservation_id);
                $stmt->execute();
                
                // Usuń stare wyposażenie
                $delete_query = "DELETE FROM reservationequipment WHERE reservation_id = ?";
                $stmt = $conn->prepare($delete_query);
                $stmt->bind_param("i", $reservation_id);
                $stmt->execute();
                
                // Dodaj nowe wyposażenie
                if(!empty($selected_equipment)) {
                    foreach($selected_equipment as $eq_id => $qty) {
                        $eq_id = intval($eq_id);
                        $qty = intval($qty);
                        
                        if($qty > 0) {
                            $eq_query = "INSERT INTO reservationequipment
                                         (reservation_id, equipment_id, quantity)
                                         VALUES (?, ?, ?)";
                            $stmt = $conn->prepare($eq_query);
                            $stmt->bind_param("iii", $reservation_id, $eq_id, $qty);
                            $stmt->execute();
                        }
                    }
                }
                
                $conn->commit();
                $_SESSION['success'] = "Rezerwacja została zaktualizowana";
                header("Location: manage_reservations.php");
                exit();
            } catch (Exception $e) {
                $conn->rollback();
                $error = "Wystąpił błąd podczas aktualizacji rezerwacji: " . $e->getMessage();
            }
        }
    }
}

require_once __DIR__ . '/../../admin/includes/admin_header.php';
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edytuj rezerwację - SkyDrive Rentals</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        :root {
            --primary: #3b82f6;
            --primary-hover: #2563eb;
            --danger: #ef4444;
            --danger-hover: #dc2626;
            --gray: #6b7280;
            --gray-hover: #4b5563;
            --light-bg: #f8f9fa;
            --border: #e5e7eb;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f1f5f9;
            color: #1f2937;
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 25px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #1f2937;
            margin-bottom: 25px;
            font-size: 28px;
            font-weight: 600;
            border-bottom: 1px solid var(--border);
            padding-bottom: 10px;
        }
        
        .reservation-info {
            background: var(--light-bg);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            border-left: 4px solid var(--primary);
        }
        
        .reservation-info p {
            margin: 8px 0;
            font-size: 16px;
        }
        
        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 25px;
            margin-bottom: 25px;
        }
        
        .form-group {
            flex: 1;
            min-width: 250px;
        }
        
        label {
            display: block;
            margin-bottom: 10px;
            font-weight: 500;
            color: #374151;
            font-size: 15px;
        }
        
        input[type="datetime-local"],
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 15px;
            transition: border-color 0.2s;
        }
        
        input[type="datetime-local"]:focus,
        select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
        }
        
        .equipment-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .equipment-item {
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 18px;
            background: #fff;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .equipment-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        
        .equipment-item h4 {
            margin: 0 0 10px 0;
            color: #1f2937;
            font-size: 18px;
        }
        
        .equipment-item p {
            margin: 8px 0;
            color: #4b5563;
            font-size: 14px;
        }
        
        .equipment-item p strong {
            color: #1f2937;
        }
        
        .equipment-controls {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 15px;
        }
        
        .btn-qty {
            width: 34px;
            height: 34px;
            border: none;
            background: var(--primary);
            color: white;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            transition: background-color 0.2s;
        }
        
        .btn-qty:hover {
            background: var(--primary-hover);
        }
        
        .btn-qty.minus {
            background: var(--danger);
        }
        
        .btn-qty.minus:hover {
            background: var(--danger-hover);
        }
        
        .equipment-controls input {
            width: 60px;
            text-align: center;
            padding: 10px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 16px;
            -moz-appearance: textfield;
        }
        
        .equipment-controls input::-webkit-outer-spin-button,
        .equipment-controls input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        
        #price_calculation {
            margin-top: 30px;
            padding: 20px;
            background: #f3f4f6;
            border-radius: 10px;
            border-left: 4px solid var(--primary);
        }
        
        #price_calculation h3 {
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 20px;
        }
        
        #price_calculation p {
            margin: 8px 0;
            font-size: 16px;
        }
        
        #price_calculation #total_cost {
            font-size: 18px;
            margin-top: 12px;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s;
            font-weight: 500;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--primary-hover);
        }
        
        .btn-secondary {
            background: var(--gray);
            color: white;
            margin-left: 12px;
        }
        
        .btn-secondary:hover {
            background: var(--gray-hover);
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 30px;
        }
        
        .alert {
            padding: 14px 18px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 15px;
        }
        
        .alert-danger {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
        }
        
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 15px;
            }
            
            .form-group {
                width: 100%;
            }
            
            .equipment-grid {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn-secondary {
                margin-left: 0;
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Edytuj rezerwację #<?= htmlspecialchars($reservation_id) ?></h1>
    
    <div class="reservation-info">
        <p><strong>Klient:</strong> <?= htmlspecialchars($reservation['customer_name']) ?></p>
        <p><strong>Pojazd:</strong> <?= htmlspecialchars($reservation['make'] . ' ' . $reservation['model']) ?></p>
        <p><strong>Typ:</strong> <?= $reservation['type'] == 'car' ? 'Samochód' : 'Samolot' ?></p>
    </div>
    
    <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <form method="post" class="reservation-form">
        <div class="form-row">
            <div class="form-group">
                <label for="pickup_date">Data odbioru:</label>
                <input type="datetime-local" name="pickup_date" id="pickup_date" 
                       value="<?= date('Y-m-d\TH:i', strtotime($reservation['pickup_date'])) ?>" required>
            </div>
            <div class="form-group">
                <label for="return_date">Data zwrotu:</label>
                <input type="datetime-local" name="return_date" id="return_date" 
                       value="<?= date('Y-m-d\TH:i', strtotime($reservation['return_date'])) ?>" required>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="pickup_location">Miejsce odbioru:</label>
                <select name="pickup_location" id="pickup_location" required>
                    <?php while($loc = $locations->fetch_assoc()): ?>
                        <option value="<?= $loc['location_id'] ?>" 
                            <?= $loc['location_id'] == $reservation['pickup_location_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($loc['city']) ?> - <?= htmlspecialchars($loc['address']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="return_location">Miejsce zwrotu:</label>
                <select name="return_location" id="return_location" required>
                    <?php
                    $locations->data_seek(0);
                    while($loc = $locations->fetch_assoc()): ?>
                        <option value="<?= $loc['location_id'] ?>" 
                            <?= $loc['location_id'] == $reservation['return_location_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($loc['city']) ?> - <?= htmlspecialchars($loc['address']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
        
        <div class="form-group">
            <label for="status">Status rezerwacji:</label>
            <select name="status" id="status" required>
                <option value="pending" <?= $reservation['status'] == 'pending' ? 'selected' : '' ?>>Oczekująca</option>
                <option value="confirmed" <?= $reservation['status'] == 'confirmed' ? 'selected' : '' ?>>Potwierdzona</option>
                <option value="cancelled" <?= $reservation['status'] == 'cancelled' ? 'selected' : '' ?>>Anulowana</option>
            </select>
        </div>
        
        <?php if($equipment->num_rows > 0): ?>
            <h3>Dodatkowe wyposażenie</h3>
            <div class="equipment-grid">
                <?php while($eq = $equipment->fetch_assoc()): ?>
                    <div class="equipment-item">
                        <h4><?= htmlspecialchars($eq['name']) ?></h4>
                        <p><?= htmlspecialchars($eq['description']) ?></p>
                        <p><strong>Cena: <?= number_format($eq['daily_cost'], 2) ?> PLN/<?= $reservation['type'] == 'car' ? 'dzień' : 'godzina' ?></strong></p>
                        <div class="equipment-controls">
                            <button type="button" class="btn-qty minus" data-eq="<?= $eq['equipment_id'] ?>">-</button>
                            <input type="number" name="equipment[<?= $eq['equipment_id'] ?>]" 
                                   id="eq_<?= $eq['equipment_id'] ?>" 
                                   value="<?= $reserved_equipment[$eq['equipment_id']] ?? 0 ?>" 
                                   min="0" max="10" readonly>
                            <button type="button" class="btn-qty plus" data-eq="<?= $eq['equipment_id'] ?>">+</button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
        
        <div id="price_calculation">
            <h3>Szacowany koszt: <span id="calculated_price"><?= number_format($reservation['total_cost'], 2) ?></span> PLN</h3>
            <p id="equipment_cost">Koszt wyposażenia: 0.00 PLN</p>
            <p id="total_cost"><strong>Łączny koszt: <?= number_format($reservation['total_cost'], 2) ?> PLN</strong></p>
        </div>
        
        <div class="form-actions">
            <button type="submit" name="update" class="btn btn-primary">Zapisz zmiany</button>
            <a href="manage_reservations.php" class="btn btn-secondary">Anuluj</a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    // Inicjalizacja kalendarza
    flatpickr("#pickup_date", {
        enableTime: true,
        dateFormat: "Y-m-d H:i",
        minDate: "today",
        onChange: function(selectedDates, dateStr, instance) {
            updatePrice();
        }
    });

    flatpickr("#return_date", {
        enableTime: true,
        dateFormat: "Y-m-d H:i",
        minDate: "today",
        onChange: function(selectedDates, dateStr, instance) {
            updatePrice();
        }
    });

    // Obsługa przycisków ilości wyposażenia
    document.querySelectorAll('.btn-qty').forEach(btn => {
        btn.addEventListener('click', function() {
            const eqId = this.getAttribute('data-eq');
            const input = document.getElementById(`eq_${eqId}`);
            let value = parseInt(input.value);

            if(this.classList.contains('minus') && value > 0) {
                value--;
            } else if(this.classList.contains('plus') && value < 10) {
                value++;
            }

            input.value = value;
            updatePrice();
        });
    });

    // Funkcja obliczająca cenę
    function updatePrice() {
        const pickupDate = document.getElementById('pickup_date').value;
        const returnDate = document.getElementById('return_date').value;

        if(pickupDate && returnDate) {
            const pickup = new Date(pickupDate);
            const returnD = new Date(returnDate);
            const diffTime = Math.abs(returnD - pickup);

            <?php if($reservation['type'] == 'car'): ?>
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                let basePrice = diffDays * <?= $reservation['daily_rate'] ?>;
            <?php else: ?>
                const diffHours = Math.ceil(diffTime / (1000 * 60 * 60));
                let basePrice = diffHours * <?= $reservation['hourly_rate'] ?>;
            <?php endif; ?>

            // Oblicz koszt wyposażenia
            let equipmentPrice = 0;
            document.querySelectorAll('input[name^="equipment"]').forEach(input => {
                const eqId = input.name.match(/\[(\d+)\]/)[1];
                const qty = parseInt(input.value);
                if(qty > 0) {
                    <?php
                    $equipment->data_seek(0);
                    while($eq = $equipment->fetch_assoc()): ?>
                        if(eqId == <?= $eq['equipment_id'] ?>) {
                            equipmentPrice += qty * <?= $eq['daily_cost'] ?> * 
                                <?= $reservation['type'] == 'car' ? 'diffDays' : 'diffHours' ?>;
                        }
                    <?php endwhile; ?>
                }
            });

            const totalPrice = basePrice + equipmentPrice;

            document.getElementById('calculated_price').textContent = basePrice.toFixed(2);
            document.getElementById('equipment_cost').textContent = 
                `Koszt wyposażenia: ${equipmentPrice.toFixed(2)} PLN`;
            document.getElementById('total_cost').innerHTML = 
                `<strong>Łączny koszt: ${totalPrice.toFixed(2)} PLN</strong>`;
        }
    }

    // Inicjalizacja ceny przy ładowaniu strony
    document.addEventListener('DOMContentLoaded', function() {
        updatePrice();
    });
</script>


</body>
</html>