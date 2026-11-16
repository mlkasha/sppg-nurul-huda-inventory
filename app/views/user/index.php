<?php
require_once __DIR__ . '/../../../config/security.php';
require_role(['admin'], 'index.php?page=dashboard');

// Ambil seluruh data user sekaligus untuk efisiensi perhitungan statistik & tabel
$users = [];
$count_admin  = 0;
$count_kepala = 0;
$count_gudang = 0;

$query = mysqli_query($koneksi, "SELECT id, username, nama_lengkap, role, no_hp, last_login FROM users ORDER BY role ASC, username ASC");
if ($query) {
    while ($row = mysqli_fetch_assoc($query)) {
        $users[] = $row;
        $r = strtolower($row['role'] ?? 'gudang');
        if ($r === 'admin') {
            $count_admin++;
        } elseif ($r === 'kepala') {
            $count_kepala++;
        } else {
            $count_gudang++;
        }
    }
}
$total_user = count($users);
$my_id = (int)($_SESSION['id'] ?? 0);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0 text-dark">Manajemen Pengguna</h4>
        <p class="text-muted small mb-0">Total terdaftar: <span class="badge bg-secondary"><?= $total_user ?> Personil</span></p>
    </div>
    <a href="index.php?page=user_tambah" class="btn btn-primary btn-sm shadow-sm">
        <i class="fas fa-user-plus me-1"></i> Tambah User
    </a>
</div>

<div class="row mb-4">
    <div class="col-md-4 mb-2 mb-md-0">
        <div class="card border-0 shadow-sm bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1 text-white-50">Administrator</h6>
                        <h3 class="fw-bold mb-0"><?= $count_admin ?></h3>
                    </div>
                    <i class="fas fa-user-shield fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-2 mb-md-0">
        <div class="card border-0 shadow-sm bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1 text-white-50">Kepala SPPG</h6>
                        <h3 class="fw-bold mb-0"><?= $count_kepala ?></h3>
                    </div>
                    <i class="fas fa-user-tie fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1 text-dark-50">Staf Gudang</h6>
                        <h3 class="fw-bold mb-0"><?= $count_gudang ?></h3>
                    </div>
                    <i class="fas fa-boxes fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h6 class="m-0 fw-bold"><i class="fas fa-list me-2 text-primary"></i>Daftar Akun Sistem</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">User</th>
                        <th>Role & Akses</th>
                        <th>Kontak</th>
                        <th>Aktivitas Terakhir</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($total_user > 0): ?>
                        <?php foreach ($users as $user):
                            $id_user      = (int)$user['id'];
                            $username     = $user['username'] ?? 'unknown';
                            $nama_display = !empty($user['nama_lengkap']) ? $user['nama_lengkap'] : ucfirst($username);
                            $user_role    = strtolower($user['role'] ?? 'gudang');
                            $last_login   = $user['last_login'] ?? null;
                            $no_hp        = !empty($user['no_hp']) ? $user['no_hp'] : '-';
                            $initials     = strtoupper(substr($nama_display, 0, 1));

                            if ($user_role === 'admin') {
                                $role_class = 'bg-danger';
                                $akses_text = 'Akses Penuh Seluruh Sistem';
                            } elseif ($user_role === 'kepala') {
                                $role_class = 'bg-success';
                                $akses_text = 'Akses Laporan & Monitoring';
                            } else {
                                $role_class = 'bg-warning text-dark';
                                $akses_text = 'Akses Inventaris & Stok';
                            }
                        ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary text-white fw-bold me-3" style="width: 40px; height: 40px;">
                                        <?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold mb-0 text-dark"><?= htmlspecialchars($nama_display, ENT_QUOTES, 'UTF-8') ?></div>
                                        <small class="text-muted">@<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge <?= $role_class ?> rounded-pill mb-1"><?= htmlspecialchars(strtoupper($user_role), ENT_QUOTES, 'UTF-8') ?></span>
                                <div class="text-muted" style="font-size: 0.75rem;"><?= $akses_text ?></div>
                            </td>
                            <td>
                                <small class="text-dark"><i class="fas fa-phone-alt me-1 text-muted"></i> <?= htmlspecialchars($no_hp, ENT_QUOTES, 'UTF-8') ?></small>
                            </td>
                            <td>
                                <div style="font-size: 0.75rem;" class="text-muted mb-1">Login terakhir:</div>
                                <small class="fw-bold text-dark">
                                    <i class="fas fa-clock me-1 text-muted"></i>
                                    <?= ($last_login && $last_login !== '0000-00-00 00:00:00') ? date('d M Y, H:i', strtotime($last_login)) : '<span class="text-danger">Belum Pernah</span>' ?>
                                </small>
                            </td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a href="index.php?page=user_edit&id=<?= $id_user ?>" class="btn btn-sm btn-outline-secondary" title="Edit">
                                        <i class="fas fa-pen text-warning"></i>
                                    </a>
                                    <?php if ($id_user !== $my_id): ?>
                                        <a href="index.php?page=user_hapus&id=<?= $id_user ?>&token=<?= csrf_token() ?>" class="btn btn-sm btn-outline-secondary" onclick="return confirm('Hapus akun ini? Tindakan tidak dapat dibatalkan.')" title="Hapus">
                                            <i class="fas fa-trash text-danger"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="btn btn-sm btn-light disabled" title="Tidak bisa menghapus akun sendiri"><i class="fas fa-lock text-muted"></i></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted">Belum ada data pengguna.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>