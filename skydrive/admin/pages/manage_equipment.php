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

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>