<?php
include("koneksi.php");

// Ambil data karyawan untuk dropdown
$query_karyawan = "SELECT karyawan_id, nama_lengkap FROM karyawan ORDER BY nama_lengkap ASC";
$result_karyawan = mysqli_query($conn, $query_karyawan);

// Ambil data alat sidik jari yang aktif
$query_alat = "SELECT alat_id, kode_alat FROM alat_fingerprint WHERE status = 'aktif' ORDER BY kode_alat ASC";
$result_alat = mysqli_query($conn, $query_alat);

// Proses form jika disubmit
if (isset($_POST['submit'])) {
    $karyawan_id = $_POST['karyawan_id'];
    $tanggal = $_POST['tanggal'];
    $jam_masuk = $_POST['jam_masuk'];
    $jam_keluar = isset($_POST['jam_keluar']) && !empty($_POST['jam_keluar']) ? $_POST['jam_keluar'] : NULL; // Jam keluar bisa kosong
    $alat_id = $_POST['alat_id'];
    $status = $_POST['status'];

    // Validasi format tanggal untuk mencegah 0000-00-00
    $valid_format = true;
    $error = "";

    // Validasi tanggal
    if (empty($tanggal)) {
        $error = "Tanggal harus diisi!";
        $valid_format = false;
    } else {
        // Konversi format tanggal dari d/m/Y ke Y-m-d untuk database
        try {
            // Mengubah format d/m/Y menjadi Y-m-d
            $date_obj = DateTime::createFromFormat('d/m/Y', $tanggal);

            if (!$date_obj) {
                $error = "Format tanggal tidak valid! Gunakan format DD/MM/YYYY.";
                $valid_format = false;
            } else {
                // Format ulang tanggal untuk database
                $tanggal = $date_obj->format('Y-m-d');
            }
        } catch (Exception $e) {
            $error = "Format tanggal tidak valid: " . $e->getMessage();
            $valid_format = false;
        }
    }

    // Validasi jam masuk
    if (!empty($jam_masuk)) {
        // Coba format jam masuk
        try {
            $time_obj = new DateTime($jam_masuk);
            $jam_masuk = $time_obj->format('H:i:s');
        } catch (Exception $e) {
            $error = "Format jam masuk tidak valid!";
            $valid_format = false;
        }
    }

    // Validasi jam keluar jika ada
    if (!empty($jam_keluar)) {
        // Coba format jam keluar
        try {
            $time_obj = new DateTime($jam_keluar);
            $jam_keluar = $time_obj->format('H:i:s');
        } catch (Exception $e) {
            $error = "Format jam keluar tidak valid!";
            $valid_format = false;
        }
    }

    // Jika semua validasi berhasil
    if ($valid_format) {
        // Query untuk menyimpan data absensi
        $query = "INSERT INTO absensi (karyawan_id, tanggal, jam_masuk, jam_keluar, alat_id, status) 
                VALUES ('$karyawan_id', '$tanggal', '$jam_masuk', " .
            (!empty($jam_keluar) ? "'$jam_keluar'" : "NULL") .
            ", '$alat_id', '$status')";

        $result = mysqli_query($conn, $query);

        if ($result) {
            // Redirect ke halaman absensi dengan pesan sukses
            header("Location: Absensi.php?status=success&message=Data absensi berhasil disimpan");
            exit();
        } else {
            $error = "Gagal menyimpan data: " . mysqli_error($conn);
        }
    }
}

// Fungsi untuk memformat tanggal dari database (Y-m-d) ke format tampilan (d/m/Y)
function formatTanggalKeIndonesia($tanggal_db)
{
    if (empty($tanggal_db))
        return '';
    $date = new DateTime($tanggal_db);
    return $date->format('d/m/Y');
}
?>