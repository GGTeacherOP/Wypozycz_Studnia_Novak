<?php
require_once __DIR__ . '/admin_functions.php';
checkAdminAuth();

require_once __DIR__ . '/../includes/config.php';

// Pobierz statystyki
$stats = [
    'users' => $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0],
    'vehicles' => $conn->query("SELECT COUNT(*) FROM vehicles")->fetch_row()[0],
    'reservations' => $conn->query("SELECT COUNT(*) FROM reservations")->fetch_row()[0],
    'active_reservations' => $conn->query("SELECT COUNT(*) FROM reservations WHERE status = 'confirmed'")->fetch_row()[0]
];

// Ostatnie rezerwacje
$recent_reservations = $conn->query("
    SELECT r.reservation_id, v.make, v.model, u.first_name, u.last_name, r.pickup_date, r.status 
    FROM reservations r
    JOIN vehicles v ON r.vehicle_id = v.vehicle_id
    JOIN users u ON r.user_id = u.user_id
    ORDER BY r.created_at DESC LIMIT 5
");

require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/manage_users.php">
                            <i class="fas fa-users"></i> Użytkownicy
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../admin/pages/admin_op.php">
                            <i class="fas fa-users"></i> Opinie
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/manage_vehicles.php">
                            <i class="fas fa-car"></i> Pojazdy
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/manage_equipment.php">
                            <i class="fas fa-cogs"></i> Wyposażenie
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/manage_reservations.php">
                            <i class="fas fa-calendar-check"></i> Rezerwacje
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/manage_payments.php">
                            <i class="fas fa-calendar-check"></i> Platnosci
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Wyloguj
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <span class="btn btn-sm btn-outline-secondary">
                            Witaj, <?= htmlspecialchars($_SESSION['admin_name']) ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Statystyki -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-primary mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Użytkownicy</h5>
                            <p class="card-text display-4"><?= $stats['users'] ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Pojazdy</h5>
                            <p class="card-text display-4"><?= $stats['vehicles'] ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-info mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Rezerwacje</h5>
                            <p class="card-text display-4"><?= $stats['reservations'] ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Aktywne</h5>
                            <p class="card-text display-4"><?= $stats['active_reservations'] ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ostatnie rezerwacje -->
            <h2>Ostatnie rezerwacje</h2>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Pojazd</th>
                            <th>Klient</th>
                            <th>Data odbioru</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($res = $recent_reservations->fetch_assoc()): ?>
                        <tr>
                            <td><?= $res['reservation_id'] ?></td>
                            <td><?= htmlspecialchars($res['make']) ?> <?= htmlspecialchars($res['model']) ?></td>
                            <td><?= htmlspecialchars($res['first_name']) ?> <?= htmlspecialchars($res['last_name']) ?></td>
                            <td><?= date('d.m.Y H:i', strtotime($res['pickup_date'])) ?></td>
                            <td>
                                <span class="badge bg-<?= 
                                    $res['status'] == 'confirmed' ? 'success' : 
                                    ($res['status'] == 'pending' ? 'warning' : 'danger') 
                                ?>">
                                    <?= 
                                        $res['status'] == 'confirmed' ? 'Potwierdzona' : 
                                        ($res['status'] == 'pending' ? 'Oczekująca' : 'Anulowana') 
                                    ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/admin_footer.php';
?>