<?php
// File: hapus_koreksi.php
include ("koneksi.php"); // Memastikan koneksi database tersedia

// Memastikan hanya request POST yang diterima dan 'koreksi_id' tersedia
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['koreksi_id'])) {
    // Ambil ID koreksi dari data POST
    $koreksi_id = $_POST['koreksi_id'];

    // Validasi ID koreksi (pastikan hanya angka untuk mencegah SQL Injection dasar)
    if (!is_numeric($koreksi_id)) {
        header("Location: Koreksi.php?status=error&message=ID koreksi tidak valid.");
        exit();
    }

    // Escape string untuk mencegah SQL Injection lebih lanjut
    $koreksi_id = mysqli_real_escape_string($conn, $koreksi_id);

    // Query SQL untuk menghapus data dari tabel 'koreksi_absensi'
    $query = "DELETE FROM koreksi_absensi WHERE koreksi_id = '$koreksi_id'";
    $result = mysqli_query($conn, $query);

    if ($result) {
        // Jika penghapusan berhasil, redirect kembali ke halaman Koreksi.php dengan pesan sukses
        header("Location: Koreksi.php?status=success&message=Data koreksi absensi berhasil dihapus.");
        exit();
    } else {
        // Jika penghapusan gagal, redirect dengan pesan error database
        header("Location: Koreksi.php?status=error&message=Gagal menghapus data koreksi absensi: " . mysqli_error($conn));
        exit();
    }
} else {
    // Jika akses tidak sah (bukan melalui POST atau 'koreksi_id' tidak ada),
    // redirect kembali ke halaman Koreksi.php dengan pesan error
    header("Location: Koreksi.php?status=error&message=Akses tidak sah untuk menghapus data koreksi.");
    exit();
}
?>