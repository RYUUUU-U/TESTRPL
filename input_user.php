<?php
require 'koneksi.php'; // Pastikan $conn terdefinisi di sini
require 'cek.php';    // Untuk pemeriksaan sesi/login

$pesan_sukses = "";
$pesan_error = "";

if (isset($_POST['tambah_user'])) {
    $username = $_POST['username'] ?? null;
    $password = $_POST['password'] ?? null; // Password asli dari form
    $role = $_POST['role'] ?? null; // Akan berupa 'admin' atau 'karyawan'

    // Validasi dasar
    if (empty($username) || empty($password) || empty($role)) {
        $pesan_error = "Semua field (Username, Password, Role) wajib diisi.";
    } elseif (strlen($password) < 3) { // Contoh validasi panjang password minimal
        $pesan_error = "Password minimal harus 3 karakter.";
    } elseif (!in_array($role, ['admin', 'karyawan'])) { // Validasi nilai role
        $pesan_error = "Nilai Role tidak valid.";
    } else {
        // Cek apakah username sudah ada
        $stmt_check_user = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
        if ($stmt_check_user) {
            $stmt_check_user->bind_param("s", $username);
            $stmt_check_user->execute();
            $result_check_user = $stmt_check_user->get_result();

            if ($result_check_user->num_rows > 0) {
                $pesan_error = "Username '" . htmlspecialchars($username) . "' sudah digunakan. Silakan pilih username lain.";
            } else {
                // Username belum ada, lanjutkan proses penyimpanan
                // PERINGATAN: Menyimpan password tanpa hashing SANGAT TIDAK AMAN.
                // Baris di bawah ini diubah untuk menyimpan password sebagai teks biasa.
                $password_to_store = $password; // Menggunakan password asli

                $sql_insert_user = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
                $stmt_insert = $conn->prepare($sql_insert_user);
                if ($stmt_insert) {
                    // Menggunakan $password_to_store yang berisi password asli
                    $stmt_insert->bind_param("sss", $username, $password_to_store, $role);

                    if ($stmt_insert->execute()) {
                        $pesan_sukses = "User '" . htmlspecialchars($username) . "' berhasil ditambahkan.";
                        // Kosongkan variabel POST agar form bersih setelah sukses
                        $_POST = array();
                    } else {
                        $pesan_error = "Gagal menambahkan user: " . $stmt_insert->error;
                    }
                    $stmt_insert->close();
                } else {
                    $pesan_error = "Gagal mempersiapkan statement insert user: " . $conn->error;
                }
            }
            $stmt_check_user->close();
        } else {
            $pesan_error = "Gagal mempersiapkan statement pengecekan user: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>WEB KP - Tambah Data User</title>
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
                        <span class="user-img"><img class="rounded-circle" src="assets/img/user.jpg" width="24" alt="Admin">
                        <span class="status online"></span></span>
                        <span>Admin</span>
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="logout.php">Logout</a>
                    </div>
                </li>
            </ul>
            <div class="dropdown mobile-user-menu float-right">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-ellipsis-v"></i></a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="logout.php">Logout</a>
                </div>
            </div>
        </div>
        <div class="sidebar" id="sidebar">
            <div class="sidebar-inner slimscroll">
                <div id="sidebar-menu" class="sidebar-menu">
                    <ul>
                        <li><a href="index.php"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a></li>
                        <li class="active"><a href="karyawan.php"><i class="fa fa-user-o"></i> <span>Karyawan</span></a></li>
                        <li><a href="karyawan_kontrak.php"><i class="fa fa-user-o"></i> <span>Karyawan Kontrak</span></a></li>
                        <li class="submenu">
                            <a href="#"><i class="fa fa-edit"></i> <span> Menu Absensi </span> <span class="menu-arrow"></span></a>
                            <ul style="display: none;">
                                <li><a href="Absensi.php">Absensi</a></li>
                                <li><a href="absensi_qr.php">Tampilkan QR Absensi</a></li>
                                <li><a href="Koreksi.php">Koreksi Absensi</a></li>
                                <li><a href="izin.php">Izin Cuti</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="#"><i class="fa fa-money"></i> <span> Menu Laporan SPK </span> <span class="menu-arrow"></span></a>
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
                    <div class="col-sm-8 col-6">
                        <h4 class="page-title">Tambah Data User Baru</h4>
                    </div>
                    <div class="col-sm-4 col-6 text-right">
                        <a href="karyawan.php" class="btn btn-secondary btn-rounded"><i class="fa fa-arrow-left"></i> Kembali ke Data Karyawan</a>
                    </div>
                </div>

                <?php if (!empty($pesan_sukses)): ?>
                    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                        <?php echo $pesan_sukses; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>
                <?php if (!empty($pesan_error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                        <?php echo $pesan_error; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="mt-3">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card-box">
                                <h4 class="card-title">Form Input User</h4>
                                    <div class="form-group row">
                                        <label class="col-form-label col-md-2">Username</label>
                                        <div class="col-md-10">
                                            <input type="text" name="username" class="form-control" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-form-label col-md-2">Password</label>
                                        <div class="col-md-10">
                                            <input type="password" name="password" class="form-control" required minlength="3">
                                            <small class="form-text text-muted">Minimal 6 karakter</small>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-form-label col-md-2">Role</label>
                                        <div class="col-md-10">
                                            <select name="role" class="form-control" required>
                                                <option value="">-- Pilih Role --</option>
                                                <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                                <option value="karyawan" <?php echo (isset($_POST['role']) && $_POST['role'] == 'karyawan') ? 'selected' : ''; ?>>Karyawan</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <button type="submit" class="btn btn-primary" name="tambah_user">Simpan User</button>
                                    </div>
                            </div>
                        </div>
                    </div>
                </form>
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