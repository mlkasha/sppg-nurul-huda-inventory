<?php
require_once __DIR__ . '/../../../config/security.php';
require_role(['admin'], 'index.php?page=dashboard');

$id_user = (int)($_GET['id'] ?? 0);

$stmt = mysqli_prepare($koneksi, "SELECT id, username, nama_lengkap, role, no_hp FROM users WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $id_user);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$u = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$u) {
    echo "
    <div class='alert alert-danger border-0 shadow-sm'>
        <i class='fas fa-exclamation-triangle me-2'></i> Data pengguna tidak ditemukan!
    </div>
    <a href='index.php?page=user' class='btn btn-secondary btn-sm'>Kembali</a>";
    return;
}
?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold text-dark">
            <i class="fas fa-user-edit me-2 text-warning"></i>Edit Data Pengguna
        </h5>
    </div>
    
    <form action="index.php?page=user_update" method="POST">
        <?= csrf_field(); ?>
        <input type="hidden" name="id" value="<?= (int)$u['id']; ?>">

        <div class="card-body p-4">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-4">
                        <label class="form-label fw-bold">Nama Lengkap</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-id-card text-muted"></i></span>
                            <input type="text" name="nama_lengkap" class="form-control border-start-0 ps-0" value="<?= htmlspecialchars($u['nama_lengkap'] ?? $u['username'], ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-4">
                        <label class="form-label fw-bold">Username</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-at text-muted"></i></span>
                            <input type="text" name="username" class="form-control border-start-0 ps-0" value="<?= htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8'); ?>" required>
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
                            <input type="text" name="no_hp" class="form-control border-start-0 ps-0" value="<?= htmlspecialchars($u['no_hp'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Contoh: 081234567890">
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-4">
                        <label class="form-label fw-bold">Role / Hak Akses</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-user-tag text-muted"></i></span>
                            <select name="role" class="form-select border-start-0 ps-0" required>
                                <option value="admin" <?= (strtolower($u['role']) === 'admin') ? 'selected' : ''; ?>>Administrator</option>
                                <option value="gudang" <?= (strtolower($u['role']) === 'gudang') ? 'selected' : ''; ?>>Staf Gudang</option>
                                <option value="kepala" <?= (strtolower($u['role']) === 'kepala') ? 'selected' : ''; ?>>Kepala SPPG</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label fw-bold">Password Baru</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                    <input type="password" name="password" class="form-control border-start-0 ps-0" minlength="6" placeholder="Kosongkan jika tidak ingin mengubah password">
                </div>
                <div class="form-text text-muted mt-2">
                    <i class="fas fa-info-circle me-1"></i> Biarkan kosong jika user tidak ingin mengganti password lamanya.
                </div>
            </div>
        </div>

        <div class="card-footer bg-light border-0 py-3 ps-4">
            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                <i class="fas fa-save me-2"></i>Simpan Perubahan
            </button>
            <a href="index.php?page=user" class="btn btn-outline-secondary px-4 ms-2">
                <i class="fas fa-times me-2"></i>Batal
            </a>
        </div>
    </form>
</div>