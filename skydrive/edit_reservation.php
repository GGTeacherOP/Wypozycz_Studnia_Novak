<?php
session_start();
include 'includes/config.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$reservation_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$error = '';
$success = '';

// Pobierz dane rezerwacji
$query = "SELECT r.*, v.make, v.model, v.type, v.daily_rate, v.hourly_rate
          FROM reservations r
          JOIN vehicles v ON r.vehicle_id = v.vehicle_id
          WHERE r.reservation_id = ? AND r.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $reservation_id, $user_id);
$stmt->execute();
$reservation = $stmt->get_result()->fetch_assoc();

if(!$reservation) {
    header("Location: my_reservations.php");
    exit();
}

// Aktualizacja rezerwacji
if(isset($_POST['update'])) {
    $pickup_date = $_POST['pickup_date'];
    $return_date = $_POST['return_date'];
    $pickup_location = intval($_POST['pickup_location']);
    $return_location = intval($_POST['return_location']);
    
    // Sprawdź czy termin jest dostępny
    $check_query = "SELECT * FROM reservations 
                   WHERE vehicle_id = ? 
                   AND reservation_id != ?
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
        // Oblicz koszt
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
        
        // Aktualizuj rezerwację
        $update_query = "UPDATE reservations 
                        SET pickup_date = ?, return_date = ?,
                        pickup_location_id = ?, return_location_id = ?,
                        total_cost = ?, status = 'pending'
                        WHERE reservation_id = ? AND user_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ssiidii", $pickup_date, $return_date, 
                         $pickup_location, $return_location, 
                         $total_cost, $reservation_id, $user_id);
        
        if($stmt->execute()) {
            $success = "Rezerwacja została zaktualizowana pomyślnie";
        } else {
            $error = "Wystąpił błąd podczas aktualizacji rezerwacji";
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
    <title>Edycja rezerwacji - SkyDrive</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h1>Edycja rezerwacji: <?= htmlspecialchars($reservation['make']) ?> <?= htmlspecialchars($reservation['model']) ?></h1>
        
        <?php if($error): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="alert success"><?= htmlspecialchars($success) ?></div>
        <?php else: ?>
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
                                <option value="<?= $loc['location_id'] ?>" <?= 
                                    $loc['location_id'] == $reservation['pickup_location_id'] ? 'selected' : '' 
                                ?>>
                                    <?= htmlspecialchars($loc['city']) ?> - <?= htmlspecialchars($loc['address']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Miejsce zwrotu:</label>
                        <select name="return_location" required>
                            <?php 
                            $locations->data_seek(0); // Resetujemy wskaźnik wyników
                            while($loc = $locations->fetch_assoc()): ?>
                                <option value="<?= $loc['location_id'] ?>" <?= 
                                    $loc['location_id'] == $reservation['return_location_id'] ? 'selected' : '' 
                                ?>>
                                    <?= htmlspecialchars($loc['city']) ?> - <?= htmlspecialchars($loc['address']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" name="update" class="btn btn-primary">Zapisz zmiany</button>
                    <a href="my_reservations.php" class="btn btn-secondary">Anuluj</a>
                </div>
            </form>
            
            <div id="price_calculation" style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
                <h3>Aktualny koszt: <?= number_format($reservation['total_cost'], 2) ?> PLN</h3>
                <h3>Nowy koszt: <span id="calculated_price"><?= number_format($reservation['total_cost'], 2) ?></span> PLN</h3>
            </div>
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
                    const price = diffDays * <?= $reservation['daily_rate'] ?>;
                    document.getElementById('calculated_price').textContent = price.toFixed(2);
                <?php else: ?>
                    const diffHours = Math.ceil(diffTime / (1000 * 60 * 60));
                    const price = diffHours * <?= $reservation['hourly_rate'] ?>;
                    document.getElementById('calculated_price').textContent = price.toFixed(2);
                <?php endif; ?>
            }
        }
    </script>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>