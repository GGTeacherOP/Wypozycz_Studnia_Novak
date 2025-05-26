<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | SkyDrive</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <li><a href="payments.php" class="<?= basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'active' : '' ?>">
    <i class="fas fa-money-bill-wave"></i> Płatności
</a></li>
    <style>
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 48px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
        }
        
        .nav-link {
            color: rgba(255, 255, 255, .75);
            margin-bottom: 5px;
        }
        
        .nav-link:hover, .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, .1);
        }
        
        .nav-link i {
            margin-right: 10px;
        }
        
        main {
            padding-top: 1.5rem;
        }
    </style>
</head>
<body>