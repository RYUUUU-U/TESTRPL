<?php
require 'koneksi.php';
require 'cek.php';
require 'tambah_koreksi.php';

// Ambil data user yang sedang login berdasarkan username
$username = $_SESSION['username']; // Menggunakan username sebagai identifier

// Sesuaikan query dengan struktur tabel yang benar
// Ubah 'users' menjadi tabel yang sesuai dan gunakan 'username' sebagai kondisi
$get_user = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
$user_data = mysqli_fetch_array($get_user);
$nama_user = isset($user_data['nama']) ? $user_data['nama'] : $_SESSION['username']; // Gunakan username jika nama tidak ditemukan

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>WEB KP - Ajukan Koreksi Absensi</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap-datetimepicker.min.css">
    <!-- Tambahan CSS untuk memperbaiki tampilan -->
    <style>
        .form-help {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
        }
        .input-group-text {
            background-color: #f8f9fa;
            border-color: #ced4da;
        }
    </style>
</head>

<body>
    <div class="main-wrapper">
        <div class="header">
            <div class="header-left">
                <a href="index_karyawan.php" class="logo">
                    <img src="assets/img/logo.png" width="35" height="35" alt="">
                    <span>CV. SEJAHTERA ABADI</span>
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
        <div class="sidebar" id="sidebar">
            <div class="sidebar-inner slimscroll">
                <div id="sidebar-menu" class="sidebar-menu">
                    <ul>
                        <li>
                            <div class="profile-section" style="text-align: center;">
                            </div>
                            <a href="index_karyawan.php"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a>
                        </li>
                        <li class="submenu">
                            <a href="#"><i class="fa fa-edit"></i> <span> Menu Absensi </span> <span
                                    class="menu-arrow"></span></a>
                            <ul style="display: none;">
                                <li><a href="Absensi_karyawan.php">Absensi</a></li>
                                <li><a class="active" href="Koreksi_karyawan.php">Koreksi Absensi</a></li>
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
                    <div class="col-sm-12">
                        <h4 class="page-title">Ajukan Koreksi Absensi</h4>
                    </div>
                </div>

                <?php if ($feedbackMessage): ?>
                    <div class="alert alert-<?php echo $feedbackType; ?> alert-dismissible fade show" role="alert">
                        <?php echo $feedbackMessage; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-8 offset-md-2">
                        <form action="" method="POST">
                            <div class="form-group">
                                <label>ID Absensi yang Akan Dikoreksi <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fa fa-hashtag"></i>
                                        </span>
                                    </div>
                                    <input type="number" class="form-control" name="absensi_id" id="absensi_id" 
                                           placeholder="Masukkan ID Absensi" required min="1">
                                </div>
                                <div class="form-help">
                                    <i class="fa fa-info-circle"></i> 
                                    Masukkan ID absensi yang ingin dikoreksi. Contoh: 1, 2, 3, dst.
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Tanggal Absensi yang Dikoreksi <span class="text-danger">*</span></label>
                                <div class="">
                                    <input type="date" class="form-control" name="tanggal_koreksi" required>
                                </div>
                                <div class="form-help">
                                    <i class="fa fa-info-circle"></i> 
                                    Pilih tanggal absensi yang akan dikoreksi.
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Alasan Koreksi <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="alasan" rows="4" required 
                                          placeholder="Jelaskan alasan mengapa absensi perlu dikoreksi..."></textarea>
                                <div class="form-help">
                                    <i class="fa fa-info-circle"></i> 
                                    Berikan alasan yang jelas dan detail untuk permintaan koreksi absensi.
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Status Koreksi</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fa fa-clock-o text-warning"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control" value="Pending" readonly>
                                </div>
                                <div class="form-help">
                                    <i class="fa fa-info-circle"></i> 
                                    Status akan berubah setelah admin memproses permintaan koreksi.
                                </div>
                            </div>

                            <div class="m-t-20 text-center">
                                <button type="submit" class="btn btn-primary submit-btn">
                                    <i class="fa fa-paper-plane"></i> Ajukan Koreksi
                                </button>
                                <button type="reset" class="btn btn-secondary ml-2">
                                    <i class="fa fa-refresh"></i> Reset Form
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Informasi Tambahan -->
                <div class="row mt-4">
                    <div class="col-md-8 offset-md-2">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fa fa-question-circle"></i> Panduan Penggunaan
                                </h5>
                            </div>
                            <div class="card-body">
                                <ol>
                                    <li><strong>ID Absensi:</strong> Masukkan nomor ID absensi yang ingin dikoreksi.</li>
                                    <li><strong>Tanggal:</strong> Pilih tanggal absensi yang sesuai dengan ID absensi.</li>
                                    <li><strong>Alasan:</strong> Jelaskan dengan detail mengapa absensi perlu dikoreksi.</li>
                                    <li><strong>Submit:</strong> Klik tombol "Ajukan Koreksi" untuk mengirim permintaan.</li>
                                </ol>
                                <div class="alert alert-info mt-3">
                                    <i class="fa fa-info-circle"></i> 
                                    <strong>Catatan:</strong> Permintaan koreksi akan diproses oleh admin. 
                                </div>
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
        $(function () {
            // Mengubah format datetimepicker menjadi date saja 
            $('.cal-icon input[name="tanggal_koreksi"]').datetimepicker({
                format: 'YYYY-MM-DD',
                useCurrent: true
            });

            // Validasi input absensi_id hanya menerima angka positif
            $('#absensi_id').on('input', function() {
                var value = $(this).val();
                if (value < 1) {
                    $(this).val('');
                }
            });

            // Form validation sebelum submit
            $('form').on('submit', function(e) {
                var absensiId = $('#absensi_id').val();
                var tanggalKoreksi = $('input[name="tanggal_koreksi"]').val();
                var alasan = $('textarea[name="alasan"]').val();

                if (!absensiId || !tanggalKoreksi || !alasan.trim()) {
                    e.preventDefault();
                    alert('Semua field yang bertanda * wajib diisi!');
                    return false;
                }

                if (absensiId < 1) {
                    e.preventDefault();
                    alert('ID Absensi harus berupa angka positif!');
                    return false;
                }

                // Konfirmasi sebelum submit
                if (!confirm('Apakah Anda yakin ingin mengajukan koreksi untuk ID Absensi #' + absensiId + '?')) {
                    e.preventDefault();
                    return false;
                }
            });

            // Auto-focus pada field pertama
            $('#absensi_id').focus();
        });
    </script>
</body>

</html>