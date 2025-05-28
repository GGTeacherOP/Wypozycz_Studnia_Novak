<?php
session_start();
require_once 'includes/config.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$reservation_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Pobierz dane rezerwacji i faktury
$query = "SELECT r.*, v.make, v.model, v.type, 
                 CONCAT(u.first_name, ' ', u.last_name) AS customer_name,
                 u.email, u.phone
          FROM reservations r
          JOIN vehicles v ON r.vehicle_id = v.vehicle_id
          JOIN users u ON r.user_id = u.user_id
          WHERE r.reservation_id = ? AND r.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $reservation_id, $_SESSION['user_id']);
$stmt->execute();
$reservation = $stmt->get_result()->fetch_assoc();

if(!$reservation || !$reservation['invoice_request']) {
    header("Location: my_reservations.php");
    exit();
}

$invoice_data = json_decode($reservation['invoice_data'], true);

// Ustawienia dla PDF (jeśli chcesz generować PDF)
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Faktura #<?= $reservation_id ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .invoice-container { max-width: 800px; margin: 0 auto; border: 1px solid #ddd; padding: 20px; }
        .header { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .invoice-title { font-size: 24px; font-weight: bold; }
        .invoice-details { margin-bottom: 30px; }
        .company-info, .client-info { margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .total { font-weight: bold; font-size: 18px; }
        .footer { margin-top: 50px; font-size: 12px; text-align: center; }
        .print-button { margin-top: 20px; text-align: center; }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="header">
            <div>
                <h1>SkyDrive Rentals</h1>
                <p>ul. Lotnicza 15, 00-001 Warszawa</p>
                <p>NIP: 1234567890</p>
            </div>
            <div>
                <div class="invoice-title">FAKTURA VAT</div>
                <p>Nr: FV/<?= date('Y') ?>/<?= str_pad($reservation_id, 5, '0', STR_PAD_LEFT) ?></p>
                <p>Data wystawienia: <?= date('d.m.Y') ?></p>
            </div>
        </div>
        
        <div class="invoice-details">
            <div class="company-info">
                <h3>Sprzedawca:</h3>
                <p>SkyDrive Rentals</p>
                <p>ul. Lotnicza 15, 00-001 Warszawa</p>
                <p>NIP: 1234567890</p>
            </div>
            
            <div class="client-info">
                <h3>Nabywca:</h3>
                <p><?= htmlspecialchars($invoice_data['company']) ?></p>
                <p>NIP: <?= htmlspecialchars($invoice_data['nip']) ?></p>
                <p>Adres: <?= htmlspecialchars($invoice_data['address']) ?></p>
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Lp.</th>
                    <th>Nazwa usługi</th>
                    <th>Okres wynajmu</th>
                    <th>Cena jednostkowa</th>
                    <th>Wartość</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>
                        Wynajem <?= htmlspecialchars($reservation['make'] . ' ' . $reservation['model']) ?><br>
                        <?= $reservation['type'] == 'car' ? 'Samochód' : 'Samolot' ?>
                    </td>
                    <td>
                        <?= date('d.m.Y H:i', strtotime($reservation['pickup_date'])) ?> - 
                        <?= date('d.m.Y H:i', strtotime($reservation['return_date'])) ?>
                    </td>
                    <td>
                        <?php if($reservation['type'] == 'car'): ?>
                            <?= number_format($reservation['total_cost'] / 
                                ceil((strtotime($reservation['return_date']) - strtotime($reservation['pickup_date'])) / (60*60*24)), 2) ?> PLN/dzień
                        <?php else: ?>
                            <?= number_format($reservation['total_cost'] / 
                                ceil((strtotime($reservation['return_date']) - strtotime($reservation['pickup_date'])) / (60*60)), 2) ?> PLN/godz.
                        <?php endif; ?>
                    </td>
                    <td><?= number_format($reservation['total_cost'], 2) ?> PLN</td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="total">RAZEM:</td>
                    <td class="total"><?= number_format($reservation['total_cost'], 2) ?> PLN</td>
                </tr>
                <tr>
                    <td colspan="4" class="total">KWOTA SŁOWNIE:</td>
                    <td><?= amountToWords($reservation['total_cost']) ?> PLN</td>
                </tr>
            </tfoot>
        </table>
        
        <div class="payment-info">
            <h3>Informacje o płatności:</h3>
            <p>Termin płatności: <?= date('d.m.Y', strtotime('+7 days')) ?></p>
            <p>Metoda płatności: <?= 
                $reservation['payment_method'] == 'credit_card' ? 'Karta kredytowa' : 
                ($reservation['payment_method'] == 'bank_transfer' ? 'Przelew bankowy' : 'Gotówka') 
            ?></p>
            <p>Nr konta: 12 3456 7890 1234 5678 9012 3456 (Bank Example)</p>
        </div>
        
        <div class="footer">
            <p>Faktura została wygenerowana automatycznie. Dziękujemy za skorzystanie z naszych usług.</p>
        </div>
        
        <div class="print-button">
            <button onclick="window.print()" class="btn btn-primary">Drukuj fakturę</button>
            <a href="my_reservations.php" class="btn btn-secondary">Powrót</a>
        </div>
    </div>
</body>
</html>