<?php
require_once __DIR__ . '/../admin_functions.php';
checkAdminAuth();

require_once __DIR__ . '/../../includes/config.php';

// Akcje na wyposażeniu
if (isset($_GET['delete'])) {
    $equipment_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM equipment WHERE equipment_id = ?");
    $stmt->bind_param("i", $equipment_id);
    $stmt->execute();
    $_SESSION['success'] = "Wyposażenie zostało usunięte";
    header("Location: manage_equipment.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_equipment'])) {
        $name = $conn->real_escape_string(trim($_POST['name']));
        $description = $conn->real_escape_string(trim($_POST['description']));
        $daily_cost = floatval($_POST['daily_cost']);
        
        $stmt = $conn->prepare("INSERT INTO equipment (name, description, daily_cost) VALUES (?, ?, ?)");
        $stmt->bind_param("ssd", $name, $description, $daily_cost);
        $stmt->execute();
        $_SESSION['success'] = "Wyposażenie zostało dodane";
    } elseif (isset($_POST['edit_equipment'])) {
        $equipment_id = intval($_POST['equipment_id']);
        $name = $conn->real_escape_string(trim($_POST['name']));
        $description = $conn->real_escape_string(trim($_POST['description']));
        $daily_cost = floatval($_POST['daily_cost']);
        
        $stmt = $conn->prepare("UPDATE equipment SET name = ?, description = ?, daily_cost = ? WHERE equipment_id = ?");
        $stmt->bind_param("ssdi", $name, $description, $daily_cost, $equipment_id);
        $stmt->execute();
        $_SESSION['success'] = "Wyposażenie zostało zaktualizowane";
    }
    header("Location: manage_equipment.php");
    exit();
}

// Pobierz wyposażenie do edycji
$edit_equipment = null;
if (isset($_GET['edit'])) {
    $equipment_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM equipment WHERE equipment_id = ?");
    $stmt->bind_param("i", $equipment_id);
    $stmt->execute();
    $edit_equipment = $stmt->get_result()->fetch_assoc();
}

// Pobierz listę wyposażenia
$equipment = $conn->query("SELECT * FROM equipment ORDER BY name");

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="container-fluid">
    <h2>Zarządzanie wyposażeniem</h2>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-5">
            <div class="card mb-4">
                <div class="card-header">
                    <h4><?= $edit_equipment ? 'Edytuj wyposażenie' : 'Dodaj nowe wyposażenie' ?></h4>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <?php if ($edit_equipment): ?>
                            <input type="hidden" name="equipment_id" value="<?= $edit_equipment['equipment_id'] ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label class="form-label">Nazwa</label>
                            <input type="text" class="form-control" name="name" 
                                   value="<?= $edit_equipment ? htmlspecialchars($edit_equipment['name']) : '' ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Opis</label>
                            <textarea class="form-control" name="description" rows="3"><?= 
                                $edit_equipment ? htmlspecialchars($edit_equipment['description']) : '' 
                            ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Koszt dzienny (PLN)</label>
                            <input type="number" step="0.01" class="form-control" name="daily_cost" 
                                   value="<?= $edit_equipment ? $edit_equipment['daily_cost'] : '' ?>" required>
                        </div>
                        
                        <button type="submit" name="<?= $edit_equipment ? 'edit_equipment' : 'add_equipment' ?>" 
                                class="btn btn-primary">
                            <?= $edit_equipment ? 'Zapisz zmiany' : 'Dodaj wyposażenie' ?>
                        </button>
                        
                        <?php if ($edit_equipment): ?>
                            <a href="manage_equipment.php" class="btn btn-secondary">Anuluj</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-7">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nazwa</th>
                            <th>Opis</th>
                            <th>Koszt dzienny</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = $equipment->fetch_assoc()): ?>
                        <tr>
                            <td><?= $item['equipment_id'] ?></td>
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td><?= htmlspecialchars($item['description']) ?></td>
                            <td><?= number_format($item['daily_cost'], 2) ?> PLN</td>
                            <td>
                                <a href="?edit=<?= $item['equipment_id'] ?>" class="btn btn-sm btn-primary">Edytuj</a>
                                <a href="?delete=<?= $item['equipment_id'] ?>" class="btn btn-sm btn-danger"
                                   onclick="return confirm('Czy na pewno chcesz usunąć to wyposażenie?')">
                                    Usuń
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
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
    content: "🛠️";
    font-size: 1.5rem;
}

/* Karty */
.card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    transition: transform 0.3s ease;
}

.card:hover {
    transform: translateY(-3px);
}

.card-header {
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
    color: white;
    border-radius: 12px 12px 0 0 !important;
    padding: 1.25rem 1.5rem;
    border: none;
}

.card-header h4 {
    margin: 0;
    font-weight: 600;
    font-size: 1.25rem;
}

.card-body {
    padding: 2rem;
}

/* Formularz */
.form-label {
    font-weight: 600;
    color: #475569;
    margin-bottom: 0.5rem;
}

.form-control {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
}

textarea.form-control {
    min-height: 120px;
}

/* Przyciski */
.btn {
    border-radius: 8px;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.btn-primary {
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
}

.btn-primary:hover {
    background: linear-gradient(135deg, #4338ca 0%, #4f46e5 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
}

.btn-secondary {
    background: linear-gradient(135deg, #64748b 0%, #94a3b8 100%);
    margin-left: 0.75rem;
}

.btn-secondary:hover {
    background: linear-gradient(135deg, #475569 0%, #64748b 100%);
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.85rem;
}

.btn-danger {
    background: linear-gradient(135deg, #ef4444 0%, #f87171 100%);
    margin-left: 0.5rem;
}

.btn-danger:hover {
    background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
}

/* Tabela */
.table-responsive {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 8px 30px rgba(0,0,0,0.08);
    border: 1px solid #f1f5f9;
}

.table {
    margin-bottom: 0;
}

.table thead th {
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
    color: white;
    font-weight: 600;
    padding: 1.25rem;
    border: none;
}

.table tbody tr {
    transition: all 0.25s ease;
}

.table tbody tr:hover {
    background-color: #f8fafc;
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}

.table td {
    padding: 1.25rem;
    vertical-align: middle;
    border-top: 1px solid #f1f5f9;
}

/* Responsywność */
@media (max-width: 992px) {
    .row {
        flex-direction: column-reverse;
    }
    
    .col-md-5, .col-md-7 {
        width: 100%;
    }
    
    .card {
        margin-top: 2rem;
    }
}

@media (max-width: 768px) {
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
        border-top: none;
    }
    
    .table td::before {
        content: attr(data-label);
        position: absolute;
        left: 1.5rem;
        width: calc(50% - 1.5rem);
        padding-right: 1rem;
        text-align: left;
        font-weight: 600;
        color: #4f46e5;
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
        background: #4f46e5;
        color: white !important;
        padding: 0.5rem 1.5rem;
        width: auto;
        border-top-left-radius: 10px;
    }
    
    .table td:first-child::before {
        display: none;
    }
    
    .btn {
        margin-bottom: 0.5rem;
    }
}
</style>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>