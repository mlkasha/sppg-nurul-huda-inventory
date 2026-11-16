<?php
require_once __DIR__ . '/../../../config/security.php';
require_role(['admin'], 'index.php?page=rekap');

$id = (int)($_GET['id'] ?? 0);

$stmt = mysqli_prepare($koneksi, "
    SELECT r.*,
           COALESCE(bb.nama_bahan, bk.nama_bahan, 'Bahan Terhapus') AS nama_bahan,
           COALESCE(bb.satuan, bk.satuan, '') AS satuan
    FROM rekap_harian r
    LEFT JOIN bahan_basah bb ON r.id_bahan_basah = bb.id
    LEFT JOIN bahan_kering bk ON r.id_bahan_kering = bk.id
    WHERE r.id_harian = ?
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$row) {
    echo "
    <div class='alert alert-danger border-0 shadow-sm'>
        <i class='fas fa-exclamation-triangle me-2'></i> Data log mutasi tidak ditemukan!
    </div>
    <a href='index.php?page=rekap' class='btn btn-secondary btn-sm'>Kembali</a>";
    return;
}
?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-warning text-dark fw-bold py-3">
        <h5 class="mb-0 fw-bold"><i class="fas fa-edit me-2"></i>Edit Log Mutasi Harian</h5>
    </div>

    <form action="index.php?page=rekap_harian_update" method="POST">
        <?= csrf_field(); ?>
        <input type="hidden" name="id_harian" value="<?= (int)$row['id_harian'] ?>">

        <div class="card-body p-4">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Nama Bahan</label>
                    <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($row['nama_bahan'] . ' (' . $row['satuan'] . ')', ENT_QUOTES, 'UTF-8') ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Tanggal Mutasi</label>
                    <input type="text" class="form-control bg-light" value="<?= date('d/m/Y', strtotime($row['tanggal'])) ?>" readonly>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Stok Awal</label>
                    <input type="number" name="stok_awal" class="form-control" step="0.001" min="0" value="<?= (float)$row['stok_awal'] ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Jumlah Masuk</label>
                    <input type="number" name="masuk" class="form-control" step="0.001" min="0" value="<?= (float)$row['masuk'] ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Jumlah Keluar</label>
                    <input type="number" name="keluar" class="form-control" step="0.001" min="0" value="<?= (float)$row['keluar'] ?>" required>
                </div>
            </div>
            <div class="form-text text-muted">
                <i class="fas fa-calculator me-1"></i> Nilai <b>Stok Akhir</b> pada baris rekap akan otomatis dihitung ulang (<code>Stok Awal + Masuk - Keluar</code>).
            </div>
        </div>

        <div class="card-footer bg-light border-0 py-3 d-flex justify-content-between">
            <a href="index.php?page=rekap" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Batal
            </a>
            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                <i class="fas fa-save me-1"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</div>