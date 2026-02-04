-- =============================================
-- DATABASE UPDATE - REJECTION REASON & FINE BREAKDOWN
-- =============================================

-- Add rejection reason column to peminjaman table
ALTER TABLE peminjaman ADD COLUMN alasan_tolak TEXT AFTER catatan;

-- Add fine breakdown columns to pengembalian table
ALTER TABLE pengembalian ADD COLUMN denda_keterlambatan DECIMAL(10,2) DEFAULT 0 AFTER denda;
ALTER TABLE pengembalian ADD COLUMN denda_kerusakan DECIMAL(10,2) DEFAULT 0 AFTER denda_keterlambatan;

-- Update existing denda values to denda_keterlambatan (for existing data)
UPDATE pengembalian SET denda_keterlambatan = denda WHERE denda > 0 AND denda_keterlambatan = 0;
