<?php
require_once '../auth/cek_login.php';
cekAdmin();
require_once '../database/koneksi.php';

// Get filter
$filter_type = $_GET['type'] ?? '';
$date_start = $_GET['start'] ?? date('Y-m-01');
$date_end = $_GET['end'] ?? date('Y-m-d');

// Build query
$sql = "
    SELECT l.*, u.nama, u.role, u.email 
    FROM log_aktivitas l 
    LEFT JOIN users u ON l.user_id = u.id 
    WHERE DATE(l.created_at) BETWEEN ? AND ?
";
$params = [$date_start, $date_end];

if ($filter_type) {
    $sql .= " AND l.tipe = ?";
    $params[] = $filter_type;
}

$sql .= " ORDER BY l.created_at DESC LIMIT 500";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Get stats
$stats = $pdo->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN tipe = 'login' THEN 1 ELSE 0 END) as login_count,
        SUM(CASE WHEN tipe = 'peminjaman' THEN 1 ELSE 0 END) as peminjaman_count,
        SUM(CASE WHEN tipe = 'pengembalian' THEN 1 ELSE 0 END) as pengembalian_count
    FROM log_aktivitas WHERE DATE(created_at) BETWEEN '{$date_start}' AND '{$date_end}'
")->fetch();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Aktivitas - Admin Marvell Rental</title>
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
                <span class="sidebar-title">Admin Panel</span>
            </div>

            <div class="sidebar-menu">
                <p class="sidebar-menu-title">Menu Utama</p>
                <ul class="sidebar-nav">
                    <li><a href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="motor.php"><i class="fas fa-motorcycle"></i> Data Motor</a></li>
                    <li><a href="petugas.php"><i class="fas fa-user-tie"></i> Data Petugas</a></li>
                    <li><a href="peminjam.php"><i class="fas fa-users"></i> Data Peminjam</a></li>
                </ul>
            </div>

            <div class="sidebar-menu">
                <p class="sidebar-menu-title">Transaksi</p>
                <ul class="sidebar-nav">
                    <li><a href="transaksi.php"><i class="fas fa-exchange-alt"></i> Semua Transaksi</a></li>
                    <li><a href="laporan.php"><i class="fas fa-file-alt"></i> Laporan</a></li>
                    <li><a href="log_aktivitas.php" class="active"><i class="fas fa-history"></i> Log Aktivitas</a></li>
                </ul>
            </div>

            <div class="sidebar-menu">
                <p class="sidebar-menu-title">Akun</p>
                <ul class="sidebar-nav">
                    <li><a href="profil.php"><i class="fas fa-user-cog"></i> Profil</a></li>
                    <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="dashboard-header">
                <h1 class="dashboard-title">Log Aktivitas</h1>
            </div>

            <!-- Stats -->
            <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 25px;">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-list"></i></div>
                    <p class="stat-value">
                        <?= $stats['total'] ?? 0 ?>
                    </p>
                    <p class="stat-label">Total Aktivitas</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="color: var(--accent-green);"><i class="fas fa-sign-in-alt"></i></div>
                    <p class="stat-value">
                        <?= $stats['login_count'] ?? 0 ?>
                    </p>
                    <p class="stat-label">Login</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="color: var(--primary);"><i class="fas fa-motorcycle"></i></div>
                    <p class="stat-value">
                        <?= $stats['peminjaman_count'] ?? 0 ?>
                    </p>
                    <p class="stat-label">Peminjaman</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="color: var(--accent-orange);"><i class="fas fa-undo"></i></div>
                    <p class="stat-value">
                        <?= $stats['pengembalian_count'] ?? 0 ?>
                    </p>
                    <p class="stat-label">Pengembalian</p>
                </div>
            </div>

            <!-- Filter -->
            <div class="card mb-3">
                <form method="GET" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Dari</label>
                        <input type="date" name="start" class="form-control" value="<?= $date_start ?>">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Sampai</label>
                        <input type="date" name="end" class="form-control" value="<?= $date_end ?>">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Tipe</label>
                        <select name="type" class="form-control">
                            <option value="">Semua</option>
                            <option value="login" <?= $filter_type == 'login' ? 'selected' : '' ?>>Login</option>
                            <option value="logout" <?= $filter_type == 'logout' ? 'selected' : '' ?>>Logout</option>
                            <option value="peminjaman" <?= $filter_type == 'peminjaman' ? 'selected' : '' ?>>Peminjaman
                            </option>
                            <option value="pengembalian" <?= $filter_type == 'pengembalian' ? 'selected' : '' ?>
                                >Pengembalian</option>
                            <option value="pembayaran" <?= $filter_type == 'pembayaran' ? 'selected' : '' ?>>Pembayaran
                            </option>
                            <option value="motor" <?= $filter_type == 'motor' ? 'selected' : '' ?>>Motor</option>
                            <option value="user" <?= $filter_type == 'user' ? 'selected' : '' ?>>User</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </form>
            </div>

            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>User</th>
                            <th>Role</th>
                            <th>Aksi</th>
                            <th>Keterangan</th>
                            <th>Tipe</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($logs) > 0): ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td style="white-space: nowrap;">
                                        <?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?>
                                    </td>
                                    <td style="color: var(--text-primary);">
                                        <?= htmlspecialchars($log['nama'] ?? 'System') ?>
                                        <br><small style="color: var(--text-secondary);">
                                            <?= htmlspecialchars($log['email'] ?? '-') ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php
                                        $roleClass = match ($log['role'] ?? '') {
                                            'admin' => 'badge-danger',
                                            'petugas' => 'badge-info',
                                            'peminjam' => 'badge-success',
                                            default => 'badge-secondary'
                                        };
                                        ?>
                                        <span class="badge <?= $roleClass ?>">
                                            <?= ucfirst($log['role'] ?? '-') ?>
                                        </span>
                                    </td>
                                    <td style="color: var(--primary);">
                                        <?= htmlspecialchars($log['aksi']) ?>
                                    </td>
                                    <td style="max-width: 300px; color: var(--text-secondary);">
                                        <?= htmlspecialchars($log['keterangan'] ?? '-') ?>
                                    </td>
                                    <td>
                                        <?php
                                        $tipeClass = match ($log['tipe'] ?? '') {
                                            'login', 'logout' => 'badge-info',
                                            'peminjaman' => 'badge-warning',
                                            'pengembalian' => 'badge-success',
                                            'pembayaran' => 'badge-primary',
                                            default => 'badge-secondary'
                                        };
                                        ?>
                                        <span class="badge <?= $tipeClass ?>">
                                            <?= ucfirst($log['tipe'] ?? 'lainnya') ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada data</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <button class="mobile-menu-btn"><i class="fas fa-bars"></i></button>
    <script src="../assets/js/main.js"></script>
</body>

</html>