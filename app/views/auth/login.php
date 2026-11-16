<?php
require_once __DIR__ . '/../../../config/security.php';

$success = $_GET['success'] ?? '';
$error   = $_GET['error'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | SPPG Nurul Huda</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="login-page">

    <div class="login-content">
        <div class="container vh-100 d-flex align-items-center justify-content-center">
            <div class="card login-card shadow border-0">
                <div class="row g-0">

                    <!-- ILUSTRASI -->
                    <div class="col-md-6 d-none d-md-flex login-illustration">
                        <img src="assets/img/login.svg" class="login-img" alt="Ilustrasi Login">
                    </div>

                    <!-- FORM -->
                    <div class="col-md-6 login-form">
                        <div class="card-body p-5">

                            <div class="text-center mb-4">
                                <img src="assets/img/Logo.png" alt="Logo SPPG" width="70" class="mb-2">
                                <h5 class="fw-bold mb-0">SPPG Nurul Huda</h5>
                                <small class="text-muted">Aplikasi Persediaan Bahan</small>
                            </div>

                            <h4 class="mb-2 text-center">Login Aplikasi</h4>
                            <p class="text-muted mb-4 text-center">
                                Silakan masuk untuk melanjutkan
                            </p>

                            <?php if ($error === '1'): ?>
                                <div class="alert alert-danger py-2 small">
                                    Username, Password, atau Role salah!
                                </div>
                            <?php elseif ($error === 'rate'): ?>
                                <div class="alert alert-warning py-2 small">
                                    Terlalu banyak percobaan login gagal. Silakan tunggu 1 menit.
                                </div>
                            <?php elseif ($error === 'csrf'): ?>
                                <div class="alert alert-danger py-2 small">
                                    Sesi formulir tidak valid. Silakan coba lagi.
                                </div>
                            <?php endif; ?>

                            <?php if ($success === 'registered'): ?>
                                <div class="alert alert-success py-2 small">
                                    Registrasi berhasil! Silakan login sebagai Petugas Gudang.
                                </div>
                            <?php elseif ($success === 'logout'): ?>
                                <div class="alert alert-info py-2 small">
                                    Anda telah berhasil keluar dari sistem.
                                </div>
                            <?php elseif ($success !== ''): ?>
                                <div class="alert alert-success py-2 small">
                                    Login berhasil 🎉
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="index.php?page=auth_login">
                                <?= csrf_field(); ?>

                                <div class="mb-3">
                                    <label class="form-label">Username</label>
                                    <input type="text" name="username" class="form-control" required autocomplete="username">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Password</label>
                                    <input type="password" name="password" class="form-control" required autocomplete="current-password">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Login Sebagai</label>
                                    <select name="role" class="form-select" required>
                                        <option value="">-- Pilih Role --</option>
                                        <option value="admin">Admin</option>
                                        <option value="gudang">Petugas Gudang</option>
                                        <option value="kepala">Kepala SPPG</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-login w-100 mb-3">
                                    Sign In
                                </button>

                                <div class="text-center small">
                                    <span class="text-muted">Belum punya akun?</span>
                                    <a href="index.php?page=register" class="text-decoration-none fw-bold">Daftar Akun</a>
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