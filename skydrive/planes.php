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
    <h1>Dostępne samoloty</h1>


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
        // Filtrowanie zapytania

    
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

                echo '<p><i class="fas fa-users"></i>' . $plane['capacity'] . ' osób</p>';
                echo '<p><i class="fas fa-tachometer-alt"></i>' . $plane['max_speed'] . ' km/h</p>';
                echo '<p><i class="fas fa-gas-pump"></i>' . $plane['fuel_type'] . '</p>';
                echo '<p><i class="fas fa-map-marked-alt"></i>' . $plane['city'] . ' - ' . $plane['address'] . '</p>';

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

<?php include 'includes/footer.php'; ?>

