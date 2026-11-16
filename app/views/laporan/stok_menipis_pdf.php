<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_login();

use Dompdf\Dompdf;
use Dompdf\Options;

$nama_pencetak = htmlspecialchars($_SESSION['nama_user'] ?? $_SESSION['username'] ?? 'Administrator', ENT_QUOTES, 'UTF-8');

$query = mysqli_query($koneksi, "
    (SELECT 'Bahan Basah' AS kategori, b.nama_bahan, b.quantity, b.satuan, s.nama_supplier, s.kontak
     FROM bahan_basah b
     LEFT JOIN supplier s ON b.supplier_id = s.id
     WHERE b.quantity < 10)
    UNION ALL
    (SELECT 'Bahan Kering' AS kategori, b.nama_bahan, b.quantity, b.satuan, s.nama_supplier, s.kontak
     FROM bahan_kering b
     LEFT JOIN supplier s ON b.supplier_id = s.id
     WHERE b.quantity < 10)
    ORDER BY quantity ASC
");

$rows_html = '';
$no = 1;
if ($query && mysqli_num_rows($query) > 0) {
    while ($r = mysqli_fetch_assoc($query)) {
        $rows_html .= '
        <tr>
            <td align="center">' . $no++ . '</td>
            <td>' . htmlspecialchars($r['kategori'], ENT_QUOTES, 'UTF-8') . '</td>
            <td><b>' . htmlspecialchars($r['nama_bahan'], ENT_QUOTES, 'UTF-8') . '</b></td>
            <td align="center" style="color:#b00020;"><b>' . format_qty($r['quantity']) . ' ' . htmlspecialchars($r['satuan'] ?? '', ENT_QUOTES, 'UTF-8') . '</b></td>
            <td>' . htmlspecialchars($r['nama_supplier'] ?? '-', ENT_QUOTES, 'UTF-8') . '</td>
            <td>' . htmlspecialchars($r['kontak'] ?? '-', ENT_QUOTES, 'UTF-8') . '</td>
        </tr>';
    }
} else {
    $rows_html = '<tr><td colspan="6" align="center">Tidak ada bahan dengan stok menipis (&lt; 10).</td></tr>';
}

$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        .header { text-align: center; border-bottom: 2px solid #d97706; padding-bottom: 10px; margin-bottom: 15px; }
        .header h2 { margin: 0; color: #b45309; }
        .header p { margin: 3px 0 0; font-size: 10px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #999; padding: 6px; }
        th { background-color: #b45309; color: #fff; font-size: 10px; }
        .footer { margin-top: 25px; width: 100%; }
        .ttd { float: right; text-align: center; width: 200px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>SPPG NURUL HUDA</h2>
        <p>LAPORAN DAFTAR BAHAN STOK MENIPIS (&lt; 10)</p>
        <p>Dicetak pada: ' . date('d F Y, H:i') . ' WIB</p>
    </div>
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="14%">Kategori</th>
                <th>Nama Bahan</th>
                <th width="15%">Sisa Stok</th>
                <th width="20%">Supplier</th>
                <th width="16%">Kontak Supplier</th>
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
$dompdf->stream("Laporan_Stok_Menipis_" . date('Ymd') . ".pdf", ["Attachment" => false]);
exit;