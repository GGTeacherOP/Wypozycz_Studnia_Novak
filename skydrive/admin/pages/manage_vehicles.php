<?php
require_once __DIR__ . '/../admin_functions.php';
checkAdminAuth();

require_once __DIR__ . '/../../includes/config.php';

// Akcje na pojazdach
if (isset($_GET['delete'])) {
    $vehicle_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM vehicles WHERE vehicle_id = ?");
    $stmt->bind_param("i", $vehicle_id);
    $stmt->execute();
    $_SESSION['success'] = "Pojazd został usunięty";
    header("Location: manage_vehicles.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vehicle_id = isset($_POST['vehicle_id']) ? intval($_POST['vehicle_id']) : 0;
    $available = isset($_POST['available']) ? 1 : 0;
    
    $stmt = $conn->prepare("UPDATE vehicles SET available = ? WHERE vehicle_id = ?");
    $stmt->bind_param("ii", $available, $vehicle_id);
    $stmt->execute();
    $_SESSION['success'] = "Status pojazdu został zaktualizowany";
    header("Location: manage_vehicles.php");
    exit();
}

// Pobierz listę pojazdów
$vehicles = $conn->query("
    SELECT v.*, l.city 
    FROM vehicles v 
    LEFT JOIN locations l ON v.location_id = l.location_id
    ORDER BY v.type, v.make, v.model
");

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="container-fluid">
    <h2>Zarządzanie pojazdami</h2>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Typ</th>
                    <th>Marka i model</th>
                    <th>Rok</th>
                    <th>Lokalizacja</th>
                    <th>Cena</th>
                    <th>Dostępny</th>
                    <th>Akcje</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($vehicle = $vehicles->fetch_assoc()): ?>
                <tr>
                    <td><?= $vehicle['vehicle_id'] ?></td>
                    <td><?= $vehicle['type'] == 'car' ? 'Samochód' : 'Samolot' ?></td>
                    <td><?= htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']) ?></td>
                    <td><?= $vehicle['year'] ?></td>
                    <td><?= htmlspecialchars($vehicle['city'] ?? 'Brak') ?></td>
                    <td>
                        <?= $vehicle['type'] == 'car' ? 
                            number_format($vehicle['daily_rate'], 2) . ' PLN/dzień' : 
                            number_format($vehicle['hourly_rate'], 2) . ' PLN/godz' ?>
                    </td>
                    <td>
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="vehicle_id" value="<?= $vehicle['vehicle_id'] ?>">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="available" 
                                    <?= $vehicle['available'] ? 'checked' : '' ?> 
                                    onchange="this.form.submit()">
                            </div>
                        </form>
                    </td>
                    <td>
                        <a href="../edit_vehicle.php?id=<?= $vehicle['vehicle_id'] ?>" class="btn btn-primary btn-sm">Edytuj</a>
                        <a href="?delete=<?= $vehicle['vehicle_id'] ?>" class="btn btn-danger btn-sm"
                           onclick="return confirm('Czy na pewno chcesz usunąć ten pojazd?')">
                            Usuń
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <a href="../add_vehicle.php" class="btn btn-success mt-3">Dodaj nowy pojazd</a>
</div>
<style>
/* Główny kontener */
.container-fluid {
    padding: 2.5rem;
    max-width: 1800px;
    margin: 0 auto;
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
    content: "🚗";
    font-size: 1.5rem;
}

/* Alerty */
.alert {
    border-radius: 10px;
    padding: 1.25rem 1.5rem;
    margin-bottom: 2rem;
    border: none;
    box-shadow: 0 2px 15px rgba(0,0,0,0.05);
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.alert-success {
    background-color: #f0fdf4;
    color: #166534;
    border-left: 4px solid #22c55e;
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
    background: white;
    margin-bottom: 2.5rem;
    border: 1px solid #f1f5f9;
}

.table {
    margin-bottom: 0;
    background: white;
    font-size: 0.95rem;
}

.table thead th {
    background-color: #1e40af;
    color: white;
    font-weight: 600;
    padding: 1.25rem 1.5rem;
    border: none;
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 0.5px;
    vertical-align: middle;
}

.table thead th:first-child {
    border-top-left-radius: 12px;
}

.table thead th:last-child {
    border-top-right-radius: 12px;
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
    padding: 1.5rem;
    vertical-align: middle;
    color: #334155;
    font-weight: 500;
}

.table td:first-child {
    font-weight: 600;
    color: #1e40af;
}

/* Przełącznik dostępności */
.form-switch .form-check-input {
    width: 3.5em;
    height: 1.75em;
    cursor: pointer;
    background-color: #e2e8f0;
    border: 1px solid #cbd5e1;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='%23fff'/%3e%3c/svg%3e");
}

.form-switch .form-check-input:checked {
    background-color: #10b981;
    border-color: #10b981;
}

/* Przyciski akcji */
.btn {
    border-radius: 8px;
    padding: 0.7rem 1.25rem;
    font-weight: 600;
    transition: all 0.3s ease;
    font-size: 0.9rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-sm {
    padding: 0.6rem 1rem;
    font-size: 0.85rem;
}

.btn-primary {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
}

.btn-primary:hover {
    background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.btn-danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.btn-danger:hover {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.btn-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    padding: 1rem 1.75rem;
    font-size: 1rem;
    margin-top: 1rem;
}

.btn-success:hover {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
}

/* Ikony w przyciskach */
.btn i, .btn svg {
    font-size: 1.1em;
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
        color: #1e40af;
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
        background: #1e40af;
        color: white !important;
        padding: 0.5rem 1.5rem;
        width: auto;
        border-top-left-radius: 10px;
    }
    
    .table td:first-child::before {
        display: none;
    }
    
    .form-switch {
        justify-content: flex-end;
    }
}
</style>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>