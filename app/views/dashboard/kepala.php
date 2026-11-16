<?php
require_once __DIR__ . '/../../../config/security.php';
require_role(['kepala', 'admin'], 'index.php?page=dashboard');

// Hitung total nilai persediaan
$nilai_basah  = (float)(mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COALESCE(SUM(harga * quantity), 0) AS total FROM bahan_basah"))['total'] ?? 0);
$nilai_kering = (float)(mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COALESCE(SUM(harga * quantity), 0) AS total FROM bahan_kering"))['total'] ?? 0);
$total_aset   = $nilai_basah + $nilai_kering;

// Stok Menipis (< 10)
$menipis_basah  = (int)(mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM bahan_basah WHERE quantity < 10"))['jml'] ?? 0);
$menipis_kering = (int)(mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM bahan_kering WHERE quantity < 10"))['jml'] ?? 0);
$tot_menipis    = $menipis_basah + $menipis_kering;

// Atensi Kadaluarsa (Basah <= H-3, Kering <= H-30)
$exp_basah  = (int)(mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM bahan_basah WHERE tanggal_kadaluarsa <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)"))['jml'] ?? 0);
$exp_kering = (int)(mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM bahan_kering WHERE tanggal_kadaluarsa <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)"))['jml'] ?? 0);
$tot_exp    = $exp_basah + $exp_kering;
?>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-primary text-white h-100">
            <div class="card-body">
                <small class="text-white-50 text-uppercase fw-bold">Estimasi Nilai Persediaan</small>
                <h3 class="fw-bold mb-0 mt-1">Rp <?= number_format($total_aset, 0, ',', '.') ?></h3>
                <small class="text-white-50">Basah: Rp <?= number_format($nilai_basah, 0, ',', '.') ?> | Kering: Rp <?= number_format($nilai_kering, 0, ',', '.') ?></small>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-warning text-dark h-100">
            <div class="card-body">
                <small class="text-dark-50 text-uppercase fw-bold">Stok Menipis (&lt; 10)</small>
                <h3 class="fw-bold mb-0 mt-1"><?= $tot_menipis ?> <span class="fs-6 fw-normal">Item</span></h3>
                <small class="text-dark-50"><?= $menipis_basah ?> Basah | <?= $menipis_kering ?> Kering</small>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-danger text-white h-100">
            <div class="card-body">
                <small class="text-white-50 text-uppercase fw-bold">Atensi Kadaluarsa (H-3 / H-30)</small>
                <h3 class="fw-bold mb-0 mt-1"><?= $tot_exp ?> <span class="fs-6 fw-normal">Item</span></h3>
                <small class="text-white-50"><?= $exp_basah ?> Basah | <?= $exp_kering ?> Kering</small>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h6 class="m-0 fw-bold"><i class="fas fa-file-pdf text-danger me-2"></i>Pusat Unduh Laporan Eksekutif (PDF)</h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <a href="index.php?page=laporan_harian_pdf&tanggal=<?= date('Y-m-d') ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary w-100 py-3 text-start">
                    <i class="fas fa-calendar-day me-2"></i> <b>Laporan Mutasi Harian</b>
                    <div class="small text-muted ps-4">Rekap keluar/masuk hari ini</div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="index.php?page=laporan_mingguan_pdf" target="_blank" rel="noopener noreferrer" class="btn btn-outline-success w-100 py-3 text-start">
                    <i class="fas fa-calendar-week me-2"></i> <b>Laporan Mutasi Mingguan</b>
                    <div class="small text-muted ps-4">Rekap 7 hari terakhir</div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="index.php?page=laporan_bulanan_pdf&bulan=<?= date('m') ?>&tahun=<?= date('Y') ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-info text-dark w-100 py-3 text-start">
                    <i class="fas fa-calendar-alt me-2"></i> <b>Laporan Mutasi Bulanan</b>
                    <div class="small text-muted ps-4">Rekap bulan <?= date('F Y') ?></div>
                </a>
            </div>
            <div class="col-md-6">
                <a href="index.php?page=laporan_stok" target="_blank" rel="noopener noreferrer" class="btn btn-outline-warning text-dark w-100 py-3 text-start">
                    <i class="fas fa-exclamation-circle me-2"></i> <b>Laporan Stok Menipis (&lt; 10)</b>
                    <div class="small text-muted ps-4">Daftar bahan yang perlu segera dipesan ulang</div>
                </a>
            </div>
            <div class="col-md-6">
                <a href="index.php?page=laporan_expired" target="_blank" rel="noopener noreferrer" class="btn btn-outline-danger w-100 py-3 text-start">
                    <i class="fas fa-hourglass-end me-2"></i> <b>Laporan Bahan Kadaluarsa / Mendekati Expired</b>
                    <div class="small text-muted ps-4">Atensi H-3 Bahan Basah &amp; H-30 Bahan Kering</div>
                </a>
            </div>
        </div>
    </div>
</div>