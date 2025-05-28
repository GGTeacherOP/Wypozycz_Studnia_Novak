<?php
session_start();
include 'includes/config.php';
include 'includes/header.php';
?>
<link rel="stylesheet" href="style.css">

<style>
.vehicles-container {
    max-width: 1200px;
    margin: 40px auto;
    padding: 0 20px;
}

.vehicles-container h1 {
    text-align: center;
    font-size: 2.8rem;
    margin-bottom: 40px;
    color: #2c3e50;
}

.filter-section {
    margin-bottom: 40px;
    background: #f9f9f9;
    padding: 20px 30px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.form-row {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    align-items: flex-end;
}

.form-group {
    flex: 1;
    min-width: 180px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
}

select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 8px;
}

button.btn, a.btn {
    padding: 10px 20px;
    border-radius: 25px;
    font-weight: bold;
    text-decoration: none;
    display: inline-block;
    cursor: pointer;
}

.btn-primary {
    background-color: #0066cc;
    color: #fff;
    border: none;
}

.btn-secondary {
    background-color: #ddd;
    color: #333;
    border: none;
    margin-left: 10px;
}

.vehicles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 30px;
}

.vehicle-card {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 6px 20px rgba(0,0,0,0.05);
    display: flex;
    flex-direction: column;
    transition: transform 0.3s ease;
}

.vehicle-card:hover {
    transform: translateY(-5px);
}

.vehicle-card img {
    width: 100%;
    height: 180px;
    object-fit: cover;
}

.vehicle-details {
    padding: 20px;
    flex-grow: 1;
}

.vehicle-details h3 {
    font-size: 1.2rem;
    margin-bottom: 10px;
    color: #2c3e50;
}

.vehicle-details p {
    margin: 4px 0;
    color: #555;
    font-size: 0.95rem;
}

.vehicle-details i {
    color: #0066cc;
    margin-right: 8px;
}

.price {
    font-weight: bold;
    font-size: 1.1rem;
    color: #0066cc;
    margin-top: 10px;
}

.no-results {
    text-align: center;
    font-size: 1.2rem;
    color: #777;
}
</style>

<div class="vehicles-container">
    <h1>Dostępne samochody</h1>
    
    <div class="filter-section">
        <form method="get">
            <div class="form-row">
                <div class="form-group">
                    <label>Marka:</label>
                    <select name="make">
                        <option value="">Wszystkie</option>
                        <?php
                        $makes = $conn->query("SELECT DISTINCT make FROM available_cars");
                        while($make = $makes->fetch_assoc()) {
                            $selected = (isset($_GET['make']) && $_GET['make'] == $make['make']) ? 'selected' : '';
                            echo "<option value='{$make['make']}' $selected>{$make['make']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Lokalizacja:</label>
                    <select name="location">
                        <option value="">Wszystkie</option>
                        <?php
                        $locations = $conn->query("SELECT DISTINCT city FROM locations WHERE is_airport = 0");
                        while($loc = $locations->fetch_assoc()) {
                            $selected = (isset($_GET['location']) && $_GET['location'] == $loc['city']) ? 'selected' : '';
                            echo "<option value='{$loc['city']}' $selected>{$loc['city']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Cena:</label>
                    <select name="price">
                        <option value="">Dowolna</option>
                        <option value="400" <?= (isset($_GET['price']) && $_GET['price'] == '400') ? 'selected' : '' ?>>Do 400 PLN/dzień</option>
                        <option value="500" <?= (isset($_GET['price']) && $_GET['price'] == '500') ? 'selected' : '' ?>>400-500 PLN/dzień</option>
                        <option value="501" <?= (isset($_GET['price']) && $_GET['price'] == '501') ? 'selected' : '' ?>>Powyżej 500 PLN/dzień</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Filtruj</button>
                    <a href="cars.php" class="btn btn-secondary">Wyczyść</a>
                </div>
            </div>
        </form>
    </div>
    
    <div class="vehicles-grid">
        <?php
        // Budowanie zapytania z filtrami
        $query = "SELECT * FROM available_cars WHERE 1=1";
        
        if(isset($_GET['make']) && !empty($_GET['make'])) {
            $make = $conn->real_escape_string($_GET['make']);
            $query .= " AND make = '$make'";
        }
        
        if(isset($_GET['location']) && !empty($_GET['location'])) {
            $location = $conn->real_escape_string($_GET['location']);
            $query .= " AND city = '$location'";
        }
        
        if(isset($_GET['price']) && !empty($_GET['price'])) {
            $price = (int)$_GET['price'];
            if($price == 400) {
                $query .= " AND daily_rate <= 400";
            } elseif($price == 500) {
                $query .= " AND daily_rate BETWEEN 400 AND 500";
            } else {
                $query .= " AND daily_rate > 500";
            }
        }
        
        $result = $conn->query($query);
        
       if($result->num_rows > 0) {
    while($car = $result->fetch_assoc()) {
        echo '<div class="vehicle-card">';
        // Zmiana tutaj - używamy bezpośrednio URL z bazy danych zamiast ścieżki lokalnej
        echo '<img src="' . $car['image_path'] . '" alt="' . $car['make'] . ' ' . $car['model'] . '">';
        echo '<div class="vehicle-details">';
        echo '<h3>' . $car['make'] . ' ' . $car['model'] . ' (' . $car['year'] . ')</h3>';
        echo '<p><i class="fas fa-users"></i> ' . $car['capacity'] . ' osób</p>';
        echo '<p><i class="fas fa-gas-pump"></i> ' . $car['fuel_type'] . '</p>';
        echo '<p><i class="fas fa-horse-head"></i> ' . $car['engine_power'] . '</p>';
        echo '<p><i class="fas fa-map-marked-alt"></i> ' . $car['city'] . ' - ' . $car['address'] . '</p>';
        echo '<p class="price">' . $car['daily_rate'] . ' PLN/dzień</p>';
        echo '<a href="reserve.php?type=car&id=' . $car['vehicle_id'] . '" class="btn btn-primary">Zarezerwuj</a>';
        echo '</div>';
        echo '</div>';
    }
} else {
    echo '<p class="no-results">Brak dostępnych samochodów dla wybranych filtrów.</p>';
}
        ?>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
