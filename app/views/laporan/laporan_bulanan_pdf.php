<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_login();

use Dompdf\Dompdf;
use Dompdf\Options;

$bulan = max(1, min(12, (int)($_GET['bulan'] ?? date('m'))));
$tahun = max(2020, min(2100, (int)($_GET['tahun'] ?? date('Y'))));
$nama_pencetak = htmlspecialchars($_SESSION['nama_user'] ?? $_SESSION['username'] ?? 'Administrator', ENT_QUOTES, 'UTF-8');

$nama_bulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

$stmt = mysqli_prepare($koneksi, "
    SELECT 
        COALESCE(bb.nama_bahan, bk.nama_bahan, 'Bahan Terhapus') AS nama_bahan,
        COALESCE(bb.satuan, bk.satuan, '') AS satuan,
        CASE WHEN r.id_bahan_basah IS NOT NULL THEN 'Basah' ELSE 'Kering' END AS kategori,
        SUM(r.masuk) AS total_masuk,
        SUM(r.keluar) AS total_keluar,
        (SELECT r2.stok_akhir_hari 
         FROM rekap_harian r2 
         WHERE MONTH(r2.tanggal) = ? AND YEAR(r2.tanggal) = ?
           AND (r2.id_bahan_basah <=> r.id_bahan_basah)
           AND (r2.id_bahan_kering <=> r.id_bahan_kering)
         ORDER BY r2.tanggal DESC, r2.id_harian DESC LIMIT 1) AS stok_akhir_bulan
    FROM rekap_harian r
    LEFT JOIN bahan_basah bb ON r.id_bahan_basah = bb.id
    LEFT JOIN bahan_kering bk ON r.id_bahan_kering = bk.id
    WHERE MONTH(r.tanggal) = ? AND YEAR(r.tanggal) = ?
    GROUP BY r.id_bahan_basah, r.id_bahan_kering, nama_bahan, satuan, kategori
    ORDER BY nama_bahan ASC
");
mysqli_stmt_bind_param($stmt, "iiii", $bulan, $tahun, $bulan, $tahun);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

$rows_html = '';
$no = 1;
if ($res && mysqli_num_rows($res) > 0) {
    while ($r = mysqli_fetch_assoc($res)) {
        $sat = htmlspecialchars($r['satuan'], ENT_QUOTES, 'UTF-8');
        $rows_html .= '
        <tr>
            <td align="center">' . $no++ . '</td>
            <td>' . htmlspecialchars($r['nama_bahan'], ENT_QUOTES, 'UTF-8') . '</td>
            <td align="center">' . $r['kategori'] . '</td>
            <td align="center">' . format_qty($r['total_masuk']) . ' ' . $sat . '</td>
            <td align="center">' . format_qty($r['total_keluar']) . ' ' . $sat . '</td>
            <td align="center"><b>' . format_qty($r['stok_akhir_bulan']) . ' ' . $sat . '</b></td>
        </tr>';
    }
} else {
    $rows_html = '<tr><td colspan="6" align="center">Tidak ada data mutasi pada bulan ' . $nama_bulan[$bulan] . ' ' . $tahun . '.</td></tr>';
}
mysqli_stmt_close($stmt);

$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        .header { text-align: center; border-bottom: 2px solid #0b2c5d; padding-bottom: 10px; margin-bottom: 15px; }
        .header h2 { margin: 0; color: #0b2c5d; }
        .header p { margin: 3px 0 0; font-size: 10px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #999; padding: 6px; }
        th { background-color: #0b2c5d; color: #fff; font-size: 10px; }
        .footer { margin-top: 25px; width: 100%; }
        .ttd { float: right; text-align: center; width: 200px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>SPPG NURUL HUDA</h2>
        <p>LAPORAN MUTASI PERSEDIAAN BULANAN</p>
        <p>Periode: ' . $nama_bulan[$bulan] . ' ' . $tahun . '</p>
    </div>
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Nama Bahan</th>
                <th width="12%">Kategori</th>
                <th width="16%">Total Masuk</th>
                <th width="16%">Total Keluar</th>
                <th width="16%">Stok Akhir Bulan</th>
            </tr>
        </thead>
        <tbody>' . $rows_html . '</tbody>
    </table>
    <div class="footer">
        <div class="ttd">
            <p>Tasikmalaya, ' . date('d F Y') . '<br>Petugas Pencetak,</p>
            <br><br><br>
            <p><b><u>' . $nama_pencetak . '</u></b></p>
        </div>
    </div>
</body>
</html>';

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

if (ob_get_length()) {
    ob_end_clean();
}
$dompdf->stream("Laporan_Mutasi_Bulanan_" . $bulan . "_" . $tahun . ".pdf", ["Attachment" => false]);
exit;