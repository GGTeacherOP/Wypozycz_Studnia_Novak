<?php
require_once __DIR__ . '/../../admin/admin_functions.php';
checkAdminAuth();

require_once __DIR__ . '/../../includes/config.php';

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
        --dark-gray: #616161;
        --border-radius: 8px;
        --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    body {
        font-family: 'Roboto', 'Segoe UI', sans-serif;
        background-color: #f8f9fa;
        color: var(--text-color);
    }

    .container-fluid {
        max-width: 1200px;
        padding: 2rem;
    }

    h2 {
        color: var(--primary-color);
        font-weight: 600;
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        position: relative;
    }

    h2:after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 60px;
        height: 3px;
        background: var(--primary-color);
    }

    /* Karta formularza */
    .card {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        background: white;
    }

    .card-body {
        padding: 2rem;
    }

    /* Alerty */
    .alert {
        padding: 0.75rem 1.25rem;
        border-radius: var(--border-radius);
        margin-bottom: 1.5rem;
        border-left: 4px solid transparent;
    }

    .alert-danger {
        background-color: #ffebee;
        color: var(--danger-color);
        border-left-color: var(--danger-color);
    }

    /* Formularz */
    .form-label {
        font-weight: 500;
        color: var(--dark-gray);
        margin-bottom: 0.5rem;
        display: block;
    }

    .form-control, .form-select {
        border: 1px solid var(--medium-gray);
        border-radius: var(--border-radius);
        padding: 0.625rem 0.875rem;
        width: 100%;
        transition: all 0.3s;
        font-size: 0.95rem;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        outline: none;
        box-shadow: 0 0 0 3px rgba(25, 118, 210, 0.1);
    }

    textarea.form-control {
        min-height: 120px;
        resize: vertical;
    }

    input[type="number"] {
        -moz-appearance: textfield;
    }

    input[type="number"]::-webkit-outer-spin-button,
    input[type="number"]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    /* Przyciski */
    .btn {
        padding: 0.625rem 1.5rem;
        border-radius: var(--border-radius);
        font-weight: 500;
        transition: all 0.3s;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .btn-primary {
        background-color: var(--primary-color);
        color: white;
    }

    .btn-primary:hover {
        background-color: #1565c0;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(25, 118, 210, 0.3);
    }

    .btn-secondary {
        background-color: white;
        color: var(--primary-color);
        border: 1px solid var(--primary-color);
    }

    .btn-secondary:hover {
        background-color: var(--primary-light);
    }

    /* Responsywność */
    @media (max-width: 768px) {
        .container-fluid {
            padding: 1.5rem;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .btn {
            width: 100%;
            margin-bottom: 0.75rem;
        }
    }

    /* Druk */
    @media print {
        .card {
            box-shadow: none;
            border: 1px solid #ddd !important;
        }
        
        .btn, .alert {
            display: none !important;
        }
    }
</style>

