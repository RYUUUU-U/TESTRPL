<?php
require 'koneksi.php';
require 'cek.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>WEB KP - Tampilan QR Code Absensi</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <style>
        .qr-page-container { text-align: center; background-color: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); max-width: 500px; margin: 2rem auto; }
        .qr-page-container h1 { color: #333; margin-bottom: 25px; }
        #qr-code-container { width: 320px; height: 320px; margin: 0 auto; padding: 15px; border: 1px solid #ddd; border-radius: 8px; display: flex; justify-content: center; align-items: center; }
        #status-text { margin-top: 25px; color: #555; font-size: 1.2em; font-weight: 500; }
    </style>
</head>
<body>
    <div class="main-wrapper">
        <!-- Header dan Sidebar Anda di sini -->
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
                        <span class="user-img"><img class="rounded-circle" src="assets/img/user.jpg" width="24" alt="Admin"><span class="status online"></span></span>
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
                        <li class="submenu">
                            <a href="#"><i class="fa fa-edit"></i> <span> Menu Absensi </span> <span class="menu-arrow"></span></a>
                            <ul style="display: none;">
                                <li><a href="Absensi.php">Absensi</a></li>
                                <li><a class="active" href="absensi_qr.php">Tampilkan QR Absensi</a></li>
                                <li><a href="Koreksi.php">Koreksi Absensi</a></li>
                                <li><a href="izin.php">Izin Cuti</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="#"><i class="fa fa-money"></i> <span> Menu Laporan SPK </span> <span class="menu-arrow"></span></a>
                            <ul style="display: none;">
                                <li><a href="alternatif.php">Alternatif</a></li>
                                <li><a href="bobot.php">Bobot & Kriteria</a></li>
                                <li><a href="matrik.php">Data Klasifikasi</a></li>
                                <li><a href="percetakan_spk.php">Percetakan SPK</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- Akhir Header dan Sidebar -->

        <div class="page-wrapper">
            <div class="content">
                <div class="row">
                    <div class="col-sm-12"><h4 class="page-title">Tampilan QR Code Absensi</h4></div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="qr-page-container">
                            <h1>Silakan Scan QR Code di Bawah Ini</h1>
                            <div id="qr-code-container"><!-- QR Code akan dibuat di sini --></div>
                            <p id="status-text">Menunggu scan dari karyawan...</p>
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
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const qrCodeContainer = document.getElementById('qr-code-container');
            const statusText = document.getElementById('status-text');
            if (!qrCodeContainer || !statusText) { return; }

            let qrCode = null;
            let pollingInterval = null; // Variabel untuk menyimpan interval polling
            const basePath = '/TESTRPL/'; 

            async function generateNewQRCode() {
                // Hentikan polling lama jika sedang berjalan
                if (pollingInterval) {
                    clearInterval(pollingInterval);
                }

                try {
                    statusText.textContent = 'Membuat QR Code baru...';
                    const apiUrl = basePath + 'assets/api/generate_token.php';
                    const response = await fetch(apiUrl);
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    
                    const data = await response.json();

                    if (data.token) {
                        const attendanceUrl = window.location.origin + basePath + 'validasi_absen.php?token=' + data.token;
                        
                        qrCodeContainer.innerHTML = '';
                        qrCode = new QRCode(qrCodeContainer, {
                            text: attendanceUrl, width: 300, height: 300
                        });
                        statusText.textContent = 'Silakan Scan QR Code.';
                        
                        // Setelah QR baru dibuat, mulai polling untuk token ini
                        startPollingForTokenStatus(data.token);
                    } else {
                        statusText.textContent = data.message || 'Gagal mendapatkan token.';
                    }
                } catch (error) {
                    console.error('Error saat membuat QR:', error);
                    statusText.textContent = 'Koneksi ke server bermasalah.';
                }
            }

            function startPollingForTokenStatus(token) {
                // Hapus interval lama untuk memastikan hanya satu yang berjalan
                if (pollingInterval) {
                    clearInterval(pollingInterval);
                }

                // Set interval baru: tanyakan ke server setiap 3 detik
                pollingInterval = setInterval(async () => {
                    try {
                        const checkUrl = basePath + 'assets/api/check_token_status.php?token=' + token;
                        const response = await fetch(checkUrl);
                        const result = await response.json();

                        // ===== PERBAIKAN DI SINI =====
                        // Cek apakah token sudah digunakan ATAU sudah kedaluwarsa
                        if (result.status === 'digunakan' || result.status === 'kedaluwarsa') {
                            clearInterval(pollingInterval); // Hentikan polling

                            // Tampilkan pesan yang sesuai dengan kondisi
                            if (result.status === 'digunakan') {
                                statusText.textContent = 'Absensi berhasil! Memuat QR Code berikutnya...';
                            } else { // Jika 'kedaluwarsa'
                                statusText.textContent = 'QR Code kedaluwarsa! Memuat yang baru...';
                            }
                            
                            // Tunda sedikit sebelum membuat QR baru agar pesan terlihat
                            setTimeout(generateNewQRCode, 1500); 
                        }
                    } catch (error) {
                        console.error('Polling error:', error);
                        clearInterval(pollingInterval); // Hentikan polling jika terjadi error
                    }
                }, 3000); // 3000 milidetik = 3 detik
            }

            // Panggil fungsi ini sekali saat halaman pertama kali dimuat
            generateNewQRCode();
        });
    </script>
</body>
</html>
