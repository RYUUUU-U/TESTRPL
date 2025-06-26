<?php
// Memulai sesi untuk bisa membacanya
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cek Data Sesi</title>
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
    <style>
        body { padding: 40px; background-color: #f8f9fa; }
        .container { max-width: 800px; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        pre { background-color: #333; color: #fff; padding: 15px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mb-4">Isi dari Sesi (<code>$_SESSION</code>) Saat Ini</h2>
        
        <?php if (empty($_SESSION)): ?>
            <div class="alert alert-danger">
                <strong>Sesi Kosong!</strong> Tidak ada data yang tersimpan di dalam sesi saat ini.
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                Berikut adalah semua data yang tersimpan di dalam sesi Anda.
            </div>
            <pre><?php print_r($_SESSION); ?></pre>
        <?php endif; ?>

        <hr>
        <a href="login.php" class="btn btn-primary">Kembali ke Halaman Login</a>
        <a href="logout.php" class="btn btn-danger">Hancurkan Sesi (Logout)</a>
    </div>
</body>
</html>
