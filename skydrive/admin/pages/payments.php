<?php
require_once __DIR__ . '/../admin_functions.php';
checkAdminAuth();

require_once __DIR__ . '/../../includes/config.php';

// Pobierz wszystkie płatności z informacjami o rezerwacjach
$query = "
    SELECT p.*, r.reservation_id, r.pickup_date, r.return_date, 
           CONCAT(u.first_name, ' ', u.last_name) AS customer_name,
           v.make, v.model
    FROM payments p
    JOIN reservations r ON p.reservation_id = r.reservation_id
    JOIN users u ON r.user_id = u.user_id
    JOIN vehicles v ON r.vehicle_id = v.vehicle_id
    ORDER BY p.payment_date DESC
";
$payments = $conn->query($query);

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="container-fluid">
    <h2>Zarządzanie płatnościami</h2>
    
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Rezerwacja</th>
                    <th>Klient</th>
                    <th>Pojazd</th>
                    <th>Kwota</th>
                    <th>Data płatności</th>
                    <th>Metoda</th>
                    <th>Status</th>
                    <th>Faktura</th>
                    <th>Akcje</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($payment = $payments->fetch_assoc()): ?>
                <tr>
                    <td><?= $payment['payment_id'] ?></td>
                    <td>#<?= $payment['reservation_id'] ?></td>
                    <td><?= htmlspecialchars($payment['customer_name']) ?></td>
                    <td><?= htmlspecialchars($payment['make'] . ' ' . $payment['model']) ?></td>
                    <td><?= number_format($payment['amount'], 2) ?> PLN</td>
                    <td><?= date('Y-m-d H:i', strtotime($payment['payment_date'])) ?></td>
                    <td><?= ucfirst(str_replace('_', ' ', $payment['payment_method'])) ?></td>
                    <td>
                        <span class="badge bg-<?= 
                            $payment['status'] == 'completed' ? 'success' : 
                            ($payment['status'] == 'pending' ? 'warning' : 'danger') 
                        ?>">
                            <?= ucfirst($payment['status']) ?>
                        </span>
                    </td>
                    <td><?= $payment['invoice_number'] ?? 'Brak' ?></td>
                    <td>
                        <a href="payment_details.php?id=<?= $payment['payment_id'] ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="edit_payment.php?id=<?= $payment['payment_id'] ?>" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>