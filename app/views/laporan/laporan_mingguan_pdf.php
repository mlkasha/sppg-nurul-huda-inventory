<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_login();

use Dompdf\Dompdf;
use Dompdf\Options;

$tgl_awal  = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['tgl_awal'] ?? '') ? $_GET['tgl_awal'] : date('Y-m-d', strtotime('-6 days'));
$tgl_akhir = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['tgl_akhir'] ?? '') ? $_GET['tgl_akhir'] : date('Y-m-d');
$nama_pencetak = htmlspecialchars($_SESSION['nama_user'] ?? $_SESSION['username'] ?? 'Administrator', ENT_QUOTES, 'UTF-8');

$stmt = mysqli_prepare($koneksi, "
    SELECT 
        COALESCE(bb.nama_bahan, bk.nama_bahan, 'Bahan Terhapus') AS nama_bahan,
        COALESCE(bb.satuan, bk.satuan, '') AS satuan,
        COALESCE(bb.quantity, bk.quantity, 0) AS stok_sekarang,
        CASE WHEN r.id_bahan_basah IS NOT NULL THEN 'Basah' ELSE 'Kering' END AS kategori,
        SUM(r.masuk) AS total_masuk,
        SUM(r.keluar) AS total_keluar
    FROM rekap_harian r
    LEFT JOIN bahan_basah bb ON r.id_bahan_basah = bb.id
    LEFT JOIN bahan_kering bk ON r.id_bahan_kering = bk.id
    WHERE r.tanggal BETWEEN ? AND ?
    GROUP BY r.id_bahan_basah, r.id_bahan_kering, nama_bahan, satuan, stok_sekarang, kategori
    ORDER BY nama_bahan ASC
");
mysqli_stmt_bind_param($stmt, "ss", $tgl_awal, $tgl_akhir);
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
            <td align="center"><b>' . format_qty($r['stok_sekarang']) . ' ' . $sat . '</b></td>
        </tr>';
    }
} else {
    $rows_html = '<tr><td colspan="6" align="center">Tidak ada data mutasi pada rentang tanggal ini.</td></tr>';
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
        <p>LAPORAN MUTASI PERSEDIAAN MINGGUAN</p>
        <p>Periode: ' . date('d/m/Y', strtotime($tgl_awal)) . ' s/d ' . date('d/m/Y', strtotime($tgl_akhir)) . '</p>
    </div>
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Nama Bahan</th>
                <th width="12%">Kategori</th>
                <th width="16%">Total Masuk</th>
                <th width="16%">Total Keluar</th>
                <th width="16%">Stok Saat Ini</th>
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
$dompdf->stream("Laporan_Mutasi_Mingguan_" . $tgl_awal . "_" . $tgl_akhir . ".pdf", ["Attachment" => false]);
exit;