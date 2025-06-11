<?php
require_once __DIR__ . '/../admin_functions.php';
checkAdminAuth();

require_once __DIR__ . '/../../includes/config.php';

// Akcje na użytkownikach
if (isset($_GET['delete'])) {
    $user_id = intval($_GET['delete']);
    if ($user_id != $_SESSION['admin_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $_SESSION['success'] = "Użytkownik został usunięty";
    } else {
        $_SESSION['error'] = "Nie możesz usunąć własnego konta";
    }
    header("Location: manage_users.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;
    
    if ($user_id > 0) {
        // Edycja istniejącego użytkownika
        $stmt = $conn->prepare("UPDATE users SET is_admin = ? WHERE user_id = ?");
        $stmt->bind_param("ii", $is_admin, $user_id);
        $stmt->execute();
        $_SESSION['success'] = "Uprawnienia użytkownika zostały zaktualizowane";
    }
    header("Location: manage_users.php");
    exit();
}

// Pobierz listę użytkowników
$users = $conn->query("SELECT * FROM users ORDER BY is_admin DESC, last_name ASC");

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="container-fluid">
    <h2>Zarządzanie użytkownikami</h2>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Imię i nazwisko</th>
                    <th>Email</th>
                    <th>Telefon</th>
                    <th>Administrator</th>
                    <th>Akcje</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $users->fetch_assoc()): ?>
                <tr>
                    <td><?= $user['user_id'] ?></td>
                    <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['phone']) ?></td>
                    <td>
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_admin" 
                                    <?= $user['is_admin'] ? 'checked' : '' ?> 
                                    onchange="this.form.submit()">
                            </div>
                        </form>
                    </td>
                    <td>
                        <?php if ($user['user_id'] != $_SESSION['admin_id']): ?>
                            <a href="?delete=<?= $user['user_id'] ?>" class="btn btn-danger btn-sm"
                               onclick="return confirm('Czy na pewno chcesz usunąć tego użytkownika?')">
                                Usuń
                            </a>
                        <?php endif; ?>
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
    padding: 2rem;
    max-width: 1400px;
    margin: 0 auto;
}

/* Nagłówek */
.container-fluid h2 {
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid #f0f2f5;
}

/* Alerty */
.alert {
    border-radius: 8px;
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
}

/* Tabela */
.table-responsive {
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.table {
    margin-bottom: 0;
    background: white;
}

.table thead th {
    background-color: #4a6baf;
    color: white;
    font-weight: 500;
    padding: 1rem 1.25rem;
    border: none;
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 0.5px;
}

.table tbody tr {
    transition: all 0.2s ease;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}

.table td {
    padding: 1.25rem;
    vertical-align: middle;
    border-top: 1px solid #f0f2f5;
    color: #495057;
}

/* Przełącznik admina */
.form-switch .form-check-input {
    width: 3em;
    height: 1.5em;
    cursor: pointer;
    background-color: #e9ecef;
    border: 1px solid #dee2e6;
}

.form-switch .form-check-input:checked {
    background-color: #4a6baf;
    border-color: #4a6baf;
}

/* Przyciski akcji */
.btn {
    border-radius: 6px;
    padding: 0.5rem 1rem;
    font-weight: 500;
    transition: all 0.2s ease;
    font-size: 0.85rem;
    box-shadow: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.btn-sm {
    padding: 0.4rem 0.8rem;
    font-size: 0.8rem;
}

.btn-danger {
    background-color: #f44336;
    border: none;
}

.btn-danger:hover {
    background-color: #d32f2f;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(244, 67, 54, 0.3);
}

/* Responsywność */
@media (max-width: 768px) {
    .table-responsive {
        border-radius: 8px;
    }
    
    .table thead {
        display: none;
    }
    
    .table, .table tbody, .table tr, .table td {
        display: block;
        width: 100%;
    }
    
    .table tr {
        margin-bottom: 1rem;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .table td {
        padding: 0.75rem;
        text-align: right;
        position: relative;
        padding-left: 50%;
    }
    
    .table td::before {
        content: attr(data-label);
        position: absolute;
        left: 1rem;
        width: calc(50% - 1rem);
        padding-right: 1rem;
        text-align: left;
        font-weight: 600;
        color: #4a6baf;
    }
    
    .table td[data-label="Akcje"] {
        text-align: center;
        padding-left: 1rem;
    }
    
    .table td[data-label="Akcje"]::before {
        display: none;
    }
}
</style>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>