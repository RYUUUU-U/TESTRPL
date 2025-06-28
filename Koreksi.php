<?php
require 'koneksi.php'; // Pastikan ini mengkoneksikan ke database 'db_absensi_digital'
require 'cek.php';

// ... (inisialisasi filter tetap sama) ...
$nama_karyawan_filter = isset($_GET['nama_karyawan']) ? trim($_GET['nama_karyawan']) : '';
$tanggal_mulai_filter = isset($_GET['tanggal_mulai']) ? $_GET['tanggal_mulai'] : '';
$tanggal_akhir_filter = isset($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : '';

$whereClauses = [];
$params = [];
$types = "";

if (!empty($nama_karyawan_filter)) {
    $whereClauses[] = "a.nama_lengkap LIKE ?";
    $params[] = "%" . $nama_karyawan_filter . "%";
    $types .= "s";
}
if (!empty($tanggal_mulai_filter)) {
    $whereClauses[] = "k.tanggal_pengajuan >= ?";
    $params[] = $tanggal_mulai_filter;
    $types .= "s";
}
if (!empty($tanggal_akhir_filter)) {
    $whereClauses[] = "k.tanggal_pengajuan <= ?";
    $params[] = $tanggal_akhir_filter;
    $types .= "s";
}

$whereSql = "";
if (!empty($whereClauses)) {
    $whereSql = " WHERE " . implode(" AND ", $whereClauses);
}

// Fixed table names - removed the database prefix from table names
$query = "SELECT k.*, a.nama_lengkap AS nama_karyawan_tampil
          FROM koreksi_absensi k
          JOIN karyawan a ON k.karyawan_id = a.karyawan_id "
          . $whereSql . " ORDER BY k.tanggal_pengajuan DESC";
// -------------------------------------------

$stmt = $conn->prepare($query); // Line 56 (approx)

if ($stmt === false) {
    die("Error preparing statement: " . htmlspecialchars($conn->error) . "<br>Query: " . htmlspecialchars($query)); // Tambahkan output query untuk debug
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// ... (sisa kode HTML dan JavaScript tetap sama) ...
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>WEB KP - Koreksi Absensi</title>
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
                        <li><a href="index.php"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a></li>
                        <li><a href="karyawan.php"><i class="fa fa-user-o"></i> <span>Karyawan</span></a></li>
                        <li><a href="karyawan_kontrak.php"><i class="fa fa-user-o"></i> <span>Karyawan Kontrak</span></a></li>
                        <li class="submenu">
                            <a href="#"><i class="fa fa-edit"></i> <span> Menu Absensi </span> <span class="menu-arrow"></span></a>
                            <ul style="display: none;">
                                <li><a href="Absensi.php">Absensi</a></li>
                                <li><a href="absensi_qr.php">Tampilkan QR Absensi</a></li>
                                <li><a class="active" href="Koreksi.php">Koreksi Absensi</a></li>
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
                <h4 class="page-title">Koreksi Absensi</h4>         
                <div class="row filter-row mt-3">
                    <form action="" method="GET" class="w-100 d-flex flex-wrap align-items-end">
                        <div class="col-sm-6 col-md-3">
                            <div class="form-group form-focus">
                                <label class="focus-label">Nama Karyawan</label>
                                <input type="text" name="nama_karyawan" class="form-control floating" value="<?php echo htmlspecialchars($nama_karyawan_filter); ?>" placeholder="Cari Nama Karyawan">
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="form-group form-focus">
                                <label class="focus-label">Dari Tanggal Pengajuan</label>
                                <div class="cal-icon">
                                    <input class="form-control floating datetimepicker" type="text" name="tanggal_mulai" value="<?php echo htmlspecialchars($tanggal_mulai_filter); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="form-group form-focus">
                                <label class="focus-label">Sampai Tanggal Pengajuan</label>
                                <div class="cal-icon">
                                    <input class="form-control floating datetimepicker" type="text" name="tanggal_akhir" value="<?php echo htmlspecialchars($tanggal_akhir_filter); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-success btn-block mb-3"> Filter </button> &nbsp;
                            <a href="<?php echo strtok($_SERVER["REQUEST_URI"], '?'); ?>" class="btn btn-warning btn-block mb-3"> Reset </a>
                        </div>
                    </form>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-striped custom-table mb-0 datatable">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Karyawan</th>
                                        <th>Tanggal</th>
                                        <th>Alasan</th>
                                        <th>Status Koreksi</th>
                                        <th class="text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 1;
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        // Menggunakan alias 'nama_karyawan_tampil' yang diambil dari JOIN dengan tabel karyawan
                                        $namaKaryawanUntukDitampilkan = isset($row['nama_karyawan_tampil']) ? $row['nama_karyawan_tampil'] : "Nama tidak ditemukan";
                                        
                                        // Jika Anda masih memerlukan logika fallback (meskipun JOIN seharusnya sudah menyediakan nama):
                                        // if ($namaKaryawanUntukDitampilkan === "Nama tidak ditemukan" || $namaKaryawanUntukDitampilkan === null) {
                                        //     // Logika fallback ini mungkin tidak lagi diperlukan jika JOIN selalu berhasil
                                        //     $karyawan_query_fallback = "SELECT nama_lengkap FROM karyawan WHERE karyawan_id = '" . mysqli_real_escape_string($conn, $row['karyawan_id']) . "'";
                                        //     $karyawan_result_fallback = mysqli_query($conn, $karyawan_query_fallback);
                                        //     if ($karyawan_result_fallback && mysqli_num_rows($karyawan_result_fallback) > 0) {
                                        //         $karyawan_data_fallback = mysqli_fetch_assoc($karyawan_result_fallback);
                                        //         $namaKaryawanUntukDitampilkan = $karyawan_data_fallback['nama_lengkap'];
                                        //     }
                                        // }

                                        $statusKoreksi = ucfirst(htmlspecialchars($row['status_koreksi']));
                                        ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo htmlspecialchars($namaKaryawanUntukDitampilkan); ?></td>
                                            <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($row['tanggal_pengajuan']))); ?></td>
                                            <td><?php echo htmlspecialchars($row['alasan']); ?></td>
                                            <td>
                                                <div class="dropdown action-label">
                                                    <a class="custom-badge status-purple dropdown-toggle" href="#"
                                                        data-toggle="dropdown" aria-expanded="false">
                                                        <?php echo $statusKoreksi; ?>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <a class="dropdown-item update-status" href="#"
                                                            data-id="<?php echo htmlspecialchars($row['koreksi_id']); ?>"
                                                            data-status="Pending">Pending</a>
                                                        <a class="dropdown-item update-status" href="#"
                                                            data-id="<?php echo htmlspecialchars($row['koreksi_id']); ?>"
                                                            data-status="Disetujui">Disetujui</a>
                                                        <a class="dropdown-item update-status" href="#"
                                                            data-id="<?php echo htmlspecialchars($row['koreksi_id']); ?>"
                                                            data-status="Ditolak">Ditolak</a>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-right">
                                                <div class="dropdown dropdown-action">
                                                    <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown"
                                                        aria-expanded="false"><i class="fa fa-ellipsis-v"></i></a>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <a class="dropdown-item" href="#" data-toggle="modal"
                                                            data-target="#delete_koreksi"
                                                            data-id="<?php echo htmlspecialchars($row['koreksi_id']); ?>"><i
                                                                class="fa fa-trash-o m-r-5"></i> Delete</a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div id="delete_koreksi" class="modal fade delete-modal" role="dialog">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-body text-center">
                            <img src="assets/img/sent.png" alt="" width="50" height="46">
                            <h3>Apakah Anda yakin ingin menghapus data koreksi ini?</h3>
                            <div class="m-t-20">
                                <form action="hapus_koreksi.php" method="POST">
                                    <input type="hidden" name="koreksi_id" id="delete_id" value="">
                                    <a href="#" class="btn btn-white" data-dismiss="modal">Batal</a>
                                    <button type="submit" class="btn btn-danger">Hapus</button>
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
    <script src="assets/js/jquery.dataTables.min.js"></script>
    <script src="assets/js/dataTables.bootstrap4.min.js"></script>
    <script src="assets/js/jquery.slimscroll.js"></script>
    <script src="assets/js/select2.min.js"></script>
    <script src="assets/js/moment.min.js"></script>
    <script src="assets/js/bootstrap-datetimepicker.min.js"></script>
    <script src="assets/js/app.js"></script>
    <script>
        $(document).ready(function () {
            $('#delete_koreksi').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var id = button.data('id');
                $(this).find('#delete_id').val(id);
            });
                $(document).ready(function () {
                    // ... (kode untuk modal delete dan update status yang sudah ada) ...

                    // TAMBAHKAN KODE DI BAWAH INI
                    $('.datetimepicker').datetimepicker({
                        format: 'DD/MM/YYYY', // Sesuaikan format tanggal sesuai kebutuhan Anda
                        locale: 'id'          // Mengatur bahasa menjadi Indonesia (memerlukan file locale moment.js)
                    });
                    // AKHIR DARI KODE TAMBAHAN

                });
            $('.update-status').on('click', function (e) {
                e.preventDefault();
                var koreksiId = $(this).data('id');
                var newStatus = $(this).data('status');

                $.ajax({
                    url: 'update_koreksi.php',
                    method: 'POST',
                    data: { id: koreksiId, status: newStatus },
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert('Gagal memperbarui status: ' + response.message);
                        }
                    },
                    error: function () {
                        alert('Terjadi kesalahan saat mengirim permintaan.');
                    }
                });
            });
        });
    </script>
</body>

</html>