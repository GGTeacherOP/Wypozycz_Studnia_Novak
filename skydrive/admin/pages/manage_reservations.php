<?php
require_once __DIR__ . '/../admin_functions.php';
checkAdminAuth();

require_once __DIR__ . '/../../includes/config.php';

// Akcje na rezerwacjach
if (isset($_GET['action']) && isset($_GET['id'])) {
    $reservation_id = intval($_GET['id']);
    $action = $_GET['action'];
    
    $valid_statuses = ['pending', 'confirmed', 'cancelled'];
    if (in_array($action, $valid_statuses)) {
        $stmt = $conn->prepare("UPDATE reservations SET status = ? WHERE reservation_id = ?");
        $stmt->bind_param("si", $action, $reservation_id);
        $stmt->execute();
        $_SESSION['success'] = "Status rezerwacji został zaktualizowany";
    }
    header("Location: manage_reservations.php");
    exit();
}

// Pobierz listę rezerwacji
$reservations = $conn->query("
    SELECT r.*, 
           v.make, v.model, v.type,
           u.first_name, u.last_name, u.email,
           pl.city as pickup_city, rl.city as return_city
    FROM reservations r
    JOIN vehicles v ON r.vehicle_id = v.vehicle_id
    JOIN users u ON r.user_id = u.user_id
    JOIN locations pl ON r.pickup_location_id = pl.location_id
    JOIN locations rl ON r.return_location_id = rl.location_id
    ORDER BY r.pickup_date DESC
");

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="container-fluid">
    <h2>Zarządzanie rezerwacjami</h2>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pojazd</th>
                    <th>Klient</th>
                    <th>Termin</th>
                    <th>Lokalizacje</th>
                    <th>Koszt</th>
                    <th>Status</th>
                    <th>Akcje</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($res = $reservations->fetch_assoc()): ?>
                <tr>
                    <td><?= $res['reservation_id'] ?></td>
                    <td>
                        <?= htmlspecialchars($res['make'] . ' ' . $res['model']) ?>
                        <small class="text-muted d-block"><?= $res['type'] == 'car' ? 'Samochód' : 'Samolot' ?></small>
                    </td>
                    <td>
                        <?= htmlspecialchars($res['first_name'] . ' ' . $res['last_name']) ?>
                        <small class="text-muted d-block"><?= htmlspecialchars($res['email']) ?></small>
                    </td>
                    <td>
                        <?= date('d.m.Y H:i', strtotime($res['pickup_date'])) ?><br>
                        do<br>
                        <?= date('d.m.Y H:i', strtotime($res['return_date'])) ?>
                    </td>
                    <td>
                        <strong>Odbiór:</strong> <?= htmlspecialchars($res['pickup_city']) ?><br>
                        <strong>Zwrot:</strong> <?= htmlspecialchars($res['return_city']) ?>
                    </td>
                    <td><?= number_format($res['total_cost'], 2) ?> PLN</td>
                    <td>
                        <span class="badge bg-<?= 
                            $res['status'] == 'confirmed' ? 'success' : 
                            ($res['status'] == 'pending' ? 'warning' : 'danger') 
                        ?>">
                            <?= 
                                $res['status'] == 'confirmed' ? 'Potwierdzona' : 
                                ($res['status'] == 'pending' ? 'Oczekująca' : 'Anulowana') 
                            ?>
                        </span>
                    </td>
                    <td>
                        <div class="btn-group">
                            <?php if ($res['status'] == 'pending'): ?>
                                <a href="?action=confirmed&id=<?= $res['reservation_id'] ?>" 
                                   class="btn btn-sm btn-success" title="Zatwierdź">
                                    <i class="fas fa-check"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($res['status'] != 'cancelled'): ?>
                                <a href="?action=cancelled&id=<?= $res['reservation_id'] ?>" 
                                   class="btn btn-sm btn-danger" title="Anuluj">
                                    <i class="fas fa-times"></i>
                                </a>
                            <?php endif; ?>
                            
                            <a href="../reservation_details.php?id=<?= $res['reservation_id'] ?>" 
                               class="btn btn-sm btn-primary" title="Szczegóły">
                                <i class="fas fa-info-circle"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?>