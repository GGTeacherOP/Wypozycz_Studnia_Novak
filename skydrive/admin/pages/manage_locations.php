<?php
include '../includes/auth.php';
include '../../includes/config.php';

// Dodawanie lokalizacji
if(isset($_POST['add_location'])) {
    $city = trim($_POST['city']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $is_airport = isset($_POST['is_airport']) ? 1 : 0;

    $query = "INSERT INTO locations (city, address, phone, email, is_airport) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssi", $city, $address, $phone, $email, $is_airport);
    
    if($stmt->execute()) {
        $_SESSION['success'] = "Lokalizacja została dodana pomyślnie";
    } else {
        $_SESSION['error'] = "Błąd podczas dodawania lokalizacji: " . $conn->error;
    }
    header("Location: manage_locations.php");
    exit();
}

// Usuwanie lokalizacji
if(isset($_GET['delete'])) {
    $location_id = intval($_GET['delete']);
    
    // Sprawdź czy lokalizacja nie jest używana
    $check_query = "SELECT COUNT(*) as count FROM vehicles WHERE location_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("i", $location_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if($result['count'] > 0) {
        $_SESSION['error'] = "Nie można usunąć lokalizacji, ponieważ jest przypisana do pojazdów";
    } else {
        $query = "DELETE FROM locations WHERE location_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $location_id);
        
        if($stmt->execute()) {
            $_SESSION['success'] = "Lokalizacja została usunięta pomyślnie";
        } else {
            $_SESSION['error'] = "Błąd podczas usuwania lokalizacji: " . $conn->error;
        }
    }
    header("Location: manage_locations.php");
    exit();
}

// Edycja lokalizacji
if(isset($_POST['edit_location'])) {
    $location_id = intval($_POST['location_id']);
    $city = trim($_POST['city']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $is_airport = isset($_POST['is_airport']) ? 1 : 0;

    $query = "UPDATE locations SET city=?, address=?, phone=?, email=?, is_airport=? WHERE location_id=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssii", $city, $address, $phone, $email, $is_airport, $location_id);
    
    if($stmt->execute()) {
        $_SESSION['success'] = "Lokalizacja została zaktualizowana pomyślnie";
    } else {
        $_SESSION['error'] = "Błąd podczas aktualizacji lokalizacji: " . $conn->error;
    }
    header("Location: manage_locations.php");
    exit();
}

// Pobieranie lokalizacji do edycji
$edit_location = null;
if(isset($_GET['edit'])) {
    $location_id = intval($_GET['edit']);
    $query = "SELECT * FROM locations WHERE location_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $location_id);
    $stmt->execute();
    $edit_location = $stmt->get_result()->fetch_assoc();
}

// Pobieranie listy lokalizacji
$locations = $conn->query("SELECT * FROM locations");
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Zarządzanie lokalizacjami</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="admin-container">
        <?php include 'admin_sidebar.php'; ?>
        
        <main class="admin-content">
            <h1>Zarządzanie lokalizacjami</h1>
            
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            
            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            
            <section class="<?= $edit_location ? 'edit-location' : 'add-location' ?>">
                <h2><?= $edit_location ? 'Edytuj lokalizację' : 'Dodaj nową lokalizację' ?></h2>
                <form method="post">
                    <?php if($edit_location): ?>
                        <input type="hidden" name="location_id" value="<?= $edit_location['location_id'] ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>Miasto:</label>
                        <input type="text" name="city" value="<?= $edit_location ? $edit_location['city'] : '' ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Adres:</label>
                        <textarea name="address" rows="2" required><?= $edit_location ? $edit_location['address'] : '' ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Telefon:</label>
                            <input type="text" name="phone" value="<?= $edit_location ? $edit_location['phone'] : '' ?>">
                        </div>
                        <div class="form-group">
                            <label>Email:</label>
                            <input type="email" name="email" value="<?= $edit_location ? $edit_location['email'] : '' ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_airport" <?= ($edit_location && $edit_location['is_airport'] == 1) ? 'checked' : '' ?>>
                            Czy to lotnisko?
                        </label>
                    </div>
                    
                    <div class="form-actions">
                        <?php if($edit_location): ?>
                            <button type="submit" name="edit_location" class="btn btn-primary">Zapisz zmiany</button>
                            <a href="manage_locations.php" class="btn btn-secondary">Anuluj</a>
                        <?php else: ?>
                            <button type="submit" name="add_location" class="btn btn-primary">Dodaj lokalizację</button>
                        <?php endif; ?>
                    </div>
                </form>
            </section>
            
            <section class="location-list">
                <h2>Lista lokalizacji</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Miasto</th>
                            <th>Adres</th>
                            <th>Telefon</th>
                            <th>Typ</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($loc = $locations->fetch_assoc()): ?>
                            <tr>
                                <td><?= $loc['location_id'] ?></td>
                                <td><?= $loc['city'] ?></td>
                                <td><?= $loc['address'] ?></td>
                                <td><?= $loc['phone'] ?></td>
                                <td><?= $loc['is_airport'] ? 'Lotnisko' : 'Biuro' ?></td>
                                <td class="actions">
                                    <a href="?edit=<?= $loc['location_id'] ?>" class="btn btn-edit">Edytuj</a>
                                    <a href="?delete=<?= $loc['location_id'] ?>" class="btn btn-delete" 
                                       onclick="return confirm('Czy na pewno chcesz usunąć tę lokalizację?')">Usuń</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
    
    <?php include '../includes/admin_footer.php'; ?>
</body>
</html>