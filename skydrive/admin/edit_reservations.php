<?php
session_start();
require_once '../includes/config.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$reservation_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;

// Pobierz dane rezerwacji
$query = "SELECT r.*, v.make, v.model, v.type, v.daily_rate, v.hourly_rate,
          v.available, v.location_id as vehicle_location
          FROM reservations r
          JOIN vehicles v ON r.vehicle_id = v.vehicle_id
          WHERE r.reservation_id = ?" . ($is_admin ? "" : " AND r.user_id = ?");
          
$stmt = $conn->prepare($query);
if($is_admin) {
    $stmt->bind_param("i", $reservation_id);
} else {
    $stmt->bind_param("ii", $reservation_id, $user_id);
}
$stmt->execute();
$reservation = $stmt->get_result()->fetch_assoc();

if(!$reservation) {
    header("Location: " . ($is_admin ? "manage_reservations.php" : "my_reservations.php"));
    exit();
}

// Sprawdź czy można edytować rezerwację
$can_edit = $is_admin || ($reservation['status'] == 'pending' && 
           strtotime($reservation['pickup_date']) > time() + 24*60*60);

if(!$can_edit) {
    $_SESSION['error'] = "Nie można edytować tej rezerwacji";
    header("Location: " . ($is_admin ? "manage_reservations.php" : "my_reservations.php"));
    exit();
}

// Pobierz dostępne lokalizacje
$locations = $conn->query("SELECT * FROM locations ORDER BY city");

// Pobierz wyposażenie
$equipment = $conn->query("SELECT * FROM equipment");
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
    $selected_equipment = isset($_POST['equipment']) ? $_POST['equipment'] : [];
    
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
                            total_cost = ?
                            WHERE reservation_id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("ssiidi", $pickup_date, $return_date,
                             $pickup_location, $return_location,
                             $total_cost, $reservation_id);
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
            header("Location: " . ($is_admin ? "reservation_details.php?id=$reservation_id" : "my_reservations.php"));
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Wystąpił błąd podczas aktualizacji rezerwacji: " . $e->getMessage();
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container">
    <h1>Edytuj rezerwację #<?= $reservation_id ?></h1>
    <p>Pojazd: <?= htmlspecialchars($reservation['make']) ?> <?= htmlspecialchars($reservation['model']) ?></p>
    
    <?php if(isset($error)): ?>
        <div class="alert error"><?= $error ?></div>
    <?php endif; ?>
    
    <form method="post" class="reservation-form">
        <div class="form-row">
            <div class="form-group">
                <label>Data odbioru:</label>
                <input type="datetime-local" name="pickup_date" id="pickup_date" 
                       value="<?= date('Y-m-d\TH:i', strtotime($reservation['pickup_date'])) ?>" required>
            </div>
            <div class="form-group">
                <label>Data zwrotu:</label>
                <input type="datetime-local" name="return_date" id="return_date" 
                       value="<?= date('Y-m-d\TH:i', strtotime($reservation['return_date'])) ?>" required>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Miejsce odbioru:</label>
                <select name="pickup_location" required>
                    <?php while($loc = $locations->fetch_assoc()): ?>
                        <option value="<?= $loc['location_id'] ?>" 
                            <?= $loc['location_id'] == $reservation['pickup_location_id'] ? 'selected' : '' ?>>
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
                        <option value="<?= $loc['location_id'] ?>" 
                            <?= $loc['location_id'] == $reservation['return_location_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($loc['city']) ?> - <?= htmlspecialchars($loc['address']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
        
        <?php if($equipment->num_rows > 0): ?>
            <h3>Dodatkowe wyposażenie</h3>
            <div class="equipment-grid">
                <?php while($eq = $equipment->fetch_assoc()): ?>
                    <div class="equipment-item">
                        <h4><?= htmlspecialchars($eq['name']) ?></h4>
                        <p><?= htmlspecialchars($eq['description']) ?></p>
                        <p><strong>Cena: <?= number_format($eq['daily_cost'], 2) ?> PLN/dzień</strong></p>
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
        
        <div id="price_calculation" style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
            <h3>Szacowany koszt: <span id="calculated_price"><?= number_format($reservation['total_cost'], 2) ?></span> PLN</h3>
            <p id="equipment_cost">Koszt wyposażenia: 0.00 PLN</p>
            <p id="total_cost"><strong>Łączny koszt: <?= number_format($reservation['total_cost'], 2) ?> PLN</strong></p>
        </div>
        
        <div class="form-group">
            <button type="submit" name="update" class="btn btn-primary">Zapisz zmiany</button>
            <a href="<?= $is_admin ? "reservation_details.php?id=$reservation_id" : "my_reservations.php" ?>" 
               class="btn btn-secondary">Anuluj</a>
        </div>
    </form>
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

<?php include 'includes/footer.php'; ?>