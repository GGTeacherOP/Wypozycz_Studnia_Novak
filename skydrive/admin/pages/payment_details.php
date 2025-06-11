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
        <a class="btn btn-success" href="manage_payments.php">Powrót</a>
        <a class="btn btn-success" href="edit_payment.php?id=<?= $payment_id ?>">Edytuj</a>
        <button class="btn btn-success" onclick="window.print()">Drukuj</button>
    </div>
</div>
<style>
    :root {
        --primary-color: #1976d2;
        --success-color: #388e3c;
        --warning-color: #f57c00;
        --danger-color: #d32f2f;
        --text-color: #333;
        --light-gray: #f5f5f5;
        --border-color: #e0e0e0;
    }

    body {
        font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        color: var(--text-color);
        background-color: #f9f9f9;
    }

    .container-fluid {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    h2 {
        color: var(--primary-color);
        font-weight: 600;
        margin-bottom: 2rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid var(--border-color);
    }

    /* Karty */
    .card {
        border: none;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transition: transform 0.2s, box-shadow 0.2s;
        margin-bottom: 1.5rem;
        overflow: hidden;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
    }

    .card-header {
        background-color: var(--primary-color);
        color: white;
        padding: 1rem 1.5rem;
        border-bottom: none;
    }

    .card-header h4 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 500;
    }

    .card-body {
        padding: 1.5rem;
    }

    /* Listy opisowe */
    dl.row dt {
        font-weight: 500;
        color: #555;
    }

    dl.row dd {
        margin-bottom: 1rem;
    }

    /* Statusy (badge) */
    .badge {
        font-size: 0.8rem;
        font-weight: 500;
        padding: 0.35em 0.65em;
        border-radius: 4px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .bg-success { background-color: var(--success-color) !important; }
    .bg-warning { background-color: var(--warning-color) !important; }
    .bg-danger { background-color: var(--danger-color) !important; }

    /* Tabela wyposażenia */
    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table th {
        background-color: var(--light-gray);
        padding: 0.75rem 1rem;
        text-align: left;
        font-weight: 500;
        border-bottom: 2px solid var(--border-color);
    }

    .table td {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid var(--border-color);
    }

    .table tr:last-child td {
        border-bottom: none;
    }

    .table tr:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }

    /* Dodatkowe informacje */
    pre {
        white-space: pre-wrap;
        background-color: var(--light-gray);
        padding: 1rem;
        border-radius: 6px;
        border-left: 4px solid var(--primary-color);
        font-family: 'Courier New', monospace;
        font-size: 0.9rem;
    }

    /* Przyciski i linki */
    .action-buttons {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid var(--border-color);
    }

    .btn {
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-outline {
        border: 1px solid var(--primary-color);
        color: var(--primary-color);
        background: transparent;
    }

    .btn-outline:hover {
        background-color: rgba(25, 118, 210, 0.08);
    }

    .btn-primary {
        background-color: var(--primary-color);
        color: white;
        border: none;
    }

    .btn-primary:hover {
        background-color: #1565c0;
        box-shadow: 0 2px 8px rgba(25, 118, 210, 0.3);
    }

    .btn-print {
        background-color: #757575;
        color: white;
        border: none;
    }

    .btn-print:hover {
        background-color: #616161;
    }

    /* Responsywność */
    @media (max-width: 768px) {
        .card-body {
            padding: 1rem;
        }
        
        dl.row dt, 
        dl.row dd {
            width: 100%;
            display: block;
        }
        
        dl.row dt {
            margin-top: 0.5rem;
        }
    }

    /* Druk */
    @media print {
        body {
            padding: 0;
            font-size: 12pt;
            background: white;
        }
        
        .container-fluid {
            padding: 0;
        }
        
        .card {
            box-shadow: none;
            border: 1px solid #ddd;
            page-break-inside: avoid;
        }
        
        .action-buttons {
            display: none;
        }
    }
</style>

