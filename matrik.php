<?php
require 'koneksi.php'; // Includes the database connection file
require 'cek.php';    // Includes a file for checking user session/authentication

if (isset($conn) && $conn instanceof mysqli) {
    $db = $conn;
} else {
    die("Koneksi database tidak valid atau variabel koneksi tidak ditemukan di koneksi.php.");
}

$jumlah_kriteria = 4; // Definisikan jumlah kriteria yang digunakan (C1-C4)
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>WEB KP - Matrik (C1-C4)</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
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
                                <li><a href="matrik.php" class="active"> Data Klasifikasi</a></li>
                                <li><a href="percetakan_spk.php"> Percetakan SPK </a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="page-wrapper">
            <div class="content">
                <div class="page-title-container">
                    <h4 class="page-title">Matrik Keputusan & Normalisasi (C1-C4)</h4>
                    
                </div>
                 <hr class="mt-0"/>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Deskripsi Perhitungan</h4>
                                <p class="card-text small">Melakukan perhitungan normalisasi untuk mendapatkan matriks nilai
                                    ternormalisasi (R), dengan ketentuan: Untuk normalisai nilai, jika faktor/atribut
                                    kriteria bertipe cost maka digunakan rumusan: R<sub>ij</sub> = ( min{X<sub>ij</sub>} / X<sub>ij</sub>) sedangkan jika
                                    faktor/atribut kriteria bertipe benefit maka digunakan rumusan: R<sub>ij</sub> = (
                                    X<sub>ij</sub> / max{X<sub>ij</sub>} )</p>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive mb-4">
                                    <h5 class="mb-3">Matrik Keputusan (X)</h5>
                                    <button type="button" class="btn btn-primary btn-rounded" data-toggle="modal"
                                            data-target="#add_nilai">
                                        <i class="fa fa-plus"></i> Isi Nilai Alternatif
                                    </button>
                                    <table class="table table-striped table-bordered custom-table display" id="matrikXTable" style="width:100%;">
                                        <thead>
                                            <tr>
                                                <th rowspan='2' class="text-center align-middle">Alternatif</th>
                                                <th colspan='<?php echo $jumlah_kriteria; ?>' class="text-center">Kriteria</th>
                                                <th rowspan='2' class="text-center align-middle">Aksi</th>
                                            </tr>
                                            <tr>
                                                <?php for ($i = 1; $i <= $jumlah_kriteria; $i++): ?>
                                                    <th class="text-center">C<?php echo $i; ?></th>
                                                <?php endfor; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sql_x_select_parts = [];
                                            for ($i = 1; $i <= $jumlah_kriteria; $i++) {
                                                $sql_x_select_parts[] = "SUM(IF(se_eval.id_criteria=$i, se_eval.value, 0)) AS C$i";
                                            }
                                            $sql_x_select_query = implode(", ", $sql_x_select_parts);

                                            $sql_x = "SELECT
                                                        se_eval.id_alternative,
                                                        k.nama_lengkap AS alternative_name,
                                                        $sql_x_select_query
                                                    FROM
                                                        saw_evaluations se_eval
                                                    JOIN saw_alternatives sa_alt ON se_eval.id_alternative = sa_alt.id_alternative
                                                    JOIN kontrak_karyawan kk ON sa_alt.kontrak_id = kk.kontrak_id
                                                    JOIN karyawan k ON kk.karyawan_id = k.karyawan_id
                                                    GROUP BY se_eval.id_alternative, k.nama_lengkap
                                                    ORDER BY se_eval.id_alternative";
                                            $result_x = $db->query($sql_x);

                                            $X_values_for_normalization = array(); 
                                            for ($crit_idx = 1; $crit_idx <= $jumlah_kriteria; $crit_idx++) {
                                                $X_values_for_normalization[$crit_idx] = array();
                                            }

                                            if (!$result_x) {
                                                echo "<tr><td colspan='" . ($jumlah_kriteria + 2) . "' class='text-center text-danger'>SQL Error Matrik X: " . htmlspecialchars($db->error) . "</td></tr>";
                                            } else {
                                                if ($result_x->num_rows > 0) {
                                                    while ($row_x = $result_x->fetch_object()) {
                                                        for ($i = 1; $i <= $jumlah_kriteria; $i++) {
                                                            $col_name = "C" . $i;
                                                            array_push($X_values_for_normalization[$i], (float)$row_x->$col_name);
                                                        }

                                                        echo "<tr>
                                                                <td class='text-left'><b>A<sub>" . htmlspecialchars($row_x->id_alternative) . "</sub></b> - " . htmlspecialchars($row_x->alternative_name) . "</td>";
                                                        for ($i = 1; $i <= $jumlah_kriteria; $i++) {
                                                            $col_name = "C" . $i;
                                                            echo "<td class='text-center'>" . round($row_x->$col_name, 3) . "</td>";
                                                        }
                                                        echo "<td class='text-center'>
                                                                    <a href='matrik-hapus-alternatif.php?id_alternative=" . htmlspecialchars($row_x->id_alternative) . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Yakin ingin menghapus semua nilai untuk alternatif ini?\")' title='Hapus semua nilai untuk alternatif ini'><i class='fa fa-trash-o'></i></a>
                                                                </td>
                                                            </tr>\n";
                                                    }
                                                } else {
                                                     echo "<tr><td colspan='" . ($jumlah_kriteria + 2) . "' class='text-center font-italic'>Belum ada data evaluasi untuk Matrik Keputusan (X).</td></tr>";
                                                }
                                                if(isset($result_x) && is_object($result_x)) $result_x->free();
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>

                                <hr class="mt-4 mb-4">
                                <div class="table-responsive">
                                    <h5 class="mb-3">Matrik Ternormalisasi (R)</h5>
                                    <table class="table table-striped table-bordered custom-table display" id="matrikRTable" style="width:100%;">
                                        <thead>
                                            <tr>
                                                <th rowspan='2' class="text-center align-middle">Alternatif</th>
                                                <th colspan='<?php echo $jumlah_kriteria; ?>' class="text-center">Kriteria</th>
                                            </tr>
                                            <tr>
                                                <?php for ($i = 1; $i <= $jumlah_kriteria; $i++): ?>
                                                    <th class="text-center">C<?php echo $i; ?></th>
                                                <?php endfor; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $criterias_attributes = array();
                                            $sql_criteria_attrs = "SELECT id_criteria, type FROM saw_criterias WHERE id_criteria <= $jumlah_kriteria ORDER BY id_criteria";
                                            $result_criteria_attrs = $db->query($sql_criteria_attrs);
                                            if ($result_criteria_attrs) {
                                                while ($attr_row = $result_criteria_attrs->fetch_object()) {
                                                    $criterias_attributes[$attr_row->id_criteria] = $attr_row->type;
                                                }
                                                $result_criteria_attrs->free();
                                            }

                                            $sql_r_base = "SELECT
                                                        se.id_alternative,
                                                        k.nama_lengkap AS alternative_name,
                                                        se.id_criteria,
                                                        se.value
                                                    FROM
                                                        saw_evaluations se
                                                    JOIN saw_alternatives sa ON se.id_alternative = sa.id_alternative
                                                    JOIN kontrak_karyawan kk ON sa.kontrak_id = kk.kontrak_id
                                                    JOIN karyawan k ON kk.karyawan_id = k.karyawan_id
                                                    WHERE se.id_criteria <= $jumlah_kriteria
                                                    ORDER BY se.id_alternative, se.id_criteria";
                                            $result_r_data = $db->query($sql_r_base);
                                            
                                            $evaluations_for_R = [];
                                            $alternative_names_for_R = [];
                                            if($result_r_data) {
                                                while($row_eval_r = $result_r_data->fetch_object()){
                                                    $evaluations_for_R[$row_eval_r->id_alternative][$row_eval_r->id_criteria] = (float)$row_eval_r->value;
                                                    if (!isset($alternative_names_for_R[$row_eval_r->id_alternative])) {
                                                        $alternative_names_for_R[$row_eval_r->id_alternative] = $row_eval_r->alternative_name;
                                                    }
                                                }
                                                $result_r_data->free();
                                            }

                                            if (!empty($evaluations_for_R)) {
                                                foreach ($evaluations_for_R as $id_alt => $criteria_values) {
                                                    echo "<tr><td class='text-left'><b>A<sub>" . htmlspecialchars($id_alt) . "</sub></b> - " . htmlspecialchars($alternative_names_for_R[$id_alt] ?? 'N/A') . "</td>";
                                                    
                                                    for ($j = 1; $j <= $jumlah_kriteria; $j++) { 
                                                        $current_value = $criteria_values[$j] ?? 0;
                                                        $attribute_type = $criterias_attributes[$j] ?? 'benefit';
                                                        
                                                        $min_val_for_norm = 0;
                                                        $max_val_for_norm = 0;

                                                        if (!empty($X_values_for_normalization[$j])) {
                                                            $min_val_for_norm = min($X_values_for_normalization[$j]);
                                                            $max_val_for_norm = max($X_values_for_normalization[$j]);
                                                        }
                                                        
                                                        $normalized_value = 0;
                                                        if ($attribute_type == 'cost') {
                                                            if ($current_value != 0) { 
                                                                $normalized_value = ($min_val_for_norm == 0 && $current_value == 0) ? 1 : $min_val_for_norm / $current_value;
                                                            } else if ($min_val_for_norm == 0) {
                                                                $normalized_value = 1;
                                                            } else { 
                                                                $normalized_value = 0; 
                                                            }
                                                        } else { // benefit
                                                            if ($max_val_for_norm != 0) { 
                                                                 $normalized_value = $current_value / $max_val_for_norm;
                                                            } else if ($current_value == 0) {
                                                                $normalized_value = 1; 
                                                            } else {
                                                                $normalized_value = 0; 
                                                            }
                                                        }
                                                        echo "<td class='text-center'>" . round($normalized_value, 3) . "</td>";
                                                    }
                                                    echo "</tr>\n";
                                                }
                                            } else {
                                                 echo "<tr><td colspan='" . ($jumlah_kriteria + 1) . "' class='text-center font-italic'>Belum ada data evaluasi untuk Matrik Ternormalisasi (R).</td></tr>";
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

    <div id="add_nilai" class="modal fade custom-modal" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Isi Nilai Kandidat (C1-C<?php echo $jumlah_kriteria; ?>)</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="matrik-simpan.php" method="POST">
                        <div class="form-group">
                            <label>Nama Alternatif (Karyawan): <span class="text-danger">*</span></label>
                            <select class="select form-control" name="id_alternative" id="modal_id_alternative" style="width:100%;">
                                <option value="">-- Pilih Alternatif --</option>
                                <?php
                                $sql_modal_alt = "SELECT sa.id_alternative, k.nama_lengkap 
                                              FROM saw_alternatives sa
                                              JOIN kontrak_karyawan kk ON sa.kontrak_id = kk.kontrak_id
                                              JOIN karyawan k ON kk.karyawan_id = k.karyawan_id
                                              ORDER BY k.nama_lengkap";
                                $result_modal_alt = $db->query($sql_modal_alt);
                                if ($result_modal_alt && $result_modal_alt->num_rows > 0) {
                                    while ($row_modal_alt = $result_modal_alt->fetch_object()) {
                                        echo '<option value="' . htmlspecialchars($row_modal_alt->id_alternative) . '">' . htmlspecialchars($row_modal_alt->nama_lengkap) . ' (A' . htmlspecialchars($row_modal_alt->id_alternative) . ')</option>';
                                    }
                                    $result_modal_alt->free();
                                } else {
                                    echo '<option value="">Tidak ada alternatif tersedia</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Kriteria: <span class="text-danger">*</span></label>
                            <select class="select form-control" name="id_criteria" id="modal_id_criteria" style="width:100%;">
                                 <option value="">-- Pilih Kriteria --</option>
                                <?php
                                $sql_modal_crit = "SELECT id_criteria, criteria_name, type FROM saw_criterias WHERE id_criteria <= $jumlah_kriteria ORDER BY id_criteria";
                                $result_modal_crit = $db->query($sql_modal_crit);
                                if ($result_modal_crit && $result_modal_crit->num_rows > 0) {
                                    while ($row_modal_crit = $result_modal_crit->fetch_object()) {
                                        echo '<option value="' . htmlspecialchars($row_modal_crit->id_criteria) . '">' . htmlspecialchars($row_modal_crit->criteria_name) . ' (C' . htmlspecialchars($row_modal_crit->id_criteria) . ' - ' . ucfirst(htmlspecialchars($row_modal_crit->type)) . ')</option>';
                                    }
                                    $result_modal_crit->free();
                                } else {
                                     echo '<option value="">Tidak ada kriteria tersedia</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Value: <span class="text-danger">*</span></label>
                            <input class="form-control" type="number" step="any" name="value" required placeholder="Masukkan nilai angka">
                        </div>
                        <div class="m-t-20 text-center">
                             <button type="button" class="btn btn-white" data-dismiss="modal">Batal</button>
                            <button type="submit" name="submit" class="btn btn-primary submit-btn">Simpan Nilai</button>
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
            var jumlahKriteria = <?php echo $jumlah_kriteria; ?>;
            var targetsTengahAngka = []; // Untuk kolom C1-C(jumlah_kriteria)
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
                    { "className": "text-left", "targets": [0] },              // Kolom Alternatif
                    { "className": "text-center", "targets": targetsTengahAngka }, // Kolom C1-C(jumlah_kriteria)
                    { "className": "text-center", "targets": [jumlahKriteria + 1], "orderable": false, "searchable": false } // Kolom Aksi
                ]
            };

            const settingsMatrikR = {
                ...commonDataTableSettings, 
                "columnDefs": [
                    { "className": "text-left", "targets": [0] },              // Kolom Alternatif
                    { "className": "text-center", "targets": targetsTengahAngka } // Kolom C1-C(jumlah_kriteria)
                ]
            };

            if ($.fn.DataTable.isDataTable('#matrikXTable')) {
                $('#matrikXTable').DataTable().destroy();
            }
            $('#matrikXTable').DataTable(settingsMatrikX);

            if ($.fn.DataTable.isDataTable('#matrikRTable')) {
                $('#matrikRTable').DataTable().destroy();
            }
            $('#matrikRTable').DataTable(settingsMatrikR);
            
            $('#add_nilai').on('hidden.bs.modal', function () {
                $(this).find('form')[0].reset();
                $('#modal_id_alternative').val(null).trigger('change');
                $('#modal_id_criteria').val(null).trigger('change');
            });
        });
    </script>
</body>
</html>