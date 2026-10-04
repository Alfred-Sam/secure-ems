<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');

session_start();

require_once 'config/database.php';
require_once 'includes/security_headers.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ----------------------------------------------------
// 1. GENERATE CSRF TOKEN (Mencegah Serangan CSRF)
// ----------------------------------------------------
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';
$error = '';

// ----------------------------------------------------
// 2. PROCESS FORM SUBMISSION (Tambah Karyawan)
// ----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_employee') {

    // VALIDASI CSRF TOKEN
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("CSRF Token Validation Failed! Akses ditolak.");
    }

    $emp_code = trim($_POST['emp_code'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $position = trim($_POST['position'] ?? '');

    if (!empty($emp_code) && !empty($full_name) && !empty($email) && !empty($position)) {
        // Validasi format email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Format email tidak valid!";
        } else {
            try {
                // ANTI-SQL INJECTION: Prepared Statement
                $stmt = $pdo->prepare("INSERT INTO employees (emp_code, full_name, email, position) VALUES (:code, :name, :email, :pos)");
                $stmt->execute([
                    'code' => $emp_code,
                    'name' => $full_name,
                    'email' => $email,
                    'pos' => $position
                ]);
                $message = "Data karyawan berhasil ditambahkan secara aman!";
            } catch (PDOException $e) {
                // Jika Kode Karyawan Duplikat
                $error = "Gagal menyimpan data: Kode Karyawan sudah ada.";
            }
        }
    } else {
        $error = "Semua kolom wajib diisi!";
    }
}

// ----------------------------------------------------
// 3. AMBIL DATA KARYAWAN DARI DATABASE
// ----------------------------------------------------
$stmt_list = $pdo->query("SELECT * FROM employees ORDER BY id DESC");
$employees = $stmt_list->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Secure EMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body class="bg-dark text-white">
    <nav class="navbar navbar-dark bg-secondary px-3">
        <span class="navbar-brand mb-0 h1">Secure EMS Dashboard</span>
        <div>
            <span class="me-3">Halo, <b><?= htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8') ?></b></span>
            <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Form Tambah Karyawan -->
            <div class="col-md-4 mb-4">
                <div class="card bg-secondary text-white shadow">
                    <div class="card-header border-bottom border-dark">
                        <h5 class="mb-0">Tambah Karyawan</h5>
                    </div>
                    <div class="card-body">
                        <form action="dashboard.php" method="POST">
                            <!-- Hidden Field CSRF Token -->
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <input type="hidden" name="action" value="add_employee">

                            <div class="mb-3">
                                <label class="form-label">Kode Karyawan (NIP)</label>
                                <input type="text" name="emp_code"
                                    class="form-control bg-dark text-white border-secondary" placeholder="EMP-001"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" name="full_name"
                                    class="form-control bg-dark text-white border-secondary" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email"
                                    class="form-control bg-dark text-white border-secondary" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Jabatan</label>
                                <input type="text" name="position"
                                    class="form-control bg-dark text-white border-secondary" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Simpan Data</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tabel Daftar Karyawan -->
            <div class="col-md-8">
                <div class="card bg-secondary text-white shadow">
                    <div class="card-header border-bottom border-dark">
                        <h5 class="mb-0">Daftar Karyawan</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-dark table-striped mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th>NIP</th>
                                        <th>Nama Lengkap</th>
                                        <th>Email</th>
                                        <th>Jabatan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($employees) > 0): ?>
                                        <?php foreach ($employees as $emp): ?>
                                            <tr>
                                                <!-- ANTI-XSS: Semua output wajib menggunakan htmlspecialchars (Keamanan)-->
                                                <td><?= htmlspecialchars($emp['emp_code'], ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars($emp['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars($emp['email'], ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars($emp['position'], ENT_QUOTES, 'UTF-8') ?></td>
                                                <td>
                                                    <!-- Tombol Aksi Upload Avatar -->
                                                    <a href="upload_avatar.php?id=<?= $emp['id'] ?>"
                                                        class="btn btn-sm btn-info">Avatar</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-3">Belum ada data karyawan.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>