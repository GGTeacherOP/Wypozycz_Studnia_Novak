<?php
include 'includes/config.php';
include 'includes/header.php';

// Pobierz dostępne samoloty
$query = "SELECT * FROM available_planes";
$result = $conn->query($query);
?>

<div class="vehicles-container">
    <h1>Dostępne samoloty</h1>
    
    <div class="filter-section">
        <form method="get">
            <div class="form-row">
                <div class="form-group">
                    <label>Lokalizacja:</label>
                    <select name="location">
                        <option value="">Wszystkie</option>
                        <?php
                        $locations = $conn->query("SELECT DISTINCT city FROM locations WHERE is_airport = 1");
                        while($loc = $locations->fetch_assoc()) {
                            $selected = (isset($_GET['location']) && $_GET['location'] == $loc['city']) ? 'selected' : '';
                            echo "<option value='{$loc['city']}' $selected>{$loc['city']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Pojemność:</label>
                    <select name="capacity">
                        <option value="">Dowolna</option>
                        <option value="4" <?= (isset($_GET['capacity']) && $_GET['capacity'] == '4') ? 'selected' : '' ?>>Do 4 osób</option>
                        <option value="9" <?= (isset($_GET['capacity']) && $_GET['capacity'] == '9') ? 'selected' : '' ?>>5-9 osób</option>
                        <option value="10" <?= (isset($_GET['capacity']) && $_GET['capacity'] == '10') ? 'selected' : '' ?>>10+ osób</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Filtruj</button>
                    <a href="planes.php" class="btn btn-secondary">Wyczyść</a>
                </div>
            </div>
        </form>
    </div>
    
    <div class="vehicles-grid">
        <?php
        // Budowanie zapytania z filtrami
        $query = "SELECT * FROM available_planes WHERE 1=1";
        
        if(isset($_GET['location']) && !empty($_GET['location'])) {
            $location = $conn->real_escape_string($_GET['location']);
            $query .= " AND city = '$location'";
        }
        
        if(isset($_GET['capacity']) && !empty($_GET['capacity'])) {
            $capacity = (int)$_GET['capacity'];
            if($capacity == 4) {
                $query .= " AND capacity <= 4";
            } elseif($capacity == 9) {
                $query .= " AND capacity BETWEEN 5 AND 9";
            } else {
                $query .= " AND capacity >= 10";
            }
        }
        
        $result = $conn->query($query);
        
        if($result->num_rows > 0) {
            while($plane = $result->fetch_assoc()) {
                echo '<div class="vehicle-card">';
                echo '<img src="' . $plane['image_path'] . '" alt="' . $plane['make'] . ' ' . $plane['model'] . '">';
                echo '<div class="vehicle-details">';
                echo '<h3>' . $plane['make'] . ' ' . $plane['model'] . ' (' . $plane['year'] . ')</h3>';
                echo '<p><i class="fas fa-users"></i> ' . $plane['capacity'] . ' osób</p>';
                echo '<p><i class="fas fa-tachometer-alt"></i> ' . $plane['max_speed'] . ' km/h</p>';
                echo '<p><i class="fas fa-gas-pump"></i> ' . $plane['fuel_type'] . '</p>';
                echo '<p><i class="fas fa-map-marked-alt"></i> ' . $plane['city'] . ' - ' . $plane['address'] . '</p>';
                echo '<p class="price">' . $plane['hourly_rate'] . ' PLN/godzina</p>';
                echo '<a href="reserve.php?type=plane&id=' . $plane['vehicle_id'] . '" class="btn btn-primary">Zarezerwuj</a>';
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo '<p class="no-results">Brak dostępnych samolotów dla wybranych filtrów.</p>';
        }
        ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>