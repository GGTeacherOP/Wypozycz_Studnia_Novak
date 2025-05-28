<?php
session_start();
include 'includes/config.php';
include 'includes/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f2f6fa;
}

.vehicle-choice h1 {
    text-align: center;
    font-size: 2.5rem;
    color: #1a2b3c;
    margin: 50px 0 40px;
}

.wrapper {
    display: flex;
    gap: 40px;
    max-width: 1200px;
    margin: 0 auto 60px;
    padding: 0 20px;
    flex-wrap: wrap;
    z-index: 1;
    position: relative;
}

.option {
    flex: 1;
    min-width: 300px;
    height: 400px;
    border-radius: 16px;
    overflow: hidden;
    position: relative;
    background-size: cover;
    background-position: center;
    transition: transform 0.3s ease;
    cursor: pointer;
}

.option:hover {
    transform: scale(1.04);
}

.overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.3);
    z-index: 1;
}

.text {
    position: relative;
    z-index: 2;
    color: #fff;
    font-size: 3.5rem;
    font-weight:lighter;
    text-align: center;
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
    text-shadow: 1px 1px 3px black;
}

a {
    display: block;
    height: 100%;
    width: 100%;
    text-decoration: none;
    color: inherit;
}

.samolot { background-image: url(images/plane.jpg); }
.samochod { background-image: url(images/car.jpg); }

.locations {
    background: #ffffff;
    padding: 60px 40px;
    border-radius: 16px;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.07);
    max-width: 1200px;
    margin: 0 auto;
}

.locations h2 {
    text-align: center;
    font-size: 2.5rem;
    color: #1a2b3c;
    font-weight: 700;
    margin-bottom: 30px;
}

.filter-buttons {
    text-align: center;
    margin-bottom: 30px;
}

.filter-buttons button {
    background: #007bff;
    color: white;
    border: none;
    padding: 10px 20px;
    margin: 0 10px;
    border-radius: 20px;
    cursor: pointer;
    font-weight: 600;
    transition: background 0.3s;
}

.filter-buttons button:hover,
.filter-buttons button.active {
    background: #0056b3;
}

.location-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 30px;
}

.location-card {
    background: white;
    border-radius: 12px;
    padding: 25px 30px;
    border-left: 5px solid #007bff;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
    transition: all 0.3s ease-in-out;
    animation: fadeInUp 0.6s ease forwards;
    opacity: 0;
}

@keyframes fadeInUp {
    from {
        transform: translateY(30px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.location-card h3 {
    font-size: 1.5rem;
    color: #2c3e50;
    margin-bottom: 18px;
    font-weight: 600;
}

.location-card p {
    color: #444;
    font-size: 1rem;
    margin: 8px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.location-card p i {
    color: #007bff;
}

.location-card p:last-child {
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid #e2e8f0;
    font-style: italic;
    font-weight: 500;
    color: #007bff;
}

@media (max-width: 768px) {
    .wrapper {
        flex-direction: column;
    }

    .option {
        height: 300px;
    }

    .locations {
        padding: 40px 20px;
    }

    .locations h2 {
        font-size: 2rem;
    }
}
</style>


<div class="vehicle-choice">
    <div class="container">
        <h1>Wybierz typ pojazdu</h1>

        <div class="wrapper">
            <div class="option samolot">
                <a href="planes.php">
                    <div class="overlay"></div>
                    <div class="text">SKY</div>
                </a>
            </div>
            <div class="option samochod">
                <a href="cars.php">
                    <div class="overlay"></div>
                    <div class="text">DRIVE</div>
                </a>
            </div>
        </div>

        <div class="locations">
            <h2>Nasze lokalizacje</h2>
            <div class="filter-buttons">
                <button class="active" onclick="filterLocations('all')">Wszystkie</button>
                <button onclick="filterLocations('airport')">Lotniska</button>
                <button onclick="filterLocations('office')">Biura</button>
            </div>


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
                    $type = $row['is_airport'] ? 'airport' : 'office';
                    echo '<div class="location-card" data-type="'.$type.'">';
                    echo '<h3>' . $row['city'] . '</h3>';
                    echo '<p><i class="fas fa-map-marker-alt"></i>' . $row['address'] . '</p>';
                    echo '<p><i class="fas fa-phone"></i>' . $row['phone'] . '</p>';
                    echo '<p><i class="fas fa-building"></i>' . ($row['is_airport'] ? 'Lotnisko' : 'Biuro wynajmu') . '</p>';

                
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


<script>
function filterLocations(type) {
    document.querySelectorAll('.filter-buttons button').forEach(btn => btn.classList.remove('active'));
    if (type === 'all') {
        document.querySelectorAll('.location-card').forEach(el => el.style.display = 'block');
        document.querySelector('.filter-buttons button:nth-child(1)').classList.add('active');
    } else {
        document.querySelectorAll('.location-card').forEach(el => {
            el.style.display = el.dataset.type === type ? 'block' : 'none';
        });
        document.querySelector(`.filter-buttons button:nth-child(${type === 'airport' ? 2 : 3})`).classList.add('active');
    }
}
</script>






<?php include 'includes/footer.php'; ?>