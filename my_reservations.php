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

// Pobierz rezerwacje użytkownika
$query = "SELECT r.*, v.make, v.model, v.type, 
          pl.city as pickup_city, pl.address as pickup_address,
          rl.city as return_city, rl.address as return_address
          FROM reservations r
          JOIN vehicles v ON r.vehicle_id = v.vehicle_id
          JOIN locations pl ON r.pickup_location_id = pl.location_id
          JOIN locations rl ON r.return_location_id = rl.location_id
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
                        
                        <div class="reservation-details">
                            <p><strong>Nr rezerwacji:</strong> <?= $res['reservation_id'] ?></p>
                            <p><strong>Data odbioru:</strong> <?= date('d.m.Y H:i', strtotime($res['pickup_date'])) ?></p>
                            <p><strong>Data zwrotu:</strong> <?= date('d.m.Y H:i', strtotime($res['return_date'])) ?></p>
                            <p><strong>Miejsce odbioru:</strong> <?= htmlspecialchars($res['pickup_city']) ?>, <?= htmlspecialchars($res['pickup_address']) ?></p>
                            <p><strong>Miejsce zwrotu:</strong> <?= htmlspecialchars($res['return_city']) ?>, <?= htmlspecialchars($res['return_address']) ?></p>
                            <p><strong>Koszt:</strong> <?= number_format($res['total_cost'], 2) ?> PLN</p>
                        </div>
                        
                        <div class="reservation-actions">
                            <?php if($res['status'] == 'pending' || $res['status'] == 'confirmed'): ?>
                                <a href="edit_reservation.php?id=<?= $res['reservation_id'] ?>" class="btn btn-edit">Edytuj</a>
                                <a href="?cancel=<?= $res['reservation_id'] ?>" class="btn btn-delete" 
                                   onclick="return confirm('Czy na pewno chcesz anulować tę rezerwację?')">Anuluj</a>
                            <?php endif; ?>
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