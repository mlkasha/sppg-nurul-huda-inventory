<?php
require_once __DIR__ . '/../../../config/security.php';
require_login();

$role = strtolower($_SESSION['role'] ?? 'gudang');
$can_manage = in_array($role, ['admin', 'gudang'], true);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0 text-dark">Persediaan Bahan Basah</h4>
        <p class="text-muted small mb-0">Kelola stok bahan segar dan masa kadaluarsa (Atensi H-3)</p>
    </div>
    <div class="d-flex gap-2">
        <a href="index.php?page=bahan_basah_pdf" target="_blank" rel="noopener noreferrer" class="btn btn-danger btn-sm shadow-sm">
            <i class="fas fa-file-pdf me-1"></i> Cetak PDF
        </a>
        <?php if ($can_manage) : ?>
            <a href="index.php?page=bahan_basah_tambah" class="btn btn-primary btn-sm shadow-sm">
                <i class="fas fa-plus me-1"></i> Tambah Bahan Basah
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" width="5%">No</th>
                        <th>Nama Bahan</th>
                        <th>Harga Satuan</th>
                        <th class="text-center">Sisa Stok</th>
                        <th class="text-center">Tgl Kadaluarsa</th>
                        <th>Supplier</th>
                        <?php if ($can_manage) : ?>
                            <th class="text-center" width="18%">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    $today = new DateTime(date('Y-m-d'));
                    $query = mysqli_query($koneksi, "SELECT b.*, s.nama_supplier 
                                                     FROM bahan_basah b 
                                                     LEFT JOIN supplier s ON b.supplier_id = s.id 
                                                     ORDER BY b.nama_bahan ASC");

                    if ($query && mysqli_num_rows($query) > 0) :
                        while ($row = mysqli_fetch_assoc($query)) :
                            $qty      = (float)$row['quantity'];
                            $satuan   = $row['satuan'] ?? 'kg';
                            $tgl_exp  = new DateTime($row['tanggal_kadaluarsa']);
                            $sisa_hr  = (int)$today->diff($tgl_exp)->format('%r%a');

                            if ($sisa_hr < 0) {
                                $exp_badge = '<span class="badge bg-danger">Kadaluarsa (' . date('d/m/Y', strtotime($row['tanggal_kadaluarsa'])) . ')</span>';
                            } elseif ($sisa_hr <= 3) {
                                $exp_badge = '<span class="badge bg-warning text-dark">H-' . $sisa_hr . ' (' . date('d/m/Y', strtotime($row['tanggal_kadaluarsa'])) . ')</span>';
                            } else {
                                $exp_badge = '<span class="badge bg-success bg-opacity-10 text-success border border-success">' . date('d/m/Y', strtotime($row['tanggal_kadaluarsa'])) . '</span>';
                            }
                    ?>
                        <tr>
                            <td class="ps-3"><?= $no++ ?></td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($row['nama_bahan'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                                <?php if ($qty < 10) : ?>
                                    <span class="badge bg-danger bg-opacity-10 text-danger" style="font-size: 0.7rem;">Stok Menipis</span>
                                <?php endif; ?>
                            </td>
                            <td>Rp <?= number_format((float)$row['harga'], 0, ',', '.') ?></td>
                            <td class="text-center">
                                <span class="fw-bold <?= ($qty < 10) ? 'text-danger' : 'text-dark' ?>">
                                    <?= format_qty($qty) ?>
                                </span>
                                <small class="text-muted"><?= htmlspecialchars($satuan, ENT_QUOTES, 'UTF-8') ?></small>
                            </td>
                            <td class="text-center"><?= $exp_badge ?></td>
                            <td class="small text-muted"><?= htmlspecialchars($row['nama_supplier'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>

                            <?php if ($can_manage) : ?>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <button type="button"
                                                class="btn btn-sm btn-outline-success"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalPakaiBasah"
                                                data-id="<?= (int)$row['id'] ?>"
                                                data-nama="<?= htmlspecialchars($row['nama_bahan'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                data-stok="<?= format_qty($qty) ?>"
                                                data-satuan="<?= htmlspecialchars($satuan, ENT_QUOTES, 'UTF-8') ?>"
                                                title="Catat Pemakaian">
                                            <i class="fas fa-utensils me-1"></i> Pakai
                                        </button>
                                        <a href="index.php?page=bahan_basah_edit&id=<?= (int)$row['id'] ?>" class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="index.php?page=bahan_basah_hapus&id=<?= (int)$row['id'] ?>&token=<?= csrf_token() ?>"
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Hapus bahan basah ini beserta riwayat mutasinya?')"
                                           title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php
                        endwhile;
                    else :
                        $colspan = $can_manage ? 7 : 6;
                        echo "<tr><td colspan='$colspan' class='text-center py-5 text-muted'>Belum ada data persediaan bahan basah.</td></tr>";
                    endif;
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($can_manage) : ?>
<!-- Modal Catat Pemakaian Bahan Basah -->
<div class="modal fade" id="modalPakaiBasah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-utensils me-2"></i>Pemakaian Bahan Basah</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?page=bahan_basah_pakai" method="POST">
                <?= csrf_field(); ?>
                <input type="hidden" name="id_bahan" id="pakai_id_bahan">

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Bahan</label>
                        <input type="text" id="pakai_nama_bahan" class="form-control bg-light" readonly>
                        <small class="text-muted">Stok tersedia saat ini: <b id="pakai_stok_tersedia" class="text-primary"></b></small>
                    </div>

                    <div class="row">
                        <div class="col-md-7 mb-3">
                            <label class="form-label fw-bold">Jumlah Dipakai</label>
                            <input type="number" name="jumlah_pakai" class="form-control" step="0.001" min="0.001" placeholder="Contoh: 0.5 atau 500" required>
                        </div>
                        <div class="col-md-5 mb-3">
                            <label class="form-label fw-bold">Satuan Pakai</label>
                            <select name="satuan_pakai" id="pakai_satuan" class="form-select" required>
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
                    <div class="alert alert-info py-2 small mb-0">
                        <i class="fas fa-info-circle me-1"></i> Sistem otomatis mengonversi satuan <b>gr ↔ kg</b> dan <b>ml ↔ liter</b>.
                    </div>
                </div>

                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-check me-1"></i> Simpan Pemakaian</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalPakai = document.getElementById('modalPakaiBasah');
    if (modalPakai) {
        modalPakai.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id     = button.getAttribute('data-id');
            const nama   = button.getAttribute('data-nama');
            const stok   = button.getAttribute('data-stok');
            const satuan = button.getAttribute('data-satuan');

            document.getElementById('pakai_id_bahan').value = id;
            document.getElementById('pakai_nama_bahan').value = nama;
            document.getElementById('pakai_stok_tersedia').textContent = stok + ' ' + satuan;

            const selectSatuan = document.getElementById('pakai_satuan');
            for (let i = 0; i < selectSatuan.options.length; i++) {
                if (selectSatuan.options[i].value.toLowerCase() === satuan.toLowerCase()) {
                    selectSatuan.selectedIndex = i;
                    break;
                }
            }
        });
    }
});
</script>
<?php endif; ?>