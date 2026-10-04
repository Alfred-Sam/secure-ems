<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');

session_start();

require_once 'config/database.php';
require_once 'includes/security_headers.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$emp_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$emp_id) {
    header("Location: dashboard.php");
    exit();
}

// Ambil data karyawan
$stmt = $pdo->prepare("SELECT * FROM employees WHERE id = :id");
$stmt->execute(['id' => $emp_id]);
$employee = $stmt->fetch();

if (!$employee) {
    die("Karyawan tidak ditemukan!");
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi Token CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("CSRF Token Validation Failed!");
    }

    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $file_tmp  = $_FILES['avatar']['tmp_name'];
        $file_name = $_FILES['avatar']['name'];
        $file_size = $_FILES['avatar']['size'];

        // 1. Validasi Ukuran File (Maksimal 2MB)
        if ($file_size > 2 * 1024 * 1024) {
            $error = "Ukuran file terlalu besar! Maksimal 2MB.";
        } else {
            // 2. Validasi Ekstensi File
            $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if (!in_array($file_ext, $allowed_exts)) {
                $error = "Ekstensi file tidak diizinkan! Hanya JPG, JPEG, PNG, dan WEBP.";
            } else {
                // 3. Validasi MIME Type Asli (Magic Bytes Inspection)
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($finfo, $file_tmp);
                finfo_close($finfo);

                $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];

                if (!in_array($mime_type, $allowed_mimes)) {
                    $error = "Tipe file tidak valid! File harus berupa gambar asli.";
                } else {
                    // 4. Generate Nama File Unik Acak (Anti-Webshell / Anti-Overwrite)
                    $new_filename = bin2hex(random_bytes(16)) . '.' . $file_ext;
                    $upload_dir = __DIR__ . '/uploads/';
                    $destination = $upload_dir . $new_filename;

                    if (move_uploaded_file($file_tmp, $destination)) {
                        // Hapus avatar lama jika bukan default
                        if ($employee['avatar'] !== 'default.png' && file_exists($upload_dir . $employee['avatar'])) {
                            unlink($upload_dir . $employee['avatar']);
                        }

                        // Update nama avatar di database
                        $stmt_update = $pdo->prepare("UPDATE employees SET avatar = :avatar WHERE id = :id");
                        $stmt_update->execute(['avatar' => $new_filename, 'id' => $emp_id]);

                        $message = "Foto avatar berhasil diperbarui secara aman!";
                        $employee['avatar'] = $new_filename;
                    } else {
                        $error = "Gagal mengunggah file ke server.";
                    }
                }
            }
        }
    } else {
        $error = "Pilih file gambar terlebih dahulu!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Avatar - Secure EMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-dark text-white">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card bg-secondary text-white shadow">
                    <div class="card-header d-flex justify-between align-items-center">
                        <h5 class="mb-0">Upload Avatar Karyawan</h5>
                        <a href="dashboard.php" class="btn btn-sm btn-outline-light">Kembali</a>
                    </div>
                    <div class="card-body text-center">
                        <?php if ($message): ?>
                            <div class="alert alert-success py-2"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="alert alert-danger py-2"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <p>Karyawan: <b><?= htmlspecialchars($employee['full_name'], ENT_QUOTES, 'UTF-8') ?></b> (<?= htmlspecialchars($employee['emp_code'], ENT_QUOTES, 'UTF-8') ?>)</p>
                            
                            <?php if ($employee['avatar'] !== 'default.png'): ?>
                                <img src="uploads/<?= htmlspecialchars($employee['avatar'], ENT_QUOTES, 'UTF-8') ?>" class="img-thumbnail rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                            <?php else: ?>
                                <div class="p-4 bg-dark rounded-circle d-inline-block mb-3">Tidak Ada Avatar</div>
                            <?php endif; ?>
                        </div>

                        <form action="upload_avatar.php?id=<?= $emp_id ?>" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <div class="mb-3">
                                <input type="file" name="avatar" class="form-control bg-dark text-white border-secondary" accept="image/*" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Unggah Foto Aman</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>