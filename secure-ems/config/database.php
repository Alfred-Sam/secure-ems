<?php
$host = '127.0.0.1';
$db   = 'secure_ems';
$user = 'root';
$pass = ''; // Default XAMPP kosong
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false, // WAJIB: Mencegah SQLi Bypass
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Jangan pernah menampilkan $e->getMessage() di production agar tidak leak info database
    die("Koneksi database gagal. Silakan hubungi administrator.");
}
?>
