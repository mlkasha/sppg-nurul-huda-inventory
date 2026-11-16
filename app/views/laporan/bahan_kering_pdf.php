<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_login();

use Dompdf\Dompdf;
use Dompdf\Options;

$nama_pencetak = htmlspecialchars($_SESSION['nama_user'] ?? $_SESSION['username'] ?? 'Administrator', ENT_QUOTES, 'UTF-8');
$query = mysqli_query($koneksi, "SELECT b.*, s.nama_supplier FROM bahan_kering b LEFT JOIN supplier s ON b.supplier_id = s.id ORDER BY b.nama_bahan ASC");

$rows_html = '';
$no = 1;
$total_nilai = 0;

if ($query && mysqli_num_rows($query) > 0) {
    while ($r = mysqli_fetch_assoc($query)) {
        $qty = (float)$r['quantity'];
        $hrg = (float)$r['harga'];
        $sub = $qty * $hrg;
        $total_nilai += $sub;

        $rows_html .= '
        <tr>
            <td align="center">' . $no++ . '</td>
            <td>' . htmlspecialchars($r['nama_bahan'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>
            <td align="right">Rp ' . number_format($hrg, 0, ',', '.') . '</td>
            <td align="center">' . format_qty($qty) . ' ' . htmlspecialchars($r['satuan'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>
            <td align="right">Rp ' . number_format($sub, 0, ',', '.') . '</td>
            <td align="center">' . date('d/m/Y', strtotime($r['tanggal_kadaluarsa'])) . '</td>
            <td>' . htmlspecialchars($r['nama_supplier'] ?? '-', ENT_QUOTES, 'UTF-8') . '</td>
        </tr>';
    }
} else {
    $rows_html = '<tr><td colspan="7" align="center">Belum ada data bahan kering.</td></tr>';
}

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
        <p>LAPORAN PERSEDIAAN BAHAN KERING</p>
        <p>Dicetak pada: ' . date('d F Y, H:i') . ' WIB</p>
    </div>
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Nama Bahan</th>
                <th width="14%">Harga Satuan</th>
                <th width="13%">Sisa Stok</th>
                <th width="16%">Total Nilai</th>
                <th width="13%">Kadaluarsa</th>
                <th width="16%">Supplier</th>
            </tr>
        </thead>
        <tbody>' . $rows_html . '
            <tr>
                <th colspan="4" align="right">TOTAL ESTIMASI NILAI STOK</th>
                <th align="right">Rp ' . number_format($total_nilai, 0, ',', '.') . '</th>
                <th colspan="2"></th>
            </tr>
        </tbody>
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
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

if (ob_get_length()) {
    ob_end_clean();
}
$dompdf->stream("Laporan_Bahan_Kering_" . date('Ymd') . ".pdf", ["Attachment" => false]);
exit;