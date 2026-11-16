<?php
require_once __DIR__ . '/../../../config/security.php';
require_role(['admin'], 'index.php?page=dashboard');

$role = strtolower($_SESSION['role'] ?? '');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Manajemen Supplier</h4>
        <p class="text-muted small mb-0">Daftar rekanan pemasok bahan makanan SPPG Nurul Huda</p>
    </div>
    
    <?php if ($role === 'admin') : ?>
        <a href="index.php?page=supplier_tambah" class="btn btn-primary btn-sm shadow-sm">
            <i class="fas fa-plus me-1"></i> Tambah Supplier
        </a>
    <?php endif; ?>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" width="5%">No</th>
                        <th width="25%">Nama Supplier</th>
                        <th width="20%">Telepon/WA</th>
                        <th width="25%">Alamat</th>
                        <th>Keterangan</th>
                        <?php if ($role === 'admin') : ?>
                            <th class="text-center">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    $query_data = mysqli_query($koneksi, "SELECT * FROM supplier ORDER BY nama_supplier ASC");
                    
                    if ($query_data && mysqli_num_rows($query_data) > 0) {
                        while ($row = mysqli_fetch_assoc($query_data)) {
                            $kontak_raw = $row['kontak'] ?? '';
                            $wa_number  = preg_replace('/[^0-9]/', '', $kontak_raw);
                    ?>
                        <tr>
                            <td class="ps-3"><?= $no++ ?></td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($row['nama_supplier'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                                <small class="text-muted text-uppercase" style="font-size: 0.65rem;">ID: SUP-<?= str_pad((int)$row['id'], 3, '0', STR_PAD_LEFT) ?></small>
                            </td>
                            <td>
                                <?php if ($wa_number !== '') : ?>
                                    <a href="https://wa.me/<?= htmlspecialchars($wa_number, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-success border-0 py-0">
                                        <i class="fab fa-whatsapp me-1"></i> <?= htmlspecialchars($kontak_raw, ENT_QUOTES, 'UTF-8') ?>
                                    </a>
                                <?php else : ?>
                                    <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="small text-muted"><?= htmlspecialchars($row['alamat'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><span class="badge bg-light text-dark fw-normal border"><?= htmlspecialchars($row['keterangan'] ?? '-', ENT_QUOTES, 'UTF-8') ?></span></td>

                            <?php if ($role === 'admin') : ?>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="index.php?page=supplier_edit&id=<?= (int)$row['id'] ?>" class="btn btn-sm btn-light border" title="Edit">
                                            <i class="fas fa-edit text-warning"></i>
                                        </a>
                                        <a href="index.php?page=supplier_hapus&id=<?= (int)$row['id'] ?>&token=<?= csrf_token() ?>" 
                                           class="btn btn-sm btn-light border" 
                                           onclick="return confirm('Hapus supplier ini? Tindakan ini tidak bisa dibatalkan.')" 
                                           title="Hapus">
                                            <i class="fas fa-trash text-danger"></i>
                                        </a>
                                    </div>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php 
                        }
                    } else {
                        $colspan = ($role === 'admin') ? 6 : 5;
                        echo "<tr><td colspan='$colspan' class='text-center py-5 text-muted'>Belum ada data supplier terdaftar.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card bg-light border-0">
            <div class="card-body p-3 text-muted">
                <h6 class="fw-bold text-dark small"><i class="fas fa-info-circle me-2"></i>Informasi:</h6>
                <ul class="x-small mb-0 ps-3" style="font-size: 0.8rem;">
                    <li>Klik nomor telepon untuk langsung membuka chat WhatsApp.</li>
                    <li>Hanya akun <b>Admin</b> yang dapat menambah, mengubah, atau menghapus data supplier.</li>
                </ul>
            </div>
        </div>
    </div>
</div>