<?php
require_once 'config/database.php';

$username = 'admin';
$password = 'admin123';
$hash = password_hash($password, PASSWORD_BCRYPT);

try {
    // Hapus user lama
    $stmt_del = $pdo->prepare("DELETE FROM users WHERE username = :username");
    $stmt_del->execute(['username' => $username]);

    // Masukkan user baru dengan hash asli PHP
    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (:username, :hash)");
    $stmt->execute(['username' => $username, 'hash' => $hash]);

    echo "<h2>AKUN SIAP!</h2>";
    echo "<p>Username: <b>admin</b></p>";
    echo "<p>Password: <b>admin123</b></p>";
    echo "<p><a href='login.php'>Klik untuk Login</a></p>";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>