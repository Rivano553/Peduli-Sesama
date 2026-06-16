-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 07, 2026 at 04:13 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `crowdfunding_sosial`
--

-- --------------------------------------------------------

--
-- Table structure for table `campaigns`
--

CREATE TABLE `campaigns` (
  `id` int(11) NOT NULL,
  `pengelola_id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `kategori` enum('Bencana Alam','Pendidikan','Kesehatan','Lingkungan','Fasilitas Umum','Lainnya') NOT NULL,
  `lokasi` varchar(200) NOT NULL,
  `deskripsi` text NOT NULL,
  `target_dana` decimal(15,2) NOT NULL,
  `dana_terkumpul` decimal(15,2) NOT NULL DEFAULT 0.00,
  `deadline` date NOT NULL,
  `poster_path` varchar(255) DEFAULT NULL,
  `info_rekening` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `campaigns`
--

INSERT INTO `campaigns` (`id`, `pengelola_id`, `judul`, `kategori`, `lokasi`, `deskripsi`, `target_dana`, `dana_terkumpul`, `deadline`, `poster_path`, `info_rekening`, `created_at`) VALUES
(1, 1, 'Bantuan Darurat Banjir Bandang di Wilayah Sumatera', 'Bencana Alam', 'Sumatra Barat', 'Banjir yang mengguyur wilayah Sumatra selama berhari-hari telah mengakibatkan ratusan rumah warga hilang. Saat ini, ribuan warga terpaksa mengungsi ke tempat yang lebih aman dengan peralatan seadanya.\r\n\r\nKebutuhan mendesak saat ini meliputi:\r\n- Makanan siap saji dan air bersih\r\n- Pakaian layak pakai dan selimut\r\n- Obat-obatan dan perlengkapan kesehatan\r\n- Perlengkapan bayi (popok, susu, dll)\r\n\r\nMari ulurkan tangan kita untuk meringankan beban saudara-saudara kita yang terdampak musibah ini.', 100000000.00, 75000000.00, '2026-06-22', 'poster_1780835221_6a2563955a28f.jpg', 'Bank BCA: 123-456-7890 a.n Yayasan Kemanusiaan Indonesia\r\nBank Mandiri: 098-765-4321 a.n Yayasan Kemanusiaan Indonesia\r\nGoPay/OVO/Dana: 0812-3456-7890', '2026-06-07 12:10:35'),
(2, 2, 'Renovasi Sekolah Dasar di Desa Terpencil NTT', 'Pendidikan', 'Kupang, Nusa Tenggara Timur', 'SD Negeri 12 Desa Fatukoli berada dalam kondisi yang sangat memprihatinkan. Atap bocor, dinding retak, dan lantai berlubang membuat proses belajar mengajar terganggu terutama di musim hujan.\r\n\r\nDana yang terkumpul akan digunakan untuk:\r\n- Perbaikan atap dan dinding kelas\r\n- Pengadaan bangku dan meja belajar baru\r\n- Pembangunan toilet yang layak\r\n- Pengecatan ulang gedung sekolah\r\n\r\nBerikan donasi Anda agar anak-anak di pelosok negeri bisa belajar dengan nyaman.', 50000000.00, 20000000.00, '2026-07-07', 'poster_1780836464_6a2568708e8af.jpg', 'Bank BRI: 456-789-0123 a.n Komunitas Guru Peduli\r\nBank BNI: 321-654-9870 a.n Komunitas Guru Peduli\r\nDana: 0821-1234-5678', '2026-06-07 12:10:35'),
(3, 1, 'Operasi Jantung untuk Adik Budi (3 Tahun)', 'Kesehatan', 'Jakarta Selatan, DKI Jakarta', 'Budi kecil, bocah 3 tahun dari keluarga tidak mampu, terdiagnosis mengalami penyakit jantung bawaan yang membutuhkan operasi segera. Biaya operasi yang sangat besar membuat keluarga kewalahan.\r\n\r\nDana yang dibutuhkan:\r\n- Biaya operasi jantung: Rp 120.000.000\r\n- Biaya rawat inap pasca operasi: Rp 20.000.000\r\n- Obat-obatan: Rp 10.000.000\r\n\r\nSetiap donasi Anda adalah harapan hidup bagi Budi kecil. Mari bantu bersama!', 150000000.00, 135000000.00, '2026-06-12', 'poster_1780835473_6a25649193817.jpeg', 'Bank Mandiri: 111-222-3333 a.n Yayasan Kemanusiaan Indonesia\r\nBCA: 444-555-6666 a.n Yayasan Kemanusiaan Indonesia', '2026-06-07 12:10:35'),
(4, 3, 'Penanaman 10.000 Mangrove di Pantai Utara', 'Lingkungan', 'Brebes, Jawa Tengah', 'Pantai utara Jawa mengalami abrasi yang sangat parah akibat berkurangnya tutupan mangrove. Hal ini mengancam kehidupan nelayan dan ekosistem laut di wilayah tersebut.\r\n\r\nProgram ini bertujuan:\r\n- Menanam 10.000 bibit mangrove di sepanjang 5 km garis pantai\r\n- Melatih masyarakat lokal dalam pengelolaan ekosistem mangrove\r\n- Menciptakan wisata mangrove yang berkelanjutan\r\n- Melindungi 200 KK nelayan dari ancaman abrasi\r\n\r\nDukung program penghijauan ini untuk masa depan lingkungan yang lebih baik.', 30000000.00, 7500000.00, '2026-07-22', 'poster_1780836824_6a2569d8c616d.jpg', 'Bank BNI: 1324-1152-7587 A.n Sahabat Alam Nusantara\r\nGopay/OVO/Dana: 0841-3456-9876', '2026-06-07 12:10:35'),
(5, 2, 'Pembangunan Perpustakaan Desa Maju Bersama', 'Fasilitas Umum', 'Wonosobo, Jawa Tengah', 'Desa Maju Bersama belum memiliki fasilitas perpustakaan yang memadai. Anak-anak dan remaja desa tidak memiliki akses ke buku dan sumber belajar yang cukup.\r\n\r\nDana akan digunakan untuk:\r\n- Pembangunan gedung perpustakaan 6x8 meter\r\n- Pengadaan 500 judul buku bacaan\r\n- Pembelian komputer untuk akses internet\r\n- Pelatihan pengelola perpustakaan\r\n\r\nBersama kita wujudkan perpustakaan desa yang bermanfaat bagi seluruh masyarakat.', 75000000.00, 15000000.00, '2026-08-06', 'poster_1780836540_6a2568bc6e1f7.jpg', 'Bank BNI: 999-111-2222 a.n Komunitas Guru Peduli\r\nTransfer Dana/OVO: 0822-3344-5566', '2026-06-07 12:10:35'),
(6, 3, 'Penanaman Pohon Di Hutan Lindung', 'Lingkungan', 'Semarang, Jawa Tengah', 'Wilayah perbukitan dan hulu Semarang mengalami degradasi lahan yang cukup serius akibat berkurangnya tutupan hutan lindung. Hal ini memicu erosi, mengancam kelestarian sumber mata air, serta meningkatkan risiko banjir kiriman dan tanah longsor yang membahayakan warga di sekitarnya.\r\n\r\nProgram ini bertujuan:\r\n- Menanam 10.000 bibit pohon hutan lindung (seperti beringin, aren, mahoni, kayu manis, atau bambu yang kuat mengikat air dan tanah) di area kritis hutan non-pesisir Semarang.\r\n- Melatih masyarakat lokal dalam pengelolaan dan menjaga kelestarian ekosistem hutan lindung darat.\r\n- Menciptakan kawasan ekowisata hutan alam yang berkelanjutan untuk meningkatkan perekonomian warga tanpa merusak alam.\r\n- Melindungi 200 KK warga setempat dari ancaman bencana tanah longsor, erosi, serta krisis air bersih.', 11000000.00, 0.00, '2027-01-06', 'poster_1780838183_6a256f27d3855.jpg', 'Bank BNI: 1324-1152-7587\r\nGopay/OVO/Dana: 0841-3456-9876', '2026-06-07 13:16:23'),
(7, 3, 'Penanaman 10.000 Mangrove di Pantai Losari', 'Lingkungan', 'Makasar, Sulawesi Selatan', 'Pantai Losari, yang menjadi ikon kebanggaan Kota Makassar, kini menghadapi tantangan lingkungan yang nyata. Meningkatnya ancaman abrasi, penurunan kualitas lingkungan pesisir, serta potensi luapan air laut akibat perubahan iklim global dapat mengancam keindahan lanskap kota sekaligus kesejahteraan masyarakat di sekitar pesisir.\r\n\r\nSebagai langkah konkret untuk memulihkan ekosistem dan melindungi kawasan ikonik ini, program \"Penanaman 10.000 Mangrove di Pantai Losari\" hadir sebagai solusi berbasis alam (nature-based solution) yang berkelanjutan.\r\n\r\nTujuan Utama Program\r\n- Restorasi Ekosistem Pesisir: Menanam 10.000 bibit mangrove berkualitas (seperti jenis Rhizophora) guna membentuk sabuk hijau (greenbelt) yang kokoh di titik-titik rawan sekitar kawasan Pantai Losari.\r\n\r\n- Mitigasi Bencana Abrasi: Melindungi garis pantai dari hantaman ombak besar, mencegah pengikisan lahan yang lebih parah, serta meminimalkan dampak luapan banjir rob ke area publik dan pemukiman.\r\n\r\n- Pemberdayaan Masyarakat Lokal: Mengedukasi dan melibatkan komunitas nelayan, pemuda, dan warga sekitar dalam proses penyemaian, penanaman, hingga perawatan berkala pohon mangrove agar memiliki rasa kepemilikan yang tinggi.\r\n\r\n- Pengembangan Ekowisata Berkelanjutan: Mengintegrasikan kawasan mangrove baru ini dengan daya tarik wisata Pantai Losari yang sudah ada, menciptakan alternatif wisata edukasi lingkungan (eco-tourism) yang ramah lingkungan.\r\n\r\nDampak dan Manfaat\r\nBagi Lingkungan: Menjadi tempat memijah (spawning ground) dan rumah baru bagi biota laut seperti kepiting, udang, dan berbagai jenis ikan, serta berkontribusi aktif dalam penyerapan emisi karbon di area perkotaan.\r\n\r\nBagi Masyarakat: Memberikan perlindungan bagi ratusan kepala keluarga (KK) nelayan dari ancaman gelombang pasang, sekaligus membantu memulihkan volume tangkapan laut mereka yang sempat menurun akibat rusaknya habitat pesisir.\r\n\r\nMelalui sinergi antara pemerintah, komunitas peduli lingkungan, dan partisipasi aktif warga, program ini diharapkan mampu menjaga keseimbangan alam Pantai Losari agar tetap lestari, tangguh, dan indah untuk generasi mendatang.', 100000000.00, 0.00, '2026-09-22', 'poster_1780838595_6a2570c373024.jpg', 'Bank BNI: 1324-1152-7587 A.n Sahabat Alam Nusantara\r\nGopay/OVO/Dana: 0841-3456-9876', '2026-06-07 13:23:15');

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `id` int(11) NOT NULL,
  `campaign_id` int(11) NOT NULL,
  `donatur_id` int(11) NOT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `metode_pembayaran` varchar(100) NOT NULL,
  `pesan` text DEFAULT NULL,
  `bukti_path` varchar(255) DEFAULT NULL,
  `status` enum('pending','verified','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `donations`
--

INSERT INTO `donations` (`id`, `campaign_id`, `donatur_id`, `nominal`, `metode_pembayaran`, `pesan`, `bukti_path`, `status`, `created_at`) VALUES
(1, 1, 4, 500000.00, 'Transfer Bank BCA', 'Semoga cepat pulih ya', NULL, 'verified', '2026-06-07 12:10:35'),
(2, 1, 5, 250000.00, 'GoPay', 'Tetap semangat!', NULL, 'verified', '2026-06-07 12:10:35'),
(3, 1, 4, 100000.00, 'Transfer Bank Mandiri', NULL, NULL, 'pending', '2026-06-07 12:10:35'),
(4, 2, 5, 200000.00, 'Dana', 'Untuk pendidikan anak bangsa', NULL, 'verified', '2026-06-07 12:10:35'),
(5, 3, 4, 1000000.00, 'Transfer Bank Mandiri', 'Semoga Budi cepat sembuh', NULL, 'verified', '2026-06-07 12:10:35'),
(6, 3, 5, 500000.00, 'Transfer Bank BCA', 'Doa untuk Budi', NULL, 'pending', '2026-06-07 12:10:35'),
(7, 4, 4, 150000.00, 'GoPay', 'Jaga lingkungan kita!', NULL, 'rejected', '2026-06-07 12:10:35'),
(8, 4, 5, 300000.00, 'Transfer Bank BCA', NULL, NULL, 'pending', '2026-06-07 12:10:35');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('donatur','pengelola') NOT NULL DEFAULT 'donatur',
  `nama_kantor` varchar(200) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `phone`, `password`, `role`, `nama_kantor`, `alamat`, `created_at`) VALUES
(1, 'Yayasan Kemanusiaan Indonesia', 'yayasan@kemanusiaan.org', '02112345678', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pengelola', 'Yayasan Kemanusiaan Indonesia', 'Jl. Sudirman No.1, Jakarta Pusat', '2026-06-07 12:10:35'),
(2, 'Komunitas Guru Peduli', 'guru@pedulisesama.org', '02198765432', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pengelola', 'Komunitas Guru Peduli Nusantara', 'Jl. Diponegoro No.45, Yogyakarta', '2026-06-07 12:10:35'),
(3, 'Sahabat Alam Nusantara', 'alam@sahabat.org', '02111223344', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pengelola', 'Sahabat Alam Nusantara', 'Jl. Gatot Subroto No.10, Surabaya', '2026-06-07 12:10:35'),
(4, 'Budi Santoso', 'budi@gmail.com', '081234567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'donatur', NULL, NULL, '2026-06-07 12:10:35'),
(5, 'Siti Rahayu', 'siti@gmail.com', '082345678901', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'donatur', NULL, NULL, '2026-06-07 12:10:35');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `campaigns`
--
ALTER TABLE `campaigns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pengelola_id` (`pengelola_id`);

--
-- Indexes for table `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `campaign_id` (`campaign_id`),
  ADD KEY `donatur_id` (`donatur_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `campaigns`
--
ALTER TABLE `campaigns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `campaigns`
--
ALTER TABLE `campaigns`
  ADD CONSTRAINT `campaigns_ibfk_1` FOREIGN KEY (`pengelola_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `donations`
--
ALTER TABLE `donations`
  ADD CONSTRAINT `donations_ibfk_1` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `donations_ibfk_2` FOREIGN KEY (`donatur_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
