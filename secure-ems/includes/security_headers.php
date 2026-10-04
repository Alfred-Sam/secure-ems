<?php
// Mencegah Clickjacking
header("X-Frame-Options: DENY");

// Mencegah MIME-sniffing
header("X-Content-Type-Options: nosniff");

// Mengaktifkan CSP dasar
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' https://cdn.jsdelivr.net;");

// Mengontrol informasi Referrer
header("Referrer-Policy: strict-origin-when-cross-origin");
?>