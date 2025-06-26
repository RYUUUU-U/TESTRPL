<?php
include("koneksi.php");
// Proses tambah atau update kontrak
if (isset($_POST['simpan_kontrak'])) {
    $id_kontrak = $_POST['id_kontrak'];
    $karyawan_id = $_POST['karyawan_id'];
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];
    $status_kontrak = $_POST['status_kontrak'];

    if ($id_kontrak == "") {
        // Insert baru
        $insert = mysqli_query($conn, "INSERT INTO kontrak_karyawan (karyawan_id, tanggal_mulai, tanggal_selesai, status_kontrak) 
                                       VALUES ('$karyawan_id', '$tanggal_mulai', '$tanggal_selesai', '$status_kontrak')");
        $message = $insert ? "Kontrak berhasil ditambahkan." : "Gagal menambahkan kontrak: " . mysqli_error($conn);
    } else {
        // Update kontrak
        $update = mysqli_query($conn, "UPDATE kontrak_karyawan SET 
                                       karyawan_id = '$karyawan_id',
                                       tanggal_mulai = '$tanggal_mulai',
                                       tanggal_selesai = '$tanggal_selesai',
                                       status_kontrak = '$status_kontrak'
                                       WHERE id_kontrak = '$id_kontrak'");
        $message = $update ? "Kontrak berhasil diperbarui." : "Gagal memperbarui kontrak: " . mysqli_error($conn);
    }

    echo "<script>alert('$message'); window.location='karyawan_kontrak.php';</script>";
}

// Ambil data untuk edit jika ada parameter GET
$editMode = false;
$id_kontrak = "";
$karyawan_id = "";
$tanggal_mulai = "";
$tanggal_selesai = "";
$status_kontrak = "aktif"; // Nilai default untuk kontrak baru

if (isset($_GET['edit'])) {
    $editMode = true;
    $id_kontrak = $_GET['edit'];
    $q = mysqli_query($conn, "SELECT * FROM kontrak_karyawan WHERE id_kontrak = '$id_kontrak'");

    if ($q && mysqli_num_rows($q) > 0) {
        $data = mysqli_fetch_assoc($q);
        $karyawan_id = $data['karyawan_id'];
        $tanggal_mulai = $data['tanggal_mulai'];
        $tanggal_selesai = $data['tanggal_selesai'];
        $status_kontrak = $data['status_kontrak']; // Jangan lakukan trim atau strtolower di sini
    } else {
        echo "<script>alert('Data kontrak tidak ditemukan'); window.location='karyawan_kontrak.php';</script>";
    }
}
?>