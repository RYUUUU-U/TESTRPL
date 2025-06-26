<?php
require 'koneksi.php'; // Sertakan file koneksi database Anda
require 'cek.php';     // Sertakan file untuk pemeriksaan sesi/autentikasi (jika ada)

// Pastikan request datang dari method POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pastikan id_criteria diterima dari form
    if (isset($_POST['id_criteria'])) {
        $id_criteria = $_POST['id_criteria'];

        // Lakukan sanitasi input untuk mencegah SQL Injection
        // Pastikan $conn sudah terdefinisi dari 'koneksi.php'
        $id_criteria = mysqli_real_escape_string($conn, $id_criteria);

        // Query untuk menghapus data kriteria berdasarkan id_criteria
        $delete_query = mysqli_query($conn, "DELETE FROM saw_criterias WHERE id_criteria = '$id_criteria'");

        if ($delete_query) {
            // Jika penghapusan berhasil
            echo "<script>alert('Kriteria berhasil dihapus!'); window.location.href='bobot.php';</script>";
        } else {
            // Jika penghapusan gagal
            echo "<script>alert('Gagal menghapus kriteria: " . mysqli_error($conn) . "'); window.location.href='bobot.php';</script>";
        }
    } else {
        // Jika id_criteria tidak diterima
        echo "<script>alert('ID Kriteria tidak ditemukan!'); window.location.href='bobot.php';</script>";
    }
} else {
    // Jika akses langsung ke file ini tanpa POST request
    echo "<script>alert('Akses tidak sah!'); window.location.href='bobot.php';</script>";
}
?>