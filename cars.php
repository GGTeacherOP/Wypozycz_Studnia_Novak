<?php
include 'includes/config.php';
include 'includes/header.php';

// Pobierz dostępne samochody
$query = "SELECT * FROM available_cars";
$result = $conn->query($query);
?>

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