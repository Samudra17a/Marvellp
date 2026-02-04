-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 04, 2026 at 08:27 AM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `marvell_rental`
--

-- --------------------------------------------------------

--
-- Table structure for table `bank_info`
--

CREATE TABLE `bank_info` (
  `id` int(11) NOT NULL,
  `nama_bank` varchar(50) NOT NULL,
  `nomor_rekening` varchar(50) NOT NULL,
  `atas_nama` varchar(100) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bank_info`
--

INSERT INTO `bank_info` (`id`, `nama_bank`, `nomor_rekening`, `atas_nama`, `logo`, `is_active`, `created_at`) VALUES
(1, 'BCA', '1234567890', 'PT Marvell Rental', NULL, 1, '2026-02-04 03:28:39'),
(2, 'Mandiri', '0987654321', 'PT Marvell Rental', NULL, 1, '2026-02-04 03:28:39'),
(3, 'BRI', '1122334455', 'PT Marvell Rental', NULL, 1, '2026-02-04 03:28:39');

-- --------------------------------------------------------

--
-- Table structure for table `log_aktivitas`
--

CREATE TABLE `log_aktivitas` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `aksi` varchar(255) NOT NULL,
  `tipe` enum('login','logout','peminjaman','pengembalian','pembayaran','motor','user','lainnya') DEFAULT 'lainnya',
  `target_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `log_aktivitas`
--

INSERT INTO `log_aktivitas` (`id`, `user_id`, `aksi`, `tipe`, `target_id`, `ip_address`, `keterangan`, `created_at`) VALUES
(1, 1, 'Login', 'lainnya', NULL, '::1', 'User login ke sistem', '2026-02-04 03:29:46'),
(2, 1, 'Logout', 'lainnya', NULL, '::1', 'User logout dari sistem', '2026-02-04 03:30:50'),
(3, 2, 'Login', 'lainnya', NULL, '::1', 'User login ke sistem', '2026-02-04 03:31:43'),
(4, 2, 'Logout', 'lainnya', NULL, '::1', 'User logout dari sistem', '2026-02-04 03:32:03'),
(5, 1, 'Login', 'lainnya', NULL, '::1', 'User login ke sistem', '2026-02-04 03:32:10'),
(6, 1, 'Logout', 'lainnya', NULL, '::1', 'User logout dari sistem', '2026-02-04 03:32:18'),
(7, 1, 'Login', 'lainnya', NULL, '::1', 'User login ke sistem', '2026-02-04 03:33:20'),
(8, 1, 'Logout', 'lainnya', NULL, '::1', 'User logout dari sistem', '2026-02-04 03:33:48'),
(9, 2, 'Login', 'lainnya', NULL, '::1', 'User login ke sistem', '2026-02-04 03:34:38'),
(10, 2, 'Logout', 'lainnya', NULL, '::1', 'User logout dari sistem', '2026-02-04 03:36:01'),
(11, 1, 'Login', 'lainnya', NULL, '::1', 'User login ke sistem', '2026-02-04 03:36:13'),
(12, 1, 'Logout', 'lainnya', NULL, '::1', 'User logout dari sistem', '2026-02-04 05:44:26'),
(13, 3, 'Login', 'lainnya', NULL, '::1', 'User login ke sistem', '2026-02-04 06:27:23'),
(14, 3, 'Ajukan Sewa', 'peminjaman', 1, '::1', 'Mengajukan sewa motor: Honda CBR 150R', '2026-02-04 06:27:40'),
(15, 3, 'Pilih Pembayaran', 'pembayaran', 1, '::1', 'Memilih metode pembayaran untuk pengajuan sewa', '2026-02-04 06:27:42'),
(16, 3, 'Ajukan Sewa', 'peminjaman', 2, '::1', 'Mengajukan sewa motor: Yamaha R15', '2026-02-04 06:27:53'),
(17, 3, 'Pilih Pembayaran', 'pembayaran', 2, '::1', 'Memilih metode pembayaran untuk pengajuan sewa', '2026-02-04 06:27:56'),
(18, 3, 'Pilih Pembayaran', 'pembayaran', 2, '::1', 'Memilih metode pembayaran untuk pengajuan sewa', '2026-02-04 06:27:56'),
(19, 3, 'Logout', 'lainnya', NULL, '::1', 'User logout dari sistem', '2026-02-04 06:29:36'),
(20, 2, 'Login', 'lainnya', NULL, '::1', 'User login ke sistem', '2026-02-04 06:29:43'),
(21, 2, 'Tolak Peminjaman', 'lainnya', NULL, '::1', 'Menolak peminjaman ID: 2. Alasan: terlalu lama', '2026-02-04 06:29:55'),
(22, 2, 'ACC Peminjaman', 'lainnya', NULL, '::1', 'Menyetujui peminjaman: Honda CBR 150R', '2026-02-04 06:29:57'),
(23, 2, 'Pengembalian', 'lainnya', NULL, '::1', 'Menyelesaikan pengembalian motor: Honda CBR 150R', '2026-02-04 06:30:26'),
(24, 2, 'Logout', 'lainnya', NULL, '::1', 'User logout dari sistem', '2026-02-04 06:30:33'),
(25, 3, 'Login', 'lainnya', NULL, '::1', 'User login ke sistem', '2026-02-04 06:30:40'),
(26, 3, 'Logout', 'lainnya', NULL, '::1', 'User logout dari sistem', '2026-02-04 06:31:18'),
(27, 1, 'Login', 'lainnya', NULL, '::1', 'User login ke sistem', '2026-02-04 06:31:27'),
(28, 1, 'Logout', 'lainnya', NULL, '::1', 'User logout dari sistem', '2026-02-04 06:33:09'),
(29, 1, 'Logout', 'lainnya', NULL, '::1', 'User logout dari sistem', '2026-02-04 06:33:09'),
(30, 3, 'Login', 'lainnya', NULL, '::1', 'User login ke sistem', '2026-02-04 06:43:03'),
(31, 3, 'Logout', 'lainnya', NULL, '::1', 'User logout dari sistem', '2026-02-04 06:51:45'),
(32, 3, 'Reset Password', 'lainnya', NULL, '::1', 'User berhasil reset password', '2026-02-04 07:13:12'),
(33, 3, 'Reset Password', 'lainnya', NULL, '::1', 'User berhasil reset password', '2026-02-04 07:15:36'),
(34, 3, 'Login', 'lainnya', NULL, '::1', 'User login ke sistem', '2026-02-04 07:15:46'),
(35, 3, 'Ganti Password', 'lainnya', NULL, '::1', 'Mengganti password akun', '2026-02-04 07:16:16'),
(36, 3, 'Logout', 'lainnya', NULL, '::1', 'User logout dari sistem', '2026-02-04 07:16:23'),
(37, 1, 'Login', 'lainnya', NULL, '::1', 'User login ke sistem', '2026-02-04 07:18:53'),
(38, 1, 'Logout', 'lainnya', NULL, '::1', 'User logout dari sistem', '2026-02-04 07:19:02'),
(39, 3, 'Login', 'lainnya', NULL, '::1', 'User login ke sistem', '2026-02-04 07:19:26');

-- --------------------------------------------------------

--
-- Table structure for table `motor`
--

CREATE TABLE `motor` (
  `id` int(11) NOT NULL,
  `nama_motor` varchar(100) NOT NULL,
  `jenis` enum('Matic','Sport','Supermoto') NOT NULL,
  `merk` varchar(50) NOT NULL,
  `tahun` int(11) DEFAULT NULL,
  `plat_nomor` varchar(20) DEFAULT NULL,
  `harga_per_hari` decimal(10,2) NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `status` enum('tersedia','disewa','maintenance') DEFAULT 'tersedia',
  `stok` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `motor`
--

INSERT INTO `motor` (`id`, `nama_motor`, `jenis`, `merk`, `tahun`, `plat_nomor`, `harga_per_hari`, `gambar`, `deskripsi`, `status`, `stok`, `created_at`, `updated_at`) VALUES
(1, 'Honda Beat', 'Matic', 'Honda', 2023, 'B 1234 ABC', '75000.00', 'honda_beat.png', 'Motor matic hemat BBM, cocok untuk harian', 'tersedia', 1, '2026-02-04 03:27:41', '2026-02-04 03:27:41'),
(2, 'Honda Vario 160', 'Matic', 'Honda', 2024, 'B 2345 DEF', '100000.00', 'honda_vario.png', 'Motor matic premium dengan fitur lengkap', 'tersedia', 1, '2026-02-04 03:27:41', '2026-02-04 03:27:41'),
(3, 'Yamaha NMAX', 'Matic', 'Yamaha', 2024, 'B 3456 GHI', '150000.00', 'yamaha_nmax.png', 'Motor maxi scooter nyaman untuk touring', 'tersedia', 1, '2026-02-04 03:27:41', '2026-02-04 03:27:41'),
(4, 'Honda PCX 160', 'Matic', 'Honda', 2024, 'B 4567 JKL', '175000.00', 'honda_pcx.png', 'Motor premium dengan teknologi terbaru', 'tersedia', 1, '2026-02-04 03:27:41', '2026-02-04 03:27:41'),
(5, 'Yamaha R15', 'Sport', 'Yamaha', 2023, 'B 5678 MNO', '200000.00', 'yamaha_r15.png', 'Motor sport bertenaga untuk penggemar speed', 'tersedia', 1, '2026-02-04 03:27:41', '2026-02-04 03:27:41'),
(6, 'Honda CBR 150R', 'Sport', 'Honda', 2023, 'B 6789 PQR', '200000.00', 'honda_cbr.png', 'Motor sport racing dengan handling responsif', 'tersedia', 1, '2026-02-04 03:27:41', '2026-02-04 06:30:26'),
(7, 'Kawasaki KLX 150', 'Supermoto', 'Kawasaki', 2023, 'B 7890 STU', '250000.00', 'kawasaki_klx.png', 'Motor trail untuk off-road dan adventure', 'tersedia', 1, '2026-02-04 03:27:41', '2026-02-04 03:27:41'),
(8, 'Honda CRF 150L', 'Supermoto', 'Honda', 2024, 'B 8901 VWX', '275000.00', 'honda_crf.png', 'Motor trail tangguh untuk segala medan', 'tersedia', 1, '2026-02-04 03:27:41', '2026-02-04 03:27:41'),
(9, 'Honda Scoopy', 'Matic', 'Honda', 2024, 'B 9012 YZA', '80000.00', 'honda_scoopy.png', 'Motor matic retro stylish, cocok untuk jalan-jalan santai dan gaya klasik', 'tersedia', 1, '2026-02-04 06:42:36', '2026-02-04 06:42:36'),
(10, 'Yamaha Aerox 155', 'Matic', 'Yamaha', 2024, 'B 0123 BCD', '125000.00', 'yamaha_aerox.png', 'Motor matic sporty dengan performa tinggi dan fitur canggih', 'tersedia', 1, '2026-02-04 06:42:36', '2026-02-04 06:42:36'),
(11, 'Yamaha Fazzio', 'Matic', 'Yamaha', 2024, 'B 1234 EFG', '90000.00', 'yamaha_fazzio.png', 'Motor matic modern retro dengan desain elegan dan irit BBM', 'tersedia', 1, '2026-02-04 06:42:36', '2026-02-04 06:42:36'),
(12, 'Honda CB150R', 'Sport', 'Honda', 2024, 'B 2345 HIJ', '175000.00', 'honda_cb150r.png', 'Motor sport naked bike dengan handling responsif dan tampilan agresif', 'tersedia', 1, '2026-02-04 06:42:36', '2026-02-04 06:42:36'),
(13, 'Yamaha Vixion', 'Sport', 'Yamaha', 2024, 'B 3456 KLM', '150000.00', 'yamaha_vixion.png', 'Motor sport legendaris dengan performa andal dan irit BBM', 'tersedia', 1, '2026-02-04 06:42:36', '2026-02-04 06:42:36'),
(14, 'Suzuki GSX-R150', 'Sport', 'Suzuki', 2024, 'B 4567 NOP', '200000.00', 'suzuki_gsxr150.png', 'Motor sport full fairing dengan DNA MotoGP dan tenaga besar', 'tersedia', 1, '2026-02-04 06:42:36', '2026-02-04 06:42:36'),
(15, 'Yamaha WR 155R', 'Supermoto', 'Yamaha', 2024, 'B 5678 QRS', '275000.00', 'yamaha_wr155.png', 'Motor trail adventure dengan performa off-road superior', 'tersedia', 1, '2026-02-04 06:42:36', '2026-02-04 06:42:36'),
(16, 'Kawasaki KLX 230', 'Supermoto', 'Kawasaki', 2024, 'B 6789 TUV', '300000.00', 'kawasaki_klx230.png', 'Motor trail tangguh dengan mesin besar untuk segala medan', 'tersedia', 1, '2026-02-04 06:42:36', '2026-02-04 06:42:36'),
(17, 'Honda CRF 250L', 'Supermoto', 'Honda', 2024, 'B 7890 WXY', '350000.00', 'honda_crf250l.png', 'Motor dual purpose adventure untuk touring dan off-road ekstrem', 'tersedia', 1, '2026-02-04 06:42:36', '2026-02-04 06:42:36');

-- --------------------------------------------------------

--
-- Table structure for table `nota`
--

CREATE TABLE `nota` (
  `id` int(11) NOT NULL,
  `peminjaman_id` int(11) NOT NULL,
  `nomor_pesanan` varchar(50) NOT NULL,
  `acc_by` int(11) DEFAULT NULL,
  `tanggal_acc` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nota`
--

INSERT INTO `nota` (`id`, `peminjaman_id`, `nomor_pesanan`, `acc_by`, `tanggal_acc`, `created_at`) VALUES
(1, 1, 'MRV-20260204-A672B', 2, '2026-02-04 06:29:57', '2026-02-04 06:29:57');

-- --------------------------------------------------------

--
-- Table structure for table `otp_verification`
--

CREATE TABLE `otp_verification` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `otp_code` varchar(6) NOT NULL,
  `expires_at` datetime NOT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `otp_verification`
--

INSERT INTO `otp_verification` (`id`, `email`, `otp_code`, `expires_at`, `is_used`, `created_at`) VALUES
(1, 'siyoshh21@gmail.com', '048882', '2026-02-04 07:00:02', 0, '2026-02-04 05:45:02'),
(3, 'freefiregarena097@gmail.com', '774966', '2026-02-04 07:27:04', 0, '2026-02-04 06:12:04'),
(5, 'dandyreyvan123@gmail.com', '319017', '2026-02-04 07:33:52', 0, '2026-02-04 06:18:52'),
(6, 'dandyrevan123@gmail.com', '053176', '2026-02-04 07:35:08', 0, '2026-02-04 06:20:08'),
(16, 'dandyrevan1234@gmail.com', '514468', '2026-02-04 08:29:39', 1, '2026-02-04 07:14:39');

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id` int(11) NOT NULL,
  `peminjaman_id` int(11) NOT NULL,
  `metode` enum('qris','bank_bca','bank_mandiri','bank_bri','tunai') NOT NULL,
  `jumlah` decimal(10,2) NOT NULL,
  `bukti_transfer` varchar(255) DEFAULT NULL,
  `status` enum('pending','verified','rejected') DEFAULT 'pending',
  `verified_by` int(11) DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pembayaran`
--

INSERT INTO `pembayaran` (`id`, `peminjaman_id`, `metode`, `jumlah`, `bukti_transfer`, `status`, `verified_by`, `verified_at`, `keterangan`, `created_at`) VALUES
(1, 1, 'tunai', '200000.00', NULL, 'pending', NULL, NULL, NULL, '2026-02-04 06:27:42'),
(2, 2, 'tunai', '4800000.00', NULL, 'pending', NULL, NULL, NULL, '2026-02-04 06:27:56'),
(3, 2, 'tunai', '4800000.00', NULL, 'pending', NULL, NULL, NULL, '2026-02-04 06:27:56');

-- --------------------------------------------------------

--
-- Table structure for table `peminjaman`
--

CREATE TABLE `peminjaman` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `motor_id` int(11) NOT NULL,
  `tanggal_pinjam` date NOT NULL,
  `tanggal_kembali` date NOT NULL,
  `tanggal_kembali_aktual` date DEFAULT NULL,
  `total_hari` int(11) NOT NULL,
  `total_harga` decimal(10,2) NOT NULL,
  `status` enum('menunggu','disetujui','ditolak','selesai') DEFAULT 'menunggu',
  `catatan` text DEFAULT NULL,
  `alasan_tolak` text DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `metode_pembayaran` enum('qris','bank_bca','bank_mandiri','bank_bri','tunai') DEFAULT NULL,
  `status_bayar` enum('belum_bayar','menunggu_verifikasi','lunas') DEFAULT 'belum_bayar',
  `bukti_bayar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `peminjaman`
--

INSERT INTO `peminjaman` (`id`, `user_id`, `motor_id`, `tanggal_pinjam`, `tanggal_kembali`, `tanggal_kembali_aktual`, `total_hari`, `total_harga`, `status`, `catatan`, `alasan_tolak`, `processed_by`, `metode_pembayaran`, `status_bayar`, `bukti_bayar`, `created_at`, `updated_at`) VALUES
(1, 3, 6, '2026-02-04', '2026-02-05', '2026-02-04', 1, '200000.00', 'selesai', NULL, NULL, 2, 'tunai', 'belum_bayar', NULL, '2026-02-04 06:27:40', '2026-02-04 06:30:26'),
(2, 3, 5, '2026-02-04', '2026-02-28', NULL, 24, '4800000.00', 'ditolak', NULL, 'terlalu lama', 2, 'tunai', 'belum_bayar', NULL, '2026-02-04 06:27:53', '2026-02-04 06:29:55');

-- --------------------------------------------------------

--
-- Table structure for table `pengembalian`
--

CREATE TABLE `pengembalian` (
  `id` int(11) NOT NULL,
  `peminjaman_id` int(11) NOT NULL,
  `tanggal_pengembalian` date NOT NULL,
  `kondisi_motor` enum('baik','rusak_ringan','rusak_berat') DEFAULT 'baik',
  `denda` decimal(10,2) DEFAULT 0.00,
  `denda_keterlambatan` decimal(10,2) DEFAULT 0.00,
  `denda_kerusakan` decimal(10,2) DEFAULT 0.00,
  `keterangan_denda` text DEFAULT NULL,
  `petugas_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengembalian`
--

INSERT INTO `pengembalian` (`id`, `peminjaman_id`, `tanggal_pengembalian`, `kondisi_motor`, `denda`, `denda_keterlambatan`, `denda_kerusakan`, `keterangan_denda`, `petugas_id`, `created_at`) VALUES
(1, 1, '2026-02-04', 'rusak_berat', '99999999.99', '0.00', '0.00', 'weqweqeqw', 2, '2026-02-04 06:30:26');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `role` enum('admin','petugas','peminjam') NOT NULL DEFAULT 'peminjam',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `password`, `no_hp`, `alamat`, `foto`, `email_verified`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'admin@marvell.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567890', NULL, NULL, 0, 'admin', '2026-02-04 03:27:41', '2026-02-04 03:27:41'),
(2, 'Petugas Rental', 'staff@marvell.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567891', NULL, NULL, 0, 'petugas', '2026-02-04 03:27:41', '2026-02-04 03:27:41'),
(3, 'Dandy Ganteng123', 'dandyrevan1234@gmail.com', '$2y$10$1/doWOezcjKfWDngBhYMUemQLIqrHymOLSmfAX4Xbmr/epGnETFgG', '0812345678', 'asdadasd', NULL, 1, 'peminjam', '2026-02-04 06:26:34', '2026-02-04 07:16:16');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bank_info`
--
ALTER TABLE `bank_info`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `motor`
--
ALTER TABLE `motor`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plat_nomor` (`plat_nomor`);

--
-- Indexes for table `nota`
--
ALTER TABLE `nota`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `peminjaman_id` (`peminjaman_id`),
  ADD UNIQUE KEY `nomor_pesanan` (`nomor_pesanan`),
  ADD KEY `acc_by` (`acc_by`);

--
-- Indexes for table `otp_verification`
--
ALTER TABLE `otp_verification`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_otp` (`otp_code`);

--
-- Indexes for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `peminjaman_id` (`peminjaman_id`),
  ADD KEY `verified_by` (`verified_by`);

--
-- Indexes for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `motor_id` (`motor_id`);

--
-- Indexes for table `pengembalian`
--
ALTER TABLE `pengembalian`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `peminjaman_id` (`peminjaman_id`),
  ADD KEY `petugas_id` (`petugas_id`);

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
-- AUTO_INCREMENT for table `bank_info`
--
ALTER TABLE `bank_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `motor`
--
ALTER TABLE `motor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `nota`
--
ALTER TABLE `nota`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `otp_verification`
--
ALTER TABLE `otp_verification`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `peminjaman`
--
ALTER TABLE `peminjaman`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pengembalian`
--
ALTER TABLE `pengembalian`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  ADD CONSTRAINT `log_aktivitas_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `nota`
--
ALTER TABLE `nota`
  ADD CONSTRAINT `nota_ibfk_1` FOREIGN KEY (`peminjaman_id`) REFERENCES `peminjaman` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `nota_ibfk_2` FOREIGN KEY (`acc_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`peminjaman_id`) REFERENCES `peminjaman` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pembayaran_ibfk_2` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD CONSTRAINT `peminjaman_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `peminjaman_ibfk_2` FOREIGN KEY (`motor_id`) REFERENCES `motor` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pengembalian`
--
ALTER TABLE `pengembalian`
  ADD CONSTRAINT `pengembalian_ibfk_1` FOREIGN KEY (`peminjaman_id`) REFERENCES `peminjaman` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pengembalian_ibfk_2` FOREIGN KEY (`petugas_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
