<?php
// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'marvell_rental');
define('DB_USER', 'root');
define('DB_PASS', '');

// Koneksi Database dengan PDO
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Fungsi untuk generate nomor pesanan
function generateNomorPesanan()
{
    return 'MRV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
}

// Fungsi untuk menghitung denda keterlambatan
// Denda = 5% dari total harga sewa per hari keterlambatan
function hitungDenda($tanggal_kembali, $tanggal_kembali_aktual, $total_harga)
{
    $tglKembali = new DateTime($tanggal_kembali);
    $tglAktual = new DateTime($tanggal_kembali_aktual);

    if ($tglAktual > $tglKembali) {
        $selisih = $tglAktual->diff($tglKembali)->days;
        return $selisih * ($total_harga * 0.05); // Denda 5% dari total harga per hari
    }
    return 0;
}

// Fungsi untuk format rupiah
function formatRupiah($angka)
{
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Fungsi untuk format tanggal Indonesia
function formatTanggal($tanggal)
{
    $bulan = [
        1 => 'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    ];

    $date = new DateTime($tanggal);
    return $date->format('d') . ' ' . $bulan[(int) $date->format('m')] . ' ' . $date->format('Y');
}

// Fungsi untuk log aktivitas
function logAktivitas($pdo, $user_id, $aksi, $keterangan = '', $tipe = 'lainnya', $target_id = null)
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $stmt = $pdo->prepare("INSERT INTO log_aktivitas (user_id, aksi, keterangan, tipe, target_id, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $aksi, $keterangan, $tipe, $target_id, $ip]);
}
?>