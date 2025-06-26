<?php
require 'koneksi.php'; // Includes the database connection file
require 'cek.php'; // Includes a file for checking user session/authentication
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">

    <title>WEB KP - Bobot Kriteria</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <link rel="stylesheet" type="text/css" href="assets/css/select2.min.css">

    <style>
        /* CSS tambahan untuk memastikan elemen DataTables yang tidak diinginkan tidak terlihat */
        /* Menyembunyikan input "Show entries" DataTables */
        div.dataTables_wrapper div.dataTables_length {
            display: none;
        }

        /* Menyembunyikan input pencarian DataTables */
        div.dataTables_wrapper div.dataTables_filter {
            display: none;
        }

        /* Menyesuaikan margin untuk elemen DataTables yang tersisa agar terlihat rapi */
        /* Misalnya, agar paginasi dan info tidak terlalu dekat dengan tabel */
        div.dataTables_wrapper div.dataTables_info,
        div.dataTables_wrapper div.dataTables_paginate {
            margin-top: 1rem;
        }
    </style>
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
                        <li class="submenu">
                            <a href="#"><i class="fa fa-money"></i> <span> Menu Laporan SPK </span> <span
                                    class="menu-arrow"></span></a>
                            <ul style="display: block;">
                                <li><a href="alternatif.php"> Alternatif</a></li>
                                <li><a href="bobot.php" class="active"> Bobot & Kriteria </a></li>
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
                    <div class="col-sm-4 col-3">
                        <h4 class="page-title">Bobot Kriteria</h4>
                    </div>
                    <div class="col-sm-8 col-9 text-right m-b-20">
                        <a href="input_kriteria.php" class="btn btn btn-primary btn-rounded float-right"><i
                                class="fa fa-plus"></i> Input Kriteria</a>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Tabel Bobot Kriteria</h4>
                                <p class="card-text">Pengambil keputusan memberi bobot preferensi dari setiap kriteria
                                    dengan masing-masing jenisnya (keuntungan/benefit atau biaya/cost):</p>
                            </div>
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped custom-table mb-0" id="criteriaTable">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Simbol</th>
                                                    <th>Kriteria</th>
                                                    <th>Bobot</th>
                                                    <th>Atribut</th>
                                                    <th class="text-right">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                // Fetch criteria data from your database (assuming 'saw_criterias' table)
                                                $ambilsemuadatakriteria = mysqli_query($conn, "SELECT * FROM saw_criterias ORDER BY id_criteria ASC");
                                                $no = 1;
                                                while ($data = mysqli_fetch_array($ambilsemuadatakriteria)) {
                                                    $id_criteria = $data['id_criteria'];
                                                    $criteria_name = $data['criteria_name'];
                                                    $weight = $data['weight'];
                                                    $type = $data['type'];
                                                    ?>
                                                    <tr>
                                                        <td><?= $no++; ?></td>
                                                        <td><?= "C" . $id_criteria; ?></td>
                                                        <td><?= $criteria_name; ?></td>
                                                        <td><?= $weight; ?></td>
                                                        <td><?= $type; ?></td>
                                                        <td class="text-right">
                                                            <div class="dropdown dropdown-action">
                                                                <a href="#" class="action-icon dropdown-toggle"
                                                                    data-toggle="dropdown" aria-expanded="false"><i
                                                                        class="fa fa-ellipsis-v"></i></a>
                                                                <div class="dropdown-menu dropdown-menu-right">
                                                                    <a class="dropdown-item"
                                                                        href="edit_kriteria.php?id=<?= $id_criteria; ?>">
                                                                        <i class="fa fa-pencil m-r-5"></i> Edit
                                                                    </a>
                                                                    <a class="dropdown-item" href="#" data-toggle="modal"
                                                                        data-target="#delete_kriteria<?= $id_criteria; ?>">
                                                                        <i class="fa fa-trash-o m-r-5"></i> Delete
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                    <div id="delete_kriteria<?= $id_criteria; ?>"
                                                        class="modal fade delete-modal" role="dialog">
                                                        <div class="modal-dialog modal-dialog-centered">
                                                            <form action="hapus_kriteria.php" method="POST">
                                                                <input type="hidden" name="id_criteria"
                                                                    value="<?= $id_criteria; ?>">
                                                                <div class="modal-content">
                                                                    <div class="modal-body text-center">
                                                                        <img src="assets/img/sent.png" alt="" width="50"
                                                                            height="46">
                                                                        <h3>Apakah Anda yakin ingin menghapus kriteria ini?
                                                                        </h3>
                                                                        <div class="m-t-20">
                                                                            <a href="#" class="btn btn-white"
                                                                                data-dismiss="modal">Batal</a>
                                                                            <button type="submit"
                                                                                class="btn btn-danger">Hapus</button>
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
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <p>Tabel Kriteria Cj</p>
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
        $(document).ready(function () {
            // Inisialisasi datetimepicker
            $('#datetimepicker3').datetimepicker({
                format: 'LT'
            });
            $('#datetimepicker4').datetimepicker({
                format: 'LT'
            });

            // Inisialisasi DataTables untuk tabel kriteria
            if (!$.fn.DataTable.isDataTable('#criteriaTable')) {
                $('#criteriaTable').DataTable({
                    "paging": true,      // Tetap aktifkan paginasi
                    "lengthChange": false, // MATIKAN dropdown "Show X entries"
                    "searching": false,  // MATIKAN kotak pencarian
                    "ordering": true,    // Tetap aktifkan pengurutan kolom
                    "info": true,        // Tetap aktifkan informasi "Showing X of Y entries"
                    "autoWidth": false,
                    // Konfigurasi DOM: hanya 't' (tabel), 'i' (info), dan 'p' (paginasi)
                    "dom": '<"row"<"col-sm-12"tr>><"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
                });
            }
        });
    </script>
</body>

</html>