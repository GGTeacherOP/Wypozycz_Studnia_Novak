<?php
session_start();
include 'includes/config.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';
$vehicle_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$type = isset($_GET['type']) ? $_GET['type'] : '';

// Pobierz dane pojazdu
$query = "SELECT * FROM vehicles WHERE vehicle_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $vehicle_id);
$stmt->execute();
$vehicle = $stmt->get_result()->fetch_assoc();

if(!$vehicle) {
    header("Location: vehicles.php");
    exit();
}

// Pobierz dostępne wyposażenie
$equipment = $conn->query("SELECT * FROM equipment");

// Przetwarzanie formularza rezerwacji
if(isset($_POST['reserve'])) {
    $pickup_date = $_POST['pickup_date'];
    $return_date = $_POST['return_date'];
    $pickup_location = intval($_POST['pickup_location']);
    $return_location = intval($_POST['return_location']);
    $user_id = $_SESSION['user_id'];
    $selected_equipment = isset($_POST['equipment']) ? $_POST['equipment'] : [];
    $payment_method = $_POST['payment_method'];
    $invoice_request = isset($_POST['invoice_request']) ? 1 : 0;

     $invoice_data = null;
    if($invoice_request) {
        $invoice_data = json_encode([
            'company' => $_POST['invoice_company'],
            'nip' => $_POST['invoice_nip'],
            'address' => $_POST['invoice_address']
        ]);
    }

    // Zmodyfikuj zapytanie INSERT
    $insert_query = "INSERT INTO reservations
                    (user_id, vehicle_id, pickup_location_id, return_location_id,
                    pickup_date, return_date, total_cost, status, 
                    payment_method, invoice_request, invoice_data)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?)";
    
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("iiiissdsss", $user_id, $vehicle_id, $pickup_location,
                     $return_location, $pickup_date, $return_date, $total_cost,
                     $payment_method, $invoice_request, $invoice_data);

    // Sprawdź dostępność pojazdu
    $check_query = "SELECT * FROM reservations 
                   WHERE vehicle_id = ? 
                   AND status IN ('pending', 'confirmed')
                   AND (
                       (pickup_date BETWEEN ? AND ?) 
                       OR (return_date BETWEEN ? AND ?) 
                       OR (? BETWEEN pickup_date AND return_date) 
                       OR (? BETWEEN pickup_date AND return_date)
                   )";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("issssss", $vehicle_id, $pickup_date, $return_date, 
                     $pickup_date, $return_date, $pickup_date, $return_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $conflicts = [];
        while($row = $result->fetch_assoc()) {
            $conflicts[] = [
                'start' => date('d.m.Y H:i', strtotime($row['pickup_date'])),
                'end' => date('d.m.Y H:i', strtotime($row['return_date']))
            ];
        }
        
        $error = "Pojazd jest już zarezerwowany w następujących terminach:<br><ul>";
        foreach($conflicts as $conflict) {
            $error .= "<li>{$conflict['start']} - {$conflict['end']}</li>";
        }
        $error .= "</ul>Proszę wybrać inny termin.";
    } else {
        // Oblicz koszt
        $start = new DateTime($pickup_date);
        $end = new DateTime($return_date);
        $interval = $start->diff($end);
        
        if($vehicle['type'] == 'car') {
            $days = $interval->days;
            $total_cost = $days * $vehicle['daily_rate'];
        } else {
            $hours = $interval->h + ($interval->days * 24);
            $total_cost = $hours * $vehicle['hourly_rate'];
        }
        
        // Dodaj koszt wyposażenia
        $equipment_cost = 0;
        if(!empty($selected_equipment)) {
            $equipment_ids = implode(",", array_map('intval', array_keys($selected_equipment)));
            $equipment_query = $conn->query("SELECT * FROM equipment WHERE equipment_id IN ($equipment_ids)");
            while($eq = $equipment_query->fetch_assoc()) {
                $qty = intval($selected_equipment[$eq['equipment_id']]);
                $equipment_cost += $eq['daily_cost'] * $qty * ($vehicle['type'] == 'car' ? $days : $hours);
            }
        }
        
        $total_cost += $equipment_cost;

        // Rozpocznij transakcję
        $conn->begin_transaction();
        
        try {
            // Dodaj rezerwację
            $insert_query = "INSERT INTO reservations 
                            (user_id, vehicle_id, pickup_location_id, return_location_id, 
                            pickup_date, return_date, total_cost, status) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("iiiissd", $user_id, $vehicle_id, $pickup_location, 
                             $return_location, $pickup_date, $return_date, $total_cost);
            $stmt->execute();
            $reservation_id = $conn->insert_id;
            
            // Dodaj wyposażenie do rezerwacji
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
            $success = "Rezerwacja została złożona pomyślnie. Numer rezerwacji: $reservation_id";
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Wystąpił błąd podczas rezerwacji: " . $e->getMessage();
        }
    }
}

// Pobierz lokalizacje
$locations = $conn->query("SELECT * FROM locations");
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Rezerwacja - SkyDrive</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .equipment-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .equipment-item {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            background: white;
        }
        .equipment-item h4 {
            margin-top: 0;
        }
        .equipment-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }
        .equipment-controls input {
            width: 60px;
            text-align: center;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h1>Rezerwacja: <?= htmlspecialchars($vehicle['make'] . ' ' . htmlspecialchars($vehicle['model'])) ?></h1>
        
        <?php if($error): ?>
            <div class="alert error"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="alert success"><?= $success ?></div>
            <a href="my_reservations.php" class="btn btn-primary">Przejdź do moich rezerwacji</a>
        <?php else: ?>
            <form method="post" class="reservation-form">
                <div class="form-row">
                    <div class="form-group">
                        <label>Data odbioru:</label>
                        <input type="datetime-local" name="pickup_date" id="pickup_date" required>
                    </div>
                    <div class="form-group">
                        <label>Data zwrotu:</label>
                        <input type="datetime-local" name="return_date" id="return_date" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Miejsce odbioru:</label>
                        <select name="pickup_location" required>
                            <?php while($loc = $locations->fetch_assoc()): ?>
                                <option value="<?= $loc['location_id'] ?>">
                                    <?= htmlspecialchars($loc['city']) ?> - <?= htmlspecialchars($loc['address']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Miejsce zwrotu:</label>
                        <select name="return_location" required>
                            <?php 
                            $locations->data_seek(0);
                            while($loc = $locations->fetch_assoc()): ?>
                                <option value="<?= $loc['location_id'] ?>">
                                    <?= htmlspecialchars($loc['city']) ?> - <?= htmlspecialchars($loc['address']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
    <label>Metoda płatności:</label>
    <select name="payment_method" id="payment_method" required>
        <option value="">Wybierz metodę płatności</option>
        <option value="credit_card">Karta kredytowa</option>
        <option value="bank_transfer">Przelew bankowy</option>
        <option value="cash">Gotówka</option>
    </select>
</div>

<div class="form-group">
    <label>
        <input type="checkbox" name="invoice_request" id="invoice_request"> 
        Chcę otrzymać fakturę
    </label>
</div>

<div id="invoice_fields" style="display: none;">
    <h3>Dane do faktury</h3>
    <div class="form-group">
        <label>Nazwa firmy:</label>
        <input type="text" name="invoice_company">
    </div>
    <div class="form-group">
        <label>NIP:</label>
        <input type="text" name="invoice_nip" pattern="[0-9]{10}">
    </div>
    <div class="form-group">
        <label>Adres:</label>
        <input type="text" name="invoice_address">
    </div>
</div>
                <h3>Dodatkowe wyposażenie</h3>
                <?php if($equipment->num_rows > 0): ?>
                    <div class="equipment-grid">
                        <?php while($eq = $equipment->fetch_assoc()): ?>
                            <div class="equipment-item">
                                <h4><?= htmlspecialchars($eq['name']) ?></h4>
                                <p><?= htmlspecialchars($eq['description']) ?></p>
                                <p><strong>Cena: <?= number_format($eq['daily_cost'], 2) ?> PLN/dzień</strong></p>
                                <div class="equipment-controls">
                                    <button type="button" class="btn-qty minus" data-eq="<?= $eq['equipment_id'] ?>">-</button>
                                    <input type="number" name="equipment[<?= $eq['equipment_id'] ?>]" 
                                           id="eq_<?= $eq['equipment_id'] ?>" value="0" min="0" max="10" readonly>
                                    <button type="button" class="btn-qty plus" data-eq="<?= $eq['equipment_id'] ?>">+</button>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p>Brak dostępnego wyposażenia.</p>
                <?php endif; ?>
                
                <div id="price_calculation" style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
                    <h3>Szacowany koszt: <span id="calculated_price">0.00</span> PLN</h3>
                    <p id="equipment_cost">Koszt wyposażenia: 0.00 PLN</p>
                    <p id="total_cost"><strong>Łączny koszt: 0.00 PLN</strong></p>
                </div>
                
                <div class="form-group">
                    <button type="submit" name="reserve" class="btn btn-primary">Zarezerwuj</button>
                    <a href="vehicles.php" class="btn btn-secondary">Anuluj</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        // Inicjalizacja kalendarza
        flatpickr("#pickup_date", {
            minDate: "today",
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            onChange: function(selectedDates, dateStr, instance) {
                updatePrice();
            }
        });
        
        flatpickr("#return_date", {
            minDate: "today",
            enableTime: true,
            dateFormat: "Y-m-d H:i",
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
                
                <?php if($vehicle['type'] == 'car'): ?>
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                    let basePrice = diffDays * <?= $vehicle['daily_rate'] ?>;
                <?php else: ?>
                    const diffHours = Math.ceil(diffTime / (1000 * 60 * 60));
                    let basePrice = diffHours * <?= $vehicle['hourly_rate'] ?>;
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
                                    <?= $vehicle['type'] == 'car' ? 'diffDays' : 'diffHours' ?>;
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

        document.getElementById('invoice_request').addEventListener('change', function() {
    document.getElementById('invoice_fields').style.display = 
        this.checked ? 'block' : 'none';
});
    </script>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>