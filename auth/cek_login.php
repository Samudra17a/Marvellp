<?php
session_start();

// Fungsi untuk cek login
function cekLogin()
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../auth/login.php');
        exit;
    }
}

// Fungsi untuk cek role admin
function cekAdmin()
{
    cekLogin();
    if ($_SESSION['role'] !== 'admin') {
        header('Location: ../auth/login.php');
        exit;
    }
}

// Fungsi untuk cek role petugas
function cekPetugas()
{
    cekLogin();
    if ($_SESSION['role'] !== 'petugas') {
        header('Location: ../auth/login.php');
        exit;
    }
}

// Fungsi untuk cek role peminjam
function cekPeminjam()
{
    cekLogin();
    if ($_SESSION['role'] !== 'peminjam') {
        header('Location: ../auth/login.php');
        exit;
    }
}

// Fungsi untuk cek admin atau petugas
function cekAdminPetugas()
{
    cekLogin();
    if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'petugas') {
        header('Location: ../auth/login.php');
        exit;
    }
}
?>