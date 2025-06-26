<?php
require 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST['karyawan_id'])) {
        $kid = $_POST['karyawan_id'];

        // Pastikan ID ada di database
        $cek = mysqli_query($conn, "SELECT * FROM karyawan WHERE karyawan_id = '$kid'");
        if (mysqli_num_rows($cek) > 0) {
            $hapus = mysqli_query($conn, "DELETE FROM karyawan WHERE karyawan_id = '$kid'");

            if ($hapus) {
                echo "<script>alert('Data berhasil dihapus'); window.location.href='karyawan.php';</script>";
            } else {
                echo "<script>alert('Gagal menghapus data'); window.location.href='karyawan.php';</script>";
            }
        } else {
            echo "<script>alert('ID tidak ditemukan'); window.location.href='karyawan.php';</script>";
        }
    } else {
        echo "<script>alert('ID tidak tersedia'); window.location.href='karyawan.php';</script>";
    }
} else {
    echo "<script>alert('Akses tidak valid'); window.location.href='karyawan.php';</script>";
}
?>