-- =============================================
-- DATABASE UPDATE - FITUR BARU
-- Jalankan setelah database utama dibuat
-- =============================================

USE marvell_rental;

-- Tambah kolom foto dan verifikasi pada users
ALTER TABLE users 
ADD COLUMN foto VARCHAR(255) DEFAULT NULL AFTER alamat,
ADD COLUMN email_verified TINYINT(1) DEFAULT 0 AFTER foto;

-- Tambah kolom metode pembayaran dan status bayar pada peminjaman
ALTER TABLE peminjaman 
ADD COLUMN metode_pembayaran ENUM('qris', 'bank_bca', 'bank_mandiri', 'bank_bri', 'tunai') DEFAULT NULL AFTER catatan,
ADD COLUMN status_bayar ENUM('belum_bayar', 'menunggu_verifikasi', 'lunas') DEFAULT 'belum_bayar' AFTER metode_pembayaran,
ADD COLUMN bukti_bayar VARCHAR(255) DEFAULT NULL AFTER status_bayar;

-- Tambah kolom detail pada log aktivitas
ALTER TABLE log_aktivitas 
ADD COLUMN tipe ENUM('login', 'logout', 'peminjaman', 'pengembalian', 'pembayaran', 'motor', 'user', 'lainnya') DEFAULT 'lainnya' AFTER aksi,
ADD COLUMN target_id INT DEFAULT NULL AFTER tipe,
ADD COLUMN ip_address VARCHAR(45) DEFAULT NULL AFTER target_id;

-- Tabel OTP untuk verifikasi email
CREATE TABLE IF NOT EXISTS otp_verification (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    otp_code VARCHAR(6) NOT NULL,
    expires_at DATETIME NOT NULL,
    is_used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_otp (otp_code)
);

-- Tabel Pembayaran
CREATE TABLE IF NOT EXISTS pembayaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    peminjaman_id INT NOT NULL,
    metode ENUM('qris', 'bank_bca', 'bank_mandiri', 'bank_bri', 'tunai') NOT NULL,
    jumlah DECIMAL(10, 2) NOT NULL,
    bukti_transfer VARCHAR(255),
    status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    verified_by INT,
    verified_at DATETIME,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (peminjaman_id) REFERENCES peminjaman(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Informasi rekening bank
CREATE TABLE IF NOT EXISTS bank_info (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_bank VARCHAR(50) NOT NULL,
    nomor_rekening VARCHAR(50) NOT NULL,
    atas_nama VARCHAR(100) NOT NULL,
    logo VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert data bank
INSERT INTO bank_info (nama_bank, nomor_rekening, atas_nama, is_active) VALUES
('BCA', '1234567890', 'PT Marvell Rental', 1),
('Mandiri', '0987654321', 'PT Marvell Rental', 1),
('BRI', '1122334455', 'PT Marvell Rental', 1);
