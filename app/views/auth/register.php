<?php
require_once __DIR__ . '/../../../config/security.php';

$error = $_GET['error'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Akun | SPPG Nurul Huda</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="login-page">

    <div class="login-content">
        <div class="container min-vh-100 py-4 d-flex align-items-center justify-content-center">
            <div class="card login-card shadow border-0">
                <div class="row g-0">

                    <!-- ILUSTRASI -->
                    <div class="col-md-5 d-none d-md-flex login-illustration">
                        <img src="assets/img/login.svg" class="login-img" alt="Ilustrasi Registrasi">
                    </div>

                    <!-- FORM -->
                    <div class="col-md-7 login-form">
                        <div class="card-body p-4 p-md-5">

                            <div class="text-center mb-3">
                                <img src="assets/img/Logo.png" alt="Logo SPPG" width="60" class="mb-2">
                                <h5 class="fw-bold mb-0">SPPG Nurul Huda</h5>
                                <small class="text-muted">Registrasi Pengguna Baru</small>
                            </div>

                            <?php if ($error === 'exists'): ?>
                                <div class="alert alert-danger py-2 small">
                                    Username sudah digunakan, silakan pilih username lain.
                                </div>
                            <?php elseif ($error === 'mismatch'): ?>
                                <div class="alert alert-danger py-2 small">
                                    Konfirmasi password tidak cocok!
                                </div>
                            <?php elseif ($error === 'invalid'): ?>
                                <div class="alert alert-danger py-2 small">
                                    Pastikan semua field terisi dan password minimal 6 karakter.
                                </div>
                            <?php elseif ($error === 'csrf'): ?>
                                <div class="alert alert-danger py-2 small">
                                    Sesi formulir tidak valid. Silakan ulangi.
                                </div>
                            <?php elseif ($error === 'failed'): ?>
                                <div class="alert alert-danger py-2 small">
                                    Terjadi kesalahan saat menyimpan akun.
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="index.php?page=auth_register">
                                <?= csrf_field(); ?>

                                <div class="mb-2">
                                    <label class="form-label small fw-bold">Nama Lengkap</label>
                                    <input type="text" name="nama_lengkap" class="form-control" placeholder="Contoh: Budi Santoso" required>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label small fw-bold">Username</label>
                                        <input type="text" name="username" class="form-control" placeholder="Tanpa spasi" required>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label small fw-bold">No. HP / WhatsApp</label>
                                        <input type="text" name="no_hp" class="form-control" placeholder="0812xxxxxxxx">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-bold">Password</label>
                                        <input type="password" name="password" class="form-control" minlength="6" placeholder="Min. 6 karakter" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-bold">Ulangi Password</label>
                                        <input type="password" name="konfirmasi_password" class="form-control" minlength="6" placeholder="Ulangi password" required>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-login w-100 mb-3">
                                    Daftar Sekarang
                                </button>

                                <div class="text-center small">
                                    <span class="text-muted">Sudah punya akun?</span>
                                    <a href="index.php?page=login" class="text-decoration-none fw-bold">Login di sini</a>
                                </div>
                            </form>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

</body>
</html>