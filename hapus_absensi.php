<?php
// File: hapus_absensi.php
include("koneksi.php");

// Validasi apakah ada data yang dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['absensi_id'])) {
    // Ambil ID absensi dari form
    $absensi_id = $_POST['absensi_id'];

    // Validasi ID absensi (pastikan numerik)
    if (!is_numeric($absensi_id)) {
        header("Location: Absensi.php?status=error&message=ID absensi tidak valid");
        exit();
    }

    // Lakukan query untuk menghapus data
    $query = "DELETE FROM absensi WHERE absensi_id = '$absensi_id'";
    $result = mysqli_query($conn, $query);

    if ($result) {
        // Jika berhasil, redirect ke halaman absensi dengan pesan sukses
        header("Location: Absensi.php?status=success&message=Data absensi berhasil dihapus");
        exit();
    } else {
        // Jika gagal, redirect dengan pesan error
        header("Location: Absensi.php?status=error&message=Gagal menghapus data: " . mysqli_error($conn));
        exit();
    }
} else {
    // Jika tidak ada data yang dikirim atau akses langsung ke file
    header("Location: Absensi.php?status=error&message=Akses tidak sah");
    exit();
}
?>