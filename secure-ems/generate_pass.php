<?php
// Jalankan fail ini melalui browser: http://localhost/secure-ems/generate_pass.php
$password_baru = 'admin';
$hash_baru = password_hash($password_baru, PASSWORD_BCRYPT);

echo "Copy hash ini dan masukkan ke dalam phpMyAdmin pada lajur password_hash:<br><br>";
echo "<b>" . $hash_baru . "</b>";
?>