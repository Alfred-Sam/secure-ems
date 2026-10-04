<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');

session_start();

require_once 'config/database.php';
require_once 'includes/security_headers.php';

$error = '';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $user_ip = $_SERVER['REMOTE_ADDR'];
    $current_time = time();

    // ANTI-BRUTE FORCE (Keamanan)
    $stmt_ip = $pdo->prepare("SELECT attempts, last_attempt FROM login_attempts WHERE ip_address = :ip");
    $stmt_ip->execute(['ip' => $user_ip]);
    $ip_data = $stmt_ip->fetch();

    $max_attempts = 5;
    $lockout_time = 15 * 60; 

    if ($ip_data && $ip_data['attempts'] >= $max_attempts) {
        $last_attempt_time = strtotime($ip_data['last_attempt']);
        $time_passed = $current_time - $last_attempt_time;

        if ($time_passed < $lockout_time) {
            $remaining = ceil(($lockout_time - $time_passed) / 60);
            $error = "Terlalu banyak percobaan gagal. Silakan coba lagi dalam $remaining menit.";
        } else {
            $stmt_reset = $pdo->prepare("UPDATE login_attempts SET attempts = 0 WHERE ip_address = :ip");
            $stmt_reset->execute(['ip' => $user_ip]);
            $ip_data['attempts'] = 0;
        }
    }

    if (empty($error)) {
        if (!empty($username) && !empty($password)) {
            // ANTI-SQL INJECTION
            $stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE username = :username");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                // Hapus rekap percobaan gagal saat sukses login
                $stmt_del = $pdo->prepare("DELETE FROM login_attempts WHERE ip_address = :ip");
                $stmt_del->execute(['ip' => $user_ip]);

                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['last_login_time'] = time();

                header("Location: dashboard.php");
                exit();
            } else {
                // Catat percobaan gagal
                $now_formatted = date('Y-m-d H:i:s', $current_time);
                if ($ip_data) {
                    $stmt_up = $pdo->prepare("UPDATE login_attempts SET attempts = attempts + 1, last_attempt = :now WHERE ip_address = :ip");
                    $stmt_up->execute(['now' => $now_formatted, 'ip' => $user_ip]);
                } else {
                    $stmt_in = $pdo->prepare("INSERT INTO login_attempts (ip_address, attempts, last_attempt) VALUES (:ip, 1, :now)");
                    $stmt_in->execute(['ip' => $user_ip, 'now' => $now_formatted]);
                }

                $error = "Username atau password salah!";
            }
        } else {
            $error = "Harap isi semua kolom!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Secure EMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-dark text-white d-flex align-items-center" style="min-height: 100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card bg-secondary text-white shadow-lg border-0">
                    <div class="card-body p-4">
                        <h4 class="card-title text-center mb-4">Secure EMS Login</h4>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger py-2" role="alert">
                                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                            </div>
                        <?php endif; ?>

                        <form action="login.php" method="POST" autocomplete="off">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" name="username" id="username" class="form-control bg-dark text-white border-secondary" placeholder="admin" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" name="password" id="password" class="form-control bg-dark text-white border-secondary" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Masuk</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>