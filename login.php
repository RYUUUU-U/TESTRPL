<?php
// koneksi.php diasumsikan sudah berisi session_start() dan koneksi $pdo atau $conn
// Untuk konsistensi dengan file lain, kita gunakan require.
require 'koneksi.php';

// Jika pengguna sudah login, langsung arahkan ke halaman yang sesuai
if (isset($_SESSION['log']) && $_SESSION['log'] === 'True') {
    if (isset($_SESSION['role'])) {
        if ($_SESSION['role'] === 'admin') {
            header('location:index.php');
            exit;
        } elseif ($_SESSION['role'] === 'headoffice') {
            header('location:index_headoffice.php'); // Arahkan headoffice ke dashboard-nya
            exit;
        } elseif ($_SESSION['role'] === 'karyawan') {
            header('location:index_karyawan.php');
            exit;
        }
    }
}

$error_message = ''; // Variabel untuk menyimpan pesan error

// Proses form login saat tombol ditekan
if (isset($_POST['login'])) {
    // Diasumsikan koneksi menggunakan mysqli ($conn) seperti file lain di repo
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    
    // Query untuk mengambil data user. Diasumsikan tidak ada join dengan karyawan di sini.
    $query = "SELECT * FROM users WHERE username = '$username'";
    $cek_user = mysqli_query($conn, $query);

    if (mysqli_num_rows($cek_user) > 0) {
        $user = mysqli_fetch_assoc($cek_user);

        // Verifikasi apakah user ditemukan DAN password cocok menggunakan hashing
        // Ganti password_verify dengan perbandingan biasa jika password tidak di-hash
        if ($user && password_verify($password, $user['password'])) {
            
            // Set semua variabel sesi yang dibutuhkan aplikasi
            $_SESSION['log'] = 'True';
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['user_id'] = $user['id_user']; // Sesuaikan nama kolom jika berbeda
            $_SESSION['nama'] = $user['nama']; // Sesuaikan nama kolom jika berbeda
            
            // =======================================================
            // == LOGIKA PENGALIHAN BERDASARKAN ROLE MASING-MASING ==
            // =======================================================
            if ($user['role'] === 'admin') {
                header('location:index.php');
                exit;
            } elseif ($user['role'] === 'headoffice') {
                header('location:index_headoffice.php');
                exit;
            } elseif ($user['role'] === 'karyawan') {
                header('location:index_karyawan.php');
                exit;
            } else {
                // Handle jika ada role lain yang tidak terdefinisi
                $error_message = 'Role pengguna tidak dikenali!';
            }

        } else {
            // Jika username atau password salah
            $error_message = 'Username atau Password Salah!';
        }
    } else {
        $error_message = 'Username atau Password Salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>WEB KP - Login</title>
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
</head>

<body>
    <div class="main-wrapper account-wrapper">
        <div class="account-page">
            <div class="account-center">
                <div class="account-box">
                    <div class="account-logo">
                        <a href="#"><img src="assets/img/logo.png" alt=""></a>
                    </div>
                    
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger text-center" role="alert">
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="login.php">
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" autofocus class="form-control" name="username" required>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-primary account-btn" name="login">Login</button>
                        </div>
                        <div class="text-center register-link">
                            Belum Punya Akun? <a href="register.php">Register Sekarang</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/jquery-3.2.1.min.js"></script>
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>