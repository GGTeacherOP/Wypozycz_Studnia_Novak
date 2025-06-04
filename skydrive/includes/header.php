<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle : 'SkyDrive Rentals' ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <a href="index.php">SkyDrive Rentals</a>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">Strona główna</a></li>
                    <li><a href="vehicles.php">Pojazdy</a></li>
                    
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li><a href="my_reservations.php">Moje rezerwacje</a></li>
                        <li>
                            <a href="logout.php" class="btn-logout">
                                <i class="fas fa-sign-out-alt"></i> Wyloguj
                            </a>
                        </li>
                        <?php if($_SESSION['is_admin'] == 1): ?>
                            <li>
                                <a href="admin/dashboard.php" class="btn-admin">
                                    <i class="fas fa-cog"></i> Panel admina
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li><a href="login.php">Zaloguj</a></li>
                        <li><a href="register.php">Zarejestruj</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>