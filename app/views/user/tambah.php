<?php
require_once __DIR__ . '/../../../config/security.php';
require_role(['admin'], 'index.php?page=dashboard');
?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold text-dark">
            <i class="fas fa-user-plus me-2 text-primary"></i>Tambah Pengguna Baru
        </h5>
    </div>

    <form action="index.php?page=user_store" method="POST">
        <?= csrf_field(); ?>

        <div class="card-body p-4">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-4">
                        <label class="form-label fw-bold">Nama Lengkap</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-id-card text-muted"></i></span>
                            <input type="text" name="nama_lengkap" class="form-control border-start-0 ps-0" placeholder="Contoh: Ahmad Fauzi" required>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mb-4">
                        <label class="form-label fw-bold">Username</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-at text-muted"></i></span>
                            <input type="text" name="username" class="form-control border-start-0 ps-0" placeholder="Username login" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-4">
                        <label class="form-label fw-bold">No. HP / WhatsApp</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-phone text-muted"></i></span>
                            <input type="text" name="no_hp" class="form-control border-start-0 ps-0" placeholder="Contoh: 081234567890">
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mb-4">
                        <label class="form-label fw-bold">Role / Hak Akses</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-user-tag text-muted"></i></span>
                            <select name="role" class="form-select border-start-0 ps-0" required>
                                <option value="gudang">Staf Gudang</option>
                                <option value="kepala">Kepala SPPG</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label fw-bold">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                    <input type="password" name="password" class="form-control border-start-0 ps-0" minlength="6" placeholder="Minimal 6 karakter" required>
                </div>
                <div class="form-text text-muted mt-2">
                    <i class="fas fa-shield-alt me-1"></i> Password akan otomatis dienkripsi menggunakan bcrypt sebelum disimpan.
                </div>
            </div>
        </div>

        <div class="card-footer bg-light border-0 py-3 ps-4">
            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                <i class="fas fa-save me-2"></i>Simpan Pengguna
            </button>
            <a href="index.php?page=user" class="btn btn-outline-secondary px-4 ms-2">
                <i class="fas fa-times me-2"></i>Batal
            </a>
        </div>
    </form>
</div>