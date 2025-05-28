<?php
require_once __DIR__ . '/../admin/admin_functions.php';
checkAdminAuth();

require_once __DIR__ . '/../includes/config.php';

// Filtry
$status = isset($_GET['status']) ? $_GET['status'] : '';
$method = isset($_GET['method']) ? $_GET['method'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Budowanie zapytania
$query = "SELECT p.*, 
          r.reservation_id, r.pickup_date, r.return_date, r.total_cost,
          CONCAT(u.first_name, ' ', u.last_name) AS customer_name,
          v.make, v.model
          FROM payments p
          JOIN reservations r ON p.reservation_id = r.reservation_id
          JOIN users u ON r.user_id = u.user_id
          JOIN vehicles v ON r.vehicle_id = v.vehicle_id
          WHERE 1=1";

if($status) {
    $query .= " AND p.status = '$status'";
}
if($method) {
    $query .= " AND p.payment_method = '$method'";
}
if($date_from) {
    $query .= " AND p.payment_date >= '$date_from'";
}
if($date_to) {
    $query .= " AND p.payment_date <= '$date_to 23:59:59'";
}

$query .= " ORDER BY p.payment_date DESC";
$payments = $conn->query($query);

// Statystyki
$stats_query = "SELECT 
                SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as completed_sum,
                SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending_sum,
                SUM(CASE WHEN status = 'failed' THEN amount ELSE 0 END) as failed_sum,
                COUNT(*) as total_count
                FROM payments";
$stats = $conn->query($stats_query)->fetch_assoc();

require_once __DIR__ . '/../admin/includes/admin_header.php';
?>

<div class="container-fluid">
    <h2>Zarządzanie płatnościami</h2>
    
    <div class="card mb-4">
        <div class="card-header">
            <h4>Statystyki płatności</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-card bg-success text-white">
                        <h5>Zakończone</h5>
                        <p><?= number_format($stats['completed_sum'], 2) ?> PLN</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card bg-warning text-dark">
                        <h5>Oczekujące</h5>
                        <p><?= number_format($stats['pending_sum'], 2) ?> PLN</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card bg-danger text-white">
                        <h5>Nieudane</h5>
                        <p><?= number_format($stats['failed_sum'], 2) ?> PLN</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card bg-primary text-white">
                        <h5>Łącznie</h5>
                        <p><?= $stats['total_count'] ?> transakcji</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <h4>Filtry</h4>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Wszystkie</option>
                        <option value="completed" <?= $status == 'completed' ? 'selected' : '' ?>>Zakończone</option>
                        <option value="pending" <?= $status == 'pending' ? 'selected' : '' ?>>Oczekujące</option>
                        <option value="failed" <?= $status == 'failed' ? 'selected' : '' ?>>Nieudane</option>
                        <option value="refunded" <?= $status == 'refunded' ? 'selected' : '' ?>>Zwrócone</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Metoda płatności</label>
                    <select name="method" class="form-select">
                        <option value="">Wszystkie</option>
                        <option value="credit_card" <?= $method == 'credit_card' ? 'selected' : '' ?>>Karta kredytowa</option>
                        <option value="bank_transfer" <?= $method == 'bank_transfer' ? 'selected' : '' ?>>Przelew bankowy</option>
                        <option value="cash" <?= $method == 'cash' ? 'selected' : '' ?>>Gotówka</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Data od</label>
                    <input type="date" name="date_from" class="form-control" value="<?= $date_from ?>">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Data do</label>
                    <input type="date" name="date_to" class="form-control" value="<?= $date_to ?>">
                </div>
                
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Filtruj</button>
                    <a href="manage_payments.php" class="btn btn-secondary">Wyczyść</a>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Rezerwacja</th>
                            <th>Klient</th>
                            <th>Pojazd</th>
                            <th>Kwota</th>
                            <th>Data</th>
                            <th>Metoda</th>
                            <th>Status</th>
                            <th>Faktura</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($payment = $payments->fetch_assoc()): ?>
                        <tr>
                            <td><?= $payment['payment_id'] ?></td>
                            <td>#<?= $payment['reservation_id'] ?></td>
                            <td><?= htmlspecialchars($payment['customer_name']) ?></td>
                            <td><?= htmlspecialchars($payment['make'] . ' ' . $payment['model']) ?></td>
                            <td><?= number_format($payment['amount'], 2) ?> PLN</td>
                            <td><?= date('d.m.Y H:i', strtotime($payment['payment_date'])) ?></td>
                            <td><?= 
                                $payment['payment_method'] == 'credit_card' ? 'Karta' : 
                                ($payment['payment_method'] == 'bank_transfer' ? 'Przelew' : 'Gotówka') 
                            ?></td>
                            <td>
                                <span class="badge bg-<?= 
                                    $payment['status'] == 'completed' ? 'success' : 
                                    ($payment['status'] == 'pending' ? 'warning' : 'danger') 
                                ?>">
                                    <?= ucfirst($payment['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?= $payment['invoice_number'] ? $payment['invoice_number'] : 'Brak' ?>
                            </td>
                            <td>
                                <a href="payment_details.php?id=<?= $payment['payment_id'] ?>" 
                                   class="btn btn-sm btn-primary" title="Szczegóły">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="edit_payment.php?id=<?= $payment['payment_id'] ?>" 
                                   class="btn btn-sm btn-warning" title="Edytuj">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if($payments->num_rows == 0): ?>
                <div class="alert alert-info mt-3">Brak płatności spełniających kryteria wyszukiwania</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .stat-card {
        padding: 15px;
        border-radius: 5px;
        text-align: center;
        margin-bottom: 15px;
    }
    .stat-card h5 {
        font-size: 16px;
        margin-bottom: 5px;
    }
    .stat-card p {
        font-size: 20px;
        font-weight: bold;
        margin-bottom: 0;
    }
</style>

<?php require_once __DIR__ . '/../admin/includes/admin_footer.php'; ?>