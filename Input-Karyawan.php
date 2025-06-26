<?php
require 'koneksi.php'; // Pastikan $conn terdefinisi di sini
require 'cek.php';    // Untuk pemeriksaan sesi/login

// Inisialisasi variabel pesan
$pesan_sukses = "";
$pesan_error = "";

if (isset($_POST['karyawan'])) {
    // Ambil data dari form
    // Nama lengkap akan digunakan untuk mencari username
    $nama_lengkap_input = $_POST['nama_lengkap'] ?? null;
    $jabatan = $_POST['jabatan'] ?? null;
    $departemen = $_POST['departemen'] ?? null;
    $no_hp = $_POST['no_hp'] ?? null;
    $status_kepegawaian = $_POST['status_kepegawaian'] ?? null;

    $found_user_id = null;

    // Validasi dasar
    if (empty($nama_lengkap_input) || empty($jabatan) || empty($departemen) || empty($no_hp) || empty($status_kepegawaian)) {
        $pesan_error = "Semua field wajib diisi.";
    } else {
        // 1. Cari user_id berdasarkan nama_lengkap_input (yang diasumsikan sebagai username)
        $stmt_find_user = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
        if ($stmt_find_user) {
            $stmt_find_user->bind_param("s", $nama_lengkap_input);
            $stmt_find_user->execute();
            $result_find_user = $stmt_find_user->get_result();

            if ($result_find_user->num_rows === 1) {
                $user_row = $result_find_user->fetch_assoc();
                $found_user_id = $user_row['user_id'];
            } elseif ($result_find_user->num_rows > 1) {
                $pesan_error = "Ditemukan lebih dari satu user dengan username '" . htmlspecialchars($nama_lengkap_input) . "'. Mohon perbaiki data di tabel users atau gunakan metode input user ID manual.";
            } else {
                $pesan_error = "Username '" . htmlspecialchars($nama_lengkap_input) . "' tidak ditemukan di tabel users. Pastikan nama lengkap sesuai dengan username yang terdaftar, atau buat akun user terlebih dahulu.";
            }
            $stmt_find_user->close();
        } else {
            $pesan_error = "Gagal mempersiapkan query pencarian user: " . $conn->error;
        }

        // Jika user_id ditemukan dan tidak ada error sebelumnya
        if ($found_user_id && empty($pesan_error)) {
            // 2. Cek apakah user_id (yang ditemukan) sudah terdaftar di tabel karyawan
            $stmt_check_karyawan = $conn->prepare("SELECT karyawan_id FROM karyawan WHERE user_id = ?");
            if ($stmt_check_karyawan) {
                $stmt_check_karyawan->bind_param("i", $found_user_id);
                $stmt_check_karyawan->execute();
                $result_check_karyawan = $stmt_check_karyawan->get_result();

                if ($result_check_karyawan->num_rows > 0) {
                    $pesan_error = "User dengan nama '" . htmlspecialchars($nama_lengkap_input) . "' (ID: ".$found_user_id.") sudah terdaftar sebagai karyawan.";
                } else {
                    // 3. Lanjutkan proses penyimpanan data karyawan
                    $sql_insert_karyawan = "INSERT INTO karyawan (user_id, nama_lengkap, jabatan, departemen, no_hp, status_kepegawaian) 
                                            VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt_insert = $conn->prepare($sql_insert_karyawan);
                    if ($stmt_insert) {
                        // nama_lengkap_input digunakan lagi untuk kolom nama_lengkap di tabel karyawan
                        $stmt_insert->bind_param("isssss", $found_user_id, $nama_lengkap_input, $jabatan, $departemen, $no_hp, $status_kepegawaian);

                        if ($stmt_insert->execute()) {
                            $pesan_sukses = "Data karyawan untuk '" . htmlspecialchars($nama_lengkap_input) . "' berhasil ditambahkan.";
                            // Kosongkan variabel POST agar form bersih setelah sukses (opsional)
                            $_POST = array();
                        } else {
                            $pesan_error = "Gagal menambahkan data karyawan: " . $stmt_insert->error;
                        }
                        $stmt_insert->close();
                    } else {
                         $pesan_error = "Gagal mempersiapkan statement insert karyawan: " . $conn->error;
                    }
                }
                $stmt_check_karyawan->close();
            } else {
                $pesan_error = "Gagal mempersiapkan statement pengecekan karyawan: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>WEB KP - Input Karyawan (Otomatis User ID)</title>
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
                    <div class="col-sm-12">
                        <h4 class="page-title">Input Data Karyawan</h4>
                    </div>
                </div>

                <?php if (!empty($pesan_sukses)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $pesan_sukses; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>
                <?php if (!empty($pesan_error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $pesan_error; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card-box">
                                <h4 class="card-title">Tambah Data Karyawan</h4>
                                    <div class="form-group row">
                                        <label class="col-form-label col-md-2">Nama Lengkap </label>
                                        <div class="col-md-10">
                                            <input type="text" name="nama_lengkap" class="form-control" required placeholder="Masukkan nama lengkap yang sesuai dengan username user" value="<?php echo isset($_POST['nama_lengkap']) ? htmlspecialchars($_POST['nama_lengkap']) : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-form-label col-md-2">Jabatan</label>
                                        <div class="col-md-10">
                                            <input type="text" name="jabatan" class="form-control" required value="<?php echo isset($_POST['jabatan']) ? htmlspecialchars($_POST['jabatan']) : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-form-label col-md-2">Departemen</label>
                                        <div class="col-md-10">
                                            <input type="text" name="departemen" class="form-control" required value="<?php echo isset($_POST['departemen']) ? htmlspecialchars($_POST['departemen']) : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-form-label col-md-2">No Telepon</label>
                                        <div class="col-md-10">
                                            <input type="text" name="no_hp" class="form-control" required value="<?php echo isset($_POST['no_hp']) ? htmlspecialchars($_POST['no_hp']) : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-form-label col-md-2">Status Kepegawaian</label>
                                        <div class="col-md-10">
                                            <select name="status_kepegawaian" class="form-control" required>
                                                <option value="">-- Pilih Status --</option>
                                                <option value="tetap" <?php echo (isset($_POST['status_kepegawaian']) && $_POST['status_kepegawaian'] == 'tetap') ? 'selected' : ''; ?>>Tetap</option>
                                                <option value="kontrak" <?php echo (isset($_POST['status_kepegawaian']) && $_POST['status_kepegawaian'] == 'kontrak') ? 'selected' : ''; ?>>Kontrak</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <button type="submit" class="btn btn-primary" name="karyawan">Submit</button>
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