<?php
require '../../koneksi.php';

// Logika Keamanan
if (
    !isset($_SESSION['log']) || $_SESSION['log'] !== 'True' ||
    $_SESSION['role'] !== 'admin' ||
    !isset($_SESSION['karyawan_id']) || empty($_SESSION['karyawan_id'])
) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Akses Ditolak.']);
    exit;
}

// --- PERBAIKAN DI SINI: LOGIKA MANIPULASI WAKTU ---

// Atur zona waktu default
date_default_timezone_set('Asia/Jakarta');

// Cek apakah mode debug waktu aktif dari sesi
if (isset($_SESSION['waktu_palsu']) && !empty($_SESSION['waktu_palsu'])) {
    // Jika ya, gunakan waktu palsu dari sesi sebagai dasar
    $waktu_efektif_timestamp = strtotime($_SESSION['waktu_palsu']);
} else {
    // Jika tidak, gunakan waktu server yang sebenarnya
    $waktu_efektif_timestamp = time();
}
// ----------------------------------------------------

// Gunakan karyawan_id yang valid dari sesi.
$generator_id = $_SESSION['karyawan_id'];

$durasi_token = 120; // Token berlaku 2 menit
$token_value  = bin2hex(random_bytes(32));

// Buat timestamp berdasarkan waktu efektif (bisa waktu asli atau waktu palsu)
$created_at   = date('Y-m-d H:i:s', $waktu_efektif_timestamp);
$expires_at   = date('Y-m-d H:i:s', $waktu_efektif_timestamp + $durasi_token);

try {
    $sql = "INSERT INTO qr_tokens (token_value, generated_by, created_at, expires_at, status) VALUES (?, ?, ?, ?, 'aktif')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$token_value, $generator_id, $created_at, $expires_at]);

    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'token' => $token_value]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal membuat token: ' . $e->getMessage()]);
}
?>
