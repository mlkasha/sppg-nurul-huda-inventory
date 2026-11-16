<?php
// Wajib dimuat pertama agar security.php mengatur cookie session HttpOnly & Secure sebelum session_start()
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

// 1. CEK HALAMAN & NORMALISASI INPUT
$page = isset($_GET['page']) ? strtolower(trim($_GET['page'])) : 'hero';

// Daftar halaman publik yang bisa diakses tanpa login
$publicPages = ['hero', 'login', 'auth_login', 'register', 'auth_register', 'logout'];

if (empty($_SESSION['login']) && !in_array($page, $publicPages, true)) {
    header("Location: index.php?page=login");
    exit;
}

// 2. PROTEKSI ROLE (RBAC)
$role = isset($_SESSION['role']) ? strtolower($_SESSION['role']) : '';

// Daftar halaman yang tidak boleh diakses oleh Gudang
$restrictedForGudang = [
    'rekap_harian_edit', 'rekap_harian_update', 'rekap_harian_hapus',
    'rekap_bulanan_edit', 'rekap_bulanan_hapus',
    'stok_akhir_edit', 'stok_akhir_hapus',
    'supplier', 'supplier_tambah', 'supplier_store', 'supplier_edit', 'supplier_update', 'supplier_hapus',
    'user', 'user_tambah', 'user_store', 'user_edit', 'user_update', 'user_hapus'
];

// Daftar halaman yang tidak boleh diakses oleh Kepala (Hanya Monitoring & Laporan)
$restrictedForKepala = [
    'bahan_basah_tambah', 'bahan_basah_edit', 'bahan_basah_hapus', 'bahan_basah_store', 'bahan_basah_update', 'bahan_basah_pakai',
    'bahan_kering_tambah', 'bahan_kering_edit', 'bahan_kering_hapus', 'bahan_kering_store', 'bahan_kering_update', 'bahan_kering_pakai',
    'rekap_harian_edit', 'rekap_harian_update', 'rekap_harian_hapus',
    'supplier', 'supplier_tambah', 'supplier_edit', 'supplier_hapus', 'supplier_store', 'supplier_update',
    'user', 'user_tambah', 'user_store', 'user_edit', 'user_update', 'user_hapus'
];

if ($role === 'gudang' && in_array($page, $restrictedForGudang, true)) {
    header("Location: index.php?page=dashboard&err=access_denied");
    exit;
}

if ($role === 'kepala' && in_array($page, $restrictedForKepala, true)) {
    header("Location: index.php?page=dashboard&err=access_denied");
    exit;
}

// Helper lokal validasi CSRF untuk aksi POST & Hapus GET
function cek_csrf_post(string $redirect): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf($_POST['csrf_token'] ?? '')) {
        header("Location: {$redirect}&err=csrf_invalid");
        exit;
    }
}

function cek_csrf_get(string $redirect): void {
    if (!verify_csrf($_GET['token'] ?? '')) {
        header("Location: {$redirect}&err=csrf_invalid");
        exit;
    }
}

// 3. LOGIKA ROUTING
switch ($page) {

    /* --- HALAMAN DEPAN & AUTENTIKASI --- */
    case 'hero':
        if (!empty($_SESSION['login'])) {
            header("Location: index.php?page=dashboard");
            exit;
        }
        include '../app/views/hero/index.php';
        break;

    case 'login':
        if (!empty($_SESSION['login'])) {
            header("Location: index.php?page=dashboard");
            exit;
        }
        include '../app/views/auth/login.php';
        break;

    case 'auth_login':
        $auth = new AuthController();
        $auth->login();
        break;

    case 'register':
        if (!empty($_SESSION['login'])) {
            header("Location: index.php?page=dashboard");
            exit;
        }
        include '../app/views/auth/register.php';
        break;

    case 'auth_register':
        $auth = new AuthController();
        $auth->register();
        break;

    case 'logout':
        $auth = new AuthController();
        $auth->logout();
        break;

    /* --- DASHBOARD --- */
    case 'dashboard':
        if (empty($_SESSION['role'])) {
            header("Location: index.php?page=login");
            exit;
        }
        $role_curr = strtolower($_SESSION['role']);
        $allowed_dashboards = ['admin', 'gudang', 'kepala'];
        if (!in_array($role_curr, $allowed_dashboards, true)) {
            $role_curr = 'gudang';
        }
        $title   = "Dashboard " . ucfirst($role_curr);
        $content = "../app/views/dashboard/" . $role_curr . ".php";

        if (file_exists($content)) {
            include '../app/views/dashboard/layout.php';
        } else {
            echo "File dashboard untuk role " . htmlspecialchars($role_curr) . " tidak ditemukan.";
        }
        break;

    /* --- BAHAN BASAH --- */
    case 'bahan_basah':
        $title   = 'Data Bahan Basah';
        $content = '../app/views/bahan_basah/index.php';
        include '../app/views/dashboard/layout.php';
        break;

    case 'bahan_basah_tambah':
        $title   = 'Tambah Bahan Basah';
        $content = '../app/views/bahan_basah/tambah.php';
        include '../app/views/dashboard/layout.php';
        break;

    case 'bahan_basah_store':
        cek_csrf_post('index.php?page=bahan_basah');

        $nama    = trim($_POST['nama_bahan'] ?? '');
        $harga   = (float)($_POST['harga'] ?? 0);
        $qty     = (float)($_POST['quantity'] ?? 0);
        $satuan  = trim($_POST['satuan'] ?? 'kg');
        $exp     = trim($_POST['tanggal_kadaluarsa'] ?? '');
        $input   = trim($_POST['tanggal_input_stok'] ?? date('Y-m-d'));
        $sid     = !empty($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : null;
        $user_id = (int)($_SESSION['id'] ?? 1);

        $stmt = mysqli_prepare($koneksi, "INSERT INTO bahan_basah (nama_bahan, harga, quantity, satuan, tanggal_kadaluarsa, tanggal_input_stok, supplier_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sddsssi", $nama, $harga, $qty, $satuan, $exp, $input, $sid);
        mysqli_stmt_execute($stmt);
        $last_id = mysqli_insert_id($koneksi);
        mysqli_stmt_close($stmt);

        if ($last_id > 0) {
            $stmt_rek = mysqli_prepare($koneksi, "INSERT INTO rekap_harian (tanggal, id_bahan_basah, id_bahan_kering, stok_awal, masuk, keluar, stok_akhir_hari, created_by) VALUES (?, ?, NULL, 0, ?, 0, ?, ?)");
            mysqli_stmt_bind_param($stmt_rek, "sdddi", $input, $last_id, $qty, $qty, $user_id);
            mysqli_stmt_execute($stmt_rek);
            mysqli_stmt_close($stmt_rek);
        }

        header("Location: index.php?page=bahan_basah&msg=success_store");
        exit;

    case 'bahan_basah_edit':
        $title   = 'Edit Bahan Basah';
        $content = '../app/views/bahan_basah/edit.php';
        include '../app/views/dashboard/layout.php';
        break;

    case 'bahan_basah_update':
        cek_csrf_post('index.php?page=bahan_basah');

        $id     = (int)($_POST['id'] ?? 0);
        $nama   = trim($_POST['nama_bahan'] ?? '');
        $harga  = (float)($_POST['harga'] ?? 0);
        $qty    = (float)($_POST['quantity'] ?? 0);
        $satuan = trim($_POST['satuan'] ?? 'kg');
        $exp    = trim($_POST['tanggal_kadaluarsa'] ?? '');
        $sid    = !empty($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : null;

        $stmt = mysqli_prepare($koneksi, "UPDATE bahan_basah SET nama_bahan = ?, harga = ?, quantity = ?, satuan = ?, tanggal_kadaluarsa = ?, supplier_id = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "sddssii", $nama, $harga, $qty, $satuan, $exp, $sid, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header("Location: index.php?page=bahan_basah&msg=success_update");
        exit;

    case 'bahan_basah_hapus':
        cek_csrf_get('index.php?page=bahan_basah');

        $id = (int)($_GET['id'] ?? 0);
        $stmt1 = mysqli_prepare($koneksi, "DELETE FROM rekap_harian WHERE id_bahan_basah = ?");
        mysqli_stmt_bind_param($stmt1, "i", $id);
        mysqli_stmt_execute($stmt1);
        mysqli_stmt_close($stmt1);

        $stmt2 = mysqli_prepare($koneksi, "DELETE FROM bahan_basah WHERE id = ?");
        mysqli_stmt_bind_param($stmt2, "i", $id);
        mysqli_stmt_execute($stmt2);
        mysqli_stmt_close($stmt2);

        header("Location: index.php?page=bahan_basah&msg=success_delete");
        exit;

    case 'bahan_basah_pakai':
        cek_csrf_post('index.php?page=bahan_basah');

        $id_bahan     = (int)($_POST['id_bahan'] ?? 0);
        $qty_input    = (float)($_POST['jumlah_pakai'] ?? 0);
        $satuan_pakai = strtolower(trim($_POST['satuan_pakai'] ?? ''));
        $tgl_pakai    = date('Y-m-d');
        $user_id      = (int)($_SESSION['id'] ?? 1);

        $stmt_cek = mysqli_prepare($koneksi, "SELECT quantity, satuan FROM bahan_basah WHERE id = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt_cek, "i", $id_bahan);
        mysqli_stmt_execute($stmt_cek);
        $res_stok = mysqli_stmt_get_result($stmt_cek);
        $d_stok   = mysqli_fetch_assoc($res_stok);
        mysqli_stmt_close($stmt_cek);

        if (!$d_stok || $qty_input <= 0) {
            header("Location: index.php?page=bahan_basah&err=invalid");
            exit;
        }

        $stok_awal   = (float)$d_stok['quantity'];
        $satuan_stok = strtolower(trim($d_stok['satuan'] ?? ''));

        // Konversi otomatis jika satuan pemakaian berbeda dengan satuan stok (misal gr/gram <-> kg)
        $qty_pakai = $qty_input;
        if (($satuan_stok === 'kg') && in_array($satuan_pakai, ['gr', 'gram'], true)) {
            $qty_pakai = $qty_input / 1000;
        } elseif (in_array($satuan_stok, ['gr', 'gram'], true) && ($satuan_pakai === 'kg')) {
            $qty_pakai = $qty_input * 1000;
        } elseif (($satuan_stok === 'liter') && ($satuan_pakai === 'ml')) {
            $qty_pakai = $qty_input / 1000;
        } elseif (($satuan_stok === 'ml') && ($satuan_pakai === 'liter')) {
            $qty_pakai = $qty_input * 1000;
        }

        if ($stok_awal < $qty_pakai) {
            header("Location: index.php?page=bahan_basah&err=stok_kurang");
            exit;
        }

        $stok_akhir = round($stok_awal - $qty_pakai, 3);

        $stmt_upd = mysqli_prepare($koneksi, "UPDATE bahan_basah SET quantity = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt_upd, "di", $stok_akhir, $id_bahan);
        mysqli_stmt_execute($stmt_upd);
        mysqli_stmt_close($stmt_upd);

        $stmt_rek = mysqli_prepare($koneksi, "INSERT INTO rekap_harian (tanggal, id_bahan_basah, id_bahan_kering, stok_awal, masuk, keluar, stok_akhir_hari, created_by) VALUES (?, ?, NULL, ?, 0, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt_rek, "sidddi", $tgl_pakai, $id_bahan, $stok_awal, $qty_pakai, $stok_akhir, $user_id);
        mysqli_stmt_execute($stmt_rek);
        mysqli_stmt_close($stmt_rek);

        header("Location: index.php?page=bahan_basah&msg=success_update");
        exit;

    /* --- BAHAN KERING --- */
    case 'bahan_kering':
        $title   = 'Data Bahan Kering';
        $content = '../app/views/bahan_kering/index.php';
        include '../app/views/dashboard/layout.php';
        break;

    case 'bahan_kering_tambah':
        $title   = 'Tambah Bahan Kering';
        $content = '../app/views/bahan_kering/tambah.php';
        include '../app/views/dashboard/layout.php';
        break;

    case 'bahan_kering_store':
        cek_csrf_post('index.php?page=bahan_kering');

        $nama    = trim($_POST['nama_bahan'] ?? '');
        $harga   = (float)($_POST['harga'] ?? 0);
        $qty     = (float)($_POST['quantity'] ?? 0);
        $satuan  = trim($_POST['satuan'] ?? 'kg');
        $exp     = trim($_POST['tanggal_kadaluarsa'] ?? '');
        $input   = trim($_POST['tanggal_input_stok'] ?? date('Y-m-d'));
        $sid     = !empty($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : null;
        $user_id = (int)($_SESSION['id'] ?? 1);

        $stmt = mysqli_prepare($koneksi, "INSERT INTO bahan_kering (nama_bahan, harga, quantity, satuan, tanggal_kadaluarsa, tanggal_input_stok, supplier_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sddsssi", $nama, $harga, $qty, $satuan, $exp, $input, $sid);
        mysqli_stmt_execute($stmt);
        $last_id = mysqli_insert_id($koneksi);
        mysqli_stmt_close($stmt);

        if ($last_id > 0) {
            $stmt_rek = mysqli_prepare($koneksi, "INSERT INTO rekap_harian (tanggal, id_bahan_basah, id_bahan_kering, stok_awal, masuk, keluar, stok_akhir_hari, created_by) VALUES (?, NULL, ?, 0, ?, 0, ?, ?)");
            mysqli_stmt_bind_param($stmt_rek, "sdddi", $input, $last_id, $qty, $qty, $user_id);
            mysqli_stmt_execute($stmt_rek);
            mysqli_stmt_close($stmt_rek);
        }

        header("Location: index.php?page=bahan_kering&msg=success_store");
        exit;

    case 'bahan_kering_edit':
        $title   = 'Edit Bahan Kering';
        $content = '../app/views/bahan_kering/edit.php';
        include '../app/views/dashboard/layout.php';
        break;

    case 'bahan_kering_update':
        cek_csrf_post('index.php?page=bahan_kering');

        $id     = (int)($_POST['id'] ?? 0);
        $nama   = trim($_POST['nama_bahan'] ?? '');
        $harga  = (float)($_POST['harga'] ?? 0);
        $qty    = (float)($_POST['quantity'] ?? 0);
        $satuan = trim($_POST['satuan'] ?? 'kg');
        $exp    = trim($_POST['tanggal_kadaluarsa'] ?? '');
        $sid    = !empty($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : null;

        $stmt = mysqli_prepare($koneksi, "UPDATE bahan_kering SET nama_bahan = ?, harga = ?, quantity = ?, satuan = ?, tanggal_kadaluarsa = ?, supplier_id = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "sddssii", $nama, $harga, $qty, $satuan, $exp, $sid, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header("Location: index.php?page=bahan_kering&msg=success_update");
        exit;

    case 'bahan_kering_hapus':
        cek_csrf_get('index.php?page=bahan_kering');

        $id = (int)($_GET['id'] ?? 0);
        $stmt1 = mysqli_prepare($koneksi, "DELETE FROM rekap_harian WHERE id_bahan_kering = ?");
        mysqli_stmt_bind_param($stmt1, "i", $id);
        mysqli_stmt_execute($stmt1);
        mysqli_stmt_close($stmt1);

        $stmt2 = mysqli_prepare($koneksi, "DELETE FROM bahan_kering WHERE id = ?");
        mysqli_stmt_bind_param($stmt2, "i", $id);
        mysqli_stmt_execute($stmt2);
        mysqli_stmt_close($stmt2);

        header("Location: index.php?page=bahan_kering&msg=success_delete");
        exit;

    case 'bahan_kering_pakai':
        cek_csrf_post('index.php?page=bahan_kering');

        $id_bahan     = (int)($_POST['id_bahan'] ?? 0);
        $qty_input    = (float)($_POST['jumlah_pakai'] ?? 0);
        $satuan_pakai = strtolower(trim($_POST['satuan_pakai'] ?? ''));
        $tgl_pakai    = date('Y-m-d');
        $user_id      = (int)($_SESSION['id'] ?? 1);

        $stmt_cek = mysqli_prepare($koneksi, "SELECT quantity, satuan FROM bahan_kering WHERE id = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt_cek, "i", $id_bahan);
        mysqli_stmt_execute($stmt_cek);
        $res_stok = mysqli_stmt_get_result($stmt_cek);
        $d_stok   = mysqli_fetch_assoc($res_stok);
        mysqli_stmt_close($stmt_cek);

        if (!$d_stok || $qty_input <= 0) {
            header("Location: index.php?page=bahan_kering&err=invalid");
            exit;
        }

        $stok_awal   = (float)$d_stok['quantity'];
        $satuan_stok = strtolower(trim($d_stok['satuan'] ?? ''));

        $qty_pakai = $qty_input;
        if (($satuan_stok === 'kg') && in_array($satuan_pakai, ['gr', 'gram'], true)) {
            $qty_pakai = $qty_input / 1000;
        } elseif (in_array($satuan_stok, ['gr', 'gram'], true) && ($satuan_pakai === 'kg')) {
            $qty_pakai = $qty_input * 1000;
        } elseif (($satuan_stok === 'liter') && ($satuan_pakai === 'ml')) {
            $qty_pakai = $qty_input / 1000;
        } elseif (($satuan_stok === 'ml') && ($satuan_pakai === 'liter')) {
            $qty_pakai = $qty_input * 1000;
        }

        if ($stok_awal < $qty_pakai) {
            header("Location: index.php?page=bahan_kering&err=stok_kurang");
            exit;
        }

        $stok_akhir = round($stok_awal - $qty_pakai, 3);

        $stmt_upd = mysqli_prepare($koneksi, "UPDATE bahan_kering SET quantity = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt_upd, "di", $stok_akhir, $id_bahan);
        mysqli_stmt_execute($stmt_upd);
        mysqli_stmt_close($stmt_upd);

        $stmt_rek = mysqli_prepare($koneksi, "INSERT INTO rekap_harian (tanggal, id_bahan_basah, id_bahan_kering, stok_awal, masuk, keluar, stok_akhir_hari, created_by) VALUES (?, NULL, ?, ?, 0, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt_rek, "sidddi", $tgl_pakai, $id_bahan, $stok_awal, $qty_pakai, $stok_akhir, $user_id);
        mysqli_stmt_execute($stmt_rek);
        mysqli_stmt_close($stmt_rek);

        header("Location: index.php?page=bahan_kering&msg=success_update");
        exit;

    /* --- REKAP MUTASI --- */
    case 'rekap':
        $title   = 'Rekap Mutasi Bahan';
        $content = '../app/views/rekap/index.php';
        include '../app/views/dashboard/layout.php';
        break;

    case 'rekap_harian_edit':
        $title   = 'Edit Log Mutasi';
        $content = '../app/views/rekap/edit.php';
        include '../app/views/dashboard/layout.php';
        break;

    case 'rekap_harian_update':
        cek_csrf_post('index.php?page=rekap');

        $id_harian = (int)($_POST['id_harian'] ?? 0);
        $stok_awal = (float)($_POST['stok_awal'] ?? 0);
        $masuk     = (float)($_POST['masuk'] ?? 0);
        $keluar    = (float)($_POST['keluar'] ?? 0);
        $stok_akhir = round($stok_awal + $masuk - $keluar, 3);

        $stmt = mysqli_prepare($koneksi, "UPDATE rekap_harian SET stok_awal = ?, masuk = ?, keluar = ?, stok_akhir_hari = ? WHERE id_harian = ?");
        mysqli_stmt_bind_param($stmt, "ddddi", $stok_awal, $masuk, $keluar, $stok_akhir, $id_harian);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header("Location: index.php?page=rekap&msg=success_update");
        exit;

    case 'rekap_harian_hapus':
        cek_csrf_get('index.php?page=rekap');

        $id = (int)($_GET['id'] ?? 0);
        $stmt = mysqli_prepare($koneksi, "DELETE FROM rekap_harian WHERE id_harian = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header("Location: index.php?page=rekap&msg=success_delete");
        exit;

    /* --- LAPORAN PDF --- */
    case 'laporan_harian_pdf':
    case 'laporan_mingguan_pdf':
    case 'laporan_bulanan_pdf':
    case 'laporan_stok':
    case 'laporan_expired':
    case 'bahan_basah_pdf':
    case 'bahan_kering_pdf':
        $file = ($page === 'laporan_stok') ? 'stok_menipis_pdf.php' : (($page === 'laporan_expired') ? 'expired_pdf.php' : $page . '.php');
        include '../app/views/laporan/' . $file;
        break;

    /* --- SUPPLIER --- */
    case 'supplier':
        $title   = 'Data Supplier';
        $content = '../app/views/supplier/index.php';
        include '../app/views/dashboard/layout.php';
        break;

    case 'supplier_tambah':
        $title   = 'Tambah Supplier';
        $content = '../app/views/supplier/tambah.php';
        include '../app/views/dashboard/layout.php';
        break;

    case 'supplier_store':
        cek_csrf_post('index.php?page=supplier');

        $nama       = trim($_POST['nama_supplier'] ?? '');
        $kontak     = trim($_POST['kontak'] ?? '');
        $alamat     = trim($_POST['alamat'] ?? '');
        $keterangan = trim($_POST['keterangan'] ?? '-');

        $stmt = mysqli_prepare($koneksi, "INSERT INTO supplier (nama_supplier, kontak, alamat, keterangan) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssss", $nama, $kontak, $alamat, $keterangan);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header("Location: index.php?page=supplier&msg=success_store");
        exit;

    case 'supplier_edit':
        $title   = 'Edit Supplier';
        $content = '../app/views/supplier/edit.php';
        include '../app/views/dashboard/layout.php';
        break;

    case 'supplier_update':
        cek_csrf_post('index.php?page=supplier');

        $id         = (int)($_POST['id'] ?? 0);
        $nama       = trim($_POST['nama_supplier'] ?? '');
        $kontak     = trim($_POST['kontak'] ?? '');
        $alamat     = trim($_POST['alamat'] ?? '');
        $keterangan = trim($_POST['keterangan'] ?? '-');

        $stmt = mysqli_prepare($koneksi, "UPDATE supplier SET nama_supplier = ?, kontak = ?, alamat = ?, keterangan = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "ssssi", $nama, $kontak, $alamat, $keterangan, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header("Location: index.php?page=supplier&msg=success_update");
        exit;

    case 'supplier_hapus':
        cek_csrf_get('index.php?page=supplier');

        $id = (int)($_GET['id'] ?? 0);
        $stmt = mysqli_prepare($koneksi, "DELETE FROM supplier WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header("Location: index.php?page=supplier&msg=success_delete");
        exit;

    /* --- MANAJEMEN PENGGUNA (USER) --- */
    case 'user':
        $title   = 'Manajemen Pengguna';
        $content = '../app/views/user/index.php';
        include '../app/views/dashboard/layout.php';
        break;

    case 'user_tambah':
        $title   = 'Tambah Pengguna';
        $content = '../app/views/user/tambah.php';
        include '../app/views/dashboard/layout.php';
        break;

    case 'user_store':
        cek_csrf_post('index.php?page=user');

        $username     = trim($_POST['username'] ?? '');
        $nama_lengkap = trim($_POST['nama_lengkap'] ?? $username);
        $no_hp        = trim($_POST['no_hp'] ?? '');
        $password     = $_POST['password'] ?? '';
        $role_usr     = strtolower(trim($_POST['role'] ?? 'gudang'));

        if ($username === '' || strlen($password) < 6) {
            header("Location: index.php?page=user&err=invalid");
            exit;
        }

        $hashed = password_hash($password, PASSWORD_BCRYPT);

        $stmt = mysqli_prepare($koneksi, "INSERT INTO users (username, nama_lengkap, password, role, no_hp) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sssss", $username, $nama_lengkap, $hashed, $role_usr, $no_hp);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header("Location: index.php?page=user&msg=success_store");
        exit;

    case 'user_edit':
        $title   = 'Edit Pengguna';
        $content = '../app/views/user/edit.php';
        include '../app/views/dashboard/layout.php';
        break;

    case 'user_update':
        cek_csrf_post('index.php?page=user');

        $id           = (int)($_POST['id'] ?? 0);
        $username     = trim($_POST['username'] ?? '');
        $nama_lengkap = trim($_POST['nama_lengkap'] ?? $username);
        $no_hp        = trim($_POST['no_hp'] ?? '');
        $role_new     = strtolower(trim($_POST['role'] ?? 'gudang'));
        $password     = $_POST['password'] ?? '';

        if (!empty($password)) {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $stmt = mysqli_prepare($koneksi, "UPDATE users SET username = ?, nama_lengkap = ?, password = ?, role = ?, no_hp = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "sssssi", $username, $nama_lengkap, $hashed, $role_new, $no_hp, $id);
        } else {
            $stmt = mysqli_prepare($koneksi, "UPDATE users SET username = ?, nama_lengkap = ?, role = ?, no_hp = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "ssssi", $username, $nama_lengkap, $role_new, $no_hp, $id);
        }

        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header("Location: index.php?page=user&msg=success_update");
        exit;

    case 'user_hapus':
        cek_csrf_get('index.php?page=user');

        $id = (int)($_GET['id'] ?? 0);
        // Cegah admin menghapus akunnya sendiri
        if ($id === (int)($_SESSION['id'] ?? 0)) {
            header("Location: index.php?page=user&err=access_denied");
            exit;
        }

        $stmt = mysqli_prepare($koneksi, "DELETE FROM users WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header("Location: index.php?page=user&msg=success_delete");
        exit;

    default:
        http_response_code(404);
        echo "<h1>404 Not Found</h1>";
        echo "<p>Halaman dengan kode <b>" . htmlspecialchars($page, ENT_QUOTES, 'UTF-8') . "</b> tidak terdaftar di index.php</p>";
        echo "<a href='index.php?page=dashboard'>Kembali ke Dashboard</a>";
        break;
}