<?php
require 'koneksi.php'; // koneksi.php sudah berisi session_start()
require 'cek.php';

// Hanya admin yang boleh mengakses halaman ini
if ($_SESSION['role'] !== 'admin') {
    die("Akses ditolak. Halaman ini hanya untuk admin.");
}

$pesan = '';

// --- PERBAIKAN ZONA WAKTU UNTUK PHP ---
// Fungsi PHP ini memerlukan nama zona waktu, bukan offset.
date_default_timezone_set('Asia/Jakarta');
// ------------------------------------

// Proses saat form disubmit untuk mengatur waktu palsu
if (isset($_POST['set_waktu'])) {
    if (!empty($_POST['waktu_palsu'])) {
        // Simpan waktu yang dipilih ke dalam sesi
        $_SESSION['waktu_palsu'] = $_POST['waktu_palsu'];
        $pesan = "Waktu debug berhasil diatur!";
    }
}

// Proses saat tombol reset ditekan
if (isset($_POST['reset_waktu'])) {
    // Hapus variabel waktu palsu dari sesi
    unset($_SESSION['waktu_palsu']);
    $pesan = "Waktu debug telah direset. Sistem kembali menggunakan waktu server asli.";
}

// Tentukan waktu efektif yang sedang digunakan oleh sistem
if (isset($_SESSION['waktu_palsu'])) {
    $waktu_efektif = date('d F Y, H:i:s', strtotime($_SESSION['waktu_palsu']));
    $status_waktu = "DEBUG (Waktu Palsu)";
} else {
    $waktu_efektif = date('d F Y, H:i:s');
    $status_waktu = "LIVE (Waktu Server Asli)";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kontrol Waktu Debug</title>
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
    <style>
        body { padding: 40px; background-color: #f8f9fa; }
        .container { max-width: 700px; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mb-3">Panel Kontrol Waktu Server</h2>
        <p>Gunakan halaman ini untuk mensimulasikan waktu absen yang berbeda untuk keperluan pengujian.</p>
        <hr>
        <div class="alert alert-info">
            Waktu Efektif Saat Ini: <strong><?php echo $waktu_efektif; ?></strong><br>
            Mode: <strong><?php echo $status_waktu; ?></strong>
        </div>

        <?php if ($pesan): ?>
            <div class="alert alert-success"><?php echo $pesan; ?></div>
        <?php endif; ?>

        <form method="post" class="mt-4">
            <div class="form-group">
                <label for="waktu_palsu"><h4>Atur Waktu Simulasi</h4></label>
                <input type="datetime-local" class="form-control" id="waktu_palsu" name="waktu_palsu" required>
                <small class="form-text text-muted">Pilih tanggal dan jam untuk disimulasikan.</small>
            </div>
            <button type="submit" name="set_waktu" class="btn btn-primary">Set Waktu Debug</button>
        </form>

        <form method="post" class="mt-3">
            <button type="submit" name="reset_waktu" class="btn btn-danger">Reset ke Waktu Server Asli</button>
        </form>
        
        <hr>
        <a href="index.php" class="btn btn-secondary">&larr; Kembali ke Dashboard</a>
    </div>
</body>
</html>
