<?php
require 'koneksi.php';
require 'cek.php';
require 'tambah_kontrak.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">

    <title>WEB KP - Kontrak Karyawan</title>
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
            </div><a id="toggle_btn" href="javascript:void(0);"><i class="fa fa-bars"></i></a>
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
                            <div class="profile-section" style="text-align: center;">
                            </div>
                            <a href="index.php"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a>
                        </li>
                        <li>
                            <a href="karyawan.php"><i class="fa fa-user-o"></i> <span>Karyawan</span></a>
                        </li>
                        <li class="active">
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
                            <ul style="display: none;">
                                <li><a href="alternatif.php"> Alternatif</a></li>
                                <li><a href="bobot.php"> Bobot & Kriteria </a></li>
                                <li><a href="matrik.php"> Data Klasifikasi</a></li>
                                <li><a href="percetakan_spk.php"> Percetakan SPK </a></li>
                            </ul>
                        </li>
                        </li>
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
                        <h4 class="page-title">Forms / <?= $editMode ? 'Edit' : 'Input' ?> Kontrak Karyawan</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card-box">
                            <h4 class="card-title"><?= $editMode ? 'Edit' : 'Input' ?> Data Kontrak Karyawan</h4>
                            <form method="post">
                                <input type="hidden" name="id_kontrak" value="<?= $id_kontrak ?>">
                                <div class="form-group row">
                                    <label class="col-form-label col-md-2">Nama Karyawan</label>
                                    <div class="col-md-10">
                                        <select class="form-control" name="karyawan_id" required>
                                            <option value="">-- Pilih Karyawan --</option>
                                            <?php
                                            $karyawan = mysqli_query($conn, "SELECT * FROM karyawan");
                                            while ($row = mysqli_fetch_assoc($karyawan)) {
                                                $selected = $row['karyawan_id'] == $karyawan_id ? 'selected' : '';
                                                echo "<option value='{$row['karyawan_id']}' $selected>{$row['nama_lengkap']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-form-label col-md-2">Tanggal Mulai</label>
                                    <div class="col-md-10">
                                        <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control"
                                            required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-form-label col-md-2">Tanggal Selesai</label>
                                    <div class="col-md-10">
                                        <input type="date" name="tanggal_selesai" id="tanggal_selesai"
                                            class="form-control" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-form-label col-md-2">Status Kontrak</label>
                                    <div class="col-md-10">
                                        <select name="status_kontrak" class="form-control" required>
                                            <option value="aktif" <?= $status_kontrak == "aktif" ? 'selected' : '' ?>>Aktif
                                            </option>
                                            <option value="berakhir" <?= $status_kontrak == "berakhir" ? 'selected' : '' ?>>Berakhir</option>
                                            <option value="diperpanjang" <?= $status_kontrak == "diperpanjang" ? 'selected' : '' ?>>Diperpanjang</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Info status kontrak saat ini untuk referensi -->
                                <?php if ($editMode): ?>
                                    <div class="alert alert-info">
                                        <small>Status kontrak saat ini: '<?= htmlspecialchars($status_kontrak) ?>'</small>
                                    </div>
                                <?php endif; ?>

                                <div class="text-right">
                                    <button type="submit" name="simpan_kontrak" class="btn btn-primary">Submit</button>
                                    <?php if ($editMode): ?>
                                        <a href="karyawan_kontrak.php" class="btn btn-secondary">Batal</a>
                                    <?php endif; ?>
                                </div>
                            </form>
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
    <script src="assets/js/app.js"></script>

    <script>
        document.querySelector("form").addEventListener("submit", function (e) {
            const tglMulai = document.getElementById("tanggal_mulai").value;
            const tglSelesai = document.getElementById("tanggal_selesai").value;

            if (tglSelesai < tglMulai) {
                alert("Tanggal selesai tidak boleh lebih awal dari tanggal mulai.");
                e.preventDefault();
            }
        });
    </script>
</body>

</html>