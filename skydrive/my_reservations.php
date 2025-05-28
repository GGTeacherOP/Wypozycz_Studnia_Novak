<?php
session_start();
include 'includes/config.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Anulowanie rezerwacji
if(isset($_GET['cancel'])) {
    $reservation_id = intval($_GET['cancel']);
    
    $query = "UPDATE reservations SET status = 'cancelled' 
              WHERE reservation_id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $reservation_id, $user_id);
    
    if($stmt->execute()) {
        $_SESSION['success'] = "Rezerwacja została anulowana";
    } else {
        $_SESSION['error'] = "Błąd podczas anulowania rezerwacji";
    }
    header("Location: my_reservations.php");
    exit();
}


// Pobierz rezerwacje użytkownika z informacjami o płatnościach
$query = "SELECT r.*, v.make, v.model, v.type, v.image_path,
          pl.city as pickup_city, pl.address as pickup_address,
          rl.city as return_city, rl.address as return_address,
          p.status as payment_status, p.payment_method, p.payment_date,
          p.amount as payment_amount, p.invoice_number

// Pobierz rezerwacje użytkownika
$query = "SELECT r.*, v.make, v.model, v.type, 
          pl.city as pickup_city, pl.address as pickup_address,
          rl.city as return_city, rl.address as return_address

          FROM reservations r
          JOIN vehicles v ON r.vehicle_id = v.vehicle_id
          JOIN locations pl ON r.pickup_location_id = pl.location_id
          JOIN locations rl ON r.return_location_id = rl.location_id

          LEFT JOIN payments p ON r.reservation_id = p.reservation_id
=======

          WHERE r.user_id = ?
          ORDER BY r.pickup_date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$reservations = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Moje rezerwacje - SkyDrive</title>
    <link rel="stylesheet" href="css/style.css">

    <style>
        .reservations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .reservation-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            position: relative;
        }
        
        .reservation-card.pending {
            border-left: 4px solid #ffc107;
        }
        
        .reservation-card.confirmed {
            border-left: 4px solid #28a745;
        }
        
        .reservation-card.cancelled {
            border-left: 4px solid #dc3545;
            opacity: 0.7;
        }
        
        .reservation-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
        }
        
        .reservation-card.pending .status-badge {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .reservation-card.confirmed .status-badge {
            background-color: #d4edda;
            color: #155724;
        }
        
        .reservation-card.cancelled .status-badge {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .reservation-details p {
            margin-bottom: 8px;
        }
        
        .reservation-actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }
        
        .vehicle-image {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        
        .payment-info {
            margin-top: 15px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .payment-status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
        }
        
        .payment-status.completed {
            background-color: #d4edda;
            color: #155724;
        }
        
        .payment-status.pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .payment-status.failed {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>

</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h1>Moje rezerwacje</h1>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if($reservations->num_rows > 0): ?>
            <div class="reservations-grid">
                <?php while($res = $reservations->fetch_assoc()): ?>
                    <div class="reservation-card <?= $res['status'] ?>">
                        <div class="reservation-header">
                            <h3><?= htmlspecialchars($res['make']) ?> <?= htmlspecialchars($res['model']) ?></h3>
                            <span class="status-badge"><?= 
                                $res['status'] == 'pending' ? 'Oczekująca' : 
                                ($res['status'] == 'confirmed' ? 'Potwierdzona' : 'Anulowana') 
                            ?></span>
                        </div>
                        

                        <?php if($res['image_path']): ?>
                            <img src="<?= htmlspecialchars($res['image_path']) ?>" alt="<?= htmlspecialchars($res['make']) ?> <?= htmlspecialchars($res['model']) ?>" class="vehicle-image">
                        <?php endif; ?>
                        

                        <div class="reservation-details">
                            <p><strong>Nr rezerwacji:</strong> <?= $res['reservation_id'] ?></p>
                            <p><strong>Data odbioru:</strong> <?= date('d.m.Y H:i', strtotime($res['pickup_date'])) ?></p>
                            <p><strong>Data zwrotu:</strong> <?= date('d.m.Y H:i', strtotime($res['return_date'])) ?></p>
                            <p><strong>Miejsce odbioru:</strong> <?= htmlspecialchars($res['pickup_city']) ?>, <?= htmlspecialchars($res['pickup_address']) ?></p>
                            <p><strong>Miejsce zwrotu:</strong> <?= htmlspecialchars($res['return_city']) ?>, <?= htmlspecialchars($res['return_address']) ?></p>
                            <p><strong>Koszt:</strong> <?= number_format($res['total_cost'], 2) ?> PLN</p>
                        </div>
                        

                        <div class="payment-info">
                            <p><strong>Płatność:</strong> 
                                <?php if($res['payment_status']): ?>
                                    <span class="payment-status <?= $res['payment_status'] ?>">
                                        <?= ucfirst($res['payment_status']) ?>
                                    </span>
                                <?php else: ?>
                                    <span>Nie dokonano</span>
                                <?php endif; ?>
                            </p>
                            <?php if($res['payment_method']): ?>
                                <p><strong>Metoda:</strong> <?= 
                                    $res['payment_method'] == 'credit_card' ? 'Karta kredytowa' : 
                                    ($res['payment_method'] == 'bank_transfer' ? 'Przelew bankowy' : 'Gotówka') 
                                ?></p>
                            <?php endif; ?>
                            <?php if($res['payment_amount']): ?>
                                <p><strong>Zapłacono:</strong> <?= number_format($res['payment_amount'], 2) ?> PLN</p>
                            <?php endif; ?>
                            <?php if($res['invoice_number']): ?>
                                <p><strong>Nr faktury:</strong> <?= $res['invoice_number'] ?></p>
                                <a href="invoice.php?id=<?= $res['reservation_id'] ?>" class="btn btn-sm btn-info">Pobierz fakturę</a>
                            <?php endif; ?>
                        </div>
                        

                        <div class="reservation-actions">
                            <?php if($res['status'] == 'pending' || $res['status'] == 'confirmed'): ?>
                                <a href="edit_reservation.php?id=<?= $res['reservation_id'] ?>" class="btn btn-edit">Edytuj</a>
                                <a href="?cancel=<?= $res['reservation_id'] ?>" class="btn btn-delete" 
                                   onclick="return confirm('Czy na pewno chcesz anulować tę rezerwację?')">Anuluj</a>
                            <?php endif; ?>

                            <a href="reservation_details.php?id=<?= $res['reservation_id'] ?>" class="btn btn-primary">Szczegóły</a>

                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>Nie masz jeszcze żadnych rezerwacji.</p>
            <a href="vehicles.php" class="btn btn-primary">Zarezerwuj pojazd</a>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>