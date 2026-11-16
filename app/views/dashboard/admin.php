<?php
require_once __DIR__ . '/../../../config/security.php';
require_role(['admin'], 'index.php?page=dashboard');

// Statistik Utama
$tot_basah    = (int)(mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM bahan_basah"))['jml'] ?? 0);
$tot_kering   = (int)(mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM bahan_kering"))['jml'] ?? 0);
$tot_supplier = (int)(mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM supplier"))['jml'] ?? 0);
$tot_user     = (int)(mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM users"))['jml'] ?? 0);

// Stok Menipis (< 10)
$menipis_basah  = (int)(mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM bahan_basah WHERE quantity < 10"))['jml'] ?? 0);
$menipis_kering = (int)(mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM bahan_kering WHERE quantity < 10"))['jml'] ?? 0);
$tot_menipis    = $menipis_basah + $menipis_kering;

// Hampir Kadaluarsa / Expired (Basah <= H-3, Kering <= H-30)
$exp_basah  = (int)(mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM bahan_basah WHERE tanggal_kadaluarsa <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)"))['jml'] ?? 0);
$exp_kering = (int)(mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM bahan_kering WHERE tanggal_kadaluarsa <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)"))['jml'] ?? 0);
$tot_exp    = $exp_basah + $exp_kering;

// Daftar Peringatan Prioritas
$q_alert = mysqli_query($koneksi, "
    (SELECT 'Basah' AS kategori, nama_bahan, quantity, satuan, tanggal_kadaluarsa 
     FROM bahan_basah 
     WHERE quantity < 10 OR tanggal_kadaluarsa <= DATE_ADD(CURDATE(), INTERVAL 3 DAY))
    UNION ALL
    (SELECT 'Kering' AS kategori, nama_bahan, quantity, satuan, tanggal_kadaluarsa 
     FROM bahan_kering 
     WHERE quantity < 10 OR tanggal_kadaluarsa <= DATE_ADD(CURDATE(), INTERVAL 30 DAY))
    ORDER BY tanggal_kadaluarsa ASC
    LIMIT 10
");
?>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-white-50 text-uppercase fw-bold">Total Item Bahan</small>
                        <h3 class="fw-bold mb-0 mt-1"><?= $tot_basah + $tot_kering ?></h3>
                        <small class="text-white-50"><?= $tot_basah ?> Basah | <?= $tot_kering ?> Kering</small>
                    </div>
                    <i class="fas fa-boxes fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-warning text-dark h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-dark-50 text-uppercase fw-bold">Stok Menipis (&lt; 10)</small>
                        <h3 class="fw-bold mb-0 mt-1"><?= $tot_menipis ?></h3>
                        <a href="index.php?page=laporan_stok" target="_blank" rel="noopener noreferrer" class="small text-dark text-decoration-underline">Cetak PDF Stok Menipis</a>
                    </div>
                    <i class="fas fa-exclamation-triangle fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-danger text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-white-50 text-uppercase fw-bold">Atensi Kadaluarsa</small>
                        <h3 class="fw-bold mb-0 mt-1"><?= $tot_exp ?></h3>
                        <a href="index.php?page=laporan_expired" target="_blank" rel="noopener noreferrer" class="small text-white text-decoration-underline">Cetak PDF Kadaluarsa</a>
                    </div>
                    <i class="fas fa-calendar-times fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-success text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-white-50 text-uppercase fw-bold">Supplier &amp; Pengguna</small>
                        <h3 class="fw-bold mb-0 mt-1"><?= $tot_supplier ?> <span class="fs-6 fw-normal">Supplier</span></h3>
                        <small class="text-white-50"><?= $tot_user ?> Akun Terdaftar</small>
                    </div>
                    <i class="fas fa-users-cog fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 fw-bold text-dark"><i class="fas fa-bell text-danger me-2"></i>Bahan Memerlukan Perhatian Segera</h6>
        <div class="d-flex gap-2">
            <a href="index.php?page=user" class="btn btn-sm btn-outline-primary"><i class="fas fa-user-shield me-1"></i>Kelola User</a>
            <a href="index.php?page=supplier" class="btn btn-sm btn-outline-secondary"><i class="fas fa-truck me-1"></i>Kelola Supplier</a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Kategori</th>
                        <th>Nama Bahan</th>
                        <th class="text-center">Sisa Stok</th>
                        <th class="text-center">Tgl Kadaluarsa</th>
                        <th>Status Peringatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($q_alert && mysqli_num_rows($q_alert) > 0) : ?>
                        <?php
                        $today = new DateTime(date('Y-m-d'));
                        while ($row = mysqli_fetch_assoc($q_alert)) :
                            $qty     = (float)$row['quantity'];
                            $tgl_exp = new DateTime($row['tanggal_kadaluarsa']);
                            $sisa_hr = (int)$today->diff($tgl_exp)->format('%r%a');
                            $batas   = ($row['kategori'] === 'Basah') ? 3 : 30;
                        ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="badge <?= ($row['kategori'] === 'Basah') ? 'bg-info text-dark' : 'bg-secondary' ?>">
                                        <?= htmlspecialchars($row['kategori'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td class="fw-bold"><?= htmlspecialchars($row['nama_bahan'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-center">
                                    <span class="fw-bold <?= ($qty < 10) ? 'text-danger' : 'text-dark' ?>">
                                        <?= format_qty($qty) ?>
                                    </span>
                                    <small class="text-muted"><?= htmlspecialchars($row['satuan'] ?? '', ENT_QUOTES, 'UTF-8') ?></small>
                                </td>
                                <td class="text-center"><?= date('d/m/Y', strtotime($row['tanggal_kadaluarsa'])) ?></td>
                                <td>
                                    <?php if ($qty < 10) : ?>
                                        <span class="badge bg-warning text-dark me-1">Stok Menipis</span>
                                    <?php endif; ?>
                                    <?php if ($sisa_hr < 0) : ?>
                                        <span class="badge bg-danger">Sudah Kadaluarsa</span>
                                    <?php elseif ($sisa_hr <= $batas) : ?>
                                        <span class="badge bg-danger bg-opacity-75">H-<?= $sisa_hr ?> Kadaluarsa</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="fas fa-check-circle text-success me-1"></i> Seluruh stok dan masa simpan bahan dalam kondisi aman.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>