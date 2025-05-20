<?php
include 'includes/config.php';
include 'includes/header.php';
?>

<div class="vehicle-choice">
    <div class="container">
        <h1>Wybierz typ pojazdu</h1>
        <div class="choice-grid">
            <a href="cars.php" class="choice-card" style="background-image: url(images/car.jpg);">
                <div class="choice-content">
                    <i class="fas fa-car"></i>
                    <h2>Samochody</h2>
                    <p>Luksusowe auta na każdą okazję</p>
                </div>
            </a>
            <a href="planes.php" class="choice-card"style="background-image: url(images/plane.jpg);">
                <div class="choice-content" >
                    <i class="fas fa-plane"></i>
                    <h2>Samoloty</h2>
                    <p>Profesjonalne maszyny do wynajęcia</p>
                </div>
            </a>
        </div>
        
        <div class="locations">
            <h2>Nasze lokalizacje</h2>
            <div class="location-grid">
                <?php
                $query = "SELECT * FROM locations";
                $result = $conn->query($query);
                
                while($row = $result->fetch_assoc()) {
                    echo '<div class="location-card">';
                    echo '<h3>' . $row['city'] . '</h3>';
                    echo '<p>' . $row['address'] . '</p>';
                    echo '<p>' . $row['phone'] . '</p>';
                    echo '<p>' . ($row['is_airport'] ? 'Lotnisko' : 'Biuro wynajmu') . '</p>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>