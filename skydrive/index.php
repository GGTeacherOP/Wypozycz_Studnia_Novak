<?php
include 'includes/config.php';
include 'includes/header.php';
?>

<div class="hero" style="background-image: url(images/background.jpg);">
    <div class="hero-content" >
        <h1>SkyDrive Rentals</h1>
        <p>Wynajmij samochód lub samolot dla swoich potrzeb</p>
        <a href="vehicles.php" class="btn btn-primary">Zarezerwuj teraz</a>
    </div>
</div>

<section class="features">
    <div class="container">
        <h2>Dlaczego warto wybrać SkyDrive?</h2>
        <div class="feature-grid">
            <div class="feature">
                <i class="fas fa-car"></i>
                <h3>Luksusowe samochody</h3>
                <p>Nowoczesne pojazdy premium z pełnym ubezpieczeniem.</p>
            </div>
            <div class="feature">
                <i class="fas fa-plane"></i>
                <h3>Profesjonalne samoloty</h3>
                <p>Bezpieczne maszyny dla biznesu i turystyki.</p>
            </div>
            <div class="feature">
                <i class="fas fa-map-marker-alt"></i>
                <h3>Wiele lokalizacji</h3>
                <p>Odbiór i zwrot w różnych miastach i na lotniskach.</p>
            </div>
        </div>
    </div>
</section>

<section class="popular-vehicles">
    <div class="container">
        <h2>Nasze popularne pojazdy</h2>
        <div class="vehicle-grid">
            <?php
            $query = "SELECT * FROM vehicles WHERE available = 1 ORDER BY RAND() LIMIT 3";
            $result = $conn->query($query);
            
            while($row = $result->fetch_assoc()) {
                echo '<div class="vehicle-card">';
                echo '<img src="' . $row['image_path'] . '" alt="' . $row['make'] . ' ' . $row['model'] . '">';
                echo '<h3>' . $row['make'] . ' ' . $row['model'] . '</h3>';
                echo '<p>' . ($row['type'] == 'car' ? 'Samochód' : 'Samolot') . ' • ' . $row['year'] . '</p>';
                echo '<p class="price">' . ($row['type'] == 'car' ? 'od ' . $row['daily_rate'] . ' PLN/dzień' : 'od ' . $row['hourly_rate'] . ' PLN/godzina') . '</p>';
                echo '<a href="vehicles.php?type=' . $row['type'] . '" class="btn btn-secondary">Zobacz więcej</a>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>