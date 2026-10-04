<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');

session_start();

require_once 'config/database.php';

// Paksa buat session login sebagai admin
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'admin';
$_SESSION['last_login_time'] = time();

echo "<h2 style='color: green;'>LOGIN DARURAT BERHASIL!</h2>";
echo "<p>Session telah dibuat secara manual.</p>";
echo "<p><a href='dashboard.php'>Klik di sini untuk buka Dashboard</a></p>";
?>