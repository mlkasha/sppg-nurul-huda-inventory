<?php
require_once __DIR__ . '/../../../config/security.php';
require_role(['admin', 'gudang'], 'index.php?page=bahan_basah');

$id = (int)($_GET['id'] ?? 0);

$stmt = mysqli_prepare($koneksi, "SELECT * FROM bahan_basah WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$row) {
    echo "
    <div class='alert alert-danger border-0 shadow-sm'>
        <i class='fas fa-exclamation-triangle me-2'></i> Data bahan basah tidak ditemukan!
    </div>
    <a href='index.php?page=bahan_basah' class='btn btn-secondary btn-sm'>Kembali</a>";
    return;
}

$suppliers = mysqli_query($koneksi, "SELECT id, nama_supplier FROM supplier ORDER BY nama_supplier ASC");
$daftar_satuan = ['kg', 'gr', 'liter', 'ml', 'pcs', 'pack', 'ons', 'dus', 'ikat', 'renceng', 'bungkus', 'ton'];
$satuan_saat_ini = strtolower($row['satuan'] ?? 'kg');
?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-warning text-dark fw-bold py-3">
        <h5 class="mb-0 fw-bold"><i class="fas fa-edit me-2"></i>Edit Data Bahan Basah</h5>
    </div>

    <form action="index.php?page=bahan_basah_update" method="POST">
        <?= csrf_field(); ?>
        <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">

        <div class="card-body p-4">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Nama Bahan Basah</label>
                    <input type="text" name="nama_bahan" class="form-control" value="<?= htmlspecialchars($row['nama_bahan'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Supplier</label>
                    <select name="supplier_id" class="form-select">
                        <option value="">-- Pilih Supplier (Opsional) --</option>
                        <?php if ($suppliers) : while ($s = mysqli_fetch_assoc($suppliers)) : ?>
                            <option value="<?= (int)$s['id'] ?>" <?= ((int)$row['supplier_id'] === (int)$s['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['nama_supplier'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endwhile; endif; ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Harga Satuan (Rp)</label>
                    <input type="number" name="harga" class="form-control" min="0" step="any" value="<?= htmlspecialchars((string)$row['harga'], ENT_QUOTES, 'UTF-8') ?>" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Sisa Stok</label>
                    <input type="number" name="quantity" class="form-control" min="0" step="0.001" value="<?= (float)$row['quantity'] ?>" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Satuan</label>
                    <select name="satuan" class="form-select" required>
                        <?php foreach ($daftar_satuan as $sat) : ?>
                            <option value="<?= $sat ?>" <?= ($satuan_saat_ini === $sat) ? 'selected' : '' ?>><?= $sat ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Tanggal Kadaluarsa</label>
                    <input type="date" name="tanggal_kadaluarsa" class="form-control" value="<?= htmlspecialchars($row['tanggal_kadaluarsa'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
            </div>
        </div>

        <div class="card-footer bg-light border-0 py-3 d-flex justify-content-between">
            <a href="index.php?page=bahan_basah" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Batal
            </a>
            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                <i class="fas fa-save me-1"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</div>