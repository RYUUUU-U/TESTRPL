<?php
include("koneksi.php");

// Ambil data berdasarkan karyawan_id dari parameter URL
if (isset($_GET['karyawan_id'])) {
    $kid = $_GET['karyawan_id'];

    // Ambil data untuk form
    $query = mysqli_query($conn, "SELECT * FROM karyawan WHERE karyawan_id = '$kid'");

    if ($query && mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);
        $user_id = $data['user_id'];
        $nama_lengkap = $data['nama_lengkap'];
        $jabatan = $data['jabatan'];
        $departemen = $data['departemen'];
        $no_hp = $data['no_hp'];
        $status_kepegawaian = $data['status_kepegawaian'];
    } else {
        echo "Data karyawan tidak ditemukan.";
        exit;
    }
} else {
    echo "Parameter karyawan_id tidak ditemukan.";
    exit;
}

// Proses update jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updatekaryawan'])) {
    $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $jabatan = mysqli_real_escape_string($conn, $_POST['jabatan']);
    $departemen = mysqli_real_escape_string($conn, $_POST['departemen']);
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $status_kepegawaian = mysqli_real_escape_string($conn, $_POST['status_kepegawaian']);

    // Update data dengan variable yang benar
    $update = mysqli_query($conn, "UPDATE karyawan SET 
        user_id = '$user_id',
        nama_lengkap = '$nama_lengkap',
        jabatan = '$jabatan',
        departemen = '$departemen',
        no_hp = '$no_hp',
        status_kepegawaian = '$status_kepegawaian'
        WHERE karyawan_id = '$kid'
    ");

    if ($update) {
        // Redirect ke halaman karyawan setelah update
        header("Location: karyawan.php?success=1");
        exit;
    } else {
        echo "Gagal memperbarui data: " . mysqli_error($conn);
    }
}
?>