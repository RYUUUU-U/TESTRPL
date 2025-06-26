<?php
require '../../koneksi.php'; 

// Keamanan: Pastikan hanya admin yang login bisa mengakses
if (!isset($_SESSION['log']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit;
}

// --- PERUBAHAN DI SINI ---
// Ambil token spesifik dari URL, bukan lagi mencari yang terakhir
if (!isset($_GET['token'])) {
    http_response_code(400); // Bad Request
    echo "data: error_no_token\n\n";
    exit;
}
$token_to_check = $_GET['token'];
// =========================

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
@ini_set('zlib.output_compression', 0);
if (function_exists('apache_setenv')) { @apache_setenv('no-gzip', 1); }

session_write_close();

while (true) {
    if (connection_aborted()) exit();
    
    // --- PERUBAHAN DI SINI ---
    // Query sekarang memeriksa status dari token yang diberikan
    $sql = "SELECT status FROM qr_tokens WHERE token_value = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$token_to_check]);
    $token_data = $stmt->fetch();

    if ($token_data && $token_data['status'] === 'digunakan') {
        echo "data: scan_success\n\n";
        ob_flush();
        flush();
        exit();
    }
    
    // Jika token tidak ditemukan lagi (misalnya karena sudah dibersihkan), hentikan loop
    if (!$token_data) {
        exit();
    }

    sleep(2);
}
?>
