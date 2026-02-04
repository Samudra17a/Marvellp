<?php
require_once '../auth/cek_login.php';
cekPetugas();
require_once '../database/koneksi.php';

$message = '';
$error = '';

// Handle pengembalian
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $peminjaman_id = $_POST['peminjaman_id'] ?? 0;
    $kondisi = $_POST['kondisi'] ?? 'baik';
    $denda = $_POST['denda'] ?? 0;
    $keterangan = $_POST['keterangan'] ?? '';

    // Get peminjaman data
    $stmt = $pdo->prepare("SELECT p.*, m.nama_motor, m.id as motor_id FROM peminjaman p JOIN motor m ON p.motor_id = m.id WHERE p.id = ?");
    $stmt->execute([$peminjaman_id]);
    $peminjaman = $stmt->fetch();

    if ($peminjaman && $peminjaman['status'] === 'disetujui') {
        // Insert pengembalian
        $stmt = $pdo->prepare("INSERT INTO pengembalian (peminjaman_id, tanggal_pengembalian, kondisi_motor, denda, keterangan_denda, petugas_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$peminjaman_id, date('Y-m-d'), $kondisi, $denda, $keterangan, $_SESSION['user_id']]);

        // Update peminjaman status
        $stmt = $pdo->prepare("UPDATE peminjaman SET status = 'selesai', tanggal_kembali_aktual = ? WHERE id = ?");
        $stmt->execute([date('Y-m-d'), $peminjaman_id]);

        // Update motor status
        $stmt = $pdo->prepare("UPDATE motor SET status = 'tersedia' WHERE id = ?");
        $stmt->execute([$peminjaman['motor_id']]);

        logAktivitas($pdo, $_SESSION['user_id'], 'Pengembalian', "Menyelesaikan pengembalian motor: " . $peminjaman['nama_motor']);
        $message = 'Pengembalian berhasil diproses!';
    } else {
        $error = 'Data peminjaman tidak valid!';
    }
}

// Get active rentals
$stmt = $pdo->query("
    SELECT p.*, u.nama as nama_peminjam, u.no_hp, m.nama_motor, m.jenis, m.plat_nomor, m.harga_per_hari,
           n.nomor_pesanan
    FROM peminjaman p 
    JOIN users u ON p.user_id = u.id 
    JOIN motor m ON p.motor_id = m.id 
    LEFT JOIN nota n ON p.id = n.peminjaman_id
    WHERE p.status = 'disetujui'
    ORDER BY p.tanggal_kembali ASC
");
$aktif = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengembalian - Petugas Marvell Rental</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <div class="sidebar-logo">MR</div>
                <span class="sidebar-title">Marvell Rental</span>
            </div>

            <div class="sidebar-menu">
                <p class="sidebar-menu-title">Menu Utama</p>
                <ul class="sidebar-nav">
                    <li><a href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="peminjaman.php"><i class="fas fa-clipboard-list"></i> Pengajuan Peminjaman</a></li>
                    <li><a href="pengembalian.php" class="active"><i class="fas fa-undo"></i> Pengembalian</a></li>
                    <li><a href="profil.php"><i class="fas fa-user-cog"></i> Profil</a></li>
                </ul>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="dashboard-header">
                <h1 class="dashboard-title">Pengembalian Motor</h1>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="table-container">
                <div class="table-header">
                    <h3 class="table-title">Motor Sedang Disewa</h3>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>No. Pesanan</th>
                            <th>Peminjam</th>
                            <th>Motor</th>
                            <th>Tgl Pinjam</th>
                            <th>Tgl Kembali</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($aktif) > 0): ?>
                            <?php foreach ($aktif as $a):
                                $tglKembali = new DateTime($a['tanggal_kembali']);
                                $today = new DateTime();
                                $isLate = $today > $tglKembali;
                                $lateDays = $isLate ? $today->diff($tglKembali)->days : 0;
                                ?>
                                <tr>
                                    <td style="color: var(--primary);">
                                        <?= htmlspecialchars($a['nomor_pesanan']) ?>
                                    </td>
                                    <td style="color: var(--text-primary);">
                                        <?= htmlspecialchars($a['nama_peminjam']) ?>
                                        <br><small style="color: var(--text-secondary);">
                                            <?= $a['no_hp'] ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($a['nama_motor']) ?>
                                        <br><small style="color: var(--text-secondary);">
                                            <?= $a['plat_nomor'] ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?= date('d/m/Y', strtotime($a['tanggal_pinjam'])) ?>
                                    </td>
                                    <td>
                                        <?= date('d/m/Y', strtotime($a['tanggal_kembali'])) ?>
                                        <?php if ($isLate): ?>
                                            <br><small style="color: var(--accent-red);">Terlambat
                                                <?= $lateDays ?> hari
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($isLate): ?>
                                            <span class="badge badge-danger">Terlambat</span>
                                        <?php else: ?>
                                            <span class="badge badge-info">Aktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary"
                                            onclick="openReturn(<?= htmlspecialchars(json_encode($a)) ?>, <?= $lateDays ?>, <?= $a['total_harga'] ?>)">
                                            <i class="fas fa-undo"></i> Kembalikan
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada motor yang sedang disewa</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Return Modal -->
    <div class="modal-overlay" id="returnModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Proses Pengembalian</h3>
                <button class="modal-close">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="peminjaman_id" id="return_id">

                <div id="returnInfo"
                    style="background: var(--bg-light); padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                    <!-- Filled by JS -->
                </div>

                <div class="form-group">
                    <label class="form-label">Kondisi Motor</label>
                    <select name="kondisi" class="form-control" required>
                        <option value="baik">Baik</option>
                        <option value="rusak_ringan">Rusak Ringan</option>
                        <option value="rusak_berat">Rusak Berat</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Denda (Rp)</label>
                    <input type="text" name="denda" id="return_denda" class="form-control" value="0" 
                           inputmode="numeric" pattern="[0-9]*" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    <small style="color: var(--text-secondary);">Denda keterlambatan: 5% dari total harga sewa per hari</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Keterangan Denda</label>
                    <textarea name="keterangan" class="form-control" rows="2" placeholder="Opsional"></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-check"></i> Proses Pengembalian
                </button>
            </form>
        </div>
    </div>

    <button class="mobile-menu-btn"><i class="fas fa-bars"></i></button>
    <script src="../assets/js/main.js"></script>
    <script>
        function openReturn(data, lateDays, totalHarga) {
            document.getElementById('return_id').value = data.id;

            // Calculate late fee: 5% of total rental price per late day
            const denda = lateDays * (totalHarga * 0.05);
            document.getElementById('return_denda').value = Math.round(denda);

            document.getElementById('returnInfo').innerHTML = `
                <p style="margin-bottom: 10px;"><strong style="color: var(--primary);">No. Pesanan:</strong> ${data.nomor_pesanan}</p>
                <p style="margin-bottom: 10px;"><strong>Peminjam:</strong> ${data.nama_peminjam}</p>
                <p style="margin-bottom: 10px;"><strong>Motor:</strong> ${data.nama_motor} (${data.plat_nomor})</p>
                <p style="margin-bottom: 10px;"><strong>Tgl Kembali:</strong> ${new Date(data.tanggal_kembali).toLocaleDateString('id-ID')}</p>
                ${lateDays > 0 ? `<p style="color: var(--accent-red);"><strong>Keterlambatan:</strong> ${lateDays} hari</p>` : ''}
            `;

            document.getElementById('returnModal').classList.add('active');
        }
    </script>
</body>

</html>