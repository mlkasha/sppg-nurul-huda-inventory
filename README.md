# Sistem Informasi Persediaan Bahan Makanan — SPPG Nurul Huda

Aplikasi web manajemen inventaris dan mutasi stok bahan makanan (Bahan Basah & Bahan Kering) untuk **Satuan Pelayanan Pemenuhan Gizi (SPPG) Nurul Huda**, yang dirancang dengan penerapan standar keamanan aplikasi web (*Secure Coding* & OWASP Top 10 Mitigation).

- **URL Aplikasi Live (HTTPS):** https://sppg-nurulhuda.rf.gd
- **Tech Stack:** PHP Native (MySQLi), MySQL/MariaDB, Bootstrap 5, Chart.js, Dompdf

---

## Fitur Utama & Kontrol Akses (RBAC)

Sistem membagi hak akses pengguna ke dalam tiga peran (*role*):

1. **Administrator (`admin`)**
   - Akses penuh seluruh modul sistem.
   - Manajemen Data Pengguna (tambah, ubah role, reset password, hapus akun dengan proteksi anti-hapus diri sendiri).
   - Manajemen Data Supplier, Bahan Basah, Bahan Kering, Rekap Mutasi Harian/Bulanan, dan Cetak Laporan PDF.
2. **Staf Gudang (`gudang`)**
   - Input stok masuk, edit data bahan, dan pencatatan pemakaian bahan harian (dilengkapi konversi satuan otomatis `kg <-> gr` dan `liter <-> ml`).
   - Monitoring daftar stok menipis (`< 10`) dan peringatan bahan mendekati/melewati tanggal kadaluarsa.
   - Diblokir dari akses manajemen user, manajemen supplier, serta perubahan/penghapusan riwayat rekap mutasi.
3. **Kepala SPPG (`kepala`)**
   - Hak akses khusus pemantauan (*Read-Only Monitoring*): melihat grafik tren penggunaan mingguan, ringkasan inventaris, rekap mutasi, dan mengunduh seluruh jenis laporan PDF.
   - Diblokir dari seluruh rute penambahan, pengubahan, pemakaian, maupun penghapusan data.

---

## Arsitektur Keamanan yang Diterapkan

- **Mitigasi SQL Injection:** Seluruh kueri SQL pada autentikasi dan operasi CRUD menggunakan *Prepared Statements* (`mysqli_prepare` & `mysqli_stmt_bind_param`) disertai *type casting* ketat (`(int)`, `(float)`).
- **Mitigasi Cross-Site Scripting (XSS):** Seluruh output variabel dinamis yang dirender ke HTML di-escape menggunakan `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')`.
- **Mitigasi Cross-Site Request Forgery (CSRF):** Menggunakan token acak kriptografis (`random_bytes(32)`) per sesi yang divalidasi dengan `hash_equals()` pada setiap form `POST` (`csrf_field()`) maupun aksi penghapusan data (`csrf_token()`).
- **Autentikasi & Perlindungan Sesi:**
  - Enkripsi kata sandi satu arah menggunakan `password_hash(..., PASSWORD_BCRYPT)` dan `password_verify()`.
  - Pembatasan percobaan login (*Rate Limiting*): maksimal 5 kali gagal login dalam 60 detik untuk mencegah *Brute-Force Attack*.
  - Regenerasi ID sesi (`session_regenerate_id(true)`) setelah login berhasil untuk mencegah *Session Fixation*, serta pengaturan cookie sesi `HttpOnly`, `Secure`, dan `SameSite=Lax`.
- **Broken Access Control & Anti-LFI:** Pemisahan rute berbasis *whitelist* pada `public/index.php` dan pengecekan peran berlapis (`require_role()`) pada setiap file tampilan maupun aksi.
- **Proteksi File Sensitif & Enkripsi Transportasi:** Kredensial database disimpan di luar web root / pada file `.env` yang diblokir melalui `.htaccess` dan `.gitignore`, serta dijalankan di atas protokol HTTPS (SSL/TLS).

---

## Struktur Direktori

```text
├── app/
│   ├── controllers/        # Logika autentikasi & rate limiting (AuthController.php)
│   └── views/              # Tampilan antarmuka berbasis role (auth, dashboard, bahan, supplier, user, rekap, laporan)
├── config/
│   ├── database.php        # Koneksi database MySQLi via environment variables
│   └── security.php        # Konfigurasi sesi aman, CSRF helper, RBAC helper, & Security Headers
├── public/
│   ├── .htaccess           # Proteksi file sensitif (.env, .git) & pembatasan akses direktori
│   └── index.php           # Front controller / router utama dengan whitelist & validasi CSRF
├── .env.example            # Template konfigurasi environment (tanpa kredensial asli)
├── .gitignore              # Pengecualian file sensitif (.env, vendor/) dari repository
└── README.md               # Dokumentasi proyek & hasil pengujian keamanan