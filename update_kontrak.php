<?php
include("koneksi.php");
// Initialize variables with default values
$kontrak_id = 0;
$karyawan_id = '';
$tanggal_mulai = '';
$tanggal_selesai = '';
$status_kontrak = 'aktif';

// Check if kontrak_id is provided in the URL
if (isset($_GET['kontrak_id'])) {
    $kontrak_id = $_GET['kontrak_id'];

    // Fetch the contract data from database
    $query = mysqli_query($conn, "SELECT * FROM kontrak_karyawan WHERE kontrak_id = '$kontrak_id'");

    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);
        $karyawan_id = $data['karyawan_id'];
        $tanggal_mulai = $data['tanggal_mulai'];
        $tanggal_selesai = $data['tanggal_selesai'];
        $status_kontrak = $data['status_kontrak'];
    } else {
        // Contract not found, redirect to contract list
        echo "<script>alert('Kontrak tidak ditemukan!'); window.location='karyawan_kontrak.php';</script>";
        exit;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $karyawan_id = $_POST['karyawan_id'];
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];
    $status_kontrak = $_POST['status_kontrak'];

    if (isset($_POST['kontrak_id']) && $_POST['kontrak_id'] > 0) {
        // Update existing contract
        $kontrak_id = $_POST['kontrak_id'];
        $update = mysqli_query($conn, "UPDATE kontrak_karyawan SET 
            karyawan_id = '$karyawan_id',
            tanggal_mulai = '$tanggal_mulai',
            tanggal_selesai = '$tanggal_selesai',
            status_kontrak = '$status_kontrak'
            WHERE kontrak_id = '$kontrak_id'");

        if ($update) {
            // Redirect to contract list after update
            header("Location: karyawan_kontrak.php");
            exit;
        } else {
            echo "Gagal memperbarui data: " . mysqli_error($conn);
        }
    } else {
        // Insert new contract
        $insert = mysqli_query($conn, "INSERT INTO kontrak_karyawan (karyawan_id, tanggal_mulai, tanggal_selesai, status_kontrak) 
            VALUES ('$karyawan_id', '$tanggal_mulai', '$tanggal_selesai', '$status_kontrak')");

        if ($insert) {
            // Redirect to contract list after insertion
            header("Location: karyawan_kontrak.php");
            exit;
        } else {
            echo "Gagal menambahkan data: " . mysqli_error($conn);
        }
    }
}
?>