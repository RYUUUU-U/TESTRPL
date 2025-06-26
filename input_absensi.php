<?php
require 'koneksi.php'; // koneksi.php sudah berisi $pdo dan $conn
require 'cek.php';

// Hanya admin yang bisa mengakses
if ($_SESSION['role'] !== 'admin') {
    die("Akses ditolak.");
}

$error_message = '';
$success_message = '';

// Mengambil daftar karyawan untuk dropdown
try {
    $karyawan_list = $pdo->query("SELECT karyawan_id, nama_lengkap FROM karyawan ORDER BY nama_lengkap ASC")->fetchAll();
} catch (PDOException $e) {
    die("Gagal mengambil daftar karyawan: " . $e->getMessage());
}

// Proses form saat disubmit
if (isset($_POST['submit'])) {
    $karyawan_id = $_POST['karyawan_id'];
    $tanggal = $_POST['tanggal'];
    $jam_masuk = $_POST['jam_masuk'];
    $jam_keluar = !empty($_POST['jam_keluar']) ? $_POST['jam_keluar'] : null;
    $status = $_POST['status'];

    // Validasi dasar
    if (empty($karyawan_id) || empty($tanggal) || empty($jam_masuk) || empty($status)) {
        $error_message = "Semua kolom yang bertanda (*) wajib diisi.";
    } else {
        try {
            // Cek apakah sudah ada data absensi untuk karyawan pada tanggal yang sama
            $stmt_check = $pdo->prepare("SELECT absensi_id FROM absensi WHERE karyawan_id = ? AND tanggal = ?");
            $stmt_check->execute([$karyawan_id, $tanggal]);
            
            if ($stmt_check->fetch()) {
                $error_message = "Data absensi untuk karyawan ini pada tanggal tersebut sudah ada.";
            } else {
                // Insert data baru jika belum ada
                $sql = "INSERT INTO absensi (karyawan_id, tanggal, jam_masuk, jam_keluar, status) VALUES (?, ?, ?, ?, ?)";
                $stmt_insert = $pdo->prepare($sql);
                $stmt_insert->execute([$karyawan_id, $tanggal, $jam_masuk, $jam_keluar, $status]);
                $success_message = "Data absensi berhasil disimpan.";
            }
        } catch (PDOException $e) {
            $error_message = "Gagal menyimpan data: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>WEB KP - Input Daftar Hadir Karyawan</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/select2.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap-datetimepicker.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
</head>

<body>
    <div class="main-wrapper">
        <!-- Header & Sidebar (Sama seperti template) -->
        <div class="header">
            <div class="header-left">
                <a href="index.php" class="logo">
                    <img src="assets/img/logo.png" width="35" height="35" alt=""> <span>CV. SEJAHTERA ABADI</span>
                </a>
            </div>
            <a id="toggle_btn" href="javascript:void(0);"><i class="fa fa-bars"></i></a>
            <a id="mobile_btn" class="mobile_btn float-left" href="#sidebar"><i class="fa fa-bars"></i></a>
            <ul class="nav user-menu float-right">
                <li class="nav-item dropdown has-arrow">
                    <a href="#" class="dropdown-toggle nav-link user-link" data-toggle="dropdown">
                        <span class="user-img"><img class="rounded-circle" src="assets/img/user.jpg" width="24" alt="Admin"><span class="status online"></span></span>
                        <span>Admin</span>
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
                        <li><a href="index.php"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a></li>
                        <li><a href="karyawan.php"><i class="fa fa-user-o"></i> <span>Karyawan</span></a></li>
                        <li class="submenu">
                            <a href="#"><i class="fa fa-edit"></i> <span> Menu Absensi </span> <span class="menu-arrow"></span></a>
                            <ul style="display: none;">
                                <li><a class="active" href="Absensi.php">Absensi</a></li>
                                <li><a href="absensi_qr.php">Tampilkan QR Absensi</a></li>
                                <li><a href="Koreksi.php">Koreksi Absensi</a></li>
                                <li><a href="izin.php">Izin Cuti</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="#"><i class="fa fa-money"></i> <span> Menu Laporan SPK </span> <span class="menu-arrow"></span></a>
                            <ul style="display: none;">
                                <li><a href="alternatif.php">Alternatif</a></li>
                                <li><a href="bobot.php">Bobot & Kriteria</a></li>
                                <li><a href="matrik.php">Data Klasifikasi</a></li>
                                <li><a href="percetakan_spk.php">Percetakan SPK</a></li>
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
                    <div class="col-lg-8 offset-lg-2">
                        <h4 class="page-title">Input Daftar Hadir Manual</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-8 offset-lg-2">
                        
                        <!-- Menampilkan Pesan Error atau Sukses -->
                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong>Error!</strong> <?php echo htmlspecialchars($error_message); ?>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($success_message)): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <strong>Sukses!</strong> <?php echo htmlspecialchars($success_message); ?>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            </div>
                        <?php endif; ?>

                        <form action="" method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Pilih Karyawan <span class="text-danger">*</span></label>
                                        <select class="select form-control" name="karyawan_id" required>
                                            <option value="">-- Pilih Karyawan --</option>
                                            <?php foreach ($karyawan_list as $karyawan): ?>
                                                <option value="<?php echo $karyawan['karyawan_id']; ?>">
                                                    <?php echo htmlspecialchars($karyawan['nama_lengkap']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tanggal <span class="text-danger">*</span></label>
                                        <div class="cal-icon">
                                            <input class="form-control datetimepicker" type="text" name="tanggal" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Jam Masuk <span class="text-danger">*</span></label>
                                        <div class="time-icon">
                                            <input type="text" class="form-control" id="datetimepicker3" name="jam_masuk" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Jam Keluar</label>
                                        <div class="time-icon">
                                            <input type="text" class="form-control" id="datetimepicker4" name="jam_keluar">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Status <span class="text-danger">*</span></label>
                                        <select class="select form-control" name="status" required>
                                            <option value="">-- Pilih Status --</option>
                                            <option value="hadir">Hadir</option>
                                            <option value="terlambat">Terlambat</option>
                                            <option value="izin">Izin</option>
                                            <option value="sakit">Sakit</option>
                                            <option value="alpha">Alpha</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="m-t-20 text-center">
                                <button type="submit" name="submit" class="btn btn-primary submit-btn">Simpan Data</button>
                                <a href="Absensi.php" class="btn btn-danger">Batal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="sidebar-overlay" data-reff=""></div>
    <script src="assets/js/jquery-3.2.1.min.js"></script>
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/jquery.slimscroll.js"></script>
    <script src="assets/js/select2.min.js"></script>
    <script src="assets/js/moment.min.js"></script>
    <script src="assets/js/bootstrap-datetimepicker.min.js"></script>
    <script src="assets/js/app.js"></script>

    <script>
        $(function () {
            // Inisialisasi datetimepicker untuk tanggal
            $('.datetimepicker').datetimepicker({
                format: 'YYYY-MM-DD', // Format yang kompatibel dengan MySQL
                defaultDate: moment()
            });
            // Inisialisasi datetimepicker untuk jam
            $('#datetimepicker3, #datetimepicker4').datetimepicker({
                format: 'HH:mm:ss' // Format yang kompatibel dengan MySQL
            });
        });
    </script>
</body>
</html>
