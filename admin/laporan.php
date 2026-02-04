<?php
require_once '../auth/cek_login.php';
cekAdmin();
require_once '../database/koneksi.php';

// Filter by date
$startDate = $_GET['start'] ?? date('Y-m-01');
$endDate = $_GET['end'] ?? date('Y-m-t');

// Get report data
$stmt = $pdo->prepare("
    SELECT p.*, u.nama as nama_peminjam, m.nama_motor, m.jenis, m.harga_per_hari,
           pg.denda, pt.nama as nama_petugas
    FROM peminjaman p 
    JOIN users u ON p.user_id = u.id 
    JOIN motor m ON p.motor_id = m.id 
    LEFT JOIN pengembalian pg ON p.id = pg.peminjaman_id
    LEFT JOIN nota n ON p.id = n.peminjaman_id
    LEFT JOIN users pt ON n.acc_by = pt.id
    WHERE p.status = 'selesai' AND p.tanggal_pinjam BETWEEN ? AND ?
    ORDER BY p.tanggal_pinjam DESC
");
$stmt->execute([$startDate, $endDate]);
$laporan = $stmt->fetchAll();

// Calculate totals
$totalPendapatan = 0;
$totalDenda = 0;
foreach ($laporan as $l) {
    $totalPendapatan += $l['total_harga'];
    $totalDenda += $l['denda'] ?? 0;
}

// Get monthly data for chart (last 6 months)
$chartData = [];
for ($i = 5; $i >= 0; $i--) {
    $monthStart = date('Y-m-01', strtotime("-$i months"));
    $monthEnd = date('Y-m-t', strtotime("-$i months"));
    $monthName = date('M Y', strtotime("-$i months"));
    
    // Get revenue for this month
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(total_harga), 0) as pendapatan,
               COUNT(*) as transaksi,
               COALESCE(SUM((SELECT COALESCE(denda, 0) FROM pengembalian WHERE peminjaman_id = p.id)), 0) as denda
        FROM peminjaman p 
        WHERE p.status = 'selesai' AND p.tanggal_pinjam BETWEEN ? AND ?
    ");
    $stmt->execute([$monthStart, $monthEnd]);
    $monthData = $stmt->fetch();
    
    $chartData[] = [
        'bulan' => $monthName,
        'pendapatan' => (int)$monthData['pendapatan'],
        'transaksi' => (int)$monthData['transaksi'],
        'denda' => (int)$monthData['denda']
    ];
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Admin Marvell Rental</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-container {
            background: var(--bg-card);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            border: var(--border-light);
        }
        .chart-title {
            color: var(--text-primary);
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .chart-title i {
            color: var(--primary);
        }
        .chart-wrapper {
            position: relative;
            height: 350px;
        }
        .chart-legend {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }
        .legend-color {
            width: 16px;
            height: 16px;
            border-radius: 4px;
        }
    </style>
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
                    <li><a href="laporan.php" class="active"><i class="fas fa-file-alt"></i> Laporan</a></li>
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
            <!-- Print Header -->
            <div class="print-header">
                <div class="company-name">MARVELL RENTAL</div>
                <h1>Laporan Keuangan</h1>
                <p class="print-date">Periode: <?= formatTanggal($startDate) ?> - <?= formatTanggal($endDate) ?></p>
                <p class="print-date">Dicetak pada: <?= date('d F Y, H:i') ?> WIB</p>
            </div>

            <div class="dashboard-header no-print" style="justify-content: flex-end;">
                <button class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print"></i> Cetak Laporan
                </button>
            </div>

            <!-- Filter -->
            <div class="card mb-3 no-print">
                <form method="GET" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Tanggal Mulai</label>
                        <div style="display: flex; gap: 5px;">
                            <input type="date" name="start" id="dateStart" class="form-control" value="<?= $startDate ?>">
                            <button type="button" class="btn btn-secondary" onclick="document.getElementById('dateStart').showPicker()" style="padding: 8px 12px;">
                                <i class="fas fa-calendar-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Tanggal Akhir</label>
                        <div style="display: flex; gap: 5px;">
                            <input type="date" name="end" id="dateEnd" class="form-control" value="<?= $endDate ?>">
                            <button type="button" class="btn btn-secondary" onclick="document.getElementById('dateEnd').showPicker()" style="padding: 8px 12px;">
                                <i class="fas fa-calendar-alt"></i>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </form>
            </div>

            <!-- Summary Stats -->
            <div class="stats-grid no-print" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 25px;">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-receipt"></i></div>
                    <p class="stat-value">
                        <?= count($laporan) ?>
                    </p>
                    <p class="stat-label">Total Transaksi</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
                    <p class="stat-value" style="font-size: 1.3rem;">
                        <?= formatRupiah($totalPendapatan) ?>
                    </p>
                    <p class="stat-label">Total Pendapatan</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="color: var(--accent-red);"><i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <p class="stat-value" style="font-size: 1.3rem;">
                        <?= formatRupiah($totalDenda) ?>
                    </p>
                    <p class="stat-label">Total Denda</p>
                </div>
            </div>

            <!-- Bar Chart - Revenue -->
            <div class="chart-container no-print">
                <div class="chart-title">
                    <i class="fas fa-chart-bar"></i>
                    Statistik Pendapatan & Transaksi (6 Bulan Terakhir)
                </div>
                <div class="chart-wrapper">
                    <canvas id="revenueChart"></canvas>
                </div>
                <div class="chart-legend">
                    <div class="legend-item">
                        <div class="legend-color" style="background: linear-gradient(135deg, #FFD700, #C9A100);"></div>
                        <span>Pendapatan (Rupiah)</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background: linear-gradient(135deg, #00C853, #00963f);"></div>
                        <span>Jumlah Transaksi</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background: linear-gradient(135deg, #FF5252, #d32f2f);"></div>
                        <span>Denda</span>
                    </div>
                </div>
            </div>

            <!-- Data Table -->
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Peminjam</th>
                            <th>Motor</th>
                            <th>Tgl Pinjam</th>
                            <th>Tgl Kembali</th>
                            <th>Total</th>
                            <th>Denda</th>
                            <th>Petugas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($laporan) > 0): ?>
                            <?php foreach ($laporan as $i => $l): ?>
                                <tr>
                                    <td style="color: var(--text-primary);">
                                        <?= $i + 1 ?>
                                    </td>
                                    <td style="color: var(--text-primary);">
                                        <?= htmlspecialchars($l['nama_peminjam']) ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($l['nama_motor']) ?>
                                    </td>
                                    <td>
                                        <?= date('d/m/Y', strtotime($l['tanggal_pinjam'])) ?>
                                    </td>
                                    <td>
                                        <?= date('d/m/Y', strtotime($l['tanggal_kembali'])) ?>
                                    </td>
                                    <td style="color: var(--primary);">
                                        <?= formatRupiah($l['total_harga']) ?>
                                    </td>
                                    <td style="color: var(--accent-red);">
                                        <?= formatRupiah($l['denda'] ?? 0) ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($l['nama_petugas'] ?: '-') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">Tidak ada data</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background: rgba(255, 215, 0, 0.1);">
                            <td colspan="5" style="text-align: right; font-weight: 600; color: var(--text-primary);">
                                TOTAL</td>
                            <td style="color: var(--primary); font-weight: 700;">
                                <?= formatRupiah($totalPendapatan) ?>
                            </td>
                            <td style="color: var(--accent-red); font-weight: 700;">
                                <?= formatRupiah($totalDenda) ?>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Print Footer -->
            <div class="print-footer">
                <p>Dokumen ini dicetak dari Sistem Marvell Rental</p>
                <p>© <?= date('Y') ?> Marvell Rental - All Rights Reserved</p>
            </div>
        </main>
    </div>

    <button class="mobile-menu-btn no-print"><i class="fas fa-bars"></i></button>
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/sweetalert-theme.js"></script>
    <script>
        // Chart Data from PHP
        const chartData = <?= json_encode($chartData) ?>;
        
        // Extract data for chart
        const labels = chartData.map(d => d.bulan);
        const pendapatanData = chartData.map(d => d.pendapatan);
        const transaksiData = chartData.map(d => d.transaksi);
        const dendaData = chartData.map(d => d.denda);

        // Create gradient for bars
        const ctx = document.getElementById('revenueChart').getContext('2d');
        
        // Gold gradient for revenue
        const goldGradient = ctx.createLinearGradient(0, 0, 0, 350);
        goldGradient.addColorStop(0, 'rgba(255, 215, 0, 0.9)');
        goldGradient.addColorStop(1, 'rgba(201, 161, 0, 0.7)');
        
        // Green gradient for transactions
        const greenGradient = ctx.createLinearGradient(0, 0, 0, 350);
        greenGradient.addColorStop(0, 'rgba(0, 200, 83, 0.9)');
        greenGradient.addColorStop(1, 'rgba(0, 150, 63, 0.7)');
        
        // Red gradient for fines
        const redGradient = ctx.createLinearGradient(0, 0, 0, 350);
        redGradient.addColorStop(0, 'rgba(255, 82, 82, 0.9)');
        redGradient.addColorStop(1, 'rgba(211, 47, 47, 0.7)');

        // Chart configuration
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Pendapatan',
                        data: pendapatanData,
                        backgroundColor: goldGradient,
                        borderColor: '#FFD700',
                        borderWidth: 2,
                        borderRadius: 8,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Denda',
                        data: dendaData,
                        backgroundColor: redGradient,
                        borderColor: '#FF5252',
                        borderWidth: 2,
                        borderRadius: 8,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Jumlah Transaksi',
                        data: transaksiData,
                        backgroundColor: greenGradient,
                        borderColor: '#00C853',
                        borderWidth: 2,
                        borderRadius: 8,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        display: false // Using custom legend
                    },
                    tooltip: {
                        backgroundColor: '#1a1a1a',
                        titleColor: '#FFD700',
                        bodyColor: '#ffffff',
                        borderColor: 'rgba(255, 215, 0, 0.3)',
                        borderWidth: 1,
                        padding: 15,
                        cornerRadius: 10,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.dataset.label === 'Jumlah Transaksi') {
                                    label += context.parsed.y + ' transaksi';
                                } else {
                                    label += 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.05)',
                            drawBorder: false
                        },
                        ticks: {
                            color: '#B0B0B0',
                            font: {
                                family: 'Poppins',
                                size: 12
                            }
                        }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        grid: {
                            color: 'rgba(255, 255, 255, 0.05)',
                            drawBorder: false
                        },
                        ticks: {
                            color: '#FFD700',
                            font: {
                                family: 'Poppins',
                                size: 11
                            },
                            callback: function(value) {
                                if (value >= 1000000) {
                                    return 'Rp ' + (value / 1000000).toFixed(1) + 'jt';
                                } else if (value >= 1000) {
                                    return 'Rp ' + (value / 1000).toFixed(0) + 'rb';
                                }
                                return 'Rp ' + value;
                            }
                        },
                        title: {
                            display: true,
                            text: 'Pendapatan & Denda (Rp)',
                            color: '#FFD700',
                            font: {
                                family: 'Poppins',
                                size: 12,
                                weight: 500
                            }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false
                        },
                        ticks: {
                            color: '#00C853',
                            font: {
                                family: 'Poppins',
                                size: 11
                            },
                            stepSize: 1
                        },
                        title: {
                            display: true,
                            text: 'Jumlah Transaksi',
                            color: '#00C853',
                            font: {
                                family: 'Poppins',
                                size: 12,
                                weight: 500
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>

</html>