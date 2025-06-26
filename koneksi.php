<?php
// Memulai sesi di baris paling atas adalah praktik terbaik.
// Ini memastikan sesi tersedia untuk semua file yang menyertakan file ini.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ===================================================================
// Data Koneksi Database (digunakan oleh kedua jenis koneksi)
// ===================================================================
$host   = 'localhost';
$dbname = 'absensi_qr'; // Pastikan nama database sudah benar
$user   = 'root';
$pass   = '';
$charset = 'utf8mb4';


// ===================================================================
// KONEKSI 1: Menggunakan MySQLi (untuk file-file lama seperti login.php)
// Menyediakan variabel: $conn
// ===================================================================
$conn = mysqli_connect($host, $user, $pass, $dbname);

// Cek koneksi MySQLi
if (!$conn) {
    // Hentikan eksekusi jika koneksi mysqli gagal.
    die("Koneksi MySQLi gagal: " . mysqli_connect_error());
}

// Mengatur charset untuk koneksi mysqli agar konsisten (praktik terbaik)
mysqli_set_charset($conn, $charset);


// ===================================================================
// KONEKSI 2: Menggunakan PDO (untuk file-file API baru)
// Menyediakan variabel: $pdo
// ===================================================================
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Tampilkan error sebagai exception
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Hasil query sebagai array asosiatif
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Gunakan prepared statements asli
];

try {
    // Membuat objek PDO yang akan kita gunakan di semua file API
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Hentikan eksekusi jika koneksi PDO gagal.
    // Sebaiknya jangan tampilkan pesan detail di lingkungan produksi
    die("Koneksi PDO gagal: " . $e->getMessage());
}
?>
