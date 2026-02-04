<?php
session_start();
require_once '../database/koneksi.php';

// Log aktivitas sebelum logout
if (isset($_SESSION['user_id'])) {
    logAktivitas($pdo, $_SESSION['user_id'], 'Logout', 'User logout dari sistem');
}

// Hapus semua session
session_unset();
session_destroy();

// Redirect ke halaman login
header('Location: login.php');
exit;
?>