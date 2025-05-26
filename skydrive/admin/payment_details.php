<?php
require_once __DIR__ . '/../admin_functions.php';
checkAdminAuth();

require_once __DIR__ . '/../includes/config.php';

if (!isset($_GET['id'])) {
    header("Location: payments.php");
    exit();
}

$payment_id = intval($_GET['id']);

// Pobierz szczegóły płatności
$stmt = $conn->prepare("
    SELECT p.*, r.reservation_id, r.pickup_date, r.return_date, r.total_cost,
           CONCAT(u.first_name, ' ', u.last_name) AS customer_name,
           u.email, u.phone,
           v.make, v.model, v.type,
           l.city AS pickup_city
    FROM payments p
    JOIN reservations r ON p.reservation_id = r.reservation_id
    JOIN users u ON r.user_id = u.user_id
    JOIN vehicles v ON r.vehicle_id = v.vehicle_id
    JOIN locations l ON r.pickup_location_id = l.location_id
    WHERE p.payment_id = ?
");
$stmt->bind_param("i", $payment_id);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();

if (!$payment) {
    header("Location: payments.php");
    exit();
}

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="container-fluid">
    <h2>Szczegóły płatności #<?= $payment['payment_id'] ?></h2>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Informacje o płatności</h4>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">ID Płatności:</dt>
                        <dd class="col-sm-8"><?= $payment['payment_id'] ?></dd>
                        
                        <dt class="col-sm-4">Rezerwacja:</dt>
                        <dd class="col-sm-8">#<?= $payment['reservation_id'] ?></dd>
                        
                        <dt class="col-sm-4">Kwota:</dt>
                        <dd class="col-sm-8"><?= number_format($payment['amount'], 2) ?> PLN</dd>
                        
                        <dt class="col-sm-4">Data płatności:</dt>
                        <dd class="col-sm-8"><?= date('Y-m-d H:i', strtotime($payment['payment_date'])) ?></dd>
                        
                        <dt class="col-sm-4">Metoda płatności:</dt>
                        <dd class="col-sm-8"><?= ucfirst(str_replace('_', ' ', $payment['payment_method'])) ?></dd>
                        
                        <dt class="col-sm-4">Status:</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-<?= 
                                $payment['status'] == 'completed' ? 'success' : 
                                ($payment['status'] == 'pending' ? 'warning' : 'danger') 
                            ?>">
                                <?= ucfirst($payment['status']) ?>
                            </span>
                        </dd>
                        
                        <dt class="col-sm-4">ID Transakcji:</dt>
                        <dd class="col-sm-8"><?= $payment['transaction_id'] ?? 'Brak' ?></dd>
                        
                        <dt class="col-sm-4">Nr faktury:</dt>
                        <dd class="col-sm-8"><?= $payment['invoice_number'] ?? 'Brak' ?></dd>
                    </dl>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Informacje o rezerwacji</h4>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Klient:</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars($payment['customer_name']) ?></dd>
                        
                        <dt class="col-sm-4">Email:</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars($payment['email']) ?></dd>
                        
                        <dt class="col-sm-4">Telefon:</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars($payment['phone']) ?></dd>
                        
                        <dt class="col-sm-4">Pojazd:</dt>
                        <dd class="col-sm-8">
                            <?= htmlspecialchars($payment['make'] . ' ' . $payment['model']) ?>
                            (<?= $payment['type'] == 'car' ? 'Samochód' : 'Samolot' ?>)
                        </dd>
                        
                        <dt class="col-sm-4">Odbiór:</dt>
                        <dd class="col-sm-8">
                            <?= date('Y-m-d H:i', strtotime($payment['pickup_date'])) ?><br>
                            <?= htmlspecialchars($payment['pickup_city']) ?>
                        </dd>
                        
                        <dt class="col-sm-4">Zwrot:</dt>
                        <dd class="col-sm-8"><?= date('Y-m-d H:i', strtotime($payment['return_date'])) ?></dd>
                        
                        <dt class="col-sm-4">Kwota całkowita:</dt>
                        <dd class="col-sm-8"><?= number_format($payment['total_cost'], 2) ?> PLN</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h4>Dodatkowe informacje</h4>
        </div>
        <div class="card-body">
            <pre><?= htmlspecialchars($payment['payment_details'] ?? 'Brak dodatkowych informacji') ?></pre>
        </div>
    </div>
    
    <div class="mt-3">
        <a href="payments.php" class="btn btn-secondary">Powrót</a>
        <a href="edit_payment.php?id=<?= $payment['payment_id'] ?>" class="btn btn-primary">Edytuj</a>
        <button class="btn btn-success" onclick="window.print()">Drukuj</button>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>