<?php
require_once __DIR__ . '/admin_functions.php';
checkAdminAuth();

require_once __DIR__ . '/../../includes/config.php';

$reservation_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Pobierz dane rezerwacji
$stmt = $conn->prepare("
    SELECT r.*, 
           v.make, v.model, v.type, v.image_path,
           u.first_name, u.last_name, u.email, u.phone,
           pl.city as pickup_city, pl.address as pickup_address,
           rl.city as return_city, rl.address as return_address
    FROM reservations r
    JOIN vehicles v ON r.vehicle_id = v.vehicle_id
    JOIN users u ON r.user_id = u.user_id
    JOIN locations pl ON r.pickup_location_id = pl.location_id
    JOIN locations rl ON r.return_location_id = rl.location_id
    WHERE r.reservation_id = ?
");
$stmt->bind_param("i", $reservation_id);
$stmt->execute();
$reservation = $stmt->get_result()->fetch_assoc();

if (!$reservation) {
    header("Location: pages/manage_reservations.php");
    exit();
}

// Pobierz wyposażenie rezerwacji
$equipment = $conn->query("
    SELECT e.name, e.description, e.daily_cost, re.quantity
    FROM reservationequipment re
    JOIN equipment e ON re.equipment_id = e.equipment_id
    WHERE re.reservation_id = $reservation_id
");

require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Szczegóły rezerwacji #<?= $reservation_id ?></h2>
        <a href="pages/manage_reservations.php" class="btn btn-secondary">Powrót</a>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Dane pojazdu</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <?php if ($reservation['image_path']): ?>
                                <img src="../images/<?= htmlspecialchars($reservation['image_path']) ?>" 
                                     alt="<?= htmlspecialchars($reservation['make'] . ' ' . $reservation['model']) ?>" 
                                     class="img-fluid">
                            <?php else: ?>
                                <div class="bg-light p-5 text-center">
                                    Brak zdjęcia
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-8">
                            <h3><?= htmlspecialchars($reservation['make'] . ' ' . $reservation['model']) ?></h3>
                            <p class="text-muted"><?= $reservation['type'] == 'car' ? 'Samochód' : 'Samolot' ?></p>
                            <p><strong>Rok produkcji:</strong> <?= $reservation['year'] ?? '---' ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Dane klienta</h4>
                </div>
                <div class="card-body">
                    <h5><?= htmlspecialchars($reservation['first_name'] . ' ' . $reservation['last_name']) ?></h5>
                    <p><strong>Email:</strong> <?= htmlspecialchars($reservation['email']) ?></p>
                    <p><strong>Telefon:</strong> <?= htmlspecialchars($reservation['phone']) ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Szczegóły rezerwacji</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h5>Odbiór</h5>
                            <p><strong>Data:</strong> <?= date('d.m.Y H:i', strtotime($reservation['pickup_date'])) ?></p>
                            <p><strong>Miejsce:</strong><br>
                                <?= htmlspecialchars($reservation['pickup_city']) ?><br>
                                <?= htmlspecialchars($reservation['pickup_address']) ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h5>Zwrot</h5>
                            <p><strong>Data:</strong> <?= date('d.m.Y H:i', strtotime($reservation['return_date'])) ?></p>
                            <p><strong>Miejsce:</strong><br>
                                <?= htmlspecialchars($reservation['return_city']) ?><br>
                                <?= htmlspecialchars($reservation['return_address']) ?>
                            </p>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <h5>Status rezerwacji</h5>
                        <span class="badge bg-<?= 
                            $reservation['status'] == 'confirmed' ? 'success' : 
                            ($reservation['status'] == 'pending' ? 'warning' : 'danger') 
                        ?>">
                            <?= 
                                $reservation['status'] == 'confirmed' ? 'Potwierdzona' : 
                                ($reservation['status'] == 'pending' ? 'Oczekująca' : 'Anulowana') 
                            ?>
                        </span>
                    </div>
                    
                    <?php if ($equipment->num_rows > 0): ?>
                    <div class="mb-3">
                        <h5>Dodatkowe wyposażenie</h5>
                        <ul class="list-group">
                            <?php while ($item = $equipment->fetch_assoc()): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?= htmlspecialchars($item['name']) ?>
                                <span class="badge bg-primary rounded-pill">
                                    <?= $item['quantity'] ?> × <?= number_format($item['daily_cost'], 2) ?> PLN
                                </span>
                            </li>
                            <?php endwhile; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <div class="alert alert-info">
                        <h5>Podsumowanie kosztów</h5>
                        <p><strong>Koszt podstawowy:</strong> 
                            <?= number_format($reservation['total_cost'], 2) ?> PLN</p>
                        <?php if ($equipment->num_rows > 0): ?>
                            <p><strong>Dodatkowe wyposażenie:</strong> 
                                <?= number_format($additional_cost, 2) ?> PLN</p>
                            <p><strong>Łączny koszt:</strong> 
                                <?= number_format($reservation['total_cost'] + $additional_cost, 2) ?> PLN</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>