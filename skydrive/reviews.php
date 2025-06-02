<?php
session_start();
require_once 'includes/config.php';

// Dodawanie opinii
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $stmt = $db->prepare("INSERT INTO reviews (user_id, content, rating) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $_POST['content'], $_POST['rating']]);
    header("Location: reviews.php");
    exit;
}

// Pobieranie opinii
$reviews = $db->query("
    SELECT users.first_name, reviews.* 
    FROM reviews 
    JOIN users ON reviews.user_id = users.user_id 
    Where reviews.is_approved = 1
    ORDER BY created_at DESC
");
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Opinie - Wypożyczalnia Studnia Novak</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Style specyficzne dla opinii */
        .reviews-container { max-width: 800px; margin: 0 auto; }
        .review { background: #f8f9fa; padding: 20px; margin-bottom: 20px; border-radius: 8px; }
        .review-header { display: flex; justify-content: space-between; }
        .rating { color: #ffc107; font-size: 24px; }
        textarea { width: 100%; padding: 10px; margin-bottom: 10px; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="reviews-container">
        <h1>Opinie klientów</h1>

        <!-- Formularz opinii -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <form method="POST" class="review-form">
                <textarea name="content" placeholder="Napisz swoją opinię..." required></textarea>
                <div class="rating-select">
                    <label>Ocena:</label>
                    <select name="rating">
                        <option value="5">★★★★★</option>
                        <option value="4">★★★★☆</option>
                        <option value="3">★★★☆☆</option>
                        <option value="2">★★☆☆☆</option>
                        <option value="1">★☆☆☆☆</option>
                    </select>
                </div>
                <button type="submit" class="btn">Dodaj opinię</button>
            </form>
        <?php else: ?>
            <p><a href="login.php">Zaloguj się</a>, aby dodać opinię.</p>
        <?php endif; ?>

        <!-- Lista opinii -->
        <div class="reviews-list">
            <?php foreach ($reviews as $review): ?>
                <div class="review">
                    <div class="review-header">
                        <h3><?= htmlspecialchars($review['first_name']) ?></h3>
                        <div class="rating"><?= str_repeat('★', $review['rating']) ?></div>
                    </div>
                    <p><?= nl2br(htmlspecialchars($review['content'])) ?></p>
                    <small><?= date('d.m.Y H:i', strtotime($review['created_at'])) ?></small>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>