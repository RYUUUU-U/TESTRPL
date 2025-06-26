<?php
require 'koneksi.php'; // Pastikan koneksi database tersedia
require 'cek.php';    // Untuk pemeriksaan sesi/login

// Set header untuk JSON response
header('Content-Type: application/json');

// Inisialisasi response
$response = array('success' => false, 'message' => '');

try {
    // Periksa apakah request adalah POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method tidak diizinkan');
    }

    // Validasi input
    if (!isset($_POST['cuti_id']) || !isset($_POST['status_cuti'])) {
        throw new Exception('Data tidak lengkap');
    }

    $cuti_id = intval($_POST['cuti_id']);
    $status_cuti = trim($_POST['status_cuti']);

    // Validasi cuti_id
    if ($cuti_id <= 0) {
        throw new Exception('ID cuti tidak valid');
    }

    // Validasi status_cuti - hanya status yang diizinkan
    $allowed_statuses = array('pending', 'disetujui', 'ditolak');
    if (!in_array($status_cuti, $allowed_statuses)) {
        throw new Exception('Status tidak valid');
    }

    // Periksa apakah data cuti ada
    $check_stmt = $conn->prepare("SELECT cuti_id FROM izin_cuti WHERE cuti_id = ?");
    if (!$check_stmt) {
        throw new Exception('Gagal menyiapkan query check: ' . $conn->error);
    }

    $check_stmt->bind_param("i", $cuti_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows === 0) {
        $check_stmt->close();
        throw new Exception('Data cuti tidak ditemukan');
    }
    $check_stmt->close();

    // Update status cuti
    $update_stmt = $conn->prepare("UPDATE izin_cuti SET status_cuti = ? WHERE cuti_id = ?");
    if (!$update_stmt) {
        throw new Exception('Gagal menyiapkan query update: ' . $conn->error);
    }

    $update_stmt->bind_param("si", $status_cuti, $cuti_id);
    
    if ($update_stmt->execute()) {
        if ($update_stmt->affected_rows > 0) {
            $response['success'] = true;
            $response['message'] = 'Status cuti berhasil diperbarui';
        } else {
            throw new Exception('Tidak ada perubahan data atau data tidak ditemukan');
        }
    } else {
        throw new Exception('Gagal mengupdate status: ' . $update_stmt->error);
    }

    $update_stmt->close();

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    
    // Log error untuk debugging (opsional)
    error_log("Error in update_status_izin.php: " . $e->getMessage());
}

// Tutup koneksi database jika masih terbuka
if (isset($conn) && $conn) {
    $conn->close();
}

// Kirim response JSON
echo json_encode($response);
exit;
?>