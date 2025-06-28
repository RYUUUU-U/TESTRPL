<?php
// File: cek.php (Sesuai dengan role yang terpisah)
session_start();

if (!isset($_SESSION['log']) || $_SESSION['log'] != 'True') {
    header('location:login.php');
    exit;
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}

function isKaryawan() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'karyawan';
}

function isHeadOffice() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'headoffice';
}

$currentPage = basename($_SERVER['PHP_SELF']);

// --- Role: Admin --- (Tidak bisa akses SPK)
$adminPages = [
    'index.php', 'input_user.php', 'register.php', 'karyawan.php', 'Input-Karyawan.php',
    'tambah_karyawan.php', 'update_karyawan.php', 'hapus_karyawan.php', 'karyawan_kontrak.php',
    'Input-Karyawan_Kontrak.php', 'tambah_kontrak.php', 'update-karyawan_kontrak.php',
    'update_kontrak.php', 'hapus_karyawan_kontrak.php', 'Absensi.php', 'tambah_absensi.php',
    'input_absensi.php', 'edit_absensi.php', 'hapus_absensi.php', 'absensi_qr.php', 'Koreksi.php',
    'update_koreksi.php', 'hapus_koreksi.php', 'izin.php', 'update_status_izin.php', 'hapus_izin.php',
    'assets/api/listen_for_scan.php','assets/api/generate_token.php','assets/api/check_token_status.php',
];

// --- Role: Head Office --- (Hanya Absensi & SPK)
$headOfficePages = [
    'index_headoffice.php', 'Absensi.php', 'alternatif.php', 'alternatif-simpan.php', 'bobot.php',
    'input_kriteria.php', 'edit_kriteria.php', 'hapus_kriteria.php', 'matrik.php',
    'matrik-simpan.php', 'matrik-hapus-alternatif.php', 'percetakan_spk.php', 'export_spk.php', 'R.php', 'W.php',
];

// --- Role: Karyawan ---
$karyawanPages = [
    'index_karyawan.php', 'Absensi_karyawan.php', 'Koreksi_karyawan.php', 'Izin_karyawan.php',
    'tambah_koreksi.php', 'tambah_izin.php', 'validasi_absen.php',
];

if (isAdmin()) {
    if (!in_array($currentPage, $adminPages)) {
        header('location:index.php'); exit;
    }
} elseif (isHeadOffice()) {
    if (!in_array($currentPage, $headOfficePages)) {
        header('location:index_headoffice.php'); exit;
    }
} elseif (isKaryawan()) {
    if (!in_array($currentPage, $karyawanPages)) {
        header('location:index_karyawan.php'); exit;
    }
} else {
    session_destroy();
    header('location:login.php');
    exit;
}
?>