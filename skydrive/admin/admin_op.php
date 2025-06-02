<?php
require_once __DIR__ . '/../includes/config.php';
session_start();

require_once __DIR__ . '/../admin/admin_functions.php';
checkAdminAuth();

// Obsługa akcji admina
if (isset($_GET['action'])) {
    $review_id = (int)$_GET['id'];
    switch ($_GET['action']) {
        case 'approve':
            $db->query("UPDATE reviews SET is_approved = 1 WHERE id = $review_id");
            break;
        case 'delete':
            $db->query("DELETE FROM reviews WHERE id = $review_id");
            break;
    }
    header("Location: index.php");
}

// Pobierz opinie do moderacji
$reviews = $db->query("
    SELECT r.*, u.first_name 
    FROM reviews r
    LEFT JOIN users u ON r.user_id = u.user_id
    ORDER BY r.is_approved, r.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Panel Admina - Opinie</title>
    <link rel="stylesheet" href="../admin/admin_op.css">
</head>
<body>
    <div class="admin-container">
        <h1>Moderacja opinii</h1>
        <table class="reviews-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Użytkownik</th>
                    <th>Treść</th>
                    <th>Ocena</th>
                    <th>Data</th>
                    <th>Akcje</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($review = $reviews->fetch()): ?>
                <tr class="<?= $review['is_approved'] ? 'approved' : 'pending' ?>">
                    <td><?= $review['id'] ?></td>
                    <td><?= htmlspecialchars($review['first_name']) ?></td>
                    <td><?= htmlspecialchars($review['content']) ?></td>
                    <td><?= str_repeat('★', $review['rating']) ?></td>
                    <td><?= date('d.m.Y H:i', strtotime($review['created_at'])) ?></td>
                    <td class="actions">
                        <?php if (!$review['is_approved']): ?>
                            <a href="?action=approve&id=<?= $review['id'] ?>" class="btn-approve">Zatwierdź</a>
                        <?php endif; ?>
                        <a href="?action=delete&id=<?= $review['id'] ?>" class="btn-delete">Usuń</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>