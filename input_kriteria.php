<?php
require 'koneksi.php'; // Includes the database connection file
require 'cek.php';     // Includes a file for checking user session/authentication

// Proses penambahan kriteria jika form disubmit
if (isset($_POST['addKriteria'])) {
    $criteria_name = $_POST['criteria_name'];
    $weight = $_POST['weight'];
    $type = $_POST['type']; // Benefit atau Cost

    // Validasi sederhana (opsional, bisa ditambahkan lebih banyak)
    if (empty($criteria_name) || empty($weight) || empty($type)) {
        echo "<script>alert('Semua bidang harus diisi.');</script>";
    } else {
        // Query untuk menyimpan data kriteria baru
        $addtotable = mysqli_query($conn, "INSERT INTO saw_criterias (criteria_name, weight, type) VALUES ('$criteria_name', '$weight', '$type')");

        if ($addtotable) {
            echo "<script>alert('Kriteria berhasil ditambahkan!'); window.location.href='bobot.php';</script>";
        } else {
            echo "<script>alert('Gagal menambahkan kriteria: " . mysqli_error($conn) . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">

    <title>WEB KP - Input Kriteria</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <link rel="stylesheet" type="text/css" href="assets/css/select2.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/dataTables.bootstrap4.min.css">
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
                        <li>
                            <div class="profile-section" style="text-align: center;">
                            </div>
                            <a href="index.php"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a>
                        </li>
                        <li>
                            <a href="karyawan.php"><i class="fa fa-user-o"></i> <span>Karyawan</span></a>
                        </li>
                        <li>
                            <a href="karyawan_kontrak.php"><i class="fa fa-user-o"></i> <span>Karyawan
                                    Kontrak</span></a>
                        </li>
                        <li class="submenu">
                            <a href="#"><i class="fa fa-edit"></i> <span> Menu Absensi </span> <span
                                    class="menu-arrow"></span></a>
                            <ul style="display: none;">
                                <li><a href="Absensi.php">Absensi</a></li>
                                <li><a href="absensi_qr.php">Tampilkan QR Absensi</a></li>
                                <li><a href="Koreksi.php">Koreksi Absensi</a></li>
                                <li><a href="izin.php">Izin Cuti</a></li>
                            </ul>
                        </li>
                        <li class="submenu active">
                            <a href="#"><i class="fa fa-money"></i> <span> Menu Laporan SPK </span> <span
                                    class="menu-arrow"></span></a>
                            <ul style="display: block;">
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
                        <h4 class="page-title">Input Kriteria Baru</h4>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8 offset-md-2">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Formulir Input Kriteria</h4>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="form-group">
                                        <label>Nama Kriteria <span class="text-danger">*</span></label>
                                        <input class="form-control" type="text" name="criteria_name" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Bobot Kriteria <span class="text-danger">*</span></label>
                                        <input class="form-control" type="number" step="0.01" name="weight" required
                                            min="0">
                                        <small class="form-text text-muted">Gunakan titik (.) untuk desimal, contoh:
                                            0.5</small>
                                    </div>
                                    <div class="form-group">
                                        <label>Atribut <span class="text-danger">*</span></label>
                                        <select class="form-control select" name="type" required>
                                            <option value="">Pilih Atribut</option>
                                            <option value="benefit">Benefit</option>
                                            <option value="cost">Cost</option>
                                        </select>
                                    </div>
                                    <div class="m-t-20 text-center">
                                        <button type="submit" name="addKriteria"
                                            class="btn btn-primary submit-btn">Simpan Kriteria</button>
                                        <a href="bobot.php" class="btn btn-link">Batal</a>
                                    </div>
                                </form>
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
    <script src="assets/js/select2.min.js"></script>
    <script src="assets/js/moment.min.js"></script>
    <script src="assets/js/bootstrap-datetimepicker.min.js"></script>
    <script src="assets/js/app.js"></script>
    <script>
        $(document).ready(function () {
            // Inisialisasi Select2 untuk dropdown atribut
            $('.select').select2({
                minimumResultsForSearch: Infinity // Sembunyikan kotak pencarian jika hanya ada sedikit opsi
            });
        });
    </script>
</body>

</html>