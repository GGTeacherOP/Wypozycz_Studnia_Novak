<?php
require_once __DIR__ . '/../admin_functions.php';
checkAdminAuth();

require_once __DIR__ . '/../includes/config.php';

if (!isset($_GET['id'])) {
    header("Location: payments.php");
    exit();
}

$payment_id = intval($_GET['id']);

// Pobierz dane płatności do edycji
$stmt = $conn->prepare("SELECT * FROM payments WHERE payment_id = ?");
$stmt->bind_param("i", $payment_id);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();

if (!$payment) {
    header("Location: payments.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'];
    $invoice_number = $_POST['invoice_number'];
    $payment_details = $_POST['payment_details'];
    
    $stmt = $conn->prepare("
        UPDATE payments 
        SET status = ?, invoice_number = ?, payment_details = ?
        WHERE payment_id = ?
    ");
    $stmt->bind_param("sssi", $status, $invoice_number, $payment_details, $payment_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Płatność została zaktualizowana";
        header("Location: payment_details.php?id=" . $payment_id);
        exit();
    } else {
        $error = "Błąd podczas aktualizacji płatności: " . $conn->error;
    }
}

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="container-fluid">
    <h2>Edytuj płatność #<?= $payment['payment_id'] ?></h2>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Status płatności</label>
                    <select class="form-select" name="status" required>
                        <option value="pending" <?= $payment['status'] == 'pending' ? 'selected' : '' ?>>Oczekująca</option>
                        <option value="completed" <?= $payment['status'] == 'completed' ? 'selected' : '' ?>>Zakończona</option>
                        <option value="failed" <?= $payment['status'] == 'failed' ? 'selected' : '' ?>>Nieudana</option>
                        <option value="refunded" <?= $payment['status'] == 'refunded' ? 'selected' : '' ?>>Zwrócona</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Numer faktury</label>
                    <input type="text" class="form-control" name="invoice_number" 
                           value="<?= htmlspecialchars($payment['invoice_number'] ?? '') ?>">
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Szczegóły płatności</label>
                    <textarea class="form-control" name="payment_details" rows="5"><?= 
                        htmlspecialchars($payment['payment_details'] ?? '') 
                    ?></textarea>
                </div>
            </div>
        </div>
        
        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Zapisz zmiany</button>
            <a href="payment_details.php?id=<?= $payment_id ?>" class="btn btn-secondary">Anuluj</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>