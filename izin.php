<?php
require 'koneksi.php'; // Pastikan $conn terdefinisi di sini
require 'cek.php';    // Untuk pemeriksaan sesi/login

// === PERUBAHAN DI SINI: BLOK UNTUK MENANGANI UPDATE STATUS VIA AJAX ===
// Logika ini hanya berjalan jika ada permintaan POST dengan action 'update_status'
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    // Set header ke JSON karena ini adalah respons untuk AJAX
    header('Content-Type: application/json');

    // Ambil data yang dikirim oleh AJAX
    $cutiId = $_POST['cuti_id'];
    $newStatus = $_POST['status_cuti'];

    // Validasi sederhana
    if (empty($cutiId) || empty($newStatus)) {
        echo json_encode(['success' => false, 'message' => 'Data tidak lengkap.']);
        exit;
    }

    // Siapkan query UPDATE dengan prepared statement untuk keamanan
    $updateStmt = $conn->prepare("UPDATE izin_cuti SET status_cuti = ? WHERE cuti_id = ?");
    if ($updateStmt) {
        // Bind parameter: 's' untuk string (status), 'i' untuk integer (id)
        $updateStmt->bind_param("si", $newStatus, $cutiId);

        // Eksekusi query
        if ($updateStmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal memperbarui database.']);
        }
        $updateStmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal mempersiapkan statement.']);
    }
    
    // Penting: Hentikan eksekusi skrip agar tidak melanjutkan ke render HTML
    exit;
}
// === AKHIR BLOK PERUBAHAN ===


// Inisialisasi variabel untuk filter (kode filter Anda tetap sama)
$nama_karyawan_filter = isset($_GET['nama_karyawan']) ? trim($_GET['nama_karyawan']) : '';
$tanggal_mulai_filter = isset($_GET['tanggal_mulai']) ? $_GET['tanggal_mulai'] : '';
$tanggal_akhir_filter = isset($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : '';

// Query untuk mengambil data izin cuti dengan filter (kode Anda tetap sama)
$whereClauses = [];
$params = [];
$types = "";

if (!empty($nama_karyawan_filter)) {
    $whereClauses[] = "k.nama_lengkap LIKE ?";
    $params[] = "%" . $nama_karyawan_filter . "%";
    $types .= "s";
}
if (!empty($tanggal_mulai_filter)) {
    $whereClauses[] = "ic.tanggal_pengajuan >= ?";
    $params[] = $tanggal_mulai_filter;
    $types .= "s";
}
if (!empty($tanggal_akhir_filter)) {
    $whereClauses[] = "ic.tanggal_pengajuan <= ?";
    $params[] = $tanggal_akhir_filter;
    $types .= "s";
}

$whereSql = "";
if (!empty($whereClauses)) {
    $whereSql = " WHERE " . implode(" AND ", $whereClauses);
}

$query_str = "SELECT ic.*, k.nama_lengkap
              FROM izin_cuti ic
              JOIN karyawan k ON ic.karyawan_id = k.karyawan_id "
             . $whereSql . " ORDER BY ic.tanggal_pengajuan DESC, ic.cuti_id DESC";

$stmt = $conn->prepare($query_str);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <!-- Judul halaman disesuaikan -->
    <title>WEB KP - Izin Cuti</title> 
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap-datetimepicker.min.css">
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
                        <!-- Struktur menu Anda tetap sama, pastikan link Izin Cuti memiliki class 'active' -->
                        <li><a href="index.php"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a></li>
                        <li><a href="karyawan.php"><i class="fa fa-user-o"></i> <span>Karyawan</span></a></li>
                        <li><a href="karyawan_kontrak.php"><i class="fa fa-user-o"></i> <span>Karyawan Kontrak</span></a></li>
                        <li class="submenu">
                            <a href="#"><i class="fa fa-edit"></i> <span> Menu Absensi </span> <span class="menu-arrow"></span></a>
                            <ul style="display: none;">
                                <li><a href="Absensi.php">Absensi</a></li>
                                <li><a href="absensi_qr.php">Tampilkan QR Absensi</a></li>
                                <li><a href="Koreksi.php">Koreksi Absensi</a></li>
                                <!-- Menandai halaman aktif -->
                                <li><a class="active" href="izin.php">Izin Cuti</a></li>
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
                    <div class="col-sm-8 col-5">
                        <h4 class="page-title">Data Izin Cuti Karyawan</h4>
                    </div>
                </div>    
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

                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-striped custom-table mb-0 datatable">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Karyawan</th>
                                        <th>Tgl. Pengajuan</th>
                                        <th>Tgl. Mulai Cuti</th>
                                        <th>Tgl. Selesai Cuti</th>
                                        <th>Jenis Cuti</th>
                                        <th>Alasan</th>
                                        <th>Status Cuti</th>
                                        <th class="text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 1;
                                    if ($result && $result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            $statusCuti = ucfirst($row['status_cuti']);
                                            // Badge class tetap menggunakan 'status-purple' sebagai basis
                                            $badge_class = 'status-purple'; 
                                    ?>
                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                                <td><?php echo htmlspecialchars(date('d-m-Y', strtotime($row['tanggal_pengajuan']))); ?></td>
                                                <td><?php echo htmlspecialchars(date('d-m-Y', strtotime($row['tanggal_mulai']))); ?></td>
                                                <td><?php echo htmlspecialchars(date('d-m-Y', strtotime($row['tanggal_selesai']))); ?></td>
                                                <td><?php echo htmlspecialchars($row['jenis_cuti']); ?></td>
                                                <td><?php echo nl2br(htmlspecialchars($row['alasan'])); ?></td>
                                                <td>
                                                    <div class="dropdown action-label">
                                                        <!-- Menggunakan class badge yang diminta -->
                                                        <a class="custom-badge <?php echo $badge_class; ?> dropdown-toggle" href="#" data-toggle="dropdown" aria-expanded="false">
                                                            <?php echo $statusCuti; ?>
                                                        </a>
                                                        <div class="dropdown-menu dropdown-menu-right">
                                                            <!-- Class diubah menjadi 'update-status' untuk konsistensi -->
                                                            <a class="dropdown-item update-status" href="#" data-id="<?php echo $row['cuti_id']; ?>" data-status="pending">Pending</a>
                                                            <a class="dropdown-item update-status" href="#" data-id="<?php echo $row['cuti_id']; ?>" data-status="disetujui">Disetujui</a>
                                                            <a class="dropdown-item update-status" href="#" data-id="<?php echo $row['cuti_id']; ?>" data-status="ditolak">Ditolak</a>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-right">
                                                    <div class="dropdown dropdown-action">
                                                        <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-ellipsis-v"></i></a>
                                                        <div class="dropdown-menu dropdown-menu-right">
                                                            <a class="dropdown-item" href="edit_izin.php?id=<?php echo $row['cuti_id']; ?>"><i class="fa fa-pencil m-r-5"></i> Edit</a>
                                                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#delete_izin_modal" data-id="<?php echo $row['cuti_id']; ?>"><i class="fa fa-trash-o m-r-5"></i> Delete</a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php
                                        }
                                    } else {
                                        echo '<tr><td colspan="9" class="text-center">Tidak ada data izin cuti ditemukan.</td></tr>';
                                    }
                                    if ($stmt) {
                                        $stmt->close();
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Delete -->
            <div id="delete_izin_modal" class="modal fade delete-modal" role="dialog">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-body text-center">
                            <img src="assets/img/sent.png" alt="" width="50" height="46">
                            <h3>Apakah Anda yakin ingin menghapus data izin cuti ini?</h3>
                            <div class="m-t-20">
                                <form action="hapus_izin.php" method="POST">
                                    <input type="hidden" name="cuti_id" id="delete_cuti_id_input" value="">
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
    <script src="assets/js/moment.min.js"></script>
    <script src="assets/js/bootstrap-datetimepicker.min.js"></script>
    <script src="assets/js/app.js"></script>
   
    <!-- === PERUBAHAN DI SINI: SCRIPT UNTUK UPDATE STATUS DAN MODAL DELETE === -->
    <script>
        $(document).ready(function () {
            // Inisialisasi datetimepicker
            $('.datetimepicker').datetimepicker({
                format: 'YYYY-MM-DD',
                locale: 'id'
            });

            // Script untuk modal delete
            $('#delete_izin_modal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var cutiId = button.data('id');
                $(this).find('#delete_cuti_id_input').val(cutiId);
            });

            // Script untuk update status cuti via AJAX tanpa konfirmasi
            // Menggunakan class '.update-status' yang sudah disesuaikan di HTML
            $('.update-status').on('click', function(e) {
                e.preventDefault(); // Mencegah link berpindah halaman
                
                var cutiId = $(this).data('id');
                var newStatus = $(this).data('status');
                
                $.ajax({
                    url: 'izin.php', // Mengirim permintaan ke halaman ini sendiri
                    method: 'POST',
                    data: {
                        action: 'update_status', // Parameter khusus untuk ditangkap PHP
                        cuti_id: cutiId,
                        status_cuti: newStatus
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Jika berhasil, muat ulang halaman untuk melihat perubahan
                            location.reload();
                        } else {
                            // Jika gagal, tampilkan pesan error dari server
                            alert('Gagal memperbarui status: ' + (response.message || 'Terjadi kesalahan.'));
                        }
                    },
                    error: function() {
                        // Jika terjadi error koneksi
                        alert('Terjadi kesalahan saat mengirim permintaan. Silakan coba lagi.');
                    }
                });
            });
        });
    </script>
</body>
</html>
