# 🛡️ Secure Employee Management System (EMS)

Sistem Manajemen Karyawan berbasis web yang dirancang menggunakan **PHP 8.x & MySQL / MariaDB**. Berbeda dengan aplikasi web CRUD biasa, proyek ini dibangun dengan standar **Secure Coding** (Pemrograman Aman) untuk menolak berbagai jenis serangan siber paling umum di dunia web (**OWASP Top 10**).

---

## 🎯 Mengapa Sistem Ini Dibangun Berbeda?

Sebagian besar aplikasi web sederhana dibuat hanya agar **"fiturnya berjalan"** (bisa login, bisa simpan data, bisa upload foto). Namun, tanpa disadari, pintunya sangat mudah dibobol oleh peretas (*hacker*).

Aplikasi **Secure EMS** ini dirancang dengan prinsip **Defense-in-Depth** (Keamanan Berlapis). Setiap kali ada data yang masuk atau keluar, sistem memasang "satpam khusus" untuk memeriksa dan menolak tindakan berbahaya.

---

## 🛡️ Rincian Fitur Keamanan (Penjelasan Detail & Awam)

### 1. Anti-SQL Injection (Pencegahan Pembobolan Database)
* **Apa itu Serangan SQLi?**
  Database adalah tempat penyimpanan seluruh data aplikasi. Pada web yang rentan, peretas bisa mengetikkan "kalimat mantra" seperti `' OR '1'='1` di kolom login. Mantra ini mengelabuhi database sehingga peretas bisa **masuk ke sistem tanpa perlu tahu password asli**.
* **Analogi Awam:**
  Pencuri mencoba masuk pintu depan menggunakan perkakas khusus untuk merusak gagang pintu agar langsung terbuka.
* **Bagaimana Kodingan Kita Menahannya?**
  Aplikasi ini menggunakan **PDO Prepared Statements** (`$pdo->prepare()`) dengan penonaktifan emulated prepares (`PDO::ATTR_EMULATE_PREPARES => false`).
  * **Cara Kerja Teknis:** Kode program memisahkan perintah database dengan data input dari pengguna.
  * **Efeknya:** Ketika peretas mengetik `' OR '1'='1`, sistem tidak menganggapnya sebagai perintah pembuka pintu, melainkan murni sebagai nama pengguna teks biasa yang aneh. Hasilnya, pembobolan gagal total.

---

### 2. Anti-Cross-Site Scripting / XSS (Pencegahan Kode Jahat)
* **Apa itu Serangan XSS?**
  Peretas memasukkan program berupa kode JavaScript jahat (misalnya `<script>alert('XSS')</script>`) ke dalam kolom input seperti *Nama Karyawan*. Jika web tidak aman, saat Admin membuka daftar karyawan, kode itu otomatis berjalan di browser Admin untuk mencuri data sesi (*cookie*) atau merusak tampilan web.
* **Analogi Awam:**
  Pencuri menempelkan "surat beracun / bom teks" di papan pengumuman kantor. Saat orang lain membaca papan pengumuman tersebut, bomnya meledak.
* **Bagaimana Kodingan Kita Menahannya?**
  Aplikasi ini menggunakan fungsi **`htmlspecialchars()`** pada setiap data yang ditampilkan ke layar.
  * **Cara Kerja Teknis:** Mengubah karakter khusus HTML seperti `<` menjadi `&lt;` dan `>` menjadi `&gt;`.
  * **Efeknya:** Kode jahat `<script>` dijinakkan dan diubah menjadi teks biasa. Kode tersebut tampil secara mentah di tabel tanpa bisa dieksekusi atau meledak di browser.

---

### 3. Anti-CSRF / Cross-Site Request Forgery (Pencegahan Pemalsuan Perintah)
* **Apa itu Serangan CSRF?**
  Saat kamu sedang login di web EMS, kamu secara tidak sengaja membuka website jebakan di tab lain. Website jebakan tersebut secara diam-diam mengirimkan perintah tersembunyi ke web EMS milikmu untuk menghapus data atau mengubah password tanpa sepengetahuanmu.
* **Analogi Awam:**
  Pencuri membuat surat perintah palsu atas namamu dan mengirimkannya ke kantor saat kamu sedang bekerja.
* **Bagaimana Kodingan Kita Menahannya?**
  Aplikasi ini menggunakan **Token Anti-CSRF** (`$_SESSION['csrf_token']` dan validasi menggunakan `hash_equals()`).
  * **Cara Kerja Teknis:** Setiap kali membuka halaman form, server membuat "stempel rahasia acak" 32-byte kriptografis yang unik untuk sesi tersebut. Saat form dikirim, server mencocokkan stempelnya.
  * **Efeknya:** Website jebakan luar tidak mengetahui stempel rahasia tersebut, sehingga perintah palsu dari luar akan langsung ditolak dan dibuang oleh server.

---

### 4. Arsitektur Unggah File Aman (Pencegahan Virus / Webshell)
* **Apa itu Serangan Remote Code Execution (RCE)?**
  Di fitur upload foto avatar, peretas mencoba mengunggah file berisi program virus (`.php` / *webshell*) bukannya file gambar (`.jpg`). Jika berhasil terunggah dan dibuka, peretas bisa mengambil alih seluruh server dari jauh.
* **Analogi Awam:**
  Pencuri menyusupkan "penjahat/mata-mata" lewat pintu belakang tempat penerimaan paket barang.
* **Bagaimana Kodingan Kita Menahannya?**
  Sistem memasang 3 lapis perlindungan di fitur upload:
  1. **Pemeriksaan Isi File (*Magic Bytes*):** Menggunakan `finfo_file()` untuk mengecek organ dalam file asli (MIME type sesungguhnya). Jika isinya bukan gambar asli, file langsung ditolak (meskipun ekstensinya dipalsukan menjadi `.jpg`).
  2. **Pengacakan Nama File:** Nama file diubah menjadi karakter heksadesimal acak unik (`bin2hex(random_bytes(16))`) agar peretas tidak tahu lokasi tepat filenya dan mencegah *path traversal*.
  3. **Kunci Mati Folder (`.htaccess`):** Folder `uploads/` dipasangi file konfigurasi `.htaccess` (`SetHandler none`, `Options -ExecCGI`). Artinya, file apapun yang ada di folder upload **DILARANG BERJALAN sebagai program PHP**.
* **Efeknya:** File virus peretas hanya akan menjadi "batu mati" yang tidak bisa dijalankan sama sekali.

---

### 5. Keamanan Login & Sesi (Anti-Tebak Password)
* **Apa itu Serangan Brute Force & Session Hijacking?**
  Peretas menggunakan program robot untuk menebak password ribuan kali per detik, atau mencoba mencuri "kartu akses" (cookie sesi) pengguna saat menggunakan Wi-Fi umum.
* **Analogi Awam:**
  Pencuri mencoba menebak kombinasi gembok brankas berkali-kali secara cepat, atau mencuri kunci fisik dari kantongmu.
* **Bagaimana Kodingan Kita Menahannya?**
  * **Rate Limiting (Anti-Brute Force):** Jika salah memasukkan password sebanyak 5 kali, sistem mencatat alamat IP dan memblokir sementara percobaan login selama 15 menit.
  * **Enkripsi BCRYPT (`password_hash`):** Password di dalam database terenkripsi satu arah dengan BCRYPT. Jika database dicuri sekalipun, password aslinya tidak bisa dibaca.
  * **Pengerasan Sesi (Session Hardening):** Cookie login diberi proteksi `HttpOnly` (tidak bisa dicuri lewat program JavaScript) dan `SameSite=Strict`. ID sesi diperbarui otomatis (`session_regenerate_id(true)`) saat berhasil login untuk mencegah *Session Fixation*.

---

### 6. HTTP Security Headers
* Menambahkan header keamanan standar: `X-Frame-Options: DENY` (anti-clickjacking), `X-Content-Type-Options: nosniff`, dan `Content-Security-Policy`.

---

## 🚀 Teknologi yang Digunakan
* **Bahasa Pemrograman:** PHP 8.x
* **Database:** MySQL / MariaDB (Driver PDO)
* **Web Server:** Apache (Lingkungan XAMPP)
* **Frontend:** Bootstrap 5 (Responsive Dark Theme)

---

## 💻 Cara Instalasi & Pengujian di Komputer Lokal

1. **Salin (*clone*) repositori ini** ke dalam folder server lokal kamu (contoh: `C:\xampp\htdocs\`):
   ```bash
   cd C:\xampp\htdocs
   git clone https://github.com/Alfred-Sam/secure-ems.git
   ```

2. **Nyalakan Apache & MySQL** pada XAMPP Control Panel.

3. **Import Database**:
   * Buka **phpMyAdmin** (`http://localhost/phpmyadmin`)
   * Buat database baru bernama `secure_ems`.
   * Import file `database.sql` yang ada di root proyek ini.
   * Atau lewat command line:
     ```bash
     mysql -u root -p secure_ems < database.sql
     ```

4. **Cek Konfigurasi Database**:
   * Pastikan setelan user dan password di file [config/database.php](file:///c:/xampp/htdocs/secure-ems/config/database.php) sesuai dengan MySQL lokal kamu (default XAMPP: user `root`, password kosong).

5. **Akses Aplikasi**:
   * Buka browser dan buka: `http://localhost/secure-ems/login.php`
   * **Akun Default Admin:**
     * **Username:** `admin`
     * **Password:** `admin123`

---

## 📁 Struktur Folder Proyek

```
secure-ems/
│
├── config/
│   └── database.php          # Koneksi database PDO aman
│
├── includes/
│   └── security_headers.php   # Header keamanan HTTP terpusat
│
├── uploads/                  # Folder upload foto karyawan
│   ├── .htaccess             # Kebijakan larangan eksekusi script
│   └── .gitkeep
│
├── database.sql              # Skema database & data akun default
├── login.php                 # Halaman login aman anti-brute force
├── dashboard.php             # Manajemen karyawan aman XSS & CSRF
├── upload_avatar.php         # Handler upload file multi-layer
├── logout.php                # Logout & pembersihan sesi
├── setup_user.php            # Skrip pembuat akun admin
├── README.md                 # Dokumentasi (Bahasa Inggris)
└── README_ID.md              # Dokumentasi (Bahasa Indonesia)
```
