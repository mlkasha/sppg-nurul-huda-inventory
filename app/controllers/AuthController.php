<?php
require_once __DIR__ . '/../../config/database.php';

class AuthController {
    // Proses Login
    public function login() {
        global $koneksi;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?page=login");
            exit;
        }

        // Validasi Token CSRF
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            header("Location: index.php?page=login&error=csrf");
            exit;
        }

        // Rate Limiting: Maksimal 5 kali gagal login dalam 60 detik
        $attempts = $_SESSION['login_attempts'] ?? 0;
        $lock_until = $_SESSION['login_lock_until'] ?? 0;

        if (time() < $lock_until) {
            header("Location: index.php?page=login&error=rate");
            exit;
        } elseif ($lock_until > 0 && time() >= $lock_until) {
            $_SESSION['login_attempts'] = 0;
            $_SESSION['login_lock_until'] = 0;
            $attempts = 0;
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role     = strtolower(trim($_POST['role'] ?? ''));

        if ($username === '' || $password === '' || $role === '') {
            header("Location: index.php?page=login&error=1");
            exit;
        }

        // Prepared Statement: Ambil user berdasarkan username dan role
        $stmt = mysqli_prepare($koneksi, "SELECT id, username, nama_lengkap, password, role FROM users WHERE username = ? AND LOWER(role) = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "ss", $username, $role);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        // Verifikasi password menggunakan bcrypt/argon2
        if ($user && password_verify($password, $user['password'])) {
            // Reset counter rate limiting
            unset($_SESSION['login_attempts'], $_SESSION['login_lock_until']);

            // Regenerasi ID sesi untuk mencegah Session Fixation
            session_regenerate_id(true);

            // Update Waktu Login terakhir
            $id_user = (int)$user['id'];
            $stmt_upd = mysqli_prepare($koneksi, "UPDATE users SET last_login = NOW() WHERE id = ?");
            mysqli_stmt_bind_param($stmt_upd, "i", $id_user);
            mysqli_stmt_execute($stmt_upd);
            mysqli_stmt_close($stmt_upd);

            // Set Session Baru (termasuk nama_user untuk layout & laporan PDF)
            $_SESSION['login']     = true;
            $_SESSION['id']        = $id_user;
            $_SESSION['username']  = $user['username'];
            $_SESSION['nama_user'] = !empty($user['nama_lengkap']) ? $user['nama_lengkap'] : $user['username'];
            $_SESSION['role']      = strtolower($user['role']);

            header("Location: index.php?page=dashboard");
            exit;
        } else {
            // Tambah hitungan gagal login
            $attempts++;
            $_SESSION['login_attempts'] = $attempts;
            if ($attempts >= 5) {
                $_SESSION['login_lock_until'] = time() + 60; // Kunci 60 detik
                header("Location: index.php?page=login&error=rate");
                exit;
            }

            header("Location: index.php?page=login&error=1");
            exit;
        }
    }

    // Proses Registrasi Akun Baru
    public function register() {
        global $koneksi;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?page=register");
            exit;
        }

        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            header("Location: index.php?page=register&error=csrf");
            exit;
        }

        $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
        $username     = trim($_POST['username'] ?? '');
        $no_hp        = trim($_POST['no_hp'] ?? '');
        $password     = $_POST['password'] ?? '';
        $konfirmasi   = $_POST['konfirmasi_password'] ?? '';
        // Role default 'gudang' demi keamanan; Admin bisa mengubah role di Manajemen User
        $role         = 'gudang';

        if ($nama_lengkap === '' || $username === '' || strlen($password) < 6) {
            header("Location: index.php?page=register&error=invalid");
            exit;
        }

        if ($password !== $konfirmasi) {
            header("Location: index.php?page=register&error=mismatch");
            exit;
        }

        // Cek apakah username sudah dipakai
        $stmt_cek = mysqli_prepare($koneksi, "SELECT id FROM users WHERE username = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt_cek, "s", $username);
        mysqli_stmt_execute($stmt_cek);
        mysqli_stmt_store_result($stmt_cek);

        if (mysqli_stmt_num_rows($stmt_cek) > 0) {
            mysqli_stmt_close($stmt_cek);
            header("Location: index.php?page=register&error=exists");
            exit;
        }
        mysqli_stmt_close($stmt_cek);

        // Hash password dengan bcrypt
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $stmt_ins = mysqli_prepare($koneksi, "INSERT INTO users (username, nama_lengkap, password, role, no_hp) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt_ins, "sssss", $username, $nama_lengkap, $hashed_password, $role, $no_hp);

        if (mysqli_stmt_execute($stmt_ins)) {
            mysqli_stmt_close($stmt_ins);
            header("Location: index.php?page=login&success=registered");
            exit;
        } else {
            mysqli_stmt_close($stmt_ins);
            header("Location: index.php?page=register&error=failed");
            exit;
        }
    }

    // Proses Logout Aman
    public function logout() {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();
        header("Location: index.php?page=login&success=logout");
        exit;
    }
}