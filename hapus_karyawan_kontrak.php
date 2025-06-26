<?php
require 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST['kontrak_id'])) {
        $kontrak_id = $_POST['kontrak_id'];

        // Pastikan ID ada di database
        $cek = mysqli_query($conn, "SELECT * FROM kontrak_karyawan WHERE kontrak_id = '$kontrak_id'");
        if (mysqli_num_rows($cek) > 0) {
            $hapus = mysqli_query($conn, "DELETE FROM kontrak_karyawan WHERE kontrak_id = '$kontrak_id'");

            if ($hapus) {
                echo "<script>alert('Data berhasil dihapus'); window.location.href='karyawan_kontrak.php';</script>";
            } else {
                echo "<script>alert('Gagal menghapus data'); window.location.href='karyawan_kontrak.php';</script>";
            }
        } else {
            echo "<script>alert('ID tidak ditemukan'); window.location.href='karyawan_kontrak.php';</script>";
        }
    } else {
        echo "<script>alert('ID tidak tersedia'); window.location.href='karyawan_kontrak.php';</script>";
    }
} else {
    echo "<script>alert('Akses tidak valid'); window.location.href='karyawan_kontrak.php';</script>";
}
?>