<?php
require 'koneksi.php'; // Hubungkan ke database
require 'cek.php';    // Skrip untuk memeriksa sesi (jika ada logika tambahan)

// Cek apakah pengguna sudah login, jika belum, alihkan ke halaman login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// 2. INISIALISASI VARIABEL
// --------------------------------------------------
$pesan_sukses = "";
$pesan_error = "";
$karyawan_id_session = null;
$nama_karyawan_session = "Karyawan"; // Nama default
$username_session = $_SESSION['username'];

// 3. AMBIL DATA KARYAWAN YANG LOGIN
// --------------------------------------------------
// Menggunakan JOIN untuk mengambil data karyawan berdasarkan username session
$query_karyawan = "SELECT k.karyawan_id, k.nama_lengkap 
                   FROM karyawan k
                   JOIN users u ON k.user_id = u.user_id
                   WHERE u.username = ?";
                   
$stmt_karyawan = $conn->prepare($query_karyawan);
if ($stmt_karyawan) {
    $stmt_karyawan->bind_param("s", $username_session);
    $stmt_karyawan->execute();
    $result_karyawan = $stmt_karyawan->get_result();

    if ($result_karyawan->num_rows > 0) {
        $karyawan_data = $result_karyawan->fetch_assoc();
        $karyawan_id_session = $karyawan_data['karyawan_id'];
        $nama_karyawan_session = $karyawan_data['nama_lengkap'];
    } else {
        $pesan_error = "Data karyawan tidak ditemukan untuk akun Anda. Silakan hubungi administrator.";
    }
    $stmt_karyawan->close();
} else {
    $pesan_error = "Terjadi kesalahan saat mengambil data Anda. " . $conn->error;
}


// 4. PROSES FORM SAAT DI-SUBMIT
// --------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajukan_izin'])) {
    // Hanya proses jika tidak ada error dan ID karyawan valid
    if ($karyawan_id_session !== null && empty($pesan_error)) {
        // Ambil data dari form
        $tanggal_mulai = $_POST['tanggal_mulai'] ?? null;
        $tanggal_selesai = $_POST['tanggal_selesai'] ?? null;
        $jenis_cuti = $_POST['jenis_cuti'] ?? null;
        $alasan = trim($_POST['alasan']) ?? null;

        // Validasi input
        if (empty($tanggal_mulai) || empty($tanggal_selesai) || empty($jenis_cuti) || empty($alasan)) {
            $pesan_error = "Semua kolom yang ditandai * wajib diisi.";
        } elseif (strtotime($tanggal_selesai) < strtotime($tanggal_mulai)) {
            $pesan_error = "Tanggal selesai tidak boleh lebih awal dari tanggal mulai.";
        } else {
            // Jika valid, masukkan ke database
            // Query disesuaikan dengan struktur tabel: cuti_id (auto increment), karyawan_id, dst.
            $sql_insert = "INSERT INTO izin_cuti (karyawan_id, tanggal_mulai, tanggal_selesai, jenis_cuti, alasan, tanggal_pengajuan, status_cuti) 
                           VALUES (?, ?, ?, ?, ?, CURDATE(), 'pending')";
            
            $stmt_insert = $conn->prepare($sql_insert);
            if ($stmt_insert) {
                $stmt_insert->bind_param("issss", $karyawan_id_session, $tanggal_mulai, $tanggal_selesai, $jenis_cuti, $alasan);
                if ($stmt_insert->execute()) {
                    $pesan_sukses = "Pengajuan izin berhasil dikirim. Anda akan menerima notifikasi setelah disetujui.";
                } else {
                    $pesan_error = "Gagal menyimpan pengajuan: " . $stmt_insert->error;
                }
                $stmt_insert->close();
            } else {
                $pesan_error = "Gagal mempersiapkan query: " . $conn->error;
            }
        }
    }
}

// Opsi untuk dropdown jenis cuti
$opsi_jenis_cuti = [
    "Cuti Tahunan",
    "Sakit",
    "Izin Khusus",
    "Cuti Melahirkan",
    "Cuti Besar",
    "Lainnya"
];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>WEB KP - Formulir Izin Cuti</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap-datetimepicker.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/select2.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
</head>

<body>
    <div class="main-wrapper">
        <!-- Header -->
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
                            <img class="rounded-circle" src="assets/img/user.jpg" width="24" alt="User">
                            <span class="status online"></span>
                        </span>
                        <span><?php echo htmlspecialchars($username_session); ?></span>
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="logout.php">Logout</a>
                    </div>
                </li>
            </ul>
        </div>
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-inner slimscroll">
                <div id="sidebar-menu" class="sidebar-menu">
                    <ul>
                        <li><a href="index_karyawan.php"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a></li>
                        <li class="submenu">
                            <a href="#"><i class="fa fa-edit"></i> <span> Menu Absensi </span> <span class="menu-arrow"></span></a>
                            <ul style="display: none;">
                                <li><a href="absensi_karyawan.php">Absensi</a></li>
                                <li><a href="koreksi_karyawan.php">Koreksi Absensi </a></li>
                                <!-- Menandai halaman ini sebagai yang aktif -->
                                <li class="active"><a href="daftar_izin.php">Izin Cuti </a></li> 
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- Page Content -->
        <div class="page-wrapper">
            <div class="content">
                <div class="row">
                    <div class="col-sm-8 col-6">
                        <h4 class="page-title">Formulir Pengajuan Izin Cuti</h4>
                    </div>
                    <div class="col-sm-4 col-6 text-right">
                        <!-- Tombol kembali ke daftar izin -->
                        <a href="daftar_izin.php" class="btn btn-secondary btn-rounded"><i class="fa fa-arrow-left"></i> Kembali</a>
                    </div>
                </div>

                <!-- Notifikasi Sukses atau Error -->
                <?php if (!empty($pesan_sukses)): ?>
                    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                        <?php echo $pesan_sukses; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                <?php endif; ?>
                <?php if (!empty($pesan_error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                        <?php echo $pesan_error; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                <?php endif; ?>

                <!-- Tampilkan form hanya jika ID karyawan ditemukan -->
                <?php if ($karyawan_id_session !== null): ?>
                <div class="card-box">
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama Karyawan</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($nama_karyawan_session); ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Tanggal Mulai Cuti <span class="text-danger">*</span></label>
                                    <div class="cal-icon">
                                        <input type="text" name="tanggal_mulai" class="form-control datetimepicker" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Tanggal Selesai Cuti <span class="text-danger">*</span></label>
                                    <div class="cal-icon">
                                        <input type="text" name="tanggal_selesai" class="form-control datetimepicker" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Jenis Cuti <span class="text-danger">*</span></label>
                                    <select name="jenis_cuti" class="form-control select" required>
                                        <option value="">-- Pilih Jenis Cuti --</option>
                                        <?php foreach ($opsi_jenis_cuti as $opsi): ?>
                                            <option value="<?php echo $opsi; ?>"><?php echo $opsi; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Alasan / Keterangan <span class="text-danger">*</span></label>
                                    <textarea name="alasan" rows="5" class="form-control" placeholder="Jelaskan alasan Anda mengajukan cuti..." required></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="text-right mt-3">
                            <button type="submit" name="ajukan_izin" class="btn btn-primary">Ajukan Izin</button>
                        </div>
                    </form>
                </div>
                <?php endif; ?>
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
        $(document).ready(function() {
            // Inisialisasi DateTimePicker untuk input tanggal
            if($('.datetimepicker').length > 0) {
                $('.datetimepicker').datetimepicker({
                    format: 'YYYY-MM-DD',
                    ignoreReadonly: true, // Memungkinkan input manual jika diperlukan
                    useCurrent: false    // Tidak otomatis mengisi tanggal hari ini
                });
            }

            // Inisialisasi Select2 untuk dropdown yang lebih baik
            if($('.select').length > 0) {
                $('.select').select2({
                    minimumResultsForSearch: -1, // Sembunyikan kotak pencarian
                    width: '100%'
                });
            }
        });
    </script>
</body>
</html>
