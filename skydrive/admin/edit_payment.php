<?php
require_once __DIR__ . '/../admin/admin_functions.php';
checkAdminAuth();

require_once __DIR__ . '/../includes/config.php';

$reservation_id = isset($_GET['reservation_id']) ? intval($_GET['reservation_id']) : 0;

// Pobierz dane płatności
$query = "SELECT * FROM payments WHERE reservation_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $reservation_id);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();

if(!$payment) {
    header("Location: manage_reservations.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = floatval($_POST['amount']);
    $payment_method = $_POST['payment_method'];
    $status = $_POST['status'];
    $invoice_number = $_POST['invoice_number'];
    $payment_details = $_POST['payment_details'];
    
    $query = "UPDATE payments SET 
              amount = ?, payment_method = ?, status = ?,
              invoice_number = ?, payment_details = ?
              WHERE payment_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("dssssi", $amount, $payment_method, $status, 
                     $invoice_number, $payment_details, $payment['payment_id']);
    
    if($stmt->execute()) {
        $_SESSION['success'] = "Płatność została zaktualizowana";
        header("Location: reservation_details.php?id=$reservation_id");
        exit();
    } else {
        $error = "Błąd podczas aktualizacji płatności: " . $conn->error;
    }
}

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="container-fluid">
    <h2>Edytuj płatność dla rezerwacji #<?= $reservation_id ?></h2>
    
    <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <form method="post">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Kwota*</label>
                            <input type="number" step="0.01" name="amount" class="form-control" 
                                   value="<?= $payment['amount'] ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Metoda płatności*</label>
                            <select name="payment_method" class="form-select" required>
                                <option value="credit_card" <?= $payment['payment_method'] == 'credit_card' ? 'selected' : '' ?>>Karta kredytowa</option>
                                <option value="bank_transfer" <?= $payment['payment_method'] == 'bank_transfer' ? 'selected' : '' ?>>Przelew bankowy</option>
                                <option value="cash" <?= $payment['payment_method'] == 'cash' ? 'selected' : '' ?>>Gotówka</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Status*</label>
                            <select name="status" class="form-select" required>
                                <option value="pending" <?= $payment['status'] == 'pending' ? 'selected' : '' ?>>Oczekująca</option>
                                <option value="completed" <?= $payment['status'] == 'completed' ? 'selected' : '' ?>>Zakończona</option>
                                <option value="failed" <?= $payment['status'] == 'failed' ? 'selected' : '' ?>>Nieudana</option>
                                <option value="refunded" <?= $payment['status'] == 'refunded' ? 'selected' : '' ?>>Zwrócona</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Nr faktury</label>
                            <input type="text" name="invoice_number" class="form-control" 
                                   value="<?= htmlspecialchars($payment['invoice_number'] ?? '') ?>">
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Szczegóły płatności</label>
                    <textarea name="payment_details" class="form-control" rows="3"><?= htmlspecialchars($payment['payment_details'] ?? '') ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">Zapisz zmiany</button>
                <a href="reservation_details.php?id=<?= $reservation_id ?>" class="btn btn-secondary">Anuluj</a>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>