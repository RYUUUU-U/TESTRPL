<?php
require 'koneksi.php'; 

$pesan = "Terjadi kesalahan.";
$status_kelas = "error";

if (isset($_GET['token']) && isset($_SESSION['karyawan_id'])) {
    $token_value = $_GET['token'];
    $karyawan_id = $_SESSION['karyawan_id'];
    
    // Cek apakah ada waktu debug di sesi, jika tidak, kirim NULL
    $waktu_debug = $_SESSION['waktu_palsu'] ?? null;
    
    try {
        // Panggil Stored Procedure dengan 3 parameter
        $stmt = $pdo->prepare("CALL ValidateAndRecordAttendance(?, ?, ?, @pesan_hasil)");
        $stmt->execute([$token_value, $karyawan_id, $waktu_debug]); // Kirim waktu debug
        $stmt->closeCursor();

        $result = $pdo->query("SELECT @pesan_hasil AS hasil")->fetch(PDO::FETCH_ASSOC);
        $hasil_sp = $result['hasil'];

        switch ($hasil_sp) {
            case 'ABSENSI_MASUK_BERHASIL': $pesan = "Absen Masuk berhasil dicatat!"; $status_kelas = "success"; break;
            case 'ABSENSI_KELUAR_BERHASIL': $pesan = "Absen Keluar berhasil dicatat!"; $status_kelas = "success"; break;
            case 'BUKAN_WAKTU_MASUK': $pesan = "Gagal! Saat ini di luar jam absen masuk (07:00 - 09:00)."; $status_kelas = "error"; break;
            case 'BUKAN_WAKTU_KELUAR': $pesan = "Gagal! Saat ini di luar jam absen keluar (16:00 - 18:00)."; $status_kelas = "error"; break;
            case 'SUDAH_ABSEN_LENGKAP': $pesan = "Anda sudah melakukan absen masuk dan keluar hari ini."; $status_kelas = "info"; break;
            case 'TOKEN_TIDAK_VALID': $pesan = "QR Code tidak valid."; $status_kelas = "error"; break;
            case 'TOKEN_SUDAH_DIGUNAKAN': $pesan = "QR Code ini sudah pernah digunakan."; $status_kelas = "error"; break;
            case 'TOKEN_KEDALUWARSA': $pesan = "QR Code sudah kedaluwarsa. Silakan scan ulang."; $status_kelas = "error"; break;
            default: $pesan = "Gagal memproses absensi."; $status_kelas = "error"; break;
        }
    } catch (PDOException $e) {
        $pesan = "Error Database: " . $e->getMessage(); $status_kelas = "error";
    }
} else {
    $pesan = isset($_SESSION['karyawan_id']) ? "Token absensi tidak ditemukan." : "Anda harus login terlebih dahulu.";
    $status_kelas = "error";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Validasi Absensi</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; background-color: #f0f2f5; }
        .container { text-align: center; background-color: white; padding: 30px 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); max-width: 90%; }
        .icon { font-size: 60px; margin-bottom: 20px; line-height: 1; }
        .message { font-size: 1.2em; margin-bottom: 0; }
        .success .icon { color: #28a745; } .success .message { color: #155724; }
        .error .icon { color: #dc3545; } .error .message { color: #721c24; }
        .info .icon { color: #17a2b8; } .info .message { color: #0c5460; }
    </style>
</head>
<body>
    <div class="container <?php echo $status_kelas; ?>">
        <div class="icon"><?php echo ($status_kelas === 'success') ? '&#x2714;' : '&#x2718;'; ?></div>
        <p class="message"><?php echo htmlspecialchars($pesan); ?></p>
    </div>
</body>
</html>
