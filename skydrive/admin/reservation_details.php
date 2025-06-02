<?php
require_once __DIR__ . '/../admin/admin_functions.php';
checkAdminAuth();

require_once __DIR__ . '/../includes/config.php';

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

require_once __DIR__ . '/../admin/includes/admin_header.php';
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
/* Główny kontener */
.container-fluid {
    padding: 2.5rem;
    max-width: 1400px;
    margin: 0 auto;
    background: #f8fafc;
}

/* Nagłówek */
.container-fluid h2 {
    color: #1e293b;
    font-weight: 700;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e2e8f0;
    font-size: 1.8rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.container-fluid h2::before {
    content: "📋";
    font-size: 1.5rem;
}

/* Karty */
.card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    margin-bottom: 1.5rem;
    transition: transform 0.3s ease;
}

.card:hover {
    transform: translateY(-3px);
}

.card-header {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
    border-radius: 12px 12px 0 0 !important;
    padding: 1.25rem 1.5rem;
    border: none;
}

.card-header h3 {
    margin: 0;
    font-weight: 600;
    font-size: 1.25rem;
}

.card-body {
    padding: 1.5rem;
}

/* Obraz pojazdu */
.img-fluid {
    border-radius: 8px;
    object-fit: cover;
    width: 100%;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border: 1px solid #f1f5f9;
}

/* Lista opisowa */
dl.row {
    margin: 0;
}

dt.col-sm-4 {
    font-weight: 600;
    color: #475569;
    padding: 0.5rem 0;
}

dd.col-sm-8 {
    color: #334155;
    padding: 0.5rem 0;
    margin-bottom: 0;
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

/* Tabela wyposażenia */
.table {
    margin-bottom: 0;
    background: white;
}

.table thead th {
    background-color: #f1f5f9;
    color: #334155;
    font-weight: 600;
    padding: 1rem;
    border-bottom: 2px solid #e2e8f0;
}

.table tbody td {
    padding: 1rem;
    border-top: 1px solid #f1f5f9;
    vertical-align: middle;
}

/* Formularz zarządzania */
.form-label {
    font-weight: 600;
    color: #475569;
    margin-bottom: 0.5rem;
}

.form-select, .form-control {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    transition: all 0.3s ease;
    background-color: #f8fafc;
}

.form-select:focus, .form-control:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
    background-color: white;
}

textarea.form-control {
    min-height: 120px;
    resize: vertical;
}

/* Przyciski */
.btn {
    border-radius: 8px;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-primary {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.btn-secondary {
    background: linear-gradient(135deg, #64748b 0%, #475569 100%);
    color: white;
    margin-left: 0.75rem;
}

.btn-secondary:hover {
    background: linear-gradient(135deg, #475569 0%, #334155 100%);
}

.btn-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
}

.btn-warning:hover {
    background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
}

/* Alert */
.alert-danger {
    background-color: #fef2f2;
    color: #b91c1c;
    border-left: 4px solid #ef4444;
    padding: 1.25rem 1.5rem;
    border-radius: 8px;
    margin-bottom: 2rem;
}

/* Preformatowany tekst */
pre {
    white-space: pre-wrap;
    word-wrap: break-word;
    background-color: #f8fafc;
    padding: 0.75rem;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    font-family: inherit;
    margin-bottom: 0;
}

/* Responsywność */
@media (max-width: 992px) {
    .row {
        flex-direction: column;
    }
    
    .col-md-6 {
        width: 100%;
    }
    
    .btn {
        width: 100%;
        margin-bottom: 0.75rem;
    }
    
    .btn-secondary {
        margin-left: 0;
    }
}

/* Animacje */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.card {
    animation: fadeIn 0.4s ease forwards;
}

/* Sekwencja animacji */
.card:nth-child(1) { animation-delay: 0.1s; }
.card:nth-child(2) { animation-delay: 0.2s; }
.card:nth-child(3) { animation-delay: 0.3s; }
.card:nth-child(4) { animation-delay: 0.4s; }
</style>
<?php require_once __DIR__ . '/../admin/includes/admin_footer.php'; ?>