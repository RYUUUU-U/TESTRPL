<?php
session_start();
require 'koneksi.php';
require 'cek.php';

// Ambil data user yang sedang login berdasarkan username
$username = $_SESSION['username']; // Menggunakan username sebagai identifier

// Sesuaikan query dengan struktur tabel yang benar
// Ubah 'users' menjadi tabel yang sesuai dan gunakan 'username' sebagai kondisi
$get_user = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
$user_data = mysqli_fetch_array($get_user);
$nama_user = isset($user_data['nama']) ? $user_data['nama'] : $_SESSION['username']; // Gunakan username jika nama tidak ditemukan

// Query untuk mengambil jumlah karyawan
$get_karyawan = mysqli_query($conn, "SELECT COUNT(*) as total_karyawan FROM karyawan");
$data_karyawan = mysqli_fetch_array($get_karyawan);
$jumlah_karyawan = $data_karyawan['total_karyawan'];

// Mendapatkan tanggal absensi terbaru
$get_absensi = mysqli_query($conn, "SELECT MAX(tanggal) as latest_date FROM absensi");
$data_absensi = mysqli_fetch_array($get_absensi);

// Format tanggal menjadi format "D Bulan"
$tanggal_absensi = "";
if ($data_absensi['latest_date']) {
    $timestamp = strtotime($data_absensi['latest_date']);
    // Array nama bulan dalam bahasa Indonesia
    $bulan = array(
        1 => 'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    );
    $tanggal_absensi = date('j', $timestamp) . ' ' . $bulan[date('n', $timestamp)];
} else {
    // Tampilkan tanggal hari ini jika tidak ada data
    $bulan = array(
        1 => 'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    );
    $tanggal_absensi = date('j') . ' ' . $bulan[date('n')];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">

    <title>WEB KP</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
</head>

<body>
    <div class="main-wrapper">
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
                        <span class="user-img">
                            <img class="rounded-circle" src="assets/img/user.jpg" width="24" alt="Admin">
                            <span class="status online"></span>
                        </span>
                        <span><?php echo $nama_user; ?></span>
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="logout.php">Logout</a>
                    </div>
                </li>
            </ul>
        </div>
    </div>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-inner slimscroll">
            <div id="sidebar-menu" class="sidebar-menu">
                <ul>
                    <li class="active">
                        <a href="index.php"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a>
                    </li>
                    <li class="submenu">
                        <a href="#"><i class="fa fa-edit"></i> <span> Menu Absensi </span> <span
                                class="menu-arrow"></span></a>
                        <ul style="display: none;">
                                <li><a href="Absensi_karyawan.php">Absensi</a></li>
                                <li><a href="Koreksi_karyawan.php">Koreksi Absensi </a></li>
                                <li><a href="tambah_izin.php">Izin Cuti </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="page-wrapper">
        <div class="content">
            <div class="row">
                <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
                    <div class="dash-widget">
                        <span class="dash-widget-bg1"><i class="fa fa-user-o" aria-hidden="true"></i></span>
                        <div class="dash-widget-info text-right">
                            <h3><?php echo $jumlah_karyawan; ?></h3>
                            <span class="widget-title1"> Data Karyawan <i class="fa fa-check"
                                    aria-hidden="true"></i></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
                    <div class="dash-widget">
                        <span class="dash-widget-bg2"><i class="fa fa-calendar"></i></span>
                        <div class="dash-widget-info text-right">
                            <h3><?php echo $tanggal_absensi; ?></h3>
                            <span class="widget-title2">Absensi<i class="fa fa-check" aria-hidden="true"></i></span>
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
    <script src="assets/js/Chart.bundle.js"></script>
    <script src="assets/js/chart.js"></script>
    <script src="assets/js/app.js"></script>
</body>

</html>