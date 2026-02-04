<?php
/**
 * Script untuk mendownload gambar motor dari sumber publik
 * Jalankan sekali untuk mengunduh semua gambar motor
 * 
 * Cara pakai: Buka http://localhost/Marvellp/download_motor_images.php
 */

// Set timeout tinggi karena download bisa lama
set_time_limit(300);

$images_dir = __DIR__ . '/assets/images/';

// Daftar motor dan URL gambar (gunakan placeholder berkualitas)
$motors = [
    // Matic
    'honda_scoopy.png' => 'https://imgcdn.oto.com/large/gallery/exterior/106/2529/honda-scoopy-right-side-viewfull-image-442541.jpg',
    'yamaha_aerox.png' => 'https://imgcdn.oto.com/large/gallery/exterior/86/2134/yamaha-aerox-155-right-side-viewfull-image-577296.jpg',
    'yamaha_fazzio.png' => 'https://imgcdn.oto.com/large/gallery/exterior/86/2561/yamaha-fazzio-right-side-viewfull-image-614971.jpg',
    
    // Sport
    'honda_cb150r.png' => 'https://imgcdn.oto.com/large/gallery/exterior/106/1915/honda-cb150r-streetfire-right-side-viewfull-image-460788.jpg',
    'yamaha_vixion.png' => 'https://imgcdn.oto.com/large/gallery/exterior/86/1869/yamaha-vixion-right-side-viewfull-image-324929.jpg',
    'suzuki_gsxr150.png' => 'https://imgcdn.oto.com/large/gallery/exterior/82/1935/suzuki-gsx-r150-right-side-viewfull-image-687135.jpg',
    
    // Supermoto
    'yamaha_wr155.png' => 'https://imgcdn.oto.com/large/gallery/exterior/86/2325/yamaha-wr-155-r-right-side-viewfull-image-577313.jpg',
    'kawasaki_klx230.png' => 'https://imgcdn.oto.com/large/gallery/exterior/87/2271/kawasaki-klx-230-right-side-viewfull-image-379063.jpg',
    'honda_crf250l.png' => 'https://imgcdn.oto.com/large/gallery/exterior/106/2052/honda-crf250rally-right-side-viewfull-image-152044.jpg',
];

echo "<html><head><title>Download Motor Images</title>";
echo "<style>body{font-family:Arial,sans-serif;padding:20px;background:#1a1a1a;color:#fff;}";
echo ".success{color:#4CAF50;}.error{color:#f44336;}.info{color:#FFD700;}";
echo "h1{color:#FFD700;}</style></head><body>";
echo "<h1>🏍️ Download Gambar Motor - Marvell Rental</h1>";

$success_count = 0;
$error_count = 0;

foreach ($motors as $filename => $url) {
    $filepath = $images_dir . $filename;
    
    echo "<p><strong>{$filename}</strong>: ";
    
    // Cek apakah sudah ada
    if (file_exists($filepath)) {
        echo "<span class='info'>⏭️ Sudah ada, dilewati</span></p>";
        continue;
    }
    
    // Download gambar
    $context = stream_context_create([
        'http' => [
            'timeout' => 30,
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ]
    ]);
    
    $image_data = @file_get_contents($url, false, $context);
    
    if ($image_data !== false) {
        // Simpan gambar
        if (file_put_contents($filepath, $image_data)) {
            echo "<span class='success'>✅ Berhasil didownload (" . round(strlen($image_data)/1024) . " KB)</span></p>";
            $success_count++;
        } else {
            echo "<span class='error'>❌ Gagal menyimpan file</span></p>";
            $error_count++;
        }
    } else {
        echo "<span class='error'>❌ Gagal download dari URL</span></p>";
        $error_count++;
    }
    
    // Delay kecil antar request
    usleep(500000); // 0.5 detik
}

echo "<hr>";
echo "<h2>📊 Hasil:</h2>";
echo "<p class='success'>✅ Berhasil: {$success_count} gambar</p>";
echo "<p class='error'>❌ Gagal: {$error_count} gambar</p>";

if ($error_count > 0) {
    echo "<h3 class='info'>💡 Tips jika ada yang gagal:</h3>";
    echo "<ol>";
    echo "<li>Download manual gambar motor dari Google Images</li>";
    echo "<li>Rename sesuai nama file yang dibutuhkan</li>";
    echo "<li>Simpan ke folder: <code>assets/images/</code></li>";
    echo "</ol>";
}

echo "<p><br><a href='index.php' style='color:#FFD700;'>← Kembali ke Home</a></p>";
echo "</body></html>";
?>
