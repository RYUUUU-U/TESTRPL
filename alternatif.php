<?php
require 'koneksi.php'; // Includes the database connection file
require 'cek.php'; // Includes session check or other authentication

// Ensure the 'db' variable is set from 'koneksi.php'
if (isset($conn)) {
    $db = $conn;
} else {
    // Handle error: $conn is not set.
    // For example, die("Koneksi database tidak ditemukan.");
    // This depends on how koneksi.php sets up the connection.
    // Assuming $conn is the mysqli connection object.
    die("Variabel koneksi database tidak terdefinisi. Pastikan koneksi.php mengaturnya dengan benar.");
}

// Fungsi untuk mendapatkan nama bulan dalam Bahasa Indonesia (jika diperlukan di halaman ini,
// namun lebih relevan di alternatif-simpan.php)
function getNamaBulanIndonesia($monthNumber) {
    $bulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    return $bulan[(int)$monthNumber];
}

// Menghapus logika simpan dari file ini. Logika ini seharusnya ada di alternatif-simpan.php
// if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_alternatif'])) { ... }

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>WEB KP - Alternatif</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <link rel="stylesheet" type="text/css" href="assets/css/select2.min.css">

    <style>
        /* CSS tambahan untuk DataTables */
        div.dataTables_wrapper div.dataTables_length { display: none; }
        div.dataTables_wrapper div.dataTables_filter { display: none; }
        div.dataTables_wrapper div.dataTables_info,
        div.dataTables_wrapper div.dataTables_paginate { margin-top: 1rem; }

        /* Style untuk tombol tambah agar lebih rapi dengan judul */
        .page-title-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px; /* Sesuaikan dengan m-b-20 pada tombol */
        }
        .page-title-container .page-title {
            margin-bottom: 0; /* Hapus margin bawah default dari h4 */
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
                        <li><a href="index.php"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a></li>
                        <li><a href="karyawan.php"><i class="fa fa-user-o"></i> <span>Karyawan</span></a></li>
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
                            <ul style="display: block;">
                                <li><a href="alternatif.php" class="active"> Alternatif</a></li>
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
                    <div class="col-sm-12 page-title-container">
                        <h4 class="page-title">Alternatif</h4>
                        <button type="button" class="btn btn-primary btn-rounded" data-toggle="modal" data-target="#add_alternatif_modal">
                            <i class="fa fa-plus"></i> Tambah Alternatif
                        </button>
                    </div>
                </div>

                <?php
                // Menampilkan pesan sukses atau error dari session jika ada (misalnya setelah redirect dari alternatif-simpan.php)
                if (isset($_SESSION['pesan_sukses'])) {
                    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">' . 
                         $_SESSION['pesan_sukses'] . 
                         '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' .
                         '</div>';
                    unset($_SESSION['pesan_sukses']);
                }
                if (isset($_SESSION['pesan_error'])) {
                    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . 
                         $_SESSION['pesan_error'] . 
                         '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' .
                         '</div>';
                    unset($_SESSION['pesan_error']);
                }
                ?>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Tabel Alternatif</h4>
                                <p class="card-text">Data-data mengenai kandidat karyawan kontrak yang akan dievaluasi direpresentasikan dalam tabel berikut:</p>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped custom-table mb-0" id="alternatifTable">
                                        <caption>Tabel Alternatif A<sub>i</sub></caption>
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Kode Alternatif</th>
                                                <th>Nama Karyawan</th>
                                                <th>Periode</th>
                                                <th>Status Kontrak</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sql = "SELECT 
                                                        sa.id_alternative,
                                                        sa.kontrak_id,
                                                        sa.periode_bulan,
                                                        kk.karyawan_id,
                                                        k.nama_lengkap,
                                                        kk.tanggal_mulai,
                                                        kk.tanggal_selesai,
                                                        kk.status_kontrak
                                                    FROM saw_alternatives sa 
                                                    JOIN kontrak_karyawan kk ON sa.kontrak_id = kk.kontrak_id
                                                    JOIN karyawan k ON kk.karyawan_id = k.karyawan_id
                                                    ORDER BY sa.id_alternative";
                                            $result = $db->query($sql);
                                            $i = 0;
                                            if ($result) { // Pastikan query berhasil
                                                while ($row = $result->fetch_object()) {
                                                    echo "<tr>
                                                            <td class='text-center'>" . (++$i) . "</td>
                                                            <td>A<sub>" . htmlspecialchars($row->id_alternative) . "}</sub></td>
                                                            <td>" . htmlspecialchars($row->nama_lengkap) . "</td>
                                                            <td>" . htmlspecialchars($row->periode_bulan) . "</td>
                                                            <td><span class='badge badge-" . ($row->status_kontrak == 'aktif' ? 'success' : ($row->status_kontrak == 'berakhir' ? 'warning' : 'danger')) . "'>" . ucfirst(htmlspecialchars($row->status_kontrak)) . "</span></td>
                                                            <td>
                                                                <div class='dropdown dropdown-action'>
                                                                    <a href='#' class='action-icon dropdown-toggle' data-toggle='dropdown' aria-expanded='false'><i class='fa fa-ellipsis-v'></i></a>
                                                                    <div class='dropdown-menu dropdown-menu-right'>
                                                                        <a class='dropdown-item' href='alternatif-edit.php?id=" . htmlspecialchars($row->id_alternative) . "'><i class='fa fa-pencil m-r-5'></i> Edit</a>
                                                                        <a class='dropdown-item' href='alternatif-hapus.php?id=" . htmlspecialchars($row->id_alternative) . "' onclick='return confirm(\"Yakin ingin menghapus data ini?\")'><i class='fa fa-trash-o m-r-5'></i> Hapus</a>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>\n";
                                                }
                                                if ($i === 0) {
                                                    echo "<tr><td colspan='6' class='text-center'>Belum ada data alternatif.</td></tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='6' class='text-center'>Gagal memuat data: " . htmlspecialchars($db->error) . "</td></tr>";
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
        </div>
    </div>

    <div id="add_alternatif_modal" class="modal fade custom-modal" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Alternatif Baru</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="alternatif-simpan.php" method="POST">
                        <div class="form-group">
                            <label>Pilih Karyawan Kontrak: <span class="text-danger">*</span></label>
                            <select class="select form-control" name="kontrak_id" id="kontrak_id_select" required style="width: 100%;">
                                <option value="">-- Pilih Karyawan Kontrak --</option>
                                <?php
                                // Ambil data karyawan kontrak yang belum ada di alternatif dan statusnya aktif
                                $sql_kontrak = "SELECT 
                                                    kk.kontrak_id,
                                                    k.nama_lengkap,
                                                    kk.tanggal_mulai,
                                                    kk.tanggal_selesai,
                                                    kk.status_kontrak
                                                FROM kontrak_karyawan kk 
                                                JOIN karyawan k ON kk.karyawan_id = k.karyawan_id
                                                WHERE kk.kontrak_id NOT IN (SELECT kontrak_id FROM saw_alternatives WHERE kontrak_id IS NOT NULL)
                                                AND kk.status_kontrak = 'aktif' 
                                                ORDER BY k.nama_lengkap";
                                $result_kontrak = $db->query($sql_kontrak);
                                if ($result_kontrak) { // Pastikan query berhasil
                                    while ($row_kontrak = $result_kontrak->fetch_object()) {
                                        // Simpan tanggal_mulai di data attribute untuk digunakan oleh JavaScript
                                        echo "<option value='" . htmlspecialchars($row_kontrak->kontrak_id) . "' data-tanggal-mulai='" . htmlspecialchars($row_kontrak->tanggal_mulai) . "'>" . htmlspecialchars($row_kontrak->nama_lengkap) . " (Kontrak: " . date("d M Y", strtotime($row_kontrak->tanggal_mulai)) . " - " . date("d M Y", strtotime($row_kontrak->tanggal_selesai)) . ")</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Periode Bulan: <span class="text-danger">*</span></label>
                            <input type="text" name="periode_bulan" id="periode_bulan_input" class="form-control" placeholder="Akan terisi otomatis" readonly required>
                        </div>
                        <div class="m-t-20 text-center">
                            <button type="button" class="btn btn-white" data-dismiss="modal">Batal</button>
                            <button type="submit" name="submit_alternatif" class="btn btn-primary submit-btn">Simpan</button>
                        </div>
                    </form>
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
    <script src="assets/js/app.js"></script>
    <script>
        $(document).ready(function () {
            if (!$.fn.DataTable.isDataTable('#alternatifTable')) {
                $('#alternatifTable').DataTable({
                    "paging": true,
                    "lengthChange": false,
                    "searching": true, // Diubah jadi true agar bisa search di tabel
                    "ordering": true,
                    "info": true,
                    "autoWidth": false,
                    // "dom": '<"row"<"col-sm-12"tr>><"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>', // default
                    "language": { // Opsional: Bahasa Indonesia untuk DataTables
                        "search": "Cari:",
                        "lengthMenu": "Tampilkan _MENU_ entri",
                        "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                        "infoEmpty": "Menampilkan 0 sampai 0 dari 0 entri",
                        "infoFiltered": "(disaring dari _MAX_ total entri)",
                        "zeroRecords": "Tidak ada data yang cocok",
                        "paginate": {
                            "first": "Awal",
                            "last": "Akhir",
                            "next": "Berikutnya",
                            "previous": "Sebelumnya"
                        }
                    },
                    "columnDefs": [
                        { "orderable": false, "targets": [5] } // Disable sorting on Action column (index 5)
                    ]
                });
            }

            // Initialize select2 for dropdowns (termasuk yang di dalam modal)
            // Pastikan ini dipanggil setelah modal HTML ada di DOM
            $('#kontrak_id_select').select2({
                dropdownParent: $('#add_alternatif_modal'), // Penting untuk select2 di dalam modal bootstrap
                width: '100%'
            });
            
            // Fungsi untuk mendapatkan nama bulan dalam bahasa Indonesia (versi JavaScript)
            function getNamaBulanIndonesiaJS(monthNumber) { // monthNumber is 0-indexed
                const bulan = [
                    "Januari", "Februari", "Maret", "April", "Mei", "Juni",
                    "Juli", "Agustus", "September", "Oktober", "November", "Desember"
                ];
                return bulan[monthNumber];
            }

            // Ketika pilihan karyawan kontrak berubah
            $('#kontrak_id_select').on('change', function() {
                var selectedOption = $(this).find('option:selected');
                var tanggalMulai = selectedOption.data('tanggal-mulai');
                var periodeBulanInput = $('#periode_bulan_input');

                if (tanggalMulai) {
                    try {
                        var dateObj = new Date(tanggalMulai);
                        if (isNaN(dateObj.getTime())) {
                             periodeBulanInput.val('Format tanggal kontrak tidak valid');
                             return;
                        }
                        var month = dateObj.getMonth(); // 0-11
                        var year = dateObj.getFullYear();
                        var namaBulan = getNamaBulanIndonesiaJS(month);
                        periodeBulanInput.val(namaBulan + " " + year);
                    } catch (e) {
                        console.error("Error parsing date: ", e, "Input date: ", tanggalMulai);
                        periodeBulanInput.val('Error format tanggal');
                    }
                } else {
                    periodeBulanInput.val(''); 
                }
            });

            // Jika ingin mengosongkan form & periode bulan saat modal ditutup
            $('#add_alternatif_modal').on('hidden.bs.modal', function () {
                $(this).find('form')[0].reset(); // Reset form fields
                $('#kontrak_id_select').val(null).trigger('change'); // Reset select2
                $('#periode_bulan_input').val(''); // Pastikan periode bulan juga kosong
            });
        });
    </script>
</body>
</html>