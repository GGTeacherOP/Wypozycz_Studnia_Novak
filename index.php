<?php require 'php/includes/header.php'; ?>

<main>
    <!-- Hero section (już masz) -->
    <section class="hero">
        <!-- ... istniejąca zawartość ... -->
        <!-- Dodaj przycisk do logowania -->
        <a href="php/auth/login.php" class="btn btn-login">Zaloguj się</a>
    </section>

    <!-- Sekcja floty (już masz) -->
    <!-- Sekcja kalkulatora (już masz) -->
</main>

<?php 
// Podłącz tylko te skrypty, które są potrzebne na stronie głównej
echo '<script src="js/main.js"></script>'; 

// Jeśli użytkownik jest zalogowany, pokaż link do dashboardu
if(isset($_SESSION['user_id'])) {
    echo '<script src="js/calendar.js"></script>';
    echo '<a href="php/dashboard.php" class="user-panel">Panel klienta</a>';
}

require 'php/includes/footer.php'; 
?>
