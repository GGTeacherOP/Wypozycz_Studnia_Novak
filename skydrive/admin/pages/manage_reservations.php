<?php
require_once __DIR__ . '/../admin_functions.php';
checkAdminAuth();

require_once __DIR__ . '/../../includes/config.php';

// Akcje na rezerwacjach
if (isset($_GET['action']) && isset($_GET['id'])) {
    $reservation_id = intval($_GET['id']);
    $action = $_GET['action'];
    
    $valid_statuses = ['pending', 'confirmed', 'cancelled'];
    if (in_array($action, $valid_statuses)) {
        $stmt = $conn->prepare("UPDATE reservations SET status = ? WHERE reservation_id = ?");
        $stmt->bind_param("si", $action, $reservation_id);
        $stmt->execute();
        $_SESSION['success'] = "Status rezerwacji został zaktualizowany";
    }
    header("Location: manage_reservations.php");
    exit();
}

// Pobierz listę rezerwacji
$reservations = $conn->query("
    SELECT r.*, 
           v.make, v.model, v.type,
           u.first_name, u.last_name, u.email,
           pl.city as pickup_city, rl.city as return_city
    FROM reservations r
    JOIN vehicles v ON r.vehicle_id = v.vehicle_id
    JOIN users u ON r.user_id = u.user_id
    JOIN locations pl ON r.pickup_location_id = pl.location_id
    JOIN locations rl ON r.return_location_id = rl.location_id
    ORDER BY r.pickup_date DESC
");

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="container-fluid">
    <h2>Zarządzanie rezerwacjami</h2>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pojazd</th>
                    <th>Klient</th>
                    <th>Termin</th>
                    <th>Lokalizacje</th>
                    <th>Koszt</th>
                    <th>Status</th>
                    <th>Akcje</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($res = $reservations->fetch_assoc()): ?>
                <tr>
                    <td><?= $res['reservation_id'] ?></td>
                    <td>
                        <?= htmlspecialchars($res['make'] . ' ' . $res['model']) ?>
                        <small class="text-muted d-block"><?= $res['type'] == 'car' ? 'Samochód' : 'Samolot' ?></small>
                    </td>
                    <td>
                        <?= htmlspecialchars($res['first_name'] . ' ' . $res['last_name']) ?>
                        <small class="text-muted d-block"><?= htmlspecialchars($res['email']) ?></small>
                    </td>
                    <td>
                        <?= date('d.m.Y H:i', strtotime($res['pickup_date'])) ?><br>
                        do<br>
                        <?= date('d.m.Y H:i', strtotime($res['return_date'])) ?>
                    </td>
                    <td>
                        <strong>Odbiór:</strong> <?= htmlspecialchars($res['pickup_city']) ?><br>
                        <strong>Zwrot:</strong> <?= htmlspecialchars($res['return_city']) ?>
                    </td>
                    <td><?= number_format($res['total_cost'], 2) ?> PLN</td>
                    <td>
                        <span class="badge bg-<?= 
                            $res['status'] == 'confirmed' ? 'success' : 
                            ($res['status'] == 'pending' ? 'warning' : 'danger') 
                        ?>">
                            <?= 
                                $res['status'] == 'confirmed' ? 'Potwierdzona' : 
                                ($res['status'] == 'pending' ? 'Oczekująca' : 'Anulowana') 
                            ?>
                        </span>
                    </td>
                    <td>
                        <div class="btn-group">
                            <?php if ($res['status'] == 'pending'): ?>
                                <a href="?action=confirmed&id=<?= $res['reservation_id'] ?>" 
                                   class="btn btn-sm btn-success" title="Zatwierdź">
                                    <i class="fas fa-check"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($res['status'] != 'cancelled'): ?>
                                <a href="?action=cancelled&id=<?= $res['reservation_id'] ?>" 
                                   class="btn btn-sm btn-danger" title="Anuluj">
                                    <i class="fas fa-times"></i>
                                </a>
                            <?php endif; ?>
                            
                            <a href="../reservation_details.php?id=<?= $res['reservation_id'] ?>" 
                               class="btn btn-sm btn-primary" title="Szczegóły">
                                <i class="fas fa-info-circle"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<style>
/* Główny kontener */
.container-fluid {
    padding: 2.5rem;
    background: #f8fafc;
    min-height: 100vh;
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
    content: "📅";
    font-size: 1.5rem;
}

/* Alerty */
.alert-success {
    background-color: #f0fdf4;
    color: #166534;
    border-left: 4px solid #22c55e;
    padding: 1.25rem 1.5rem;
    border-radius: 8px;
    margin-bottom: 2rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    display: flex;
    align-items: center;
    gap: 1rem;
}

.alert-success::before {
    content: "✓";
    font-size: 1.2rem;
}

/* Tabela */
.table-responsive {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 8px 30px rgba(0,0,0,0.08);
    border: 1px solid #f1f5f9;
    background: white;
}

.table {
    margin-bottom: 0;
}

.table thead th {
    background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
    color: white;
    font-weight: 600;
    padding: 1.25rem;
    border: none;
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 0.5px;
    vertical-align: middle;
    position: sticky;
    top: 0;
}

.table tbody tr {
    transition: all 0.25s ease;
    border-bottom: 1px solid #f1f5f9;
}

.table tbody tr:last-child {
    border-bottom: none;
}

.table tbody tr:hover {
    background-color: #f8fafc;
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}

.table td {
    padding: 1.25rem;
    vertical-align: middle;
    color: #334155;
}

/* Statusy rezerwacji */
.badge {
    padding: 0.5rem 0.75rem;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge.bg-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.badge.bg-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
}

.badge.bg-danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

/* Przyciski akcji */
.btn-group {
    display: flex;
    gap: 0.5rem;
    flex-wrap: nowrap;
}

.btn {
    border-radius: 8px;
    padding: 0.6rem;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
}

.btn-sm {
    padding: 0.5rem;
    width: 32px;
    height: 32px;
}

.btn-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.btn-success:hover {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn-danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.btn-danger:hover {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
}

.btn-primary {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
}

.btn-primary:hover {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
}

/* Tekst pomocniczy */
.text-muted {
    color: #94a3b8 !important;
    font-size: 0.8rem;
}

/* Responsywność */
@media (max-width: 1200px) {
    .table td, .table th {
        padding: 1rem;
    }
}

@media (max-width: 992px) {
    .container-fluid {
        padding: 1.5rem;
    }
    
    .table-responsive {
        border-radius: 10px;
    }
    
    .table thead {
        display: none;
    }
    
    .table, .table tbody, .table tr, .table td {
        display: block;
        width: 100%;
    }
    
    .table tr {
        margin-bottom: 1.5rem;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        position: relative;
        padding-top: 2.5rem;
    }
    
    .table td {
        padding: 0.75rem 1.5rem;
        text-align: right;
        position: relative;
        padding-left: 50%;
    }
    
    .table td::before {
        content: attr(data-label);
        position: absolute;
        left: 1.5rem;
        width: calc(50% - 1.5rem);
        padding-right: 1rem;
        text-align: left;
        font-weight: 600;
        color: #0ea5e9;
    }
    
    .table td[data-label="Akcje"] {
        text-align: center;
        padding-left: 1.5rem;
        border-top: 1px solid #f1f5f9;
        background: #f8fafc;
    }
    
    .table td[data-label="Akcje"]::before {
        display: none;
    }
    
    .table td:first-child {
        position: absolute;
        top: 0;
        left: 0;
        background: #0ea5e9;
        color: white !important;
        padding: 0.5rem 1.5rem;
        width: auto;
        border-top-left-radius: 10px;
    }
    
    .table td:first-child::before {
        display: none;
    }
    
    .btn-group {
        justify-content: center;
    }
    
    .text-muted {
        display: inline !important;
        margin-left: 0.5rem;
    }
}

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>