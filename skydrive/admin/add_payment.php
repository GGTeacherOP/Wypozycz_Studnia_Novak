<?php
require_once __DIR__ . '/../admin/admin_functions.php';
checkAdminAuth();

require_once __DIR__ . '/../includes/config.php';

$reservation_id = isset($_GET['reservation_id']) ? intval($_GET['reservation_id']) : 0;

// Pobierz dane rezerwacji
$query = "SELECT r.total_cost, r.reservation_id, 
          CONCAT(u.first_name, ' ', u.last_name) as customer_name,
          v.make, v.model
          FROM reservations r
          JOIN users u ON r.user_id = u.user_id
          JOIN vehicles v ON r.vehicle_id = v.vehicle_id
          WHERE r.reservation_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $reservation_id);
$stmt->execute();
$reservation = $stmt->get_result()->fetch_assoc();

if(!$reservation) {
    header("Location: manage_reservations.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = floatval($_POST['amount']);
    $payment_method = $_POST['payment_method'];
    $status = $_POST['status'];
    $invoice_number = $_POST['invoice_number'];
    $payment_details = $_POST['payment_details'];
    
    $query = "INSERT INTO payments 
              (reservation_id, amount, payment_date, payment_method, 
               status, transaction_id, invoice_number, payment_details)
              VALUES (?, ?, NOW(), ?, ?, NULL, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("idssss", $reservation_id, $amount, $payment_method, 
                     $status, $invoice_number, $payment_details);
    
    if($stmt->execute()) {
        $_SESSION['success'] = "Płatność została dodana";
        header("Location: reservation_details.php?id=$reservation_id");
        exit();
    } else {
        $error = "Błąd podczas dodawania płatności: " . $conn->error;
    }
}

require_once __DIR__ . '/../admin/includes/admin_header.php';
?>

<div class="container-fluid">
    <h2>Dodaj płatność dla rezerwacji #<?= $reservation_id ?></h2>
    
    <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <form method="post">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Klient</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($reservation['customer_name']) ?>" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Pojazd</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($reservation['make'] . ' ' . $reservation['model']) ?>" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Kwota rezerwacji</label>
                            <input type="text" class="form-control" value="<?= number_format($reservation['total_cost'], 2) ?> PLN" readonly>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Kwota płatności*</label>
                            <input type="number" step="0.01" name="amount" class="form-control" 
                                   value="<?= $reservation['total_cost'] ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Metoda płatności*</label>
                            <select name="payment_method" class="form-select" required>
                                <option value="credit_card">Karta kredytowa</option>
                                <option value="bank_transfer">Przelew bankowy</option>
                                <option value="cash">Gotówka</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Status*</label>
                            <select name="status" class="form-select" required>
                                <option value="pending">Oczekująca</option>
                                <option value="completed">Zakończona</option>
                                <option value="failed">Nieudana</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Nr faktury</label>
                            <input type="text" name="invoice_number" class="form-control">
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Szczegóły płatności</label>
                    <textarea name="payment_details" class="form-control" rows="3"></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">Dodaj płatność</button>
                <a href="reservation_details.php?id=<?= $reservation_id ?>" class="btn btn-secondary">Anuluj</a>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../admin/includes/admin_footer.php'; ?>