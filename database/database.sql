-- =============================================
-- DATABASE RENTAL SEPEDA MOTOR - MARVELL RENTAL
-- =============================================

CREATE DATABASE IF NOT EXISTS marvell_rental;
USE marvell_rental;

-- Tabel Users (Admin, Petugas, Peminjam)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    no_hp VARCHAR(20),
    alamat TEXT,
    role ENUM('admin', 'petugas', 'peminjam') NOT NULL DEFAULT 'peminjam',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Motor
CREATE TABLE motor (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_motor VARCHAR(100) NOT NULL,
    jenis ENUM('Matic', 'Sport', 'Supermoto') NOT NULL,
    merk VARCHAR(50) NOT NULL,
    tahun INT,
    plat_nomor VARCHAR(20) UNIQUE,
    harga_per_hari DECIMAL(10, 2) NOT NULL,
    gambar VARCHAR(255),
    deskripsi TEXT,
    status ENUM('tersedia', 'disewa', 'maintenance') DEFAULT 'tersedia',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Peminjaman
CREATE TABLE peminjaman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    motor_id INT NOT NULL,
    tanggal_pinjam DATE NOT NULL,
    tanggal_kembali DATE NOT NULL,
    tanggal_kembali_aktual DATE,
    total_hari INT NOT NULL,
    total_harga DECIMAL(10, 2) NOT NULL,
    status ENUM('menunggu', 'disetujui', 'ditolak', 'selesai') DEFAULT 'menunggu',
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (motor_id) REFERENCES motor(id) ON DELETE CASCADE
);

-- Tabel Pengembalian
CREATE TABLE pengembalian (
    id INT AUTO_INCREMENT PRIMARY KEY,
    peminjaman_id INT NOT NULL UNIQUE,
    tanggal_pengembalian DATE NOT NULL,
    kondisi_motor ENUM('baik', 'rusak_ringan', 'rusak_berat') DEFAULT 'baik',
    denda DECIMAL(10, 2) DEFAULT 0,
    keterangan_denda TEXT,
    petugas_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (peminjaman_id) REFERENCES peminjaman(id) ON DELETE CASCADE,
    FOREIGN KEY (petugas_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Tabel Nota
CREATE TABLE nota (
    id INT AUTO_INCREMENT PRIMARY KEY,
    peminjaman_id INT NOT NULL UNIQUE,
    nomor_pesanan VARCHAR(50) NOT NULL UNIQUE,
    acc_by INT,
    tanggal_acc TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (peminjaman_id) REFERENCES peminjaman(id) ON DELETE CASCADE,
    FOREIGN KEY (acc_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Log Aktivitas
CREATE TABLE log_aktivitas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    aksi VARCHAR(255) NOT NULL,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- =============================================
-- DATA AWAL (SAMPLE DATA)
-- =============================================

-- Insert Admin Default
INSERT INTO users (nama, email, password, no_hp, role) VALUES
('Administrator', 'admin@marvell.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567890', 'admin'),
('Petugas Rental', 'staff@marvell.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567891', 'petugas');

-- Password default: password (sudah di-hash dengan bcrypt)

-- Insert Sample Motor
INSERT INTO motor (nama_motor, jenis, merk, tahun, plat_nomor, harga_per_hari, gambar, deskripsi, status) VALUES
('Honda Beat', 'Matic', 'Honda', 2023, 'B 1234 ABC', 75000.00, 'honda_beat.png', 'Motor matic hemat BBM, cocok untuk harian', 'tersedia'),
('Honda Vario 160', 'Matic', 'Honda', 2024, 'B 2345 DEF', 100000.00, 'honda_vario.png', 'Motor matic premium dengan fitur lengkap', 'tersedia'),
('Yamaha NMAX', 'Matic', 'Yamaha', 2024, 'B 3456 GHI', 150000.00, 'yamaha_nmax.png', 'Motor maxi scooter nyaman untuk touring', 'tersedia'),
('Honda PCX 160', 'Matic', 'Honda', 2024, 'B 4567 JKL', 175000.00, 'honda_pcx.png', 'Motor premium dengan teknologi terbaru', 'tersedia'),
('Yamaha R15', 'Sport', 'Yamaha', 2023, 'B 5678 MNO', 200000.00, 'yamaha_r15.png', 'Motor sport bertenaga untuk penggemar speed', 'tersedia'),
('Honda CBR 150R', 'Sport', 'Honda', 2023, 'B 6789 PQR', 200000.00, 'honda_cbr.png', 'Motor sport racing dengan handling responsif', 'tersedia'),
('Kawasaki KLX 150', 'Supermoto', 'Kawasaki', 2023, 'B 7890 STU', 250000.00, 'kawasaki_klx.png', 'Motor trail untuk off-road dan adventure', 'tersedia'),
('Honda CRF 150L', 'Supermoto', 'Honda', 2024, 'B 8901 VWX', 275000.00, 'honda_crf.png', 'Motor trail tangguh untuk segala medan', 'tersedia');
