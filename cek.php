<?php
// File: cek.php
// Kita sudah tidak perlu session_start() di sini karena sudah dipanggil sebelumnya
// session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['log']) || $_SESSION['log'] != 'True') {
    header('location:login.php');
    exit;
}

// Fungsi untuk cek apakah yang login adalah admin
function isAdmin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}

// Fungsi untuk cek apakah yang login adalah karyawan
function isKaryawan()
{
    return isset($_SESSION['role']) && $_SESSION['role'] == 'karyawan';
}

// Fungsi untuk memastikan hanya admin yang bisa akses
function mustBeAdmin()
{
    if (!isAdmin()) {
        header('location:login.php');
        exit;
    }
}

// Fungsi untuk memastikan hanya karyawan yang bisa akses
function mustBeKaryawan()
{
    if (!isKaryawan()) {
        header('location:login.php');
        exit;
    }
}

// Cek role untuk halaman saat ini
$currentPage = basename($_SERVER['PHP_SELF']);

// Halaman yang hanya boleh diakses oleh admin
$adminPages = ['index.php', 'karyawan.php', 'karyawan_kontrak.php', 'Absensi.php', 'Izin.php'];

// Halaman yang hanya boleh diakses oleh karyawan
$karyawanPages = ['index_karyawan.php', 'Absensi_karyawan.php', 'Izin_karyawan.php'];

// Redirect sesuai role dan halaman yang diakses
if (in_array($currentPage, $adminPages) && !isAdmin()) {
    header('location:index_karyawan.php');
    exit;
} else if (in_array($currentPage, $karyawanPages) && !isKaryawan()) {
    header('location:index.php');
    exit;
}

// JANGAN PANGGIL QUERY DI CEK.PHP
// Kode query dipindahkan ke file yang menggunakan cek.php
?>