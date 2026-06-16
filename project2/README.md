# 🌱 PeduliSesama - Sistem Crowdfunding Sosial (Mini Project #2)

Website crowdfunding sosial dinamis berbasis PHP & MySQL.

---

## 🗂️ Struktur Folder

```
sistem-crowdfunding-sosial-v2/
├── config/
│   └── db.php                  ← Konfigurasi database (EDIT INI)
├── includes/
│   ├── auth.php                ← Helper autentikasi & session
│   ├── header.php              ← Komponen header
│   └── footer.php              ← Komponen footer
├── uploads/
│   ├── kampanye/               ← Poster kampanye (disimpan di server)
│   └── bukti/                  ← Bukti transfer donasi
├── dashboard/
│   └── donatur.php             ← Halaman riwayat donasi donatur
├── pengelola/
│   ├── index.php               ← Dashboard kelola kampanye
│   ├── tambah.php              ← Tambah kampanye baru
│   ├── edit.php                ← Edit kampanye
│   ├── hapus.php               ← Hapus kampanye
│   └── donasi_list.php         ← Daftar & verifikasi donasi
├── index.php                   ← Halaman utama (search + pagination)
├── detail.php                  ← Detail kampanye
├── donasi.php                  ← Form donasi
├── login.php                   ← Halaman login
├── logout.php                  ← Logout
├── style.css                   ← Stylesheet
├── logo.jpeg                   ← Logo website
└── database.sql                ← Schema + sample data SQL
```

---

## ⚙️ Cara Instalasi

### 1. Persyaratan
- PHP >= 7.4
- MySQL >= 5.7 / MariaDB
- Web server: Apache (XAMPP/WAMP/Laragon) atau Nginx

### 2. Setup Database

Buka phpMyAdmin atau MySQL terminal, lalu jalankan:

```sql
SOURCE /path/ke/folder/database.sql;
```

### 3. Konfigurasi Database

Edit file `config/db.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // ← Ganti sesuai user MySQL Anda
define('DB_PASS', '');           // ← Ganti sesuai password MySQL Anda
define('DB_NAME', 'crowdfunding_sosial');
```

### 4. Letakkan di Web Server

Contoh untuk XAMPP: letakkan folder di `C:/xampp/htdocs/crowdfunding/`

Akses melalui: `http://localhost/crowdfunding/`

---

## 👤 Akun Demo

Password semua akun: **`password`**

| Role | Email |
|------|-------|
| Donatur | budi@gmail.com |
| Donatur | siti@gmail.com |
| Pengelola | yayasan@kemanusiaan.org |
| Pengelola | guru@pedulisesama.org |
| Pengelola | alam@sahabat.org |

---

## ✅ Fitur yang Diimplementasi

### Halaman Utama
- ✅ Daftar kampanye dari database (hanya yang belum lewat deadline)
- ✅ Fungsi search: Judul, Kategori, Lokasi
- ✅ Sorting: deadline terdekat + dana terkecil
- ✅ Pagination (6 kampanye per halaman)

### Login & Logout
- ✅ Login untuk donatur dan pengelola
- ✅ Validasi login dari database
- ✅ Session (nama user tampil di header)
- ✅ Tombol login berubah jadi logout
- ✅ Redirect ke login jika akses tanpa login

### Halaman Detail Kampanye
- ✅ Data dari database
- ✅ Progress bar persentase
- ✅ Tombol "Donasi Sekarang"

### Halaman Donasi
- ✅ Wajib login sebagai donatur
- ✅ Tampil ringkasan kampanye + data donatur dari DB
- ✅ Input: nominal, metode pembayaran, pesan, bukti transfer
- ✅ Validasi: nominal minimal Rp 10.000, bukti wajib
- ✅ Bukti disimpan di server (bukan blob DB)
- ✅ Status PENDING setelah submit

### Pengelolaan Kampanye (Pengelola)
- ✅ CRUD kampanye lengkap
- ✅ Kampanye dengan dana ≥ Rp 10.000 tidak dapat dihapus
- ✅ Poster disimpan di server
- ✅ Lihat daftar donatur per kampanye

### Verifikasi Donasi (Pengelola)
- ✅ Hanya bisa diakses pengelola
- ✅ Lihat semua donasi (verified/pending/rejected)
- ✅ Verifikasi → dana bertambah ke total
- ✅ Tolak → dana tidak bertambah
- ✅ Tampil ringkasan dana per kampanye

### BONUS
- ✅ Ringkasan donasi donatur (Verified/Pending/Ditolak)
- ✅ Riwayat donasi lengkap untuk donatur
- ✅ Indikator visual warna (hijau/kuning/merah)
