<?php
require_once __DIR__ . '/../../../config/security.php';
require_role(['admin'], 'index.php?page=dashboard');
?>

<div class="container-fluid px-0">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white py-3">
            <h5 class="mb-0 fw-bold"><i class="fas fa-truck me-2"></i>Tambah Supplier Baru</h5>
        </div>
        <div class="card-body p-4">
            <form action="index.php?page=supplier_store" method="POST">
                <?= csrf_field(); ?>

                <div class="mb-3">
                    <label class="form-label fw-bold">Nama Supplier</label>
                    <input type="text" name="nama_supplier" class="form-control" placeholder="Contoh: Toko Sayur Segar" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">No. HP / WhatsApp (Kontak)</label>
                    <input type="text" name="kontak" class="form-control" placeholder="Contoh: 62812345678" required>
                    <small class="text-muted">Gunakan format 62... untuk WhatsApp otomatis.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Alamat</label>
                    <textarea name="alamat" class="form-control" rows="3" placeholder="Alamat lengkap supplier..."></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Keterangan</label>
                    <input type="text" name="keterangan" class="form-control" placeholder="Contoh: Supplier Daging / Sayur">
                </div>

                <hr>
                <div class="d-flex justify-content-between">
                    <a href="index.php?page=supplier" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Simpan Supplier
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>