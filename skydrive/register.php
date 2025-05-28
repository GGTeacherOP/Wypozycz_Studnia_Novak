<?php
include 'includes/config.php';
include 'includes/header.php';

$error = '';
$success = '';

if(isset($_POST['register'])) {
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $driverLicense = trim($_POST['driver_license']);
    $pilotLicense = trim($_POST['pilot_license']);

    // Walidacja
    if(empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
        $error = 'Wypełnij wszystkie wymagane pola';
    } elseif($password !== $confirmPassword) {
        $error = 'Hasła nie są identyczne';
    } elseif(strlen($password) < 8) {
        $error = 'Hasło musi mieć co najmniej 8 znaków';
    } else {
        $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if($stmt->num_rows > 0) {
            $error = 'Email jest już zarejestrowany';
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password_hash, phone, address, driver_license_number, pilot_license_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", $firstName, $lastName, $email, $passwordHash, $phone, $address, $driverLicense, $pilotLicense);
            
            if($stmt->execute()) {
                $success = 'Rejestracja zakończona sukcesem. Możesz się teraz zalogować.';
            } else {
                $error = 'Wystąpił błąd podczas rejestracji: ' . $conn->error;
            }
        }
    }
}
?>

<style>
.register-form {
    max-width: 600px;
    margin: 60px auto;
    padding: 30px 25px;
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

.register-form h1 {
    text-align: center;
    margin-bottom: 25px;
    color: #333;
    font-size: 26px;
}

.form-row {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}

.form-group {
    flex: 1;
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
    color: #444;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 15px;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #3f87a6;
    box-shadow: 0 0 5px rgba(63, 135, 166, 0.3);
}

.btn {
    width: 100%;
    padding: 12px;
    background-color: #3f87a6;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.btn:hover {
    background-color: #336c86;
}

.alert {
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 20px;
}

.alert.error {
    background-color: #ffe6e6;
    border-left: 4px solid #f44336;
    color: #b30000;
}

.alert.success {
    background-color: #e6ffe6;
    border-left: 4px solid #4CAF50;
    color: #006600;
}

.register-form p {
    text-align: center;
    margin-top: 16px;
}

.register-form a {
    color: #3f87a6;
    text-decoration: none;
}

.register-form a:hover {
    text-decoration: underline;
}
</style>

<div class="register-form">
    <h1>Rejestracja</h1>
    
    <?php if($error): ?>
        <div class="alert error"><?= $error ?></div>
    <?php endif; ?>
    
    <?php if($success): ?>
        <div class="alert success"><?= $success ?></div>
    <?php else: ?>
        <form method="post">
            <div class="form-row">
                <div class="form-group">
                    <label>Imię*</label>
                    <input type="text" name="first_name" value="<?= isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : '' ?>" required>
                </div>
                <div class="form-group">
                    <label>Nazwisko*</label>
                    <input type="text" name="last_name" value="<?= isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : '' ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>Email*</label>
                <input type="email" name="email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Hasło* (min. 8 znaków)</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Potwierdź hasło*</label>
                    <input type="password" name="confirm_password" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Telefon</label>
                    <input type="text" name="phone" value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>">
                </div>
                <div class="form-group">
                    <label>Numer prawa jazdy</label>
                    <input type="text" name="driver_license" value="<?= isset($_POST['driver_license']) ? htmlspecialchars($_POST['driver_license']) : '' ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label>Adres</label>
                <textarea name="address" rows="2"><?= isset($_POST['address']) ? htmlspecialchars($_POST['address']) : '' ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Numer licencji pilota (jeśli posiadasz)</label>
                <input type="text" name="pilot_license" value="<?= isset($_POST['pilot_license']) ? htmlspecialchars($_POST['pilot_license']) : '' ?>">
            </div>
            
            <button type="submit" name="register" class="btn">Zarejestruj się</button>
            <p>Masz już konto? <a href="login.php">Zaloguj się</a></p>
        </form>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
