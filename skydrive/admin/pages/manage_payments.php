<?php
require_once __DIR__ . '/../../admin/admin_functions.php';
checkAdminAuth();

require_once __DIR__ . '/../../includes/config.php';

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

require_once __DIR__ . '/../../admin/includes/admin_header.php';
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
<style>
/* Główny kontener */
.container-fluid {
    padding: 2rem;
    background: #f8fafc;
}

/* Nagłówek */
.container-fluid h2 {
    color: #1e293b;
    font-weight: 700;
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid #e2e8f0;
    font-size: 1.8rem;
}

/* Karty */
.card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    margin-bottom: 1.5rem;
    overflow: hidden;
}

.card-header {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    color: white;
    padding: 1.25rem 1.5rem;
    border-bottom: none;
}

.card-header h4 {
    margin: 0;
    font-weight: 600;
    font-size: 1.25rem;
}

.card-body {
    padding: 1.5rem;
}

/* Karty statystyk */
.stat-card {
    padding: 1.5rem;
    border-radius: 10px;
    text-align: center;
    height: 100%;
    box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-card h5 {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.stat-card p {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 0;
}

.bg-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
}

.bg-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
}

.bg-danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
}

.bg-primary {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important;
}

/* Formularz filtrów */
.form-label {
    font-weight: 600;
    color: #475569;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.form-control, .form-select {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #8b5cf6;
    box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2);
}

/* Przyciski */
.btn {
    border-radius: 8px;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.btn-primary {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
}

.btn-primary:hover {
    background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
}

.btn-secondary {
    background: linear-gradient(135deg, #64748b 0%, #475569 100%);
    margin-left: 0.75rem;
}

.btn-secondary:hover {
    background: linear-gradient(135deg, #475569 0%, #334155 100%);
}

/* Tabela */
.table-responsive {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}

.table {
    margin-bottom: 0;
}

.table thead th {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    color: white;
    font-weight: 600;
    padding: 1rem 1.25rem;
    border: none;
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 0.5px;
}

.table tbody tr {
    transition: all 0.2s ease;
}

.table tbody tr:hover {
    background-color: #f8fafc;
}

.table td {
    padding: 1.25rem;
    vertical-align: middle;
    border-top: 1px solid #f1f5f9;
    color: #334155;
}

/* Statusy */
.badge {
    padding: 0.5rem 0.75rem;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.bg-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.bg-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white !important;
}

.bg-danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

/* Przyciski akcji */
.btn-sm {
    padding: 0.5rem;
    width: 32px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.btn-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
    margin-left: 0.5rem;
}

.btn-warning:hover {
    background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
}

/* Alert */
.alert-info {
    background-color: #e0f2fe;
    color: #0369a1;
    border-left: 4px solid #0ea5e9;
    border-radius: 8px;
}

/* Responsywność */
@media (max-width: 768px) {
    .container-fluid {
        padding: 1rem;
    }
    
    .row > div {
        margin-bottom: 1rem;
    }
    
    .stat-card {
        padding: 1rem;
    }
    
    .stat-card p {
        font-size: 1.25rem;
    }
    
    .table-responsive {
        border-radius: 10px;
    }
    
    .table thead {
        display: none;
    }
    
    .table, .table tbody, .table tr, .table td {
        display: block;
        width: 100%;
    }
    
    .table tr {
        margin-bottom: 1rem;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .table td {
        padding: 0.75rem 1rem;
        text-align: right;
        position: relative;
        padding-left: 50%;
    }
    
    .table td::before {
        content: attr(data-label);
        position: absolute;
        left: 1rem;
        width: calc(50% - 1rem);
        padding-right: 1rem;
        text-align: left;
        font-weight: 600;
        color: #8b5cf6;
    }
    
    .table td[data-label="Akcje"] {
        text-align: center;
        padding-left: 1rem;
    }
    
    .table td[data-label="Akcje"]::before {
        display: none;
    }
    
    .btn-group {
        justify-content: center;
    }
}
</style>

<?php require_once __DIR__ . '/../../admin/includes/admin_footer.php'; ?>