-- ============================================================
-- DATABASE: PeduliSesama - Sistem Crowdfunding Sosial
-- Mini Project #2
-- ============================================================

CREATE DATABASE IF NOT EXISTS crowdfunding_sosial CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE crowdfunding_sosial;

-- ============================================================
-- TABLE: users (donatur + pengelola dalam satu tabel)
-- ============================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(150) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('donatur', 'pengelola') NOT NULL DEFAULT 'donatur',
    -- Khusus pengelola
    nama_kantor VARCHAR(200) NULL,
    alamat TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE: campaigns
-- ============================================================
CREATE TABLE campaigns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pengelola_id INT NOT NULL,
    judul VARCHAR(255) NOT NULL,
    kategori ENUM('Bencana Alam','Pendidikan','Kesehatan','Lingkungan','Fasilitas Umum','Lainnya') NOT NULL,
    lokasi VARCHAR(200) NOT NULL,
    deskripsi TEXT NOT NULL,
    target_dana DECIMAL(15,2) NOT NULL,
    dana_terkumpul DECIMAL(15,2) NOT NULL DEFAULT 0,
    deadline DATE NOT NULL,
    poster_path VARCHAR(255) NULL,
    info_rekening TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pengelola_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================
-- TABLE: donations
-- ============================================================
CREATE TABLE donations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    campaign_id INT NOT NULL,
    donatur_id INT NOT NULL,
    nominal DECIMAL(15,2) NOT NULL,
    metode_pembayaran VARCHAR(100) NOT NULL,
    pesan TEXT NULL,
    bukti_path VARCHAR(255) NULL,
    status ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE,
    FOREIGN KEY (donatur_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================
-- SAMPLE DATA: Users
-- password: donatur123 => password_hash
-- password: pengelola123 => password_hash
-- (gunakan PHP untuk generate, di bawah ini pakai bcrypt manual)
-- ============================================================

-- Pengelola 1
INSERT INTO users (nama, email, phone, password, role, nama_kantor, alamat) VALUES
('Yayasan Kemanusiaan Indonesia', 'yayasan@kemanusiaan.org', '02112345678',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
 'pengelola', 'Yayasan Kemanusiaan Indonesia', 'Jl. Sudirman No.1, Jakarta Pusat');

-- Pengelola 2
INSERT INTO users (nama, email, phone, password, role, nama_kantor, alamat) VALUES
('Komunitas Guru Peduli', 'guru@pedulisesama.org', '02198765432',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'pengelola', 'Komunitas Guru Peduli Nusantara', 'Jl. Diponegoro No.45, Yogyakarta');

-- Pengelola 3
INSERT INTO users (nama, email, phone, password, role, nama_kantor, alamat) VALUES
('Sahabat Alam Nusantara', 'alam@sahabat.org', '02111223344',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'pengelola', 'Sahabat Alam Nusantara', 'Jl. Gatot Subroto No.10, Surabaya');

-- Donatur 1
INSERT INTO users (nama, email, phone, password, role) VALUES
('Budi Santoso', 'budi@gmail.com', '081234567890',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'donatur');

-- Donatur 2
INSERT INTO users (nama, email, phone, password, role) VALUES
('Siti Rahayu', 'siti@gmail.com', '082345678901',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'donatur');

-- ============================================================
-- SAMPLE DATA: Campaigns
-- ============================================================
INSERT INTO campaigns (pengelola_id, judul, kategori, lokasi, deskripsi, target_dana, dana_terkumpul, deadline, poster_path, info_rekening) VALUES
(1, 'Bantuan Darurat Banjir Bandang di Wilayah Pesisir', 'Bencana Alam',
 'Semarang, Jawa Tengah',
 'Hujan deras yang mengguyur wilayah pesisir selama tiga hari berturut-turut telah mengakibatkan banjir bandang yang merendam ratusan rumah warga. Saat ini, ribuan warga terpaksa mengungsi ke tempat yang lebih aman dengan peralatan seadanya.\n\nKebutuhan mendesak saat ini meliputi:\n- Makanan siap saji dan air bersih\n- Pakaian layak pakai dan selimut\n- Obat-obatan dan perlengkapan kesehatan\n- Perlengkapan bayi (popok, susu, dll)\n\nMari ulurkan tangan kita untuk meringankan beban saudara-saudara kita yang terdampak musibah ini.',
 100000000, 75000000, DATE_ADD(CURDATE(), INTERVAL 15 DAY),
 NULL,
 'Bank BCA: 123-456-7890 a.n Yayasan Kemanusiaan Indonesia\nBank Mandiri: 098-765-4321 a.n Yayasan Kemanusiaan Indonesia\nGoPay/OVO/Dana: 0812-3456-7890');

INSERT INTO campaigns (pengelola_id, judul, kategori, lokasi, deskripsi, target_dana, dana_terkumpul, deadline, poster_path, info_rekening) VALUES
(2, 'Renovasi Sekolah Dasar di Desa Terpencil NTT', 'Pendidikan',
 'Kupang, Nusa Tenggara Timur',
 'SD Negeri 12 Desa Fatukoli berada dalam kondisi yang sangat memprihatinkan. Atap bocor, dinding retak, dan lantai berlubang membuat proses belajar mengajar terganggu terutama di musim hujan.\n\nDana yang terkumpul akan digunakan untuk:\n- Perbaikan atap dan dinding kelas\n- Pengadaan bangku dan meja belajar baru\n- Pembangunan toilet yang layak\n- Pengecatan ulang gedung sekolah\n\nBerikan donasi Anda agar anak-anak di pelosok negeri bisa belajar dengan nyaman.',
 50000000, 20000000, DATE_ADD(CURDATE(), INTERVAL 30 DAY),
 NULL,
 'Bank BRI: 456-789-0123 a.n Komunitas Guru Peduli\nBank BNI: 321-654-9870 a.n Komunitas Guru Peduli\nDana: 0821-1234-5678');

INSERT INTO campaigns (pengelola_id, judul, kategori, lokasi, deskripsi, target_dana, dana_terkumpul, deadline, poster_path, info_rekening) VALUES
(1, 'Operasi Jantung untuk Adik Budi (3 Tahun)', 'Kesehatan',
 'Jakarta Selatan, DKI Jakarta',
 'Budi kecil, bocah 3 tahun dari keluarga tidak mampu, terdiagnosis mengalami penyakit jantung bawaan yang membutuhkan operasi segera. Biaya operasi yang sangat besar membuat keluarga kewalahan.\n\nDana yang dibutuhkan:\n- Biaya operasi jantung: Rp 120.000.000\n- Biaya rawat inap pasca operasi: Rp 20.000.000\n- Obat-obatan: Rp 10.000.000\n\nSetiap donasi Anda adalah harapan hidup bagi Budi kecil. Mari bantu bersama!',
 150000000, 135000000, DATE_ADD(CURDATE(), INTERVAL 5 DAY),
 NULL,
 'Bank Mandiri: 111-222-3333 a.n Yayasan Kemanusiaan Indonesia\nBCA: 444-555-6666 a.n Yayasan Kemanusiaan Indonesia');

INSERT INTO campaigns (pengelola_id, judul, kategori, lokasi, deskripsi, target_dana, dana_terkumpul, deadline, poster_path, info_rekening) VALUES
(3, 'Penanaman 10.000 Mangrove di Pantai Utara', 'Lingkungan',
 'Brebes, Jawa Tengah',
 'Pantai utara Jawa mengalami abrasi yang sangat parah akibat berkurangnya tutupan mangrove. Hal ini mengancam kehidupan nelayan dan ekosistem laut di wilayah tersebut.\n\nProgram ini bertujuan:\n- Menanam 10.000 bibit mangrove di sepanjang 5 km garis pantai\n- Melatih masyarakat lokal dalam pengelolaan ekosistem mangrove\n- Menciptakan wisata mangrove yang berkelanjutan\n- Melindungi 200 KK nelayan dari ancaman abrasi\n\nDukung program penghijauan ini untuk masa depan lingkungan yang lebih baik.',
 30000000, 7500000, DATE_ADD(CURDATE(), INTERVAL 45 DAY),
 NULL,
 'Bank BCA: 777-888-9999 a.n Sahabat Alam Nusantara\nGoPay/OVO: 0811-9988-7766');

INSERT INTO campaigns (pengelola_id, judul, kategori, lokasi, deskripsi, target_dana, dana_terkumpul, deadline, poster_path, info_rekening) VALUES
(2, 'Pembangunan Perpustakaan Desa Maju Bersama', 'Fasilitas Umum',
 'Wonosobo, Jawa Tengah',
 'Desa Maju Bersama belum memiliki fasilitas perpustakaan yang memadai. Anak-anak dan remaja desa tidak memiliki akses ke buku dan sumber belajar yang cukup.\n\nDana akan digunakan untuk:\n- Pembangunan gedung perpustakaan 6x8 meter\n- Pengadaan 500 judul buku bacaan\n- Pembelian komputer untuk akses internet\n- Pelatihan pengelola perpustakaan\n\nBersama kita wujudkan perpustakaan desa yang bermanfaat bagi seluruh masyarakat.',
 75000000, 15000000, DATE_ADD(CURDATE(), INTERVAL 60 DAY),
 NULL,
 'Bank BNI: 999-111-2222 a.n Komunitas Guru Peduli\nTransfer Dana/OVO: 0822-3344-5566');

-- ============================================================
-- SAMPLE DATA: Donations
-- ============================================================
INSERT INTO donations (campaign_id, donatur_id, nominal, metode_pembayaran, pesan, status) VALUES
(1, 4, 500000, 'Transfer Bank BCA', 'Semoga cepat pulih ya', 'verified'),
(1, 5, 250000, 'GoPay', 'Tetap semangat!', 'verified'),
(1, 4, 100000, 'Transfer Bank Mandiri', NULL, 'pending'),
(2, 5, 200000, 'Dana', 'Untuk pendidikan anak bangsa', 'verified'),
(3, 4, 1000000, 'Transfer Bank Mandiri', 'Semoga Budi cepat sembuh', 'verified'),
(3, 5, 500000, 'Transfer Bank BCA', 'Doa untuk Budi', 'pending'),
(4, 4, 150000, 'GoPay', 'Jaga lingkungan kita!', 'rejected'),
(4, 5, 300000, 'Transfer Bank BCA', NULL, 'pending');
