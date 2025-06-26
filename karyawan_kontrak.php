<?php
require 'koneksi.php';
require 'cek.php';
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
    <link rel="stylesheet" type="text/css" href="assets/css/select2.min.css">
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
                    <li class="active">
                        <a href="karyawan_kontrak.php"><i class="fa fa-user-o"></i> <span>Karyawan Kontrak</span></a>
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
                    <li class="submenu">
                        <a href="#"><i class="fa fa-money"></i> <span> Menu Laporan SPK </span> <span
                                class="menu-arrow"></span></a>
                        <ul style="display: none;">
                            <li><a href="alternatif.php"> Alternatif</a></li>
                            <li><a href="bobot.php"> Bobot & Kriteria </a></li>
                            <li><a href="matrik.php"> Data Klasifikasi</a></li>
                            <li><a href="percetakan_spk.php"> Percetakan SPK </a></li>
                        </ul>
                    </li>
                    </li>
                </ul>
                </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="page-wrapper">
        <div class="content">
            <div class="row">
                <div class="col-sm-4 col-3">
                    <h4 class="page-title">Data Karyawan Kontrak</h4>
                </div>
                <div class="col-sm-8 col-9 text-right m-b-20">
                    <a href="Input-Karyawan_Kontrak.php" class="btn btn btn-primary btn-rounded float-right"><i
                            class="fa fa-plus"></i> Tambah Data Karyawan Kontrak</a>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-striped custom-table mb-0 datatable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Karyawan</th>
                                    <th>Tanggal Mulai</th>
                                    <th>Tanggal Selesai</th>
                                    <th>Status Kontrak</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $ambilsemuadatakaryawan = mysqli_query($conn, "
                                SELECT kk.*, k.nama_lengkap 
                                FROM kontrak_karyawan kk
                                JOIN karyawan k ON kk.karyawan_id = k.karyawan_id");
                                $i = 1;
                                while ($data = mysqli_fetch_array($ambilsemuadatakaryawan)) {
                                    $nama_lengkap = $data['nama_lengkap'];
                                    $tanggal_mulai = $data['tanggal_mulai'];
                                    $tanggal_selesai = $data['tanggal_selesai'];
                                    $status_kontrak = trim(strtolower($data['status_kontrak'])); // Normalisasi nilai
                                    $kid = $data['karyawan_id'];
                                    $kontrak_id = $data['kontrak_id'];
                                    ?>
                                    <tr>
                                        <td><?= $i++; ?></td>
                                        <td>
                                            <img width="28" height="28" src="assets/img/user.jpg" class="rounded-circle"
                                                alt="">
                                            <h2><?= $nama_lengkap ?></h2>
                                        </td>
                                        <td><?= $tanggal_mulai ?></td>
                                        <td><?= $tanggal_selesai ?></td>
                                        <td>
                                            <span
                                                class="custom-badge <?= $status_kontrak === 'aktif' ? 'status-green' : 'status-red'; ?>">
                                                <?= ucfirst($status_kontrak) ?>
                                            </span>
                                        </td>
                                        <td class="text-right">
                                            <div class="dropdown dropdown-action">
                                                <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown"
                                                    aria-expanded="false"><i class="fa fa-ellipsis-v"></i></a>
                                                <div class="dropdown-menu dropdown-menu-right"><a class="dropdown-item"
                                                        href="update-karyawan_kontrak.php?kontrak_id=<?= $kontrak_id; ?>">
                                                        <i class="fa fa-pencil m-r-5"></i> Edit
                                                    </a><a class="dropdown-item" href="#" data-toggle="modal"
                                                        data-target="#delete_karyawan<?= $kontrak_id; ?>">
                                                        <i class="fa fa-trash-o m-r-5"></i> Delete
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <!-- Modal Delete -->
                                    <div id="delete_karyawan<?= $kontrak_id; ?>" class="modal fade delete-modal"
                                        role="dialog">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <form action="hapus_karyawan_kontrak.php" method="POST">
                                                <input type="hidden" name="kontrak_id" value="<?= $kontrak_id; ?>">
                                                <div class="modal-content">
                                                    <div class="modal-body text-center">
                                                        <img src="assets/img/sent.png" alt="" width="50" height="46">
                                                        <h3>Apakah Anda yakin ingin menghapus data karyawan ini?</h3>
                                                        <div class="m-t-20">
                                                            <a href="#" class="btn btn-white" data-dismiss="modal">Batal</a>
                                                            <button type="submit" class="btn btn-danger">Hapus</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
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
    <script>
        $(function () {
            $('#datetimepicker3').datetimepicker({
                format: 'LT'
            });
            $('#datetimepicker4').datetimepicker({
                format: 'LT'
            });
        });
    </script>
</body>

</html>