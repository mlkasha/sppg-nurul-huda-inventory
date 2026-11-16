<?php
require_once __DIR__ . '/../../../config/security.php';
require_login();

// Ambil data dari session
$role = strtolower($_SESSION['role'] ?? '');
$nama = htmlspecialchars($_SESSION['nama_user'] ?? $_SESSION['username'] ?? 'User', ENT_QUOTES, 'UTF-8');
$currentPage = isset($_GET['page']) ? strtolower($_GET['page']) : 'dashboard';

// Judul halaman (jika belum didefinisikan di index.php)
$title = htmlspecialchars($title ?? "Dashboard", ENT_QUOTES, 'UTF-8');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> | SPPG Nurul Huda</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    
    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; overflow-x: hidden; }
        
        .sidebar { 
            width: 260px; 
            background: #0b2c5d; 
            height: 100vh; 
            position: fixed; 
            left: 0; 
            top: 0;
            display: flex;
            flex-direction: column; 
            z-index: 1000;
            transition: all 0.3s;
        }

        .nav-link { 
            color: rgba(255,255,255,0.7); 
            border-radius: 8px; 
            margin: 2px 10px; 
            padding: 10px 15px; 
            transition: all 0.3s;
            display: flex;
            align-items: center;
        }
        .nav-link i { width: 25px; text-align: center; }
        .nav-link:hover { color: #fff; background: rgba(255,255,255,0.1); }
        .nav-link.active { color: #fff !important; background: #0d6efd !important; font-weight: bold; box-shadow: 0 4px 6px rgba(0,0,0,0.2); }
        
        main { 
            margin-left: 260px; 
            width: calc(100% - 260px); 
            min-height: 100vh;
            transition: all 0.3s;
        }

        .header-top {
            background: white;
            padding: 12px 25px;
            border-bottom: 1px solid #e3e6f0;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        @media (max-width: 768px) {
            .sidebar { width: 70px; }
            .sidebar .text-link, .sidebar small, .sidebar h5, .sidebar .badge, .sidebar .text-uppercase { display: none; }
            main { margin-left: 70px; width: calc(100% - 70px); }
            .nav-link { justify-content: center; padding: 12px 0; margin: 5px; }
            .nav-link i { margin: 0 !important; font-size: 1.2rem; }
        }
    </style>
</head>
<body>

<div class="d-flex">
    <aside class="sidebar p-3 text-white shadow">
        <div class="mb-4 ps-2 mt-2">
            <h5 class="fw-bold mb-0 text-white"><i class="fas fa-box-open me-2 text-info"></i><span class="text-link">SPPG</span></h5>
            <small class="text-white-50">Nurul Huda Inventory</small>
        </div>

        <div class="mb-4 ps-2 border-bottom border-secondary pb-3">
            <div class="fw-semibold text-truncate text-white small"><?= $nama ?></div>
            <span class="badge bg-<?= ($role == 'kepala') ? 'warning text-dark' : 'info text-dark' ?>" style="font-size: 9px;">
                <i class="fas <?= ($role == 'kepala') ? 'fa-eye' : 'fa-user-cog' ?> me-1"></i><?= htmlspecialchars(strtoupper($role), ENT_QUOTES, 'UTF-8') ?>
            </span>
        </div>

        <ul class="nav flex-column flex-grow-1">
            <li class="nav-item">
                <a class="nav-link <?= ($currentPage === 'dashboard') ? 'active' : '' ?>" href="index.php?page=dashboard">
                    <i class="fas fa-chart-line me-2"></i> <span class="text-link">Dashboard</span>
                </a>
            </li>

            <li class="small text-white-50 mt-3 mb-2 ps-2 text-uppercase" style="font-size: 10px; letter-spacing: 1px;">Persediaan</li>
            <a class="nav-link <?= (strpos($currentPage, 'bahan_basah') === 0) ? 'active' : '' ?>" href="index.php?page=bahan_basah">
                <i class="fas fa-tint me-2 text-primary"></i> <span class="text-link">Bahan Basah</span>
            </a>
            <a class="nav-link <?= (strpos($currentPage, 'bahan_kering') === 0) ? 'active' : '' ?>" href="index.php?page=bahan_kering">
                <i class="fas fa-box me-2 text-warning"></i> <span class="text-link">Bahan Kering</span>
            </a>
            
            <li class="small text-white-50 mt-3 mb-2 ps-2 text-uppercase" style="font-size: 10px; letter-spacing: 1px;">Monitoring</li>
            <a class="nav-link <?= (strpos($currentPage, 'rekap') === 0) ? 'active' : '' ?>" href="index.php?page=rekap">
                <i class="fas fa-history me-2 text-info"></i> <span class="text-link">Rekap Mutasi</span>
            </a>

            <?php if ($role === 'admin') : ?>
                <li class="small text-white-50 mt-3 mb-2 ps-2 text-uppercase" style="font-size: 10px; letter-spacing: 1px;">Master Data</li>
                <a class="nav-link <?= (strpos($currentPage, 'supplier') === 0) ? 'active' : '' ?>" href="index.php?page=supplier">
                    <i class="fas fa-truck me-2"></i> <span class="text-link">Supplier</span>
                </a>
                <a class="nav-link <?= (strpos($currentPage, 'user') === 0) ? 'active' : '' ?>" href="index.php?page=user">
                    <i class="fas fa-user-shield me-2"></i> <span class="text-link">Manajemen User</span>
                </a>
            <?php endif; ?>
        </ul>

        <div class="mt-auto">
            <hr class="border-secondary mb-3">
            <a class="nav-link text-danger fw-bold mb-2" href="index.php?page=logout" onclick="return confirm('Apakah Anda yakin ingin keluar?')">
                <i class="fas fa-power-off me-2"></i> <span class="text-link">Keluar</span>
            </a>
        </div>
    </aside>

    <main>
        <header class="header-top d-flex justify-content-between align-items-center shadow-sm">
            <div>
                <h5 class="fw-bold mb-0 text-dark"><?= $title ?></h5>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="font-size: 11px;">
                        <li class="breadcrumb-item"><a href="index.php?page=dashboard" class="text-decoration-none">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?= $title ?></li>
                    </ol>
                </nav>
            </div>
            <div class="text-end border-start ps-3 d-none d-md-block">
                <div class="text-muted small fw-bold text-uppercase" style="font-size: 10px;"><?= date('l, d F Y') ?></div>
                <div class="fw-bold text-primary"><i class="far fa-clock me-1"></i><?= date('H:i') ?> WIB</div>
            </div>
        </header>

        <section class="p-4">
            <?php if (isset($_GET['msg'])): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4 py-3" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle fa-lg me-3"></i>
                        <div>
                            <?php 
                                switch ($_GET['msg']) {
                                    case 'success_store': echo "Data berhasil disimpan."; break;
                                    case 'success_update': echo "Perubahan berhasil diperbarui."; break;
                                    case 'success_delete': echo "Data berhasil dihapus."; break;
                                    default: echo "Operasi berhasil.";
                                }
                            ?>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['err'])): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4 py-3" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle fa-lg me-3"></i>
                        <div>
                            <?php 
                                switch ($_GET['err']) {
                                    case 'access_denied': echo "Akses ditolak! Anda tidak memiliki izin."; break;
                                    case 'csrf_invalid': echo "Token keamanan tidak valid. Silakan ulangi."; break;
                                    case 'stok_kurang': echo "Stok bahan tidak mencukupi untuk pemakaian ini."; break;
                                    default: echo "Terjadi kesalahan saat memproses data.";
                                }
                            ?>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php 
                if (isset($content) && file_exists($content)) {
                    include $content; 
                } else {
                    $safePage = htmlspecialchars($currentPage, ENT_QUOTES, 'UTF-8');
                    echo "
                    <div class='card shadow-sm border-0'>
                        <div class='card-body text-center py-5'>
                            <h4 class='text-muted fw-bold'>Halaman Tidak Ditemukan</h4>
                            <p class='text-secondary'>Modul <code>$safePage</code> tidak ditemukan.</p>
                            <a href='index.php?page=dashboard' class='btn btn-primary px-4'>Kembali ke Dashboard</a>
                        </div>
                    </div>";
                }
            ?>
        </section>
    </main>
</div>

<script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>