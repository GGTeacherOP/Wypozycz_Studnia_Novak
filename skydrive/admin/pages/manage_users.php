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

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
