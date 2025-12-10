<?php
// includes/db.php
// PDO connection - edit these if your MySQL credentials differ.

$DB_HOST = '127.0.0.1';
$DB_NAME = 'appointment_system';
$DB_USER = 'root';
$DB_PASS = '';

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO("mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4", $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
    // Display a friendly message; in production log it instead.
    die("Database connection failed: " . $e->getMessage());
}
