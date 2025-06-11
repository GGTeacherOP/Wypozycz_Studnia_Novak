<?php
require_once __DIR__ . '/../../admin/admin_functions.php';
checkAdminAuth();

require_once __DIR__ . '/../../includes/config.php';

$reservation_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Pobierz szczegóły rezerwacji
$query = "SELECT r.*, v.make, v.model, v.type, v.image_path,
          pl.city as pickup_city, pl.address as pickup_address,
          rl.city as return_city, rl.address as return_address,
          p.status as payment_status, p.payment_method, p.payment_date,
          p.amount as payment_amount, p.invoice_number, p.payment_details,
          CONCAT(u.first_name, ' ', u.last_name) as customer_name,
          u.email, u.phone, u.driver_license_number, u.pilot_license_number
          FROM reservations r
          JOIN vehicles v ON r.vehicle_id = v.vehicle_id
          JOIN locations pl ON r.pickup_location_id = pl.location_id
          JOIN locations rl ON r.return_location_id = rl.location_id
          JOIN users u ON r.user_id = u.user_id
          LEFT JOIN payments p ON r.reservation_id = p.reservation_id
          WHERE r.reservation_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $reservation_id);
$stmt->execute();
$reservation = $stmt->get_result()->fetch_assoc();

if(!$reservation) {
    header("Location: manage_reservations.php");
    exit();
}

// Pobierz wyposażenie
$equipment_query = "SELECT e.name, e.description, e.daily_cost, re.quantity
                   FROM reservationequipment re
                   JOIN equipment e ON re.equipment_id = e.equipment_id
                   WHERE re.reservation_id = ?";
$stmt = $conn->prepare($equipment_query);
$stmt->bind_param("i", $reservation_id);
$stmt->execute();
$equipment = $stmt->get_result();

// Zmiana statusu
if(isset($_POST['change_status'])) {
    $new_status = $_POST['status'];
    $notes = $_POST['notes'];
    
    $update_query = "UPDATE reservations SET status = ?, notes = ? WHERE reservation_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ssi", $new_status, $notes, $reservation_id);
    
    if($stmt->execute()) {
        $_SESSION['success'] = "Status rezerwacji został zaktualizowany";
        header("Location: reservation_details.php?id=$reservation_id");
        exit();
    } else {
        $error = "Błąd podczas aktualizacji statusu: " . $conn->error;
    }
}

require_once __DIR__ . '/../../admin/includes/admin_header.php';
?>

<div class="container-fluid">
    <h2>Szczegóły rezerwacji #<?= $reservation_id ?></h2>
    
    <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h3>Informacje o rezerwacji</h3>
                </div>
                <div class="card-body">
                    <?php if($reservation['image_path']): ?>
                        <img src="<?= htmlspecialchars($reservation['image_path']) ?>" 
                             class="img-fluid mb-3" style="max-height: 200px;">
                    <?php endif; ?>
                    
                    <dl class="row">
                        <dt class="col-sm-4">Pojazd:</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars($reservation['make'] . ' ' . $reservation['model']) ?></dd>
                        
                        <dt class="col-sm-4">Typ:</dt>
                        <dd class="col-sm-8"><?= $reservation['type'] == 'car' ? 'Samochód' : 'Samolot' ?></dd>
                        
                        <dt class="col-sm-4">Data odbioru:</dt>
                        <dd class="col-sm-8"><?= date('d.m.Y H:i', strtotime($reservation['pickup_date'])) ?></dd>
                        
                        <dt class="col-sm-4">Data zwrotu:</dt>
                        <dd class="col-sm-8"><?= date('d.m.Y H:i', strtotime($reservation['return_date'])) ?></dd>
                        
                        <dt class="col-sm-4">Miejsce odbioru:</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars($reservation['pickup_city']) ?>, <?= htmlspecialchars($reservation['pickup_address']) ?></dd>
                        
                        <dt class="col-sm-4">Miejsce zwrotu:</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars($reservation['return_city']) ?>, <?= htmlspecialchars($reservation['return_address']) ?></dd>
                        
                        <dt class="col-sm-4">Koszt całkowity:</dt>
                        <dd class="col-sm-8"><?= number_format($reservation['total_cost'], 2) ?> PLN</dd>
                    </dl>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h3>Klient</h3>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Imię i nazwisko:</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars($reservation['customer_name']) ?></dd>
                        
                        <dt class="col-sm-4">Email:</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars($reservation['email']) ?></dd>
                        
                        <dt class="col-sm-4">Telefon:</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars($reservation['phone']) ?></dd>
                        
                        <?php if($reservation['type'] == 'car' && $reservation['driver_license_number']): ?>
                            <dt class="col-sm-4">Nr prawa jazdy:</dt>
                            <dd class="col-sm-8"><?= htmlspecialchars($reservation['driver_license_number']) ?></dd>
                        <?php endif; ?>
                        
                        <?php if($reservation['type'] == 'plane' && $reservation['pilot_license_number']): ?>
                            <dt class="col-sm-4">Nr licencji pilota:</dt>
                            <dd class="col-sm-8"><?= htmlspecialchars($reservation['pilot_license_number']) ?></dd>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h3>Płatność</h3>
                </div>
                <div class="card-body">
                    <?php if($reservation['payment_status']): ?>
                        <dl class="row">
                            <dt class="col-sm-4">Status:</dt>
                            <dd class="col-sm-8">
                                <span class="badge bg-<?= 
                                    $reservation['payment_status'] == 'completed' ? 'success' : 
                                    ($reservation['payment_status'] == 'pending' ? 'warning' : 'danger') 
                                ?>">
                                    <?= ucfirst($reservation['payment_status']) ?>
                                </span>
                            </dd>
                            
                            <dt class="col-sm-4">Metoda:</dt>
                            <dd class="col-sm-8"><?= 
                                $reservation['payment_method'] == 'credit_card' ? 'Karta kredytowa' : 
                                ($reservation['payment_method'] == 'bank_transfer' ? 'Przelew bankowy' : 'Gotówka') 
                            ?></dd>
                            
                            <dt class="col-sm-4">Data płatności:</dt>
                            <dd class="col-sm-8"><?= date('d.m.Y H:i', strtotime($reservation['payment_date'])) ?></dd>
                            
                            <dt class="col-sm-4">Kwota:</dt>
                            <dd class="col-sm-8"><?= number_format($reservation['payment_amount'], 2) ?> PLN</dd>
                            
                            <?php if($reservation['invoice_number']): ?>
                                <dt class="col-sm-4">Nr faktury:</dt>
                                <dd class="col-sm-8"><?= $reservation['invoice_number'] ?></dd>
                            <?php endif; ?>
                            
                            <?php if($reservation['payment_details']): ?>
                                <dt class="col-sm-4">Szczegóły:</dt>
                                <dd class="col-sm-8"><pre><?= htmlspecialchars($reservation['payment_details']) ?></pre></dd>
                            <?php endif; ?>
                        </dl>
                        
                        <a href="edit_payment.php?reservation_id=<?= $reservation_id ?>" class="btn btn-warning">
                            Edytuj płatność
                        </a>
                    <?php else: ?>
                        <p>Płatność nie została jeszcze dokonana.</p>
                        <a href="add_payment.php?reservation_id=<?= $reservation_id ?>" class="btn btn-primary">
                            Dodaj płatność
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if($equipment->num_rows > 0): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h3>Wyposażenie</h3>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nazwa</th>
                                    <th>Ilość</th>
                                    <th>Cena</th>
                                    <th>Łącznie</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($eq = $equipment->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($eq['name']) ?></td>
                                        <td><?= $eq['quantity'] ?></td>
                                        <td><?= number_format($eq['daily_cost'], 2) ?> PLN/dzień</td>
                                        <td><?= number_format($eq['daily_cost'] * $eq['quantity'] * 
                                            ($reservation['type'] == 'car' ? 
                                                ceil((strtotime($reservation['return_date']) - strtotime($reservation['pickup_date'])) / (60*60*24)) : 
                                                ceil((strtotime($reservation['return_date']) - strtotime($reservation['pickup_date'])) / (60*60)))
                                            , 2) ?> PLN</td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h3>Zarządzanie rezerwacją</h3>
                </div>
                <div class="card-body">
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label">Status rezerwacji</label>
                            <select name="status" class="form-select" required>
                                <option value="pending" <?= $reservation['status'] == 'pending' ? 'selected' : '' ?>>Oczekująca</option>
                                <option value="confirmed" <?= $reservation['status'] == 'confirmed' ? 'selected' : '' ?>>Potwierdzona</option>
                                <option value="cancelled" <?= $reservation['status'] == 'cancelled' ? 'selected' : '' ?>>Anulowana</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Notatki</label>
                            <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($reservation['notes'] ?? '') ?></textarea>
                        </div>
                        
                        <button type="submit" name="change_status" class="btn btn-primary">Zapisz zmiany</button>
                        <a href="manage_reservations.php" class="btn btn-secondary">Powrót</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    :root {
        --primary-color: #1976d2;
        --primary-light: #e3f2fd;
        --success-color: #388e3c;
        --warning-color: #f57c00;
        --danger-color: #d32f2f;
        --text-color: #333;
        --light-gray: #f5f5f5;
        --medium-gray: #e0e0e0;
        --dark-gray: #757575;
        --border-radius: 8px;
    }

    body {
        font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        color: var(--text-color);
        background-color: #fafafa;
        line-height: 1.6;
    }

    .container-fluid {
        max-width: 1400px;
        padding: 2rem;
    }

    h2 {
        color: var(--primary-color);
        font-weight: 600;
        margin-bottom: 2rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid var(--medium-gray);
        position: relative;
    }

    h2:after {
        content: '';
        position: absolute;
        left: 0;
        bottom: -2px;
        width: 100px;
        height: 2px;
        background: var(--primary-color);
    }

    h3 {
        font-size: 1.25rem;
        font-weight: 500;
        color: var(--text-color);
        margin: 0;
    }

    /* Karty */
    .card {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        margin-bottom: 1.5rem;
        overflow: hidden;
    }

    .card:hover {
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.12);
    }

    .card-header {
        background-color: var(--primary-color);
        color: white;
        padding: 1rem 1.5rem;
        border-bottom: none;
    }

    .card-body {
        padding: 1.5rem;
    }

    /* Alerty */
    .alert {
        padding: 0.75rem 1.25rem;
        border-radius: var(--border-radius);
        margin-bottom: 1.5rem;
    }

    .alert-danger {
        background-color: #ffebee;
        color: var(--danger-color);
        border-left: 4px solid var(--danger-color);
    }

    /* Listy opisowe */
    dl.row dt {
        font-weight: 500;
        color: var(--dark-gray);
        padding-top: 0.25rem;
    }

    dl.row dd {
        margin-bottom: 0.75rem;
        padding-top: 0.25rem;
    }

    /* Statusy */
    .badge {
        font-size: 0.75rem;
        font-weight: 500;
        padding: 0.35em 0.65em;
        border-radius: 4px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .bg-success { background-color: var(--success-color) !important; }
    .bg-warning { background-color: var(--warning-color) !important; }
    .bg-danger { background-color: var(--danger-color) !important; }

    /* Tabele */
    .table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0;
    }

    .table th {
        background-color: var(--light-gray);
        padding: 0.75rem 1rem;
        text-align: left;
        font-weight: 500;
        border-bottom: 2px solid var(--medium-gray);
    }

    .table td {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid var(--medium-gray);
        vertical-align: middle;
    }

    .table tr:last-child td {
        border-bottom: none;
    }

    .table tr:hover {
        background-color: rgba(25, 118, 210, 0.03);
    }

    /* Formularze */
    .form-label {
        font-weight: 500;
        color: var(--dark-gray);
        margin-bottom: 0.5rem;
        display: block;
    }

    .form-control, .form-select {
        border: 1px solid var(--medium-gray);
        border-radius: var(--border-radius);
        padding: 0.5rem 0.75rem;
        width: 100%;
        transition: border 0.3s;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        outline: none;
        box-shadow: 0 0 0 2px rgba(25, 118, 210, 0.2);
    }

    textarea.form-control {
        min-height: 100px;
    }

    /* Przyciski */
    .btn {
        padding: 0.5rem 1.25rem;
        border-radius: var(--border-radius);
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        cursor: pointer;
        border: 1px solid transparent;
    }

    .btn-primary {
        background-color: var(--primary-color);
        color: white;
    }

    .btn-primary:hover {
        background-color: #1565c0;
        transform: translateY(-1px);
    }

    .btn-secondary {
        background-color: var(--dark-gray);
        color: white;
    }

    .btn-secondary:hover {
        background-color: #616161;
    }

    .btn-warning {
        background-color: var(--warning-color);
        color: white;
    }

    .btn-warning:hover {
        background-color: #e65100;
    }

    /* Obrazy */
    .img-fluid {
        max-width: 100%;
        height: auto;
        border-radius: var(--border-radius);
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    /* Preformowany tekst */
    pre {
        white-space: pre-wrap;
        background-color: var(--light-gray);
        padding: 1rem;
        border-radius: var(--border-radius);
        border-left: 4px solid var(--primary-color);
        font-family: 'Courier New', monospace;
        font-size: 0.9rem;
        margin: 0;
    }

    /* Responsywność */
    @media (max-width: 992px) {
        .container-fluid {
            padding: 1.5rem;
        }
    }

    @media (max-width: 768px) {
        .card-body {
            padding: 1.25rem;
        }
        
        dl.row dt, 
        dl.row dd {
            width: 100%;
            display: block;
        }
        
        dl.row dt {
            margin-top: 0.5rem;
            padding-top: 0.5rem;
            border-top: 1px dashed var(--medium-gray);
        }
    }

    /* Druk */
    @media print {
        body {
            padding: 0 !important;
            font-size: 12pt;
            background: white !important;
        }
        
        .container-fluid {
            padding: 0 !important;
            max-width: 100% !important;
        }
        
        .card {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
            page-break-inside: avoid;
        }
        
        .btn, .form-control, .form-select, .form-label {
            display: none !important;
        }
        
        h2:after {
            display: none;
        }
    }
</style>

