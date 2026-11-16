<?php
require_once __DIR__ . '/../../../config/security.php';
require_role(['gudang', 'admin'], 'index.php?page=dashboard');

$tot_basah  = (int)(mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM bahan_basah"))['jml'] ?? 0);
$tot_kering = (int)(mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM bahan_kering"))['jml'] ?? 0);

$menipis_basah  = (int)(mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM bahan_basah WHERE quantity < 10"))['jml'] ?? 0);
$menipis_kering = (int)(mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM bahan_kering WHERE quantity < 10"))['jml'] ?? 0);
$tot_menipis    = $menipis_basah + $menipis_kering;

$exp_basah  = (int)(mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM bahan_basah WHERE tanggal_kadaluarsa <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)"))['jml'] ?? 0);
$exp_kering = (int)(mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM bahan_kering WHERE tanggal_kadaluarsa <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)"))['jml'] ?? 0);
$tot_exp    = $exp_basah + $exp_kering;

$tgl_hari_ini = date('Y-m-d');
$mutasi_hari_ini = (int)(mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM rekap_harian WHERE tanggal = '$tgl_hari_ini'"))['jml'] ?? 0);
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h5 class="fw-bold mb-0">Ringkasan Operasional Gudang</h5>
        <small class="text-muted">Pantau ketersediaan stok harian dan catat pemakaian bahan dapur</small>
    </div>
    <div class="d-flex gap-2">
        <a href="index.php?page=bahan_basah_tambah" class="btn btn-primary btn-sm shadow-sm">
            <i class="fas fa-plus me-1"></i> Input Bahan Basah
        </a>
        <a href="index.php?page=bahan_kering_tambah" class="btn btn-warning btn-sm shadow-sm text-dark fw-semibold">
            <i class="fas fa-plus me-1"></i> Input Bahan Kering
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-primary text-white h-100">
            <div class="card-body">
                <small class="text-white-50 text-uppercase fw-bold">Bahan Basah</small>
                <h3 class="fw-bold mb-0 mt-1"><?= $tot_basah ?> <span class="fs-6 fw-normal">Item</span></h3>
                <a href="index.php?page=bahan_basah" class="small text-white text-decoration-underline">Kelola Bahan Basah &rarr;</a>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-info text-dark h-100">
            <div class="card-body">
                <small class="text-dark-50 text-uppercase fw-bold">Bahan Kering</small>
                <h3 class="fw-bold mb-0 mt-1"><?= $tot_kering ?> <span class="fs-6 fw-normal">Item</span></h3>
                <a href="index.php?page=bahan_kering" class="small text-dark text-decoration-underline">Kelola Bahan Kering &rarr;</a>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-warning text-dark h-100">
            <div class="card-body">
                <small class="text-dark-50 text-uppercase fw-bold">Stok Menipis (&lt; 10)</small>
                <h3 class="fw-bold mb-0 mt-1"><?= $tot_menipis ?> <span class="fs-6 fw-normal">Item</span></h3>
                <a href="index.php?page=laporan_stok" target="_blank" rel="noopener noreferrer" class="small text-dark text-decoration-underline">Lihat Daftar Stok Menipis</a>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-danger text-white h-100">
            <div class="card-body">
                <small class="text-white-50 text-uppercase fw-bold">Atensi Kadaluarsa</small>
                <h3 class="fw-bold mb-0 mt-1"><?= $tot_exp ?> <span class="fs-6 fw-normal">Item</span></h3>
                <small class="text-white-50"><?= $mutasi_hari_ini ?> transaksi mutasi hari ini</small>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 fw-bold"><i class="fas fa-history text-primary me-2"></i>Aktivitas Mutasi Hari Ini (<?= date('d M Y') ?>)</h6>
        <a href="index.php?page=rekap" class="btn btn-sm btn-outline-primary">Lihat Semua Rekap</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Nama Bahan</th>
                        <th>Kategori</th>
                        <th class="text-center">Masuk</th>
                        <th class="text-center">Keluar</th>
                        <th class="text-center">Stok Akhir</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $q_today = mysqli_query($koneksi, "
                        SELECT r.*,
                               COALESCE(bb.nama_bahan, bk.nama_bahan, 'Bahan Terhapus') AS nama_bahan,
                               COALESCE(bb.satuan, bk.satuan, '') AS satuan,
                               CASE WHEN r.id_bahan_basah IS NOT NULL THEN 'Basah' ELSE 'Kering' END AS kategori
                        FROM rekap_harian r
                        LEFT JOIN bahan_basah bb ON r.id_bahan_basah = bb.id
                        LEFT JOIN bahan_kering bk ON r.id_bahan_kering = bk.id
                        WHERE r.tanggal = '$tgl_hari_ini'
                        ORDER BY r.id_harian DESC
                        LIMIT 10
                    ");
                    if ($q_today && mysqli_num_rows($q_today) > 0) :
                        while ($r = mysqli_fetch_assoc($q_today)) :
                            $sat = htmlspecialchars($r['satuan'], ENT_QUOTES, 'UTF-8');
                    ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?= htmlspecialchars($r['nama_bahan'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <span class="badge <?= ($r['kategori'] === 'Basah') ? 'bg-info text-dark' : 'bg-secondary' ?>">
                                        <?= $r['kategori'] ?>
                                    </span>
                                </td>
                                <td class="text-center text-success fw-semibold">+<?= format_qty($r['masuk']) ?> <?= $sat ?></td>
                                <td class="text-center text-danger fw-semibold">-<?= format_qty($r['keluar']) ?> <?= $sat ?></td>
                                <td class="text-center fw-bold"><?= format_qty($r['stok_akhir_hari']) ?> <?= $sat ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Belum ada catatan mutasi masuk atau keluar hari ini.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>