-- =============================================
-- UPDATE DATABASE - STOCK & PROCESSED BY
-- =============================================

-- Tambah kolom stok pada tabel motor (untuk beberapa unit motor yang sama)
ALTER TABLE motor ADD COLUMN stok INT DEFAULT 1 AFTER status;

-- Tambah kolom processed_by untuk menyimpan siapa yang ACC/Tolak peminjaman
ALTER TABLE peminjaman ADD COLUMN processed_by INT AFTER alasan_tolak;

-- Tambah foreign key untuk processed_by
-- ALTER TABLE peminjaman ADD FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL;

-- Update stok default untuk motor yang sudah ada
UPDATE motor SET stok = 1 WHERE stok IS NULL;
