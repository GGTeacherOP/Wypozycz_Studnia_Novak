<?php 
require 'includes/db.php';
require 'includes/header.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}
?>

<section class="dashboard">
    <h1>Witaj, <?= htmlspecialchars($_SESSION['user_name']) ?>!</h1>
    
    <div class="calendar-container">
        <select id="car-select" class="form-control">
            <?php 
            $cars = $pdo->query("SELECT car_id, brand, model FROM cars")->fetchAll();
            foreach($cars as $car): 
            ?>
                <option value="<?= $car['car_id'] ?>">
                    <?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <div id="calendar"></div>
    </div>
</section>

<link rel="stylesheet" href="/css/calendar.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="/js/calendar.js"></script>

<?php require 'includes/footer.php'; ?>