<?php
session_start();
include 'includes/config.php';
include 'includes/header.php';
?>
<style>

/* Sekcja lokalizacji */
.locations {
    margin-top: 80px;
    background: white;
    padding: 50px;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

.locations h2 {
    text-align: center;
    font-size: 2rem;
    color: #2c3e50;
    margin-bottom: 40px;
    font-weight: 700;
    position: relative;
}

.locations h2::after {
    content: '';
    display: block;
    width: 60px;
    height: 3px;
    background: #0066cc;
    margin: 15px auto 0;
    border-radius: 2px;
}

.location-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
}

.location-card {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 30px;
    transition: all 0.3s ease;
    border-left: 4px solid #0066cc;
}

.location-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

.location-card h3 {
    font-size: 1.3rem;
    color: #2c3e50;
    margin-bottom: 15px;
    font-weight: 600;
}

.location-card p {
    margin-bottom: 10px;
    color: #555;
    display: flex;
    align-items: center;
}

.location-card p::before {
    content: '';
    display: inline-block;
    width: 6px;
    height: 6px;
    background: #0066cc;
    border-radius: 50%;
    margin-right: 10px;
}

.location-card p:last-child {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #eee;
    font-style: italic;
    color: #0066cc;
    font-weight: 500;
}

/* Responsywność */
@media (max-width: 768px) {
    .choice-grid {
        grid-template-columns: 1fr;
    }
    
    .choice-card {
        height: 250px;
    }
    
    .locations {
        padding: 30px 20px;
    }
    
    .location-grid {
        grid-template-columns: 1fr;
    }
}
</style>
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