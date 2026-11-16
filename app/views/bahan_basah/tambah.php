<?php
require_once __DIR__ . '/../../../config/security.php';
require_role(['admin', 'gudang'], 'index.php?page=bahan_basah');

$suppliers = mysqli_query($koneksi, "SELECT id, nama_supplier FROM supplier ORDER BY nama_supplier ASC");
?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-primary text-white py-3">
        <h5 class="mb-0 fw-bold"><i class="fas fa-plus-circle me-2"></i>Tambah Bahan Basah</h5>
    </div>

    <form action="index.php?page=bahan_basah_store" method="POST">
        <?= csrf_field(); ?>

        <div class="card-body p-4">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Nama Bahan Basah</label>
                    <input type="text" name="nama_bahan" class="form-control" placeholder="Contoh: Daging Ayam Segar" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Supplier</label>
                    <select name="supplier_id" class="form-select">
                        <option value="">-- Pilih Supplier (Opsional) --</option>
                        <?php if ($suppliers) : while ($s = mysqli_fetch_assoc($suppliers)) : ?>
                            <option value="<?= (int)$s['id'] ?>"><?= htmlspecialchars($s['nama_supplier'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endwhile; endif; ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Harga Satuan (Rp)</label>
                    <input type="number" name="harga" class="form-control" min="0" step="any" placeholder="Contoh: 35000" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Jumlah Stok Awal</label>
                    <input type="number" name="quantity" class="form-control" min="0.001" step="0.001" placeholder="Contoh: 10 atau 2.5" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Satuan</label>
                    <select name="satuan" class="form-select" required>
                        <option value="kg">kg</option>
                        <option value="gr">gr</option>
                        <option value="liter">liter</option>
                        <option value="ml">ml</option>
                        <option value="pcs">pcs</option>
                        <option value="pack">pack</option>
                        <option value="ons">ons</option>
                        <option value="dus">dus</option>
                        <option value="ikat">ikat</option>
                        <option value="renceng">renceng</option>
                        <option value="bungkus">bungkus</option>
                        <option value="ton">ton</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Tanggal Input Stok</label>
                    <input type="date" name="tanggal_input_stok" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Tanggal Kadaluarsa</label>
                    <input type="date" name="tanggal_kadaluarsa" class="form-control" required>
                </div>
            </div>
        </div>

        <div class="card-footer bg-light border-0 py-3 d-flex justify-content-between">
            <a href="index.php?page=bahan_basah" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
            <button type="submit" class="btn btn-success px-4">
                <i class="fas fa-save me-1"></i> Simpan Bahan Basah
            </button>
        </div>
    </form>
</div>