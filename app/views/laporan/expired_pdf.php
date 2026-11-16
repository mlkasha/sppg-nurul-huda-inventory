<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_login();

use Dompdf\Dompdf;
use Dompdf\Options;

$nama_pencetak = htmlspecialchars($_SESSION['nama_user'] ?? $_SESSION['username'] ?? 'Administrator', ENT_QUOTES, 'UTF-8');

$query = mysqli_query($koneksi, "
    (SELECT 'Bahan Basah' AS kategori, nama_bahan, quantity, satuan, tanggal_kadaluarsa
     FROM bahan_basah
     WHERE tanggal_kadaluarsa <= DATE_ADD(CURDATE(), INTERVAL 3 DAY))
    UNION ALL
    (SELECT 'Bahan Kering' AS kategori, nama_bahan, quantity, satuan, tanggal_kadaluarsa
     FROM bahan_kering
     WHERE tanggal_kadaluarsa <= DATE_ADD(CURDATE(), INTERVAL 30 DAY))
    ORDER BY tanggal_kadaluarsa ASC
");

$rows_html = '';
$no = 1;
$today = new DateTime(date('Y-m-d'));

if ($query && mysqli_num_rows($query) > 0) {
    while ($r = mysqli_fetch_assoc($query)) {
        $tgl_exp = new DateTime($r['tanggal_kadaluarsa']);
        $sisa_hr = (int)$today->diff($tgl_exp)->format('%r%a');
        $status  = ($sisa_hr < 0) ? '<b style="color:#b00020;">SUDAH KADALUARSA</b>' : '<b style="color:#b45309;">Mendekati Expired (H-' . $sisa_hr . ')</b>';

        $rows_html .= '
        <tr>
            <td align="center">' . $no++ . '</td>
            <td>' . htmlspecialchars($r['kategori'], ENT_QUOTES, 'UTF-8') . '</td>
            <td><b>' . htmlspecialchars($r['nama_bahan'], ENT_QUOTES, 'UTF-8') . '</b></td>
            <td align="center">' . format_qty($r['quantity']) . ' ' . htmlspecialchars($r['satuan'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>
            <td align="center">' . date('d/m/Y', strtotime($r['tanggal_kadaluarsa'])) . '</td>
            <td align="center">' . $status . '</td>
        </tr>';
    }
} else {
    $rows_html = '<tr><td colspan="6" align="center">Tidak ada bahan kadaluarsa atau mendekati kadaluarsa.</td></tr>';
}

$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        .header { text-align: center; border-bottom: 2px solid #b00020; padding-bottom: 10px; margin-bottom: 15px; }
        .header h2 { margin: 0; color: #b00020; }
        .header p { margin: 3px 0 0; font-size: 10px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #999; padding: 6px; }
        th { background-color: #b00020; color: #fff; font-size: 10px; }
        .footer { margin-top: 25px; width: 100%; }
        .ttd { float: right; text-align: center; width: 200px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>SPPG NURUL HUDA</h2>
        <p>LAPORAN ATENSI BAHAN KADALUARSA (BASAH H-3 / KERING H-30)</p>
        <p>Dicetak pada: ' . date('d F Y, H:i') . ' WIB</p>
    </div>
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Kategori</th>
                <th>Nama Bahan</th>
                <th width="15%">Sisa Stok</th>
                <th width="15%">Tgl Kadaluarsa</th>
                <th width="22%">Status</th>
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
$dompdf->stream("Laporan_Kadaluarsa_" . date('Ymd') . ".pdf", ["Attachment" => false]);
exit;