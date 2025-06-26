<?php
// koneksi.php sudah berisi $pdo dan session_start()
require 'koneksi.php';
// cek.php akan memastikan hanya user yang sudah login bisa mengakses halaman ini
require 'cek.php';

// Pastikan yang login adalah karyawan
if ($_SESSION['role'] !== 'karyawan') {
    // Jika bukan karyawan, mungkin arahkan ke halaman lain atau tampilkan pesan error
    die("Halaman ini hanya untuk karyawan.");
}

// Ambil karyawan_id dari sesi. Ini lebih aman daripada query ulang.
$karyawan_id = $_SESSION['karyawan_id'];

// --- Logika Filter Tanggal (Versi Aman) ---
$tanggal_mulai = $_GET['tanggal_mulai'] ?? ''; // Gunakan null coalescing operator
$tanggal_akhir = $_GET['tanggal_akhir'] ?? '';

// Array untuk menampung parameter query
$params = [':karyawan_id' => $karyawan_id];

// Query dasar
$query_absensi = "SELECT a.absensi_id, k.nama_lengkap, a.tanggal, a.jam_masuk, a.jam_keluar, a.status 
                  FROM absensi a 
                  LEFT JOIN karyawan k ON a.karyawan_id = k.karyawan_id 
                  WHERE a.karyawan_id = :karyawan_id";

// Tambahkan filter tanggal jika ada input
if (!empty($tanggal_mulai) && !empty($tanggal_akhir)) {
    $query_absensi .= " AND a.tanggal BETWEEN :tanggal_mulai AND :tanggal_akhir";
    $params[':tanggal_mulai'] = $tanggal_mulai;
    $params[':tanggal_akhir'] = $tanggal_akhir;
}

$query_absensi .= " ORDER BY a.tanggal DESC, a.jam_masuk DESC";

try {
    // Persiapkan dan eksekusi query dengan cara yang aman
    $stmt = $pdo->prepare($query_absensi);
    $stmt->execute($params);
    $result_absensi = $stmt->fetchAll();
} catch (PDOException $e) {
    // Tangani error database dengan baik
    die("Error saat mengambil data absensi: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>WEB KP - Riwayat Absensi Saya</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
</head>
<body>
    <div class="main-wrapper">
        <!-- Header & Sidebar (Sama seperti template Anda) -->
        <div class="header">
            <div class="header-left">
                <a href="index_karyawan.php" class="logo">
                    <img src="assets/img/logo.png" width="35" height="35" alt=""> <span>CV. SEJAHTERA ABADI</span>
                </a>
            </div>
            <a id="toggle_btn" href="javascript:void(0);"><i class="fa fa-bars"></i></a>
            <a id="mobile_btn" class="mobile_btn float-left" href="#sidebar"><i class="fa fa-bars"></i></a>
            <ul class="nav user-menu float-right">
                <li class="nav-item dropdown has-arrow">
                    <a href="#" class="dropdown-toggle nav-link user-link" data-toggle="dropdown">
                        <span class="user-img">
                            <img class="rounded-circle" src="assets/img/user.jpg" width="24" alt="User">
                            <span class="status online"></span>
                        </span>
                        <span><?php echo htmlspecialchars($_SESSION['nama']); ?></span>
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="logout.php">Logout</a>
                    </div>
                </li>
            </ul>
        </div>
        <div class="sidebar" id="sidebar">
            <div class="sidebar-inner slimscroll">
                <div id="sidebar-menu" class="sidebar-menu">
                    <ul>
                        <li><a href="index_karyawan.php"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a></li>
                        <li class="submenu">
                            <a href="#"><i class="fa fa-edit"></i> <span> Menu Absensi </span> <span class="menu-arrow"></span></a>
                            <ul style="display: none;">
                                <li><a class ="active" href="Absensi_karyawan.php">Absensi</a></li>
                                <li><a href="Koreksi_karyawan.php">Koreksi Absensi </a></li>
                                <li><a href="tambah_izin.php">Izin Cuti </a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- Akhir Header & Sidebar -->

        <div class="page-wrapper">
            <div class="content">
                <div class="row">
                    <div class="col-sm-8 col-7">
                        <h4 class="page-title">Riwayat Daftar Hadir Saya</h4>
                    </div>
                    <!-- ===== PERUBAHAN 1: TOMBOL SCAN QR DITAMBAHKAN DI SINI ===== -->
                    <div class="col-sm-4 col-5 text-right">
                        <button class="btn btn-primary btn-rounded" data-toggle="modal" data-target="#scan-qr-modal">
                            <i class="fa fa-qrcode"></i> Scan QR Absensi
                        </button>
                    </div>
                </div>

                <!-- Form Filter -->
                <div class="row filter-row">
                    <form action="" method="GET" class="w-100 d-flex flex-wrap align-items-end">
                        <div class="col-sm-6 col-md-3">
                            <div class="form-group form-focus">
                                    <label class="focus-label">Tanggal Mulai</label>
                                    <input class="form-control floating datetimepicker"type="text" name="tanggal_mulai" value="<?php echo htmlspecialchars($tanggal_mulai); ?>">
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <div class="form-group form-focus">
                                    <label class="focus-label">Tanggal Akhir</label>
                                    <div class="cal-icon">
                                    <input class="form-control floating datetimepicker"type="text" name="tanggal_akhir" value="<?php echo htmlspecialchars($tanggal_akhir); ?>">
                                </div>
                            </div>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <button type="submit" class="btn btn-success btn-block"> Filter </button>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <a href="absensi_karyawan.php" class="btn btn-warning btn-block"> Reset </a>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Tabel Riwayat Absensi -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-striped custom-table mb-0 datatable">
                                <thead>
                                    <tr>
                                        <th>ID Absensi</th>
                                        <th>Tanggal</th>
                                        <th>Jam Masuk</th>
                                        <th>Jam Keluar</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($result_absensi) > 0): ?>
                                        <?php foreach ($result_absensi as $row): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['absensi_id']); ?></td>
                                                <td><?php echo htmlspecialchars(date('d-m-Y', strtotime($row['tanggal']))); ?></td>
                                                <td><?php echo htmlspecialchars($row['jam_masuk']); ?></td>
                                                <td><?php echo htmlspecialchars($row['jam_keluar'] ?? '-'); ?></td>
                                                <td>
                                                    <span class="custom-badge status-green"><?php echo htmlspecialchars(ucfirst($row['status'])); ?></span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="5" class="text-center">Tidak ada data absensi untuk ditampilkan.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== PERUBAHAN 2: MODAL UNTUK TAMPILAN SCANNER ===== -->
    <div id="scan-qr-modal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Arahkan Kamera ke QR Code</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div style="width: 100%;" id="qr-reader"></div>
                    <div id="qr-reader-results" class="mt-3 text-center"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="sidebar-overlay" data-reff=""></div>
    <script src="assets/js/jquery-3.2.1.min.js"></script>
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/jquery.dataTables.min.js"></script>
    <script src="assets/js/dataTables.bootstrap4.min.js"></script>
    <script src="assets/js/jquery.slimscroll.js"></script>
    <script src="assets/js/select2.min.js"></script>
    <script src="assets/js/moment.min.js"></script>
    <script src="assets/js/bootstrap-datetimepicker.min.js"></script>
    <script src="assets/js/app.js"></script>

    <!-- ===== PERUBAHAN 3: SCRIPT UNTUK MENJALANKAN QR SCANNER ===== -->
    <!-- 1. Memuat library scanner -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    
    <!-- 2. Logika untuk menjalankan scanner -->
    <script>
        function onScanSuccess(decodedText, decodedResult) {
            // Arahkan browser ke URL yang berhasil di-scan
            console.log(`Scan berhasil, URL: ${decodedText}`);
            // Hentikan proses scan agar tidak redirect berkali-kali
            html5QrcodeScanner.clear().catch(error => {
                console.error("Gagal membersihkan scanner.", error);
            });
            // Arahkan ke halaman validasi
            window.location.href = decodedText;
        }

        function onScanFailure(error) {
            // Fungsi ini bisa diabaikan atau digunakan untuk menampilkan pesan error
            // console.warn(`Scan gagal, coba lagi. Error: ${error}`);
        }

        // Inisialisasi variabel scanner
        let html5QrcodeScanner = new Html5QrcodeScanner(
            "qr-reader", // ID dari div tempat scanner akan muncul
            { fps: 10, qrbox: {width: 250, height: 250} }, // Konfigurasi scanner
            /* verbose= */ false
        );

        // Saat modal ditampilkan, jalankan scanner
        $('#scan-qr-modal').on('shown.bs.modal', function () {
            html5QrcodeScanner.render(onScanSuccess, onScanFailure);
        });
        
        // Saat modal ditutup, hentikan scanner untuk mematikan kamera
        $('#scan-qr-modal').on('hidden.bs.modal', function () {
            // Pastikan scanner sedang berjalan sebelum mencoba menghentikannya
            if (html5QrcodeScanner.getState() === Html5QrcodeScannerState.SCANNING) {
                 html5QrcodeScanner.clear().catch(error => {
                    console.error("Gagal membersihkan scanner saat modal ditutup.", error);
                });
            }
        });
    </script>
</body>
</html>
