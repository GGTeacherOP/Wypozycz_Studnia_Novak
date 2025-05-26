<?php
require_once __DIR__ . '/../admin/admin_functions.php';
checkAdminAuth();

require_once __DIR__ . '/../includes/config.php';

// Filtry
$status = isset($_GET['status']) ? $_GET['status'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';

// Budowanie zapytania
$query = "SELECT r.*, v.make, v.model, v.type, 
          CONCAT(u.first_name, ' ', u.last_name) as customer_name,
          pl.city as pickup_city, rl.city as return_city
          FROM reservations r
          JOIN vehicles v ON r.vehicle_id = v.vehicle_id
          JOIN users u ON r.user_id = u.user_id
          JOIN locations pl ON r.pickup_location_id = pl.location_id
          JOIN locations rl ON r.return_location_id = rl.location_id
          WHERE 1=1";

if($status) {
    $query .= " AND r.status = '$status'";
}
if($type) {
    $query .= " AND v.type = '$type'";
}

$query .= " ORDER BY r.pickup_date DESC";
$reservations = $conn->query($query);

require_once __DIR__ . '/../admin/includes/admin_header.php';
?>

<div class="container-fluid">
    <h2>Zarządzanie rezerwacjami</h2>
    
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Wszystkie</option>
                        <option value="pending" <?= $status == 'pending' ? 'selected' : '' ?>>Oczekujące</option>
                        <option value="confirmed" <?= $status == 'confirmed' ? 'selected' : '' ?>>Potwierdzone</option>
                        <option value="cancelled" <?= $status == 'cancelled' ? 'selected' : '' ?>>Anulowane</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Typ pojazdu</label>
                    <select name="type" class="form-select">
                        <option value="">Wszystkie</option>
                        <option value="car" <?= $type == 'car' ? 'selected' : '' ?>>Samochody</option>
                        <option value="plane" <?= $type == 'plane' ? 'selected' : '' ?>>Samoloty</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Filtruj</button>
                    <a href="manage_reservations.php" class="btn btn-secondary ms-2">Wyczyść</a>
                </div>
            </form>
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Klient</th>
                    <th>Pojazd</th>
                    <th>Termin</th>
                    <th>Lokalizacje</th>
                    <th>Koszt</th>
                    <th>Status</th>
                    <th>Akcje</th>
                </tr>
            </thead>
            <tbody>
                <?php while($res = $reservations->fetch_assoc()): ?>
                <tr>
                    <td><?= $res['reservation_id'] ?></td>
                    <td><?= htmlspecialchars($res['customer_name']) ?></td>
                    <td><?= htmlspecialchars($res['make'] . ' ' . $res['model']) ?></td>
                    <td>
                        <?= date('d.m.Y', strtotime($res['pickup_date'])) ?><br>
                        <?= date('d.m.Y', strtotime($res['return_date'])) ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($res['pickup_city']) ?><br>
                        <?= htmlspecialchars($res['return_city']) ?>
                    </td>
                    <td><?= number_format($res['total_cost'], 2) ?> PLN</td>
                    <td>
                        <span class="badge bg-<?= 
                            $res['status'] == 'confirmed' ? 'success' : 
                            ($res['status'] == 'pending' ? 'warning' : 'danger') 
                        ?>">
                            <?= $res['status'] == 'pending' ? 'Oczekująca' : 
                               ($res['status'] == 'confirmed' ? 'Potwierdzona' : 'Anulowana') ?>
                        </span>
                    </td>
                    <td>
                        <a href="reservation_details.php?id=<?= $res['reservation_id'] ?>" 
                           class="btn btn-sm btn-primary" title="Szczegóły">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="edit_reservation.php?id=<?= $res['reservation_id'] ?>" 
                           class="btn btn-sm btn-warning" title="Edytuj">
                            <i class="fas fa-edit"></i>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../admin/includes/admin_footer.php'; ?>