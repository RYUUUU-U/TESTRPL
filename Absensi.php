<?php
require 'koneksi.php';
require 'cek.php';

// Filter berdasarkan tanggal jika ada
$filter_tanggal = "";
$tanggal_mulai = "";
$tanggal_akhir = "";
$filter_nama = ""; // Tambahkan variabel untuk filter nama
$nama_karyawan = ""; // Tambahkan variabel untuk menampung input nama karyawan

if (isset($_GET['tanggal_mulai']) && isset($_GET['tanggal_akhir'])) {
    $tanggal_mulai = $_GET['tanggal_mulai'];
    $tanggal_akhir = $_GET['tanggal_akhir'];

    if (!empty($tanggal_mulai) && !empty($tanggal_akhir)) {
        $filter_tanggal = "AND a.tanggal BETWEEN '$tanggal_mulai' AND '$tanggal_akhir'";
    }
}

// Filter berdasarkan nama karyawan jika ada
if (isset($_GET['nama_karyawan'])) {
    $nama_karyawan = $_GET['nama_karyawan'];
    if (!empty($nama_karyawan)) {
        // Gunakan LIKE untuk pencarian sebagian nama
        // Penting: Selalu gunakan mysqli_real_escape_string untuk mencegah SQL Injection
        $nama_karyawan_escaped = mysqli_real_escape_string($conn, $nama_karyawan);
        $filter_nama = "AND k.nama_lengkap LIKE '%$nama_karyawan_escaped%'";
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">

    <title>WEB KP - Absensi</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap-datetimepicker.min.css">

    <link rel="stylesheet" type="text/css"
        href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css" />

    <style>
        /* CSS untuk form-group-flex agar label di atas input (jika floating label tidak bekerja sempurna) */
        .form-group-flex {
            display: flex;
            flex-direction: column;
            margin-bottom: 15px;
        }

        .form-group-flex label {
            margin-bottom: 5px;
        }

        /* Memastikan tombol sejajar dengan input di atasnya */
        .filter-row .col-sm-6.col-md-3.d-flex.align-items-end {
            display: flex;
            align-items: flex-end;
            margin-bottom: 15px;
        }

        /* Memberi jarak antar tombol di kolom yang sama */
        .filter-row .col-sm-6.col-md-3.d-flex.align-items-end .btn:not(:last-child) {
            margin-right: 10px;
        }

        /* Style untuk menyesuaikan posisi "Show entries" DataTables */
        .dataTables_wrapper .row:first-child {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .dataTables_wrapper .dataTables_length {
            margin-bottom: 15px;
        }

        .dataTables_wrapper .dataTables_length label {
            margin-bottom: 0;
        }
        
        /* Menyesuaikan style untuk tombol export DataTables */
        div.dt-buttons {
            float: right;
            margin-bottom: 15px;
        }

        div.dt-buttons .btn {
            margin-left: 5px;
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
                            <a href="index.php"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a>
                        </li>
                        <li>
                            <a href="karyawan.php"><i class="fa fa-user-o"></i> <span>Karyawan</span></a>
                        </li>
                        <li>
                            <a href="karyawan_kontrak.php"><i class="fa fa-user-o"></i> <span>Karyawan Kontrak</span></a>
                        </li>
                        <li class="submenu">
                            <a href="#"><i class="fa fa-edit"></i> <span> Menu Absensi </span> <span
                                    class="menu-arrow"></span></a>
                            <ul style="display: none;">
                                <li><a class="active" href="Absensi.php">Absensi</a></li>
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
        <div class="page-wrapper">
            <div class="content">
                <div class="row">
                    <div class="col-sm-4 col-3">
                        <h4 class="page-title">Daftar Hadir Karyawan</h4>
                    </div>
                    <div class="col-sm-8 col-9 text-right m-b-20">
                        <a href="input_absensi.php" class="btn btn-primary float-right btn-rounded"><i
                                class="fa fa-plus"></i> Input Daftar Hadir Karyawan</a>
                    </div>
                </div>
                
                <div class="row filter-row">
                    <form action="" method="GET" class="w-100 d-flex flex-wrap align-items-end">
                        <div class="col-sm-6 col-md-3">
                            <div class="form-group form-focus">
                                <label class="focus-label">Nama Karyawan</label>
                                <input type="text" name="nama_karyawan" class="form-control floating" value="<?php echo htmlspecialchars($nama_karyawan); ?>" placeholder="Cari Nama Karyawan">
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="form-group form-focus">
                                <label class="focus-label">Tanggal Mulai</label>
                                <div class="cal-icon">
                                    <input class="form-control floating datetimepicker" type="text" name="tanggal_mulai" value="<?php echo htmlspecialchars($tanggal_mulai); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="form-group form-focus">
                                <label class="focus-label">Tanggal Akhir</label>
                                <div class="cal-icon">
                                    <input class="form-control floating datetimepicker" type="text" name="tanggal_akhir" value="<?php echo htmlspecialchars($tanggal_akhir); ?>">
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
                                        <th style="min-width:200px;">Nama Karyawan</th>
                                        <th>Tanggal</th>
                                        <th>Jam Masuk</th>
                                        <th>Jam Keluar</th>
                                        <th>Status</th>
                                        <th class="text-right no-export">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql_query = "SELECT a.*, k.nama_lengkap FROM absensi a
                                                    LEFT JOIN karyawan k ON a.karyawan_id = k.karyawan_id
                                                    WHERE 1=1 " . $filter_tanggal . " " . $filter_nama . "
                                                    ORDER BY a.tanggal DESC";
                                    $result = mysqli_query($conn, $sql_query);

                                    if (!$result) {
                                        die("Query Error: " . mysqli_error($conn));
                                    }

                                    $no = 1;
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        $display_nama_karyawan = isset($row['nama_lengkap']) && $row['nama_lengkap'] !== null ? htmlspecialchars($row['nama_lengkap']) : "Nama tidak ditemukan";
                                        ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo $display_nama_karyawan; ?></td>
                                            <td><?php
                                            $tanggal = $row['tanggal'];
                                            if ($tanggal && $tanggal != '0000-00-00') {
                                                echo date('Y-m-d', strtotime($tanggal));
                                            } else {
                                                echo "Tanggal tidak valid";
                                            }
                                            ?></td>
                                            <td><?php echo $row['jam_masuk']; ?></td>
                                            <td><?php echo $row['jam_keluar']; ?></td>
                                            <td><?php echo $row['status']; ?></td>
                                            <td class="text-right no-export">
                                                <div class="dropdown dropdown-action">
                                                    <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown"
                                                        aria-expanded="false"><i class="fa fa-ellipsis-v"></i></a>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <a class="dropdown-item"
                                                            href="edit_absensi.php?id=<?php echo $row['absensi_id']; ?>"><i
                                                                class="fa fa-pencil m-r-5"></i> Edit</a>
                                                        <a class="dropdown-item" href="#" data-toggle="modal"
                                                            data-target="#delete_absensi"
                                                            data-id="<?php echo $row['absensi_id']; ?>"><i
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

            <div id="delete_absensi" class="modal fade delete-modal" role="dialog">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-body text-center">
                            <img src="assets/img/sent.png" alt="" width="50" height="46">
                            <h3>Apakah Anda yakin ingin menghapus data absensi ini?</h3>
                            <div class="m-t-20">
                                <form action="hapus_absensi.php" method="POST">
                                    <input type="hidden" name="absensi_id" id="delete_id" value="">
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

    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script>
    $(document).ready(function () {
        // Inisialisasi datetimepicker
        $('.datetimepicker').datetimepicker({
            format: 'YYYY-MM-DD'
        });

        // Inisialisasi DataTables
        $('.datatable').DataTable({
            "destroy": true, 

            // PERUBAHAN: 'f' (filter/search) dihilangkan dari string dom untuk menyembunyikan kotak pencarian
            // Saya juga menambahkan class 'text-right' agar tombol tetap di kanan
            "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 text-right"B>><"row"<"col-sm-12"tr>><"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            
            "buttons": [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fa fa-file-excel-o"></i> Excel',
                    className: 'btn btn-success',
                    title: 'Laporan Absensi Karyawan',
                    exportOptions: { 
                        columns: ':not(.no-export)'
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fa fa-file-pdf-o"></i> PDF',
                    className: 'btn btn-danger',
                    orientation: 'portrait',
                    pageSize: 'A4',
                    title: 'Laporan Absensi Karyawan',
                    exportOptions: { 
                        columns: ':not(.no-export)'
                    },
                    customize: function (doc) {
                        doc.content[1].table.widths = ['auto', '*', 'auto', 'auto', 'auto', 'auto'];
                        doc.styles.tableHeader = { bold: true, fontSize: 10, fillColor: '#4CAF50', color: 'white', alignment: 'center' };
                        doc.styles.tableBody = { fontSize: 9 };
                        doc.defaultStyle.alignment = 'left';
                        doc.content[1].alignment = 'center';

                        var filterTitle = 'Laporan Absensi Karyawan';
                        var nama = "<?php echo addslashes(htmlspecialchars($nama_karyawan)); ?>";
                        var mulai = "<?php echo addslashes(htmlspecialchars($tanggal_mulai)); ?>";
                        var akhir = "<?php echo addslashes(htmlspecialchars($tanggal_akhir)); ?>";
                        var subtitle = [];
                        if (nama) subtitle.push('Nama: ' + nama);
                        if (mulai && akhir) subtitle.push('Periode: ' + mulai + ' s/d ' + akhir);

                        doc.content.splice(0, 1, {
                            text: filterTitle,
                            fontSize: 16,
                            bold: true,
                            alignment: 'center',
                            margin: [0, 0, 0, 8]
                        }, {
                            text: subtitle.join(' | '),
                            fontSize: 10,
                            alignment: 'center',
                            margin: [0, 0, 0, 12]
                        });
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="fa fa-print"></i> Print',
                    className: 'btn btn-info',
                    title: 'Laporan Absensi Karyawan',
                    exportOptions: { 
                        columns: ':not(.no-export)'
                    }
                }
            ]
        });

        // Event listener untuk modal delete
        $('#delete_absensi').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');
            $(this).find('#delete_id').val(id);
        });
    });
</script>
</body>

</html>