<?php
require 'koneksi.php';
require 'cek.php';
require "W.php"; // Memuat array $W (bobot)
require "R.php"; // Memuat array $R (matriks ternormalisasi)

// Pastikan $jumlah_kriteria terdefinisi untuk digunakan di JavaScript
// Biasanya ini adalah jumlah bobot atau jumlah kolom kriteria di matriks R
if (isset($W) && is_array($W)) {
    $jumlah_kriteria = count($W);
} elseif (isset($R[1]) && is_array($R[1])) { // Jika $W tidak ada, coba hitung dari kolom $R (asumsi $R[1] ada)
    $jumlah_kriteria = count($R[1]);
} else {
    $jumlah_kriteria = 0; // Default jika tidak bisa ditentukan
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>WEB KP - Matrik (C1-C4) & Preferensi</title> <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <link rel="stylesheet" type="text/css" href="assets/css/select2.min.css">

    <style>
        div.dataTables_wrapper div.dataTables_info,
        div.dataTables_wrapper div.dataTables_paginate {
            margin-top: 1rem;
        }
        .table th, .table td {
            vertical-align: middle !important;
        }
        .page-title-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .page-title-container .page-title {
            margin-bottom: 0;
        }
        /* Styling untuk page-wrapper content jika diperlukan */
        .page-wrapper {
            padding: 20px; /* Contoh padding */
        }
        .page-heading h3 {
            margin-bottom: 1.5rem; /* Jarak bawah untuk judul */
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
                                <li><a href="alternatif.php"> Alternatif</a></li>
                                <li><a href="bobot.php"> Bobot & Kriteria </a></li>
                                <li><a href="matrik.php" > Data Klasifikasi</a></li>
                                <li><a href="percetakan_spk.php" class="active"> Percetakan SPK </a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="page-wrapper">
            <div class="page-heading">
              <h3>Nilai Preferensi (P)</h3>
            </div>
            <div class="page-content">
              <section class="row">
                <div class="col-12">
                  <div class="card">
                    <div class="card-header">
                      <h4 class="card-title">Tabel Nilai Preferensi (P)</h4>
                    </div>
                    <div class="card-content">
                      <div class="card-body">
                        <p class="card-text">
                          Nilai preferensi (P) merupakan penjumlahan dari perkalian matriks ternormalisasi R dengan vektor bobot W.
                        </p>
                      </div>
                      <div class="table-responsive p-3"> <table class="table table-striped table-bordered mb-0" id="preferensiPTable"> <thead> <tr>
                              <th>No</th>
                              <th>Alternatif</th>
                              <th>Hasil</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php
                            $P = array();
                            if (isset($R) && isset($W) && is_array($R) && is_array($W)) {
                                $m = count($W); // Jumlah kriteria
                                $no = 0;
                                foreach ($R as $i => $r_values) {
                                    if (!is_array($r_values)) continue; // Lewati jika $r_values bukan array
                                    $P[$i] = 0; // Inisialisasi P[i]
                                    for ($j = 0; $j < $m; $j++) {
                                        // Pastikan $r_values[$j] dan $W[$j] ada
                                        if (isset($r_values[$j]) && isset($W[$j])) {
                                            $P[$i] += $r_values[$j] * $W[$j];
                                        }
                                    }
                                    echo "<tr class='center'>
                                            <td>" . (++$no) . "</td>
                                            <td>A{$i}</td>
                                            <td>" . round($P[$i], 4) . "</td> </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='3' class='text-center'>Data R atau W tidak tersedia.</td></tr>";
                            }
                            ?>
                          </tbody>
                          <tfoot> <tr>
                                <td colspan="3">Nilai Preferensi (P)</td>
                            </tr>
                          </tfoot>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>
              </section>
            </div>
        </div>
        </div> <div class="sidebar-overlay" data-reff=""></div>

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
            var jumlahKriteria = <?php echo $jumlah_kriteria; ?>;
            var targetsTengahAngka = []; // Untuk kolom C1-C(jumlah_kriteria) pada matrikX dan matrikR
            for (var i = 1; i <= jumlahKriteria; i++) {
                targetsTengahAngka.push(i);
            }

            $('#modal_id_alternative').select2({
                dropdownParent: $('#add_nilai'),
                placeholder: "-- Pilih Alternatif --",
                allowClear: true,
                width: '100%'
            });
            $('#modal_id_criteria').select2({
                dropdownParent: $('#add_nilai'),
                placeholder: "-- Pilih Kriteria --",
                allowClear: true,
                width: '100%'
            });

            const commonDataTableSettings = {
                "paging": true,
                "lengthChange": false,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true,
                "language": {
                    "search": "Cari:",
                    "zeroRecords": "Tidak ada data yang cocok ditemukan",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                    "infoEmpty": "Tidak ada entri tersedia",
                    "infoFiltered": "(disaring dari _MAX_ total entri)",
                    "paginate": {
                        "first": "Awal",
                        "last": "Akhir",
                        "next": "Berikutnya",
                        "previous": "Sebelumnya"
                    }
                }
            };

            const settingsMatrikX = {
                ...commonDataTableSettings,
                "columnDefs": [
                    { "className": "text-left", "targets": [0] },    // Kolom Alternatif
                    { "className": "text-center", "targets": targetsTengahAngka }, // Kolom C1-C(jumlah_kriteria)
                    { "className": "text-center", "targets": [jumlahKriteria + 1], "orderable": false, "searchable": false } // Kolom Aksi
                ]
            };

            const settingsMatrikR = {
                ...commonDataTableSettings,
                "columnDefs": [
                    { "className": "text-left", "targets": [0] },    // Kolom Alternatif
                    { "className": "text-center", "targets": targetsTengahAngka } // Kolom C1-C(jumlah_kriteria)
                ]
            };

            // Pengaturan DataTable untuk Tabel Nilai Preferensi (P)
            const settingsPreferensiP = {
                ...commonDataTableSettings,
                "columnDefs": [
                    { "className": "text-center", "targets": [0] }, // Kolom No
                    { "className": "text-left", "targets": [1] },   // Kolom Alternatif
                    { "className": "text-center", "targets": [2] }  // Kolom Hasil
                ]
            };

            // Inisialisasi DataTable untuk tabel yang mungkin sudah ada (Matrik X dan Matrik R)
            // Pastikan tabel dengan ID #matrikXTable dan #matrikRTable ada di HTML jika Anda menggunakannya
            if ($('#matrikXTable').length) { // Cek apakah tabel ada sebelum inisialisasi
                if ($.fn.DataTable.isDataTable('#matrikXTable')) {
                    $('#matrikXTable').DataTable().destroy();
                }
                $('#matrikXTable').DataTable(settingsMatrikX);
            }

            if ($('#matrikRTable').length) { // Cek apakah tabel ada sebelum inisialisasi
                 if ($.fn.DataTable.isDataTable('#matrikRTable')) {
                    $('#matrikRTable').DataTable().destroy();
                }
                $('#matrikRTable').DataTable(settingsMatrikR);
            }

            // Inisialisasi DataTable untuk Tabel Nilai Preferensi (P)
            if ($('#preferensiPTable').length) { // Cek apakah tabel ada
                if ($.fn.DataTable.isDataTable('#preferensiPTable')) {
                    $('#preferensiPTable').DataTable().destroy();
                }
                $('#preferensiPTable').DataTable(settingsPreferensiP);
            }

            $('#add_nilai').on('hidden.bs.modal', function () {
                $(this).find('form')[0].reset();
                $('#modal_id_alternative').val(null).trigger('change');
                $('#modal_id_criteria').val(null).trigger('change');
            });
        });
    </script>
</body>
</html>