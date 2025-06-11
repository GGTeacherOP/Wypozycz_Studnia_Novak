<?php
require_once __DIR__ . '/../../includes/config.php';
session_start();

require_once __DIR__ . '/../../admin/admin_functions.php';
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
    header("Location: /../../../skydrive/admin/pages/admin_op.php");
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
    <link rel="stylesheet" href="admin_op.css">
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
    <a href='../../admin/dashboard.php' class="back-to-dashboard">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M19 12H5M12 19l-7-7 7-7"/>
    </svg>
    Powrót do panelu
</a>
<style>
    /* Główny kontener */
body {
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    background-color: #f8fafc;
    margin: 0;
    padding: 0;
    color: #1e293b;
    min-height: 100vh;
}

.admin-container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 2rem;
    background: white;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

/* Nagłówek */
h1 {
    color: #2c3e50;
    font-weight: 700;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f0f2f5;
    font-size: 2rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}

h1::before {
    content: "✍️";
    font-size: 1.8rem;
}

/* Tabela */
.reviews-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    margin-top: 1.5rem;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
}

.reviews-table thead th {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    color: white;
    font-weight: 600;
    padding: 1.25rem;
    text-align: left;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
    position: sticky;
    top: 0;
}

.reviews-table th:first-child {
    border-top-left-radius: 12px;
}

.reviews-table th:last-child {
    border-top-right-radius: 12px;
}

.reviews-table tbody tr {
    transition: all 0.3s ease;
}

.reviews-table tbody tr:hover {
    background-color: #f8fafc;
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}

.reviews-table td {
    padding: 1.25rem;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}

.reviews-table tr:last-child td {
    border-bottom: none;
}

/* Statusy opinii */
.reviews-table tr.approved {
    background-color: #f0fdf4;
}

.reviews-table tr.pending {
    background-color: #fffbeb;
    position: relative;
}

.reviews-table tr.pending::after {
    content: "OCZEKUJE";
    position: absolute;
    right: -5px;
    top: 50%;
    transform: translateY(-50%) rotate(90deg);
    background-color: #f59e0b;
    color: white;
    font-weight: 600;
    font-size: 0.7rem;
    padding: 0.25rem 1rem;
    border-radius: 0 0 4px 4px;
    letter-spacing: 1px;
}

/* Oceny gwiazdkowe */
.reviews-table td:nth-child(4) {
    color: #fbbf24;
    font-size: 1.25rem;
    letter-spacing: 2px;
}

/* Przyciski akcji */
.actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.btn-approve, .btn-delete {
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.85rem;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: none;
    cursor: pointer;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    min-width: 90px;
}

.btn-approve {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
}

.btn-approve:hover {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);
}

.btn-delete {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
}

.btn-delete:hover {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3);
}

/* Responsywność */
@media (max-width: 768px) {
    .admin-container {
        padding: 1rem;
        margin: 1rem;
    }
    
    .reviews-table {
        display: block;
        overflow-x: auto;
    }
    
    .reviews-table thead {
        display: none;
    }
    
    .reviews-table tbody tr {
        display: block;
        margin-bottom: 1rem;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        position: relative;
        padding-top: 2.5rem;
    }
    
    .reviews-table td {
        display: block;
        text-align: right;
        padding-left: 50%;
        position: relative;
        border-bottom: none;
    }
    
    .reviews-table td::before {
        content: attr(data-label);
        position: absolute;
        left: 1rem;
        width: calc(50% - 1rem);
        padding-right: 1rem;
        text-align: left;
        font-weight: 600;
        color: #8b5cf6;
    }
    
    .reviews-table td:first-child {
        position: absolute;
        top: 0;
        left: 0;
        background: #8b5cf6;
        color: white !important;
        padding: 0.5rem 1rem;
        border-top-left-radius: 8px;
    }
    
    .reviews-table td:last-child {
        text-align: center;
        padding-left: 1rem;
        border-top: 1px solid #f1f5f9;
    }
    
    .reviews-table td:last-child::before {
        display: none;
    }
    
    .actions {
        justify-content: center;
    }
    
    .reviews-table tr.pending::after {
        transform: translateY(0) rotate(0);
        right: 0;
        top: 0;
        border-radius: 0 8px 0 4px;
    }
}

/* Animacje */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.reviews-table tbody tr {
    animation: fadeIn 0.4s ease forwards;
}

/* Sekwencja animacji */
.reviews-table tbody tr:nth-child(1) { animation-delay: 0.1s; }
.reviews-table tbody tr:nth-child(2) { animation-delay: 0.2s; }
.reviews-table tbody tr:nth-child(3) { animation-delay: 0.3s; }
.reviews-table tbody tr:nth-child(4) { animation-delay: 0.4s; }
</style>

<style>
.back-to-dashboard {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    background: linear-gradient(135deg, #4a6baf 0%, #3a5a9f 100%);
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-family: 'Segoe UI', sans-serif;
    font-weight: 600;
    margin: 20px 0;
    transition: all 0.3s ease;
    box-shadow: 0 3px 6px rgba(0,0,0,0.1);
    border: none;
    cursor: pointer;
}

.back-to-dashboard:hover {
    background: linear-gradient(135deg, #3a5a9f 0%, #2a4a8f 100%);
    transform: translateY(-2px);
    box-shadow: 0 5px 10px rgba(0,0,0,0.15);
}

.back-to-dashboard:active {
    transform: translateY(0);
}

.back-to-dashboard svg {
    margin-right: 5px;
}
</style>
</body>
</html>
