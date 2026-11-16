<?php
require_once __DIR__ . '/../../../config/security.php';
require_login();

$role = strtolower($_SESSION['role'] ?? 'gudang');
$tab  = $_GET['tab'] ?? 'harian';
if (!in_array($tab, ['harian', 'mingguan', 'bulanan'], true)) {
    $tab = 'harian';
}

// Parameter Filter
$tgl_filter   = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['tanggal'] ?? '') ? $_GET['tanggal'] : date('Y-m-d');
$tgl_awal_mgg = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['tgl_awal'] ?? '') ? $_GET['tgl_awal'] : date('Y-m-d', strtotime('-6 days'));
$tgl_akhir_mgg= preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['tgl_akhir'] ?? '') ? $_GET['tgl_akhir'] : date('Y-m-d');
$bln_filter   = max(1, min(12, (int)($_GET['bulan'] ?? date('m'))));
$thn_filter   = max(2020, min(2100, (int)($_GET['tahun'] ?? date('Y'))));

$nama_bulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark">Rekap Mutasi Persediaan</h4>
        <p class="text-muted small mb-0">Riwayat stok masuk dan pemakaian bahan dapur SPPG Nurul Huda</p>
    </div>
</div>

<!-- Navigasi Tab -->
<ul class="nav nav-pills mb-4 bg-white p-2 rounded shadow-sm">
    <li class="nav-item">
        <a class="nav-link <?= ($tab === 'harian') ? 'active' : '' ?>" href="index.php?page=rekap&tab=harian">
            <i class="fas fa-calendar-day me-1"></i> Harian
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= ($tab === 'mingguan') ? 'active' : '' ?>" href="index.php?page=rekap&tab=mingguan">
            <i class="fas fa-calendar-week me-1"></i> Mingguan
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= ($tab === 'bulanan') ? 'active' : '' ?>" href="index.php?page=rekap&tab=bulanan">
            <i class="fas fa-calendar-alt me-1"></i> Bulanan
        </a>
    </li>
</ul>

<?php if ($tab === 'harian') : ?>
    <!-- TAB HARIAN -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="index.php" class="row g-2 align-items-end">
                <input type="hidden" name="page" value="rekap">
                <input type="hidden" name="tab" value="harian">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Pilih Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" value="<?= htmlspecialchars($tgl_filter, ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="col-md-8 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-1"></i> Tampilkan</button>
                    <a href="index.php?page=laporan_harian_pdf&tanggal=<?= urlencode($tgl_filter) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-danger">
                        <i class="fas fa-file-pdf me-1"></i> Cetak PDF Harian
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">No</th>
                            <th>Nama Bahan</th>
                            <th>Kategori</th>
                            <th class="text-center">Stok Awal</th>
                            <th class="text-center">Masuk</th>
                            <th class="text-center">Keluar</th>
                            <th class="text-center">Stok Akhir</th>
                            <th>Operator</th>
                            <?php if ($role === 'admin') : ?>
                                <th class="text-center">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = mysqli_prepare($koneksi, "
                            SELECT r.*,
                                   COALESCE(bb.nama_bahan, bk.nama_bahan, 'Bahan Terhapus') AS nama_bahan,
                                   COALESCE(bb.satuan, bk.satuan, '') AS satuan,
                                   CASE WHEN r.id_bahan_basah IS NOT NULL THEN 'Basah' ELSE 'Kering' END AS kategori,
                                   COALESCE(NULLIF(u.nama_lengkap, ''), u.username, 'Sistem') AS nama_display
                            FROM rekap_harian r
                            LEFT JOIN bahan_basah bb ON r.id_bahan_basah = bb.id
                            LEFT JOIN bahan_kering bk ON r.id_bahan_kering = bk.id
                            LEFT JOIN users u ON r.created_by = u.id
                            WHERE r.tanggal = ?
                            ORDER BY r.id_harian DESC
                        ");
                        mysqli_stmt_bind_param($stmt, "s", $tgl_filter);
                        mysqli_stmt_execute($stmt);
                        $res = mysqli_stmt_get_result($stmt);

                        $no = 1;
                        if ($res && mysqli_num_rows($res) > 0) :
                            while ($r = mysqli_fetch_assoc($res)) :
                                $sat = htmlspecialchars($r['satuan'], ENT_QUOTES, 'UTF-8');
                        ?>
                                <tr>
                                    <td class="ps-3"><?= $no++ ?></td>
                                    <td class="fw-bold"><?= htmlspecialchars($r['nama_bahan'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <span class="badge <?= ($r['kategori'] === 'Basah') ? 'bg-info text-dark' : 'bg-secondary' ?>">
                                            <?= $r['kategori'] ?>
                                        </span>
                                    </td>
                                    <td class="text-center"><?= format_qty($r['stok_awal']) ?> <?= $sat ?></td>
                                    <td class="text-center text-success fw-semibold">+<?= format_qty($r['masuk']) ?> <?= $sat ?></td>
                                    <td class="text-center text-danger fw-semibold">-<?= format_qty($r['keluar']) ?> <?= $sat ?></td>
                                    <td class="text-center fw-bold"><?= format_qty($r['stok_akhir_hari']) ?> <?= $sat ?></td>
                                    <td class="small text-muted"><?= htmlspecialchars($r['nama_display'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <?php if ($role === 'admin') : ?>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <a href="index.php?page=rekap_harian_edit&id=<?= (int)$r['id_harian'] ?>" class="btn btn-sm btn-outline-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="index.php?page=rekap_harian_hapus&id=<?= (int)$r['id_harian'] ?>&token=<?= csrf_token() ?>"
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('Hapus catatan mutasi ini?')"
                                                   title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endwhile; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="<?= ($role === 'admin') ? 9 : 8 ?>" class="text-center py-5 text-muted">
                                    Tidak ada data mutasi pada tanggal <?= date('d/m/Y', strtotime($tgl_filter)) ?>.
                                </td>
                            </tr>
                        <?php endif; mysqli_stmt_close($stmt); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php elseif ($tab === 'mingguan') : ?>
    <!-- TAB MINGGUAN -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="index.php" class="row g-2 align-items-end">
                <input type="hidden" name="page" value="rekap">
                <input type="hidden" name="tab" value="mingguan">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Dari Tanggal</label>
                    <input type="date" name="tgl_awal" class="form-control" value="<?= htmlspecialchars($tgl_awal_mgg, ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Sampai Tanggal</label>
                    <input type="date" name="tgl_akhir" class="form-control" value="<?= htmlspecialchars($tgl_akhir_mgg, ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-1"></i> Tampilkan</button>
                    <a href="index.php?page=laporan_mingguan_pdf&tgl_awal=<?= urlencode($tgl_awal_mgg) ?>&tgl_akhir=<?= urlencode($tgl_akhir_mgg) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-danger">
                        <i class="fas fa-file-pdf me-1"></i> Cetak PDF Mingguan
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">No</th>
                            <th>Nama Bahan</th>
                            <th>Kategori</th>
                            <th class="text-center">Total Masuk</th>
                            <th class="text-center">Total Keluar</th>
                            <th class="text-center">Stok Saat Ini</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt_mgg = mysqli_prepare($koneksi, "
                            SELECT 
                                COALESCE(bb.nama_bahan, bk.nama_bahan, 'Bahan Terhapus') AS nama_bahan,
                                COALESCE(bb.satuan, bk.satuan, '') AS satuan,
                                COALESCE(bb.quantity, bk.quantity, 0) AS stok_sekarang,
                                CASE WHEN r.id_bahan_basah IS NOT NULL THEN 'Basah' ELSE 'Kering' END AS kategori,
                                SUM(r.masuk) AS total_masuk,
                                SUM(r.keluar) AS total_keluar
                            FROM rekap_harian r
                            LEFT JOIN bahan_basah bb ON r.id_bahan_basah = bb.id
                            LEFT JOIN bahan_kering bk ON r.id_bahan_kering = bk.id
                            WHERE r.tanggal BETWEEN ? AND ?
                            GROUP BY r.id_bahan_basah, r.id_bahan_kering, nama_bahan, satuan, stok_sekarang, kategori
                            ORDER BY nama_bahan ASC
                        ");
                        mysqli_stmt_bind_param($stmt_mgg, "ss", $tgl_awal_mgg, $tgl_akhir_mgg);
                        mysqli_stmt_execute($stmt_mgg);
                        $res_mgg = mysqli_stmt_get_result($stmt_mgg);

                        $no = 1;
                        if ($res_mgg && mysqli_num_rows($res_mgg) > 0) :
                            while ($r = mysqli_fetch_assoc($res_mgg)) :
                                $sat = htmlspecialchars($r['satuan'], ENT_QUOTES, 'UTF-8');
                        ?>
                                <tr>
                                    <td class="ps-3"><?= $no++ ?></td>
                                    <td class="fw-bold"><?= htmlspecialchars($r['nama_bahan'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><span class="badge <?= ($r['kategori'] === 'Basah') ? 'bg-info text-dark' : 'bg-secondary' ?>"><?= $r['kategori'] ?></span></td>
                                    <td class="text-center text-success fw-semibold">+<?= format_qty($r['total_masuk']) ?> <?= $sat ?></td>
                                    <td class="text-center text-danger fw-semibold">-<?= format_qty($r['total_keluar']) ?> <?= $sat ?></td>
                                    <td class="text-center fw-bold"><?= format_qty($r['stok_sekarang']) ?> <?= $sat ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">Tidak ada transaksi mutasi pada rentang tanggal tersebut.</td>
                            </tr>
                        <?php endif; mysqli_stmt_close($stmt_mgg); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php else : ?>
    <!-- TAB BULANAN -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="index.php" class="row g-2 align-items-end">
                <input type="hidden" name="page" value="rekap">
                <input type="hidden" name="tab" value="bulanan">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Bulan</label>
                    <select name="bulan" class="form-select">
                        <?php foreach ($nama_bulan as $num => $nm) : ?>
                            <option value="<?= $num ?>" <?= ($bln_filter === $num) ? 'selected' : '' ?>><?= $nm ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Tahun</label>
                    <input type="number" name="tahun" class="form-control" min="2020" max="2100" value="<?= $thn_filter ?>">
                </div>
                <div class="col-md-7 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-1"></i> Tampilkan</button>
                    <a href="index.php?page=laporan_bulanan_pdf&bulan=<?= $bln_filter ?>&tahun=<?= $thn_filter ?>" target="_blank" rel="noopener noreferrer" class="btn btn-danger">
                        <i class="fas fa-file-pdf me-1"></i> Cetak PDF Bulanan
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">No</th>
                            <th>Nama Bahan</th>
                            <th>Kategori</th>
                            <th class="text-center">Total Masuk</th>
                            <th class="text-center">Total Keluar</th>
                            <th class="text-center">Stok Akhir Tercatat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt_bln = mysqli_prepare($koneksi, "
                            SELECT 
                                COALESCE(bb.nama_bahan, bk.nama_bahan, 'Bahan Terhapus') AS nama_bahan,
                                COALESCE(bb.satuan, bk.satuan, '') AS satuan,
                                CASE WHEN r.id_bahan_basah IS NOT NULL THEN 'Basah' ELSE 'Kering' END AS kategori,
                                SUM(r.masuk) AS total_masuk,
                                SUM(r.keluar) AS total_keluar,
                                (SELECT r2.stok_akhir_hari 
                                 FROM rekap_harian r2 
                                 WHERE MONTH(r2.tanggal) = ? AND YEAR(r2.tanggal) = ?
                                   AND (r2.id_bahan_basah <=> r.id_bahan_basah)
                                   AND (r2.id_bahan_kering <=> r.id_bahan_kering)
                                 ORDER BY r2.tanggal DESC, r2.id_harian DESC LIMIT 1) AS stok_akhir_bulan
                            FROM rekap_harian r
                            LEFT JOIN bahan_basah bb ON r.id_bahan_basah = bb.id
                            LEFT JOIN bahan_kering bk ON r.id_bahan_kering = bk.id
                            WHERE MONTH(r.tanggal) = ? AND YEAR(r.tanggal) = ?
                            GROUP BY r.id_bahan_basah, r.id_bahan_kering, nama_bahan, satuan, kategori
                            ORDER BY nama_bahan ASC
                        ");
                        mysqli_stmt_bind_param($stmt_bln, "iiii", $bln_filter, $thn_filter, $bln_filter, $thn_filter);
                        mysqli_stmt_execute($stmt_bln);
                        $res_bln = mysqli_stmt_get_result($stmt_bln);

                        $no = 1;
                        if ($res_bln && mysqli_num_rows($res_bln) > 0) :
                            while ($r = mysqli_fetch_assoc($res_bln)) :
                                $sat = htmlspecialchars($r['satuan'], ENT_QUOTES, 'UTF-8');
                        ?>
                                <tr>
                                    <td class="ps-3"><?= $no++ ?></td>
                                    <td class="fw-bold"><?= htmlspecialchars($r['nama_bahan'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><span class="badge <?= ($r['kategori'] === 'Basah') ? 'bg-info text-dark' : 'bg-secondary' ?>"><?= $r['kategori'] ?></span></td>
                                    <td class="text-center text-success fw-semibold">+<?= format_qty($r['total_masuk']) ?> <?= $sat ?></td>
                                    <td class="text-center text-danger fw-semibold">-<?= format_qty($r['total_keluar']) ?> <?= $sat ?></td>
                                    <td class="text-center fw-bold"><?= format_qty($r['stok_akhir_bulan']) ?> <?= $sat ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    Tidak ada data mutasi pada bulan <?= $nama_bulan[$bln_filter] ?> <?= $thn_filter ?>.
                                </td>
                            </tr>
                        <?php endif; mysqli_stmt_close($stmt_bln); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>