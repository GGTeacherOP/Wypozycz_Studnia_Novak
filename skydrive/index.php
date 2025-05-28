<?php
session_start();
include 'includes/config.php';
include 'includes/header.php';
?>


<style>
.hero {
    background-size: cover;
    background-position: center;
    height: 90vh;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: #fff;
    position: relative;
}

.hero::after {
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.6);
    z-index: 1;
}

.hero-content {
    position: relative;
    z-index: 2;
}

.hero-content h1 {
    font-size: 4rem;
    font-weight: 800;
    margin-bottom: 20px;
}

.hero-content p {
    font-size: 1.5rem;
    margin-bottom: 30px;
}

.btn-primary {
    background-color: #0066cc;
    color: #fff;
    padding: 12px 30px;
    border-radius: 30px;
    font-weight: bold;
    text-decoration: none;
    transition: background 0.3s ease;
}

.btn-primary:hover {
    background-color: #004d99;
}

.features {
    padding: 80px 20px;
    background: #f9f9f9;
    text-align: center;
}

.features h2 {
    font-size: 2.5rem;
    margin-bottom: 40px;
    color: #2c3e50;
}

.feature-grid {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 40px;
}

.feature {
    background: #fff;
    padding: 30px;
    border-radius: 12px;
    width: 280px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
    transition: transform 0.3s ease;
}

.feature:hover {
    transform: translateY(-5px);
}

.feature i {
    font-size: 2.5rem;
    margin-bottom: 15px;
    color: #0066cc;
}

.feature h3 {
    font-size: 1.3rem;
    margin-bottom: 10px;
}

.feature p {
    color: #555;
    font-size: 0.95rem;
}

.popular-vehicles {
    padding: 80px 20px;
}

.popular-vehicles h2 {
    text-align: center;
    font-size: 2.5rem;
    margin-bottom: 40px;
    color: #2c3e50;
}

.vehicle-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 30px;
    max-width: 1200px;
    margin: 0 auto;
}

.vehicle-card {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
    transition: transform 0.3s ease;
    display: flex;
    flex-direction: column;
}

.vehicle-card:hover {
    transform: translateY(-5px);
}

.vehicle-card img {
    width: 100%;
    height: 180px;
    object-fit: cover;
}

.vehicle-card h3 {
    padding: 15px 20px 0;
    font-size: 1.2rem;
    color: #2c3e50;
}

.vehicle-card p {
    padding: 0 20px;
    font-size: 0.95rem;
    color: #555;
}

.vehicle-card .price {
    font-weight: bold;
    color: #0066cc;
    padding: 10px 20px;
}

.vehicle-card .btn-secondary {
    margin: 15px 20px 20px;
    background-color: #f0f0f0;
    padding: 10px 20px;
    border-radius: 20px;
    font-weight: bold;
    text-align: center;
    text-decoration: none;
    color: #0066cc;
    transition: background 0.3s ease;
}

.vehicle-card .btn-secondary:hover {
    background-color: #dbeaff;
}
</style>

<div class="hero" style="background-image: url(images/background.jpg);">
    <div class="hero-content">

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

                echo '<a href="vehicles.php?type=' . $row['type'] . '" class="btn-secondary">Zobacz więcej</a>';

                echo '<a href="vehicles.php?type=' . $row['type'] . '" class="btn btn-secondary">Zobacz więcej</a>';

                echo '</div>';
            }
            ?>
        </div>
    </div>
</section>


<?php include 'includes/footer.php'; ?>
=======
<?php include 'includes/footer.php'; ?>

