<?php
include("koneksi.php");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ambil data karyawan yang sedang login
$loggedInKaryawanId = $_SESSION['karyawan_id'] ?? null;
$loggedInNamaLengkap = $_SESSION['nama_lengkap'] ?? 'Nama Karyawan Tidak Ditemukan';

// Inisialisasi variabel untuk pesan feedback
$feedbackMessage = '';
$feedbackType = ''; // 'success' or 'danger'

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $absensi_id = $_POST['absensi_id'] ?? null;
    $tanggal_pengajuan = $_POST['tanggal_koreksi'] ?? null; // Ambil dari form input tanggal_koreksi
    $alasan = $_POST['alasan'] ?? null;

    // Validasi input
    if (empty($absensi_id) || empty($tanggal_pengajuan) || empty($alasan)) {
        $feedbackMessage = "Semua kolom wajib diisi.";
        $feedbackType = 'danger';
    } else {
        // Santisasi input
        $absensi_id = mysqli_real_escape_string($conn, $absensi_id);
        $tanggal_pengajuan = mysqli_real_escape_string($conn, $tanggal_pengajuan);
        $alasan = mysqli_real_escape_string($conn, $alasan);

        // Validasi apakah absensi_id ada
        $check_absensi = "SELECT a.*, k.nama_lengkap 
                         FROM absensi a 
                         JOIN karyawan k ON a.karyawan_id = k.karyawan_id 
                         WHERE a.absensi_id = '$absensi_id'";
        $check_result = mysqli_query($conn, $check_absensi);

        if (!$check_result || mysqli_num_rows($check_result) == 0) {
            $feedbackMessage = "ID Absensi #$absensi_id tidak ditemukan dalam database.";
            $feedbackType = 'danger';
        } else {
            $absensi_data = mysqli_fetch_assoc($check_result);
            $karyawan_id = $absensi_data['karyawan_id'];
            $tanggal_absensi_asli = $absensi_data['tanggal'];
            $nama_karyawan = $absensi_data['nama_lengkap'];

            // Cek pengajuan sebelumnya
            $check_existing = "SELECT absensi_id FROM koreksi_absensi 
                              WHERE absensi_id = '$absensi_id' 
                              AND status_koreksi = 'Pending'";
            $existing_result = mysqli_query($conn, $check_existing);

            if ($existing_result && mysqli_num_rows($existing_result) > 0) {
                $feedbackMessage = "Sudah ada pengajuan koreksi yang sedang diproses untuk ID Absensi #$absensi_id.";
                $feedbackType = 'warning';
            } else {
                $status_koreksi = 'Pending';

                // Simpan ke database
                $insert_query = "INSERT INTO koreksi_absensi 
                                (absensi_id, karyawan_id, tanggal_pengajuan, alasan, status_koreksi) 
                                VALUES 
                                ('$absensi_id', '$karyawan_id', '$tanggal_pengajuan', '$alasan', '$status_koreksi')";

                if (mysqli_query($conn, $insert_query)) {
                    $feedbackMessage = "Pengajuan koreksi absensi untuk <strong>$nama_karyawan</strong> (ID Absensi: #$absensi_id) berhasil dikirim. Menunggu persetujuan admin.";
                    $feedbackType = 'success';
                    $_POST = array();
                } else {
                    $feedbackMessage = "Gagal mengajukan koreksi absensi: " . mysqli_error($conn);
                    $feedbackType = 'danger';
                }
            }
        }
    }
}

// Fungsi tambahan
function getAbsensiDetail($conn, $absensi_id) {
    $query = "SELECT a.*, k.nama_lengkap 
              FROM absensi a 
              JOIN karyawan k ON a.karyawan_id = k.karyawan_id 
              WHERE a.absensi_id = '" . mysqli_real_escape_string($conn, $absensi_id) . "'";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}

function checkExistingKoreksi($conn, $absensi_id) {
    $query = "SELECT * FROM koreksi_absensi 
              WHERE absensi_id = '" . mysqli_real_escape_string($conn, $absensi_id) . "' 
              ORDER BY tanggal_pengajuan DESC LIMIT 1";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}

function getRiwayatKoreksi($conn, $limit = 10) {
    $query = "SELECT ka.*, k.nama_lengkap, a.tanggal as tanggal_absensi_asli
              FROM koreksi_absensi ka
              JOIN karyawan k ON ka.karyawan_id = k.karyawan_id
              JOIN absensi a ON ka.absensi_id = a.absensi_id
              ORDER BY ka.tanggal_pengajuan DESC 
              LIMIT $limit";
    $result = mysqli_query($conn, $query);
    $riwayat = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $riwayat[] = $row;
        }
    }
    return $riwayat;
}
?>
