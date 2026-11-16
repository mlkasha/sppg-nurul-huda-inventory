<?php
require_once __DIR__ . '/../../../config/security.php';
require_role(['admin'], 'index.php?page=dashboard');

$id = (int)($_GET['id'] ?? 0);

$stmt = mysqli_prepare($koneksi, "SELECT * FROM supplier WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$row) {
    echo "
    <div class='alert alert-danger border-0 shadow-sm'>
        <i class='fas fa-exclamation-triangle me-2'></i> Data supplier tidak ditemukan!
    </div>
    <a href='index.php?page=supplier' class='btn btn-secondary btn-sm'>Kembali</a>";
    return;
}
?>

<div class="container-fluid px-0">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-warning text-dark fw-bold py-3">
            <i class="fas fa-edit me-2"></i>Edit Data Supplier
        </div>
        <div class="card-body p-4">
            <form action="index.php?page=supplier_update" method="POST">
                <?= csrf_field(); ?>
                <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">

                <div class="mb-3">
                    <label class="form-label fw-bold">Nama Supplier</label>
                    <input type="text" name="nama_supplier" value="<?= htmlspecialchars($row['nama_supplier'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">No. HP / WhatsApp (Kontak)</label>
                    <input type="text" name="kontak" value="<?= htmlspecialchars($row['kontak'] ?? $row['no_hp'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Alamat</label>
                    <textarea name="alamat" class="form-control" rows="3"><?= htmlspecialchars($row['alamat'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Keterangan</label>
                    <input type="text" name="keterangan" value="<?= htmlspecialchars($row['keterangan'] ?? '-', ENT_QUOTES, 'UTF-8') ?>" class="form-control">
                </div>

                <hr>
                <div class="d-flex justify-content-between">
                    <a href="index.php?page=supplier" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary shadow-sm">
                        <i class="fas fa-save me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>