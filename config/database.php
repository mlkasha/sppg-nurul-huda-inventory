<?php
require_once __DIR__ . '/security.php';

// Muat file .env dari root proyek
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $name  = trim($name);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        if (!array_key_exists($name, $_ENV)) {
            $_ENV[$name] = $value;
            putenv("$name=$value");
        }
    }
}

$host     = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
$username = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root';
$password = $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '';
$database = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'persediaan_bahan';

mysqli_report(MYSQLI_REPORT_OFF);
$koneksi = mysqli_connect($host, $username, $password, $database);

if (!$koneksi) {
    error_log("Database connection error: " . mysqli_connect_error());
    die("Terjadi gangguan koneksi ke server database. Silakan hubungi administrator.");
}

mysqli_set_charset($koneksi, 'utf8mb4');
date_default_timezone_set('Asia/Jakarta');