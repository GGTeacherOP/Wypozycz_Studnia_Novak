<?php
require_once __DIR__ . '/../../admin/admin_functions.php';
checkAdminAuth();

require_once __DIR__ . '/../../includes/config.php';

$payment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Pobierz szczegóły płatności
$query = "SELECT p.*, 
          r.reservation_id, r.pickup_date, r.return_date, r.total_cost, r.status as reservation_status,
          CONCAT(u.first_name, ' ', u.last_name) AS customer_name,
          u.email, u.phone,
          v.make, v.model, v.type,
          pl.city as pickup_city, pl.address as pickup_address,
          rl.city as return_city, rl.address as return_address
          FROM payments p
          JOIN reservations r ON p.reservation_id = r.reservation_id
          JOIN users u ON r.user_id = u.user_id
          JOIN vehicles v ON r.vehicle_id = v.vehicle_id
          JOIN locations pl ON r.pickup_location_id = pl.location_id
          JOIN locations rl ON r.return_location_id = rl.location_id
          WHERE p.payment_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $payment_id);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();

if(!$payment) {
    header("Location: manage_payments.php");
    exit();
}

// Pobierz wyposażenie rezerwacji
$equipment_query = "SELECT e.name, e.description, e.daily_cost, re.quantity
                   FROM reservationequipment re
                   JOIN equipment e ON re.equipment_id = e.equipment_id
                   WHERE re.reservation_id = ?";
$stmt = $conn->prepare($equipment_query);
$stmt->bind_param("i", $payment['reservation_id']);
$stmt->execute();
$equipment = $stmt->get_result();

require_once __DIR__ . '/../../admin/includes/admin_header.php';
?>

<div class="container-fluid">
    <h2>Szczegóły płatności #<?= $payment_id ?></h2>
    
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
                        <dd class="col-sm-8"><?= date('d.m.Y H:i', strtotime($payment['payment_date'])) ?></dd>
                        
                        <dt class="col-sm-4">Metoda płatności:</dt>
                        <dd class="col-sm-8"><?= 
                            $payment['payment_method'] == 'credit_card' ? 'Karta kredytowa' : 
                            ($payment['payment_method'] == 'bank_transfer' ? 'Przelew bankowy' : 'Gotówka') 
                        ?></dd>
                        
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
                            <?= date('d.m.Y H:i', strtotime($payment['pickup_date'])) ?><br>
                            <?= htmlspecialchars($payment['pickup_city']) ?>, <?= htmlspecialchars($payment['pickup_address']) ?>
                        </dd>
                        
                        <dt class="col-sm-4">Zwrot:</dt>
                        <dd class="col-sm-8">
                            <?= date('d.m.Y H:i', strtotime($payment['return_date'])) ?><br>
                            <?= htmlspecialchars($payment['return_city']) ?>, <?= htmlspecialchars($payment['return_address']) ?>
                        </dd>
                        
                        <dt class="col-sm-4">Status rezerwacji:</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-<?= 
                                $payment['reservation_status'] == 'confirmed' ? 'success' : 
                                ($payment['reservation_status'] == 'pending' ? 'warning' : 'danger') 
                            ?>">
                                <?= $payment['reservation_status'] == 'pending' ? 'Oczekująca' : 
                                   ($payment['reservation_status'] == 'confirmed' ? 'Potwierdzona' : 'Anulowana') ?>
                            </span>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
    
    <?php if($equipment->num_rows > 0): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h4>Wyposażenie</h4>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nazwa</th>
                            <th>Opis</th>
                            <th>Ilość</th>
                            <th>Cena/dzień</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($eq = $equipment->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($eq['name']) ?></td>
                                <td><?= htmlspecialchars($eq['description']) ?></td>
                                <td><?= $eq['quantity'] ?></td>
                                <td><?= number_format($eq['daily_cost'], 2) ?> PLN</td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h4>Dodatkowe informacje</h4>
        </div>
        <div class="card-body">
            <pre><?= htmlspecialchars($payment['payment_details'] ?? 'Brak dodatkowych informacji') ?></pre>
        </div>
    </div>
    
    <div class="mt-3">
        <a href="manage_payments.php">Powrót</a>
        <a href="edit_payment.php?id=<?= $payment_id ?>">Edytuj</a>
        <button class="btn btn-success" onclick="window.print()">Drukuj</button>
    </div>
</div>

<?php require_once __DIR__ . '/../../admin/includes/admin_footer.php'; ?>