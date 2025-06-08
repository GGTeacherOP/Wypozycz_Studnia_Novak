<?php
require_once __DIR__ . '/../../admin/admin_functions.php';
checkAdminAuth();

require_once __DIR__ . '/../../includes/config.php';

// Komunikaty
if(isset($_SESSION['error'])) {
    $error_message = $_SESSION['error'];
    unset($_SESSION['error']);
}

if(isset($_SESSION['success'])) {
    $success_message = $_SESSION['success'];
    unset($_SESSION['success']);
}

// Filtry
$status = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : '';
$type = isset($_GET['type']) ? $conn->real_escape_string($_GET['type']) : '';

// Zapytanie SQL z prepared statements
$query = "SELECT r.*, v.make, v.model, v.type, 
          CONCAT(u.first_name, ' ', u.last_name) as customer_name,
          pl.city as pickup_city, rl.city as return_city
          FROM reservations r
          JOIN vehicles v ON r.vehicle_id = v.vehicle_id
          JOIN users u ON r.user_id = u.user_id
          JOIN locations pl ON r.pickup_location_id = pl.location_id
          JOIN locations rl ON r.return_location_id = rl.location_id
          WHERE 1=1";

$params = [];
$types = '';

if ($status) {
    $query .= " AND r.status = ?";
    $params[] = $status;
    $types .= 's';
}

if ($type) {
    $query .= " AND v.type = ?";
    $params[] = $type;
    $types .= 's';
}

$query .= " ORDER BY r.pickup_date DESC";

$stmt = $conn->prepare($query);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$reservations = $stmt->get_result();

require_once __DIR__ . '/../../admin/includes/admin_header.php';
?>

<style>
    .container-fluid h2 {
        margin-top: 20px;
        font-weight: 600;
        color: #1f2937;
    }

    .card {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }

    .form-label {
        font-weight: 500;
        color: #374151;
    }

    .form-select, .btn {
        min-width: 100px;
    }

    .table thead th {
        background-color: #f9fafb;
        color: #1f2937;
        font-weight: 600;
        border-bottom: 2px solid #e5e7eb;
    }

    .table-striped tbody tr:nth-child(odd) {
        background-color: #f3f4f6;
    }

    .table td, .table th {
        vertical-align: middle;
    }

    .badge {
        font-size: 14px;
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 500;
        text-transform: uppercase;
    }

    .badge.bg-success {
        background-color: #34d399;
    }

    .badge.bg-warning {
        background-color: #facc15;
        color: #78350f;
    }

    .badge.bg-danger {
        background-color: #f87171;
    }

    .table .btn-sm {
        margin-right: 5px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
    }

    .table .btn-sm i {
        font-size: 14px;
    }

    .alert {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .alert-danger {
        background-color: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fca5a5;
    }

    .alert-success {
        background-color: #dcfce7;
        color: #166534;
        border: 1px solid #86efac;
    }
</style>

<div class="container-fluid">
    <h2>Zarządzanie rezerwacjami</h2>

    <?php if(isset($error_message)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <?php if(isset($success_message)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>

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
                        <option value="completed" <?= $status == 'completed' ? 'selected' : '' ?>>Zakończone</option>
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
        <table class="table table-striped table-hover align-middle">
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
                <?php while ($res = $reservations->fetch_assoc()): ?>
                <tr>
                    <td><?= $res['reservation_id'] ?></td>
                    <td><?= htmlspecialchars($res['customer_name']) ?></td>
                    <td><?= htmlspecialchars($res['make'] . ' ' . $res['model']) ?></td>
                    <td>
                        <?= date('d.m.Y H:i', strtotime($res['pickup_date'])) ?><br>
                        <?= date('d.m.Y H:i', strtotime($res['return_date'])) ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($res['pickup_city']) ?><br>
                        <?= htmlspecialchars($res['return_city']) ?>
                    </td>
                    <td><?= number_format($res['total_cost'], 2) ?> PLN</td>
                    <td>
                        <span class="badge bg-<?= 
                            $res['status'] == 'confirmed' ? 'success' :
                            ($res['status'] == 'pending' ? 'warning' : 
                            ($res['status'] == 'completed' ? 'primary' : 'danger')) ?>">
                            <?= $res['status'] == 'pending' ? 'Oczekująca' :
                               ($res['status'] == 'confirmed' ? 'Potwierdzona' : 
                               ($res['status'] == 'completed' ? 'Zakończona' : 'Anulowana')) ?>
                        </span>
                    </td>
                    <td>
                        <a href="reservation_details.php?id=<?= $res['reservation_id'] ?>" 
                           class="btn btn-sm btn-primary" title="Szczegóły">
                            <i class="fas fa-eye"></i> Podgląd
                        </a>
                        <a href="edit_reservation.php?reservation_id=<?= $res['reservation_id'] ?>" 
                           class="btn btn-sm btn-warning" title="Edytuj">
                            <i class="fas fa-edit"></i> Edytuj
                        </a>
                        <?php if($res['status'] == 'pending'): ?>
                            <a href="update_reservation_status.php?reservation_id=<?= $res['reservation_id'] ?>&status=confirmed" 
                               class="btn btn-sm btn-success" title="Potwierdź">
                                <i class="fas fa-check"></i> Potwierdź
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../admin/includes/admin_footer.php'; ?>