<?php
require '../../koneksi.php'; 

// Keamanan: Pastikan hanya admin yang login bisa mengakses
if (!isset($_SESSION['log']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak.']);
    exit;
}

// Ambil token dari parameter URL
if (!isset($_GET['token'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Token tidak disediakan.']);
    exit;
}
$token_to_check = $_GET['token'];

try {
    // Ambil status DAN waktu kedaluwarsa dari token
    $sql = "SELECT status, expires_at FROM qr_tokens WHERE token_value = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$token_to_check]);
    $token_data = $stmt->fetch();

    header('Content-Type: application/json');

    if ($token_data) {
        // Atur zona waktu agar konsisten dengan saat pembuatan token
        date_default_timezone_set('Asia/Jakarta');
        $waktu_sekarang = new DateTime();
        $waktu_kedaluwarsa = new DateTime($token_data['expires_at']);

        // 1. Cek jika sudah digunakan (prioritas utama)
        if ($token_data['status'] === 'digunakan') {
            echo json_encode(['status' => 'digunakan']);
        } 
        // 2. Cek jika sudah kedaluwarsa berdasarkan waktu saat ini
        else if ($waktu_sekarang > $waktu_kedaluwarsa) {
            echo json_encode(['status' => 'kedaluwarsa']);
        } 
        // 3. Jika tidak keduanya, maka token masih aktif
        else {
            echo json_encode(['status' => 'aktif']);
        }

    } else {
        // Jika token tidak ditemukan di database
        echo json_encode(['status' => 'not_found']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error.']);
}
?>
