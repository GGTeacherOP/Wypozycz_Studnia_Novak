<?php
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "skydriverentals";

// Utwórz połączenie
$conn = new mysqli($servername, $username, $password, $dbname);

// Sprawdź połączenie
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ustaw kodowanie
$conn->set_charset("utf8mb4");

function amountToWords($number) {
    $words = [
        0 => 'zero',
        1 => 'jeden',
        2 => 'dwa',
        3 => 'trzy',
        4 => 'cztery',
        5 => 'pięć',
        6 => 'sześć',
        7 => 'siedem',
        8 => 'osiem',
        9 => 'dziewięć',
        10 => 'dziesięć',
        11 => 'jedenaście',
        12 => 'dwanaście',
        13 => 'trzynaście',
        14 => 'czternaście',
        15 => 'piętnaście',
        16 => 'szesnaście',
        17 => 'siedemnaście',
        18 => 'osiemnaście',
        19 => 'dziewiętnaście',
        20 => 'dwadzieścia',
        30 => 'trzydzieści',
        40 => 'czterdzieści',
        50 => 'pięćdziesiąt',
        60 => 'sześćdziesiąt',
        70 => 'siedemdziesiąt',
        80 => 'osiemdziesiąt',
        90 => 'dziewięćdziesiąt',
        100 => 'sto',
        200 => 'dwieście',
        300 => 'trzysta',
        400 => 'czterysta',
        500 => 'pięćset',
        600 => 'sześćset',
        700 => 'siedemset',
        800 => 'osiemset',
        900 => 'dziewięćset'
    ];

    $zl = floor($number);
    $gr = round(($number - $zl) * 100);

    $result = '';
    
    if ($zl == 0) {
        $result .= 'zero';
    } else {
        if ($zl >= 100) {
            $hundreds = floor($zl / 100) * 100;
            $result .= $words[$hundreds] . ' ';
            $zl -= $hundreds;
        }
        
        if ($zl > 0) {
            if ($zl <= 20) {
                $result .= $words[$zl];
            } else {
                $tens = floor($zl / 10) * 10;
                $units = $zl % 10;
                $result .= $words[$tens];
                if ($units > 0) {
                    $result .= ' ' . $words[$units];
                }
            }
        }
    }

    $result .= ' złotych';
    
    if ($gr > 0) {
        $result .= ' i ';
        if ($gr <= 20) {
            $result .= $words[$gr];
        } else {
            $tens = floor($gr / 10) * 10;
            $units = $gr % 10;
            $result .= $words[$tens];
            if ($units > 0) {
                $result .= ' ' . $words[$units];
            }
        }
        $result .= ' groszy';
    }
    
    return ucfirst($result);
}
$db = new PDO('mysql:host=localhost;dbname=skydriverentals', 'root', '');
?>
