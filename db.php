<?php
session_start();

$config = [
    'host' => 'localhost',
    'db'   => 'wypozyczalnia',
    'user' => 'root',
    'pass' => '',
    'charset' => 'utf8mb4'
];

$dsn = "mysql:host={$config['host']};dbname={$config['db']};charset={$config['charset']}";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $config['user'], $config['pass'], $options);
} catch (PDOException $e) {
    die("Błąd połączenia z bazą: " . $e->getMessage());
}