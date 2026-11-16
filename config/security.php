<?php
// 1. HTTP Security Headers
if (!headers_sent()) {
    header("X-Frame-Options: SAMEORIGIN");
    header("X-Content-Type-Options: nosniff");
    header("Referrer-Policy: strict-origin-when-cross-origin");
    header("X-XSS-Protection: 1; mode=block");
}

// 2. Manajemen Sesi Aman (Cookie HttpOnly, Secure jika HTTPS, SameSite Strict)
if (session_status() === PHP_SESSION_NONE) {
    $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (($_SERVER['SERVER_PORT'] ?? 80) == 443);

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $is_https,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);

    session_start();
}

// 3. Generator & Validator Token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrf_token(): string {
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') . '">';
}

function verify_csrf(?string $token): bool {
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

// 4. Helper Kontrol Akses Berdasarkan Role (RBAC)
function require_login(): void {
    if (empty($_SESSION['login']) || $_SESSION['login'] !== true) {
        header("Location: index.php?page=login");
        exit;
    }
}

function require_role(array $allowed_roles, string $redirect = 'index.php?page=dashboard'): void {
    require_login();
    $current_role = strtolower($_SESSION['role'] ?? '');
    if (!in_array($current_role, $allowed_roles, true)) {
        echo "<script>alert('Akses Ditolak! Anda tidak memiliki otoritas untuk halaman/aksi ini.'); window.location='{$redirect}';</script>";
        exit;
    }
}

// 5. Helper Format Angka Stok (Menampilkan desimal hanya jika ada pecahan, misal 1.5 bukan 1.500 atau terpotong floor)
function format_qty($angka): string {
    $val = (float)$angka;
    if (floor($val) == $val) {
        return number_format($val, 0, ',', '.');
    }
    return rtrim(rtrim(number_format($val, 3, ',', '.'), '0'), ',');
}