<?php
session_start();
require 'koneksi.php';
require 'cek.php'; // Pastikan cek.php sudah diperbarui (lihat Langkah 2)

// Pastikan hanya Head Office yang bisa mengakses halaman ini
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'headoffice') {
    // Jika bukan, tendang ke halaman login
    header('location:login.php');
    exit;
}

// Ambil data user yang sedang login untuk menampilkan nama
$username = $_SESSION['username'];
$get_user = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
$user_data = mysqli_fetch_array($get_user);
$nama_user = isset($user_data['nama']) ? $user_data['nama'] : $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Dashboard - Head Office</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
</head>
<body>
    <div class="main-wrapper">
        <div class="header">
            <div class="header-left">
                <a href="index_headoffice.php" class="logo">
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
                        <span>Head Office</span>
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
                        <li class="active">
                            <a href="index_headoffice.php"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a>
                        </li>
                        <li>
                            <a href="Absensi.php"><i class="fa fa-calendar-check-o"></i> <span>Laporan Absensi</span></a>
                        </li>
                        <li class="submenu">
                            <a href="#"><i class="fa fa-file-text-o"></i> <span> Menu Laporan SPK </span> <span class="menu-arrow"></span></a>
                            <ul style="display: none;">
                                <li><a href="alternatif.php"> Alternatif</a></li>
                                <li><a href="bobot.php"> Bobot & Kriteria </a></li>
                                <li><a href="matrik.php"> Data Klasifikasi</a></li>
                                <li><a href="percetakan_spk.php"> Percetakan SPK </a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="page-wrapper">
            <div class="content">
                <div class="row">
                    <div class="col-sm-12">
                        <h4 class="page-title">Selamat Datang, <?php echo htmlspecialchars($nama_user); ?>!</h4>
                        <p>Anda login sebagai Head Office. Silakan gunakan menu di samping untuk mengakses fitur yang tersedia.</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body text-center">
                                <i class="fa fa-calendar-check-o fa-3x mb-2 text-info"></i>
                                <h4 class="card-title">Laporan Absensi</h4>
                                <p class="card-text">Lihat laporan absensi seluruh karyawan.</p>
                                <a href="Absensi.php" class="btn btn-info">Buka Laporan</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body text-center">
                                <i class="fa fa-file-text-o fa-3x mb-2 text-success"></i>
                                <h4 class="card-title">Laporan SPK</h4>
                                <p class="card-text">Akses fitur untuk mengelola dan mencetak Laporan SPK.</p>
                                <a href="alternatif.php" class="btn btn-success">Buka Menu SPK</a>
                            </div>
                        </div>
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
    <script src="assets/js/app.js"></script>
</body>
</html>