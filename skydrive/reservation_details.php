<?php
session_start();
require_once 'includes/config.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$reservation_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];

// Pobierz szczegóły rezerwacji
$query = "SELECT r.*, v.make, v.model, v.type, v.image_path,
          pl.city as pickup_city, pl.address as pickup_address,
          rl.city as return_city, rl.address as return_address,
          p.status as payment_status, p.payment_method, p.payment_date,
          p.amount as payment_amount, p.invoice_number, p.payment_details,
          CONCAT(u.first_name, ' ', u.last_name) as customer_name,
          u.email, u.phone
          FROM reservations r
          JOIN vehicles v ON r.vehicle_id = v.vehicle_id
          JOIN locations pl ON r.pickup_location_id = pl.location_id
          JOIN locations rl ON r.return_location_id = rl.location_id
          JOIN users u ON r.user_id = u.user_id
          LEFT JOIN payments p ON r.reservation_id = p.reservation_id
          WHERE r.reservation_id = ? AND r.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $reservation_id, $user_id);
$stmt->execute();
$reservation = $stmt->get_result()->fetch_assoc();

if(!$reservation) {
    header("Location: my_reservations.php");
    exit();
}

// Pobierz wyposażenie rezerwacji
$equipment_query = "SELECT e.name, e.description, e.daily_cost, re.quantity
                   FROM reservationequipment re
                   JOIN equipment e ON re.equipment_id = e.equipment_id
                   WHERE re.reservation_id = ?";
$stmt = $conn->prepare($equipment_query);
$stmt->bind_param("i", $reservation_id);
$stmt->execute();
$equipment = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Szczegóły rezerwacji #<?= $reservation_id ?> - SkyDrive</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .reservation-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .reservation-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .vehicle-image {
            width: 100%;
            max-height: 300px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        .detail-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .detail-card h3 {
            margin-top: 0;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .equipment-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: bold;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-confirmed {
            background-color: #d4edda;
            color: #155724;
        }
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        .payment-status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 14px;
        }
        .payment-completed {
            background-color: #d4edda;
            color: #155724;
        }
        .payment-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .payment-failed {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container reservation-container">
        <div class="reservation-header">
            <h1>Szczegóły rezerwacji #<?= $reservation_id ?></h1>
            <span class="status-badge status-<?= $reservation['status'] ?>">
                <?= $reservation['status'] == 'pending' ? 'Oczekująca' : 
                   ($reservation['status'] == 'confirmed' ? 'Potwierdzona' : 'Anulowana') ?>
            </span>
        </div>
        
        <?php if($reservation['image_path']): ?>
            <img src="<?= htmlspecialchars($reservation['image_path']) ?>" 
                 alt="<?= htmlspecialchars($reservation['make']) ?> <?= htmlspecialchars($reservation['model']) ?>" 
                 class="vehicle-image">
        <?php endif; ?>
        
        <div class="details-grid">
            <div class="detail-card">
                <h3>Informacje o rezerwacji</h3>
                <p><strong>Pojazd:</strong> <?= htmlspecialchars($reservation['make']) ?> <?= htmlspecialchars($reservation['model']) ?></p>
                <p><strong>Typ:</strong> <?= $reservation['type'] == 'car' ? 'Samochód' : 'Samolot' ?></p>
                <p><strong>Data odbioru:</strong> <?= date('d.m.Y H:i', strtotime($reservation['pickup_date'])) ?></p>
                <p><strong>Data zwrotu:</strong> <?= date('d.m.Y H:i', strtotime($reservation['return_date'])) ?></p>
                <p><strong>Miejsce odbioru:</strong> <?= htmlspecialchars($reservation['pickup_city']) ?>, <?= htmlspecialchars($reservation['pickup_address']) ?></p>
                <p><strong>Miejsce zwrotu:</strong> <?= htmlspecialchars($reservation['return_city']) ?>, <?= htmlspecialchars($reservation['return_address']) ?></p>
                <p><strong>Koszt całkowity:</strong> <?= number_format($reservation['total_cost'], 2) ?> PLN</p>
            </div>
            
            <div class="detail-card">
                <h3>Informacje o płatności</h3>
                <?php if($reservation['payment_status']): ?>
                    <p><strong>Status:</strong> 
                        <span class="payment-status payment-<?= $reservation['payment_status'] ?>">
                            <?= ucfirst($reservation['payment_status']) ?>
                        </span>
                    </p>
                    <p><strong>Metoda płatności:</strong> <?= 
                        $reservation['payment_method'] == 'credit_card' ? 'Karta kredytowa' : 
                        ($reservation['payment_method'] == 'bank_transfer' ? 'Przelew bankowy' : 'Gotówka') 
                    ?></p>
                    <p><strong>Data płatności:</strong> <?= date('d.m.Y H:i', strtotime($reservation['payment_date'])) ?></p>
                    <p><strong>Zapłacono:</strong> <?= number_format($reservation['payment_amount'], 2) ?> PLN</p>
                    <?php if($reservation['invoice_number']): ?>
                        <p><strong>Nr faktury:</strong> <?= $reservation['invoice_number'] ?></p>
                        <a href="invoice.php?id=<?= $reservation_id ?>" class="btn btn-primary">Pobierz fakturę</a>
                    <?php endif; ?>
                <?php else: ?>
                    <p>Płatność nie została jeszcze dokonana.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if($equipment->num_rows > 0): ?>
            <div class="detail-card">
                <h3>Dodatkowe wyposażenie</h3>
                <?php
$pickup = strtotime($reservation['pickup_date']);
$return = strtotime($reservation['return_date']);

// Określ czas trwania rezerwacji w dniach (dla samochodu) lub godzinach (dla samolotu)
$duration = $reservation['type'] == 'car'
    ? ceil(($return - $pickup) / (60 * 60 * 24))
    : ceil(($return - $pickup) / (60 * 60));

while($eq = $equipment->fetch_assoc()):
    $item_cost = $eq['daily_cost'] * $eq['quantity'] * $duration;
?>
    <div class="equipment-item">
        <div>
            <h4><?= htmlspecialchars($eq['name']) ?></h4>
            <p><?= htmlspecialchars($eq['description']) ?></p>
            <p>Cena: <?= number_format($eq['daily_cost'], 2) ?> PLN/<?= $reservation['type'] == 'car' ? 'dzień' : 'godzinę' ?></p>
        </div>
        <div>
            <p>Ilość: <?= $eq['quantity'] ?></p>
            <p>Koszt: <?= number_format($item_cost, 2) ?> PLN</p>
        </div>
    </div>
<?php endwhile; ?>

            </div>
        <?php endif; ?>
        
        <div class="reservation-actions" style="margin-top: 20px;">
            <?php if($reservation['status'] == 'pending' || $reservation['status'] == 'confirmed'): ?>
                <a href="edit_reservation.php?id=<?= $reservation_id ?>" class="btn btn-edit">Edytuj</a>
                <a href="?cancel=<?= $reservation_id ?>" class="btn btn-delete" 
                   onclick="return confirm('Czy na pewno chcesz anulować tę rezerwację?')">Anuluj</a>
            <?php endif; ?>
            <a href="my_reservations.php" class="btn btn-secondary">Powrót</a>
        </div>
    </div>
    
    
</body>
</html>
<?php include 'includes/footer.php'; ?>