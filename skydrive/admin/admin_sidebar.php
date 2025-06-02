<aside class="admin-sidebar">
    <nav>
        <ul>
            <li><a href="dashboard.php" <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'class="active"' : '' ?>>Dashboard</a></li>
            <li><a href="manage_vehicles.php" <?= basename($_SERVER['PHP_SELF']) == 'manage_vehicles.php' ? 'class="active"' : '' ?>>Pojazdy</a></li>
            <li><a href="manage_equipment.php" <?= basename($_SERVER['PHP_SELF']) == 'manage_equipment.php' ? 'class="active"' : '' ?>>Wyposażenie</a></li>
            <li><a href="manage_locations.php" <?= basename($_SERVER['PHP_SELF']) == 'manage_locations.php' ? 'class="active"' : '' ?>>Lokalizacje</a></li>
            <li><a href="manage_users.php" <?= basename($_SERVER['PHP_SELF']) == 'manage_users.php' ? 'class="active"' : '' ?>>Użytkownicy</a></li>
            <li><a href="manage_reservations.php" <?= basename($_SERVER['PHP_SELF']) == 'manage_reservations.php' ? 'class="active"' : '' ?>>Rezerwacje</a></li>
            <li><a href="admin_op.php" <?= basename($_SERVER['PHP_SELF']) == 'admin_op.php' ? 'class="active"' : '' ?>>Opinie</a></li>
            <li><a href="../logout.php">Wyloguj</a></li>
        </ul>
    </nav>
</aside>