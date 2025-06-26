<?php
require 'koneksi.php'; // Sertakan file koneksi database Anda
require 'cek.php';     // Sertakan file untuk pemeriksaan sesi/autentikasi (jika ada)

// Inisialisasi variabel untuk menyimpan data kriteria
$id_criteria = '';
$criteria_name = '';
$weight = '';
$type = '';
$error_message = '';
$success_message = '';

// --- BAGIAN 1: Mengambil Data Kriteria untuk Diedit (GET Request) ---
if (isset($_GET['id'])) {
    $id_criteria_to_edit = $_GET['id'];

    // Sanitasi input ID
    $id_criteria_to_edit = mysqli_real_escape_string($conn, $id_criteria_to_edit);

    // Query untuk mengambil data kriteria
    $query = mysqli_query($conn, "SELECT * FROM saw_criterias WHERE id_criteria = '$id_criteria_to_edit'");

    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_array($query);
        $id_criteria = $data['id_criteria'];
        $criteria_name = $data['criteria_name'];
        $weight = $data['weight'];
        $type = $data['type'];
    } else {
        $error_message = "Kriteria tidak ditemukan.";
    }
} else {
    // Jika ID tidak ada di URL
    $error_message = "ID Kriteria tidak diberikan.";
}


// --- BAGIAN 2: Memperbarui Data Kriteria (POST Request) ---
if (isset($_POST['updateKriteria'])) {
    $id_criteria_post = $_POST['id_criteria']; // ID yang tersembunyi dari form
    $new_criteria_name = $_POST['criteria_name'];
    $new_weight = $_POST['weight'];
    $new_type = $_POST['type'];

    // Sanitasi semua input dari form
    $id_criteria_post = mysqli_real_escape_string($conn, $id_criteria_post);
    $new_criteria_name = mysqli_real_escape_string($conn, $new_criteria_name);
    $new_weight = mysqli_real_escape_string($conn, $new_weight);
    $new_type = mysqli_real_escape_string($conn, $new_type);

    // Validasi sederhana
    if (empty($new_criteria_name) || empty($new_weight) || empty($new_type)) {
        $error_message = "Semua bidang harus diisi.";
    } else {
        // Query untuk memperbarui data kriteria
        $update_query = mysqli_query($conn, "UPDATE saw_criterias SET 
                                            criteria_name = '$new_criteria_name', 
                                            weight = '$new_weight', 
                                            type = '$new_type' 
                                            WHERE id_criteria = '$id_criteria_post'");

        if ($update_query) {
            $success_message = "Kriteria berhasil diperbarui!";
            // Redirect setelah sukses untuk menghindari resubmission form
            echo "<script>alert('" . $success_message . "'); window.location.href='bobot.php';</script>";
            exit(); // Penting untuk menghentikan eksekusi script setelah redirect
        } else {
            $error_message = "Gagal memperbarui kriteria: " . mysqli_error($conn);
        }
    }
    // Jika ada error setelah update, pastikan nilai form tetap terisi
    $id_criteria = $id_criteria_post;
    $criteria_name = $new_criteria_name;
    $weight = $new_weight;
    $type = $new_type;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">

    <title>WEB KP - Edit Kriteria</title>
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
                    <div class="col-sm-12">
                        <h4 class="page-title">Edit Kriteria</h4>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8 offset-md-2">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Formulir Edit Kriteria</h4>
                            </div>
                            <div class="card-body">
                                <?php if ($error_message): ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <?= $error_message ?>
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                <?php endif; ?>
                                <?php if ($success_message): ?>
                                <?php endif; ?>

                                <?php if (!empty($id_criteria)): ?>
                                    <form method="POST">
                                        <input type="hidden" name="id_criteria"
                                            value="<?= htmlspecialchars($id_criteria); ?>">
                                        <div class="form-group">
                                            <label>Nama Kriteria <span class="text-danger">*</span></label>
                                            <input class="form-control" type="text" name="criteria_name"
                                                value="<?= htmlspecialchars($criteria_name); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Bobot Kriteria <span class="text-danger">*</span></label>
                                            <input class="form-control" type="number" step="0.01" name="weight"
                                                value="<?= htmlspecialchars($weight); ?>" required min="0">
                                            <small class="form-text text-muted">Gunakan titik (.) untuk desimal, contoh:
                                                0.5</small>
                                        </div>
                                        <div class="form-group">
                                            <label>Atribut <span class="text-danger">*</span></label>
                                            <select class="form-control select" name="type" required>
                                                <option value="">Pilih Atribut</option>
                                                <option value="benefit" <?= ($type == 'benefit') ? 'selected' : ''; ?>>Benefit
                                                </option>
                                                <option value="cost" <?= ($type == 'cost') ? 'selected' : ''; ?>>Cost</option>
                                            </select>
                                        </div>
                                        <div class="m-t-20 text-center">
                                            <button type="submit" name="updateKriteria"
                                                class="btn btn-primary submit-btn">Update Kriteria</button>
                                            <a href="bobot.php" class="btn btn-link">Batal</a>
                                        </div>
                                    </form>
                                <?php else: ?>
                                    <p class="text-center">Kriteria tidak dapat dimuat. Silakan kembali ke halaman <a
                                            href="bobot.php">Bobot Kriteria</a>.</p>
                                <?php endif; ?>
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