<?php
require 'koneksi.php';
require 'cek.php';
require 'update.php';
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
                        <span>Admin</span>
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="logout.php">Logout</a>
                    </div>
                </li>
            </ul>
            <div class="dropdown mobile-user-menu float-right">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i
                        class="fa fa-ellipsis-v"></i></a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="logout.php">Logout</a>
                </div>
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
                        <li class="active">
                            <a href="karyawan.php"><i class="fa fa-user-o"></i> <span>Karyawan</span></a>
                        </li>
                        <li>
                            <a href="karyawan.php"><i class="fa fa-user-o"></i> <span>Karyawan Kontrak</span></a>
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
                    </ul>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="page-wrapper">
            <div class="content">
                <div class="row">
                    <div class="col-sm-12">
                        <h4 class="page-title">Update Data Karyawan</h4>
                    </div>
                </div>

                <!-- Alert Success (jika ada) -->
                <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Data karyawan berhasil diperbarui!
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <!-- Form Update Karyawan -->
                <form method="POST">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card-box">
                                <h4 class="card-title">Informasi Karyawan</h4>

                                <div class="form-group row">
                                    <label class="col-form-label col-md-2">User ID</label>
                                    <div class="col-md-10">
                                        <input type="text" name="user_id"
                                            value="<?= isset($user_id) ? htmlspecialchars($user_id) : ''; ?>"
                                            class="form-control" required>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-form-label col-md-2">Nama Lengkap</label>
                                    <div class="col-md-10">
                                        <input type="text" name="nama_lengkap"
                                            value="<?= isset($nama_lengkap) ? htmlspecialchars($nama_lengkap) : ''; ?>"
                                            class="form-control" required>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-form-label col-md-2">Jabatan</label>
                                    <div class="col-md-10">
                                        <input type="text" name="jabatan"
                                            value="<?= isset($jabatan) ? htmlspecialchars($jabatan) : ''; ?>"
                                            class="form-control" required>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-form-label col-md-2">Department</label>
                                    <div class="col-md-10">
                                        <input type="text" name="departemen"
                                            value="<?= isset($departemen) ? htmlspecialchars($departemen) : ''; ?>"
                                            class="form-control" required>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-form-label col-md-2">No Telepon</label>
                                    <div class="col-md-10">
                                        <input type="text" name="no_hp"
                                            value="<?= isset($no_hp) ? htmlspecialchars($no_hp) : ''; ?>"
                                            class="form-control" required>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-form-label col-md-2">Status Kepegawaian</label>
                                    <div class="col-md-10">
                                        <select name="status_kepegawaian" class="form-control" required>
                                            <option value="">Pilih Status</option>
                                            <option value="Tetap" <?= (isset($status_kepegawaian) && $status_kepegawaian == 'Tetap') ? 'selected' : ''; ?>>Tetap</option>
                                            <option value="Kontrak" <?= (isset($status_kepegawaian) && $status_kepegawaian == 'Kontrak') ? 'selected' : ''; ?>>Kontrak</option>
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-md-12">
                                        <div class="text-right">
                                            <a href="karyawan.php" class="btn btn-secondary mr-2">
                                                <i class="fa fa-arrow-left"></i> Kembali
                                            </a>
                                            <button type="submit" class="btn btn-primary" name="updatekaryawan">
                                                <i class="fa fa-save"></i> Update Data
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" data-reff=""></div>

    <!-- JavaScript Files -->
    <script src="assets/js/jquery-3.2.1.min.js"></script>
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/jquery.slimscroll.js"></script>
    <script src="assets/js/app.js"></script>

    <!-- Custom JavaScript for Form Validation -->
    <script>
        $(document).ready(function () {
            // Form validation
            $('form').on('submit', function (e) {
                var isValid = true;
                var errorMessage = '';

                // Validate User ID
                var userId = $('input[name="user_id"]').val().trim();
                if (userId === '') {
                    isValid = false;
                    errorMessage += 'User ID tidak boleh kosong.\n';
                }

                // Validate Nama Lengkap
                var namaLengkap = $('input[name="nama_lengkap"]').val().trim();
                if (namaLengkap === '') {
                    isValid = false;
                    errorMessage += 'Nama Lengkap tidak boleh kosong.\n';
                }

                // Validate No HP (should be numeric)
                var noHp = $('input[name="no_hp"]').val().trim();
                if (noHp === '') {
                    isValid = false;
                    errorMessage += 'No Telepon tidak boleh kosong.\n';
                } else if (!/^\d+$/.test(noHp)) {
                    isValid = false;
                    errorMessage += 'No Telepon harus berupa angka.\n';
                }

                if (!isValid) {
                    e.preventDefault();
                    alert(errorMessage);
                }
            });

            // Auto hide alert after 5 seconds
            setTimeout(function () {
                $('.alert').fadeOut('slow');
            }, 5000);
        });
    </script>
</body>

</html>