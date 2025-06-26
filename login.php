<?php
// koneksi.php sudah berisi session_start() dan koneksi $pdo
require 'koneksi.php';

// Jika pengguna sudah login, langsung arahkan ke halaman yang sesuai
if (isset($_SESSION['log']) && $_SESSION['log'] === 'True') {
    if ($_SESSION['role'] === 'admin') {
        header('location:index.php');
        exit;
    } else if ($_SESSION['role'] === 'karyawan') {
        header('location:index_karyawan.php');
        exit;
    }
}

$error_message = ''; // Variabel untuk menyimpan pesan error

// Proses form login saat tombol ditekan
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        // Query yang benar untuk mengambil data user dan karyawan terkait
        $sql = "SELECT users.*, karyawan.karyawan_id, karyawan.nama_lengkap 
                FROM users 
                LEFT JOIN karyawan ON users.user_id = karyawan.user_id
                WHERE users.username = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // Verifikasi apakah user ditemukan DAN password cocok menggunakan hashing
        if ($user && password_verify($password, $user['password'])) {
            
            // Set semua variabel sesi yang dibutuhkan aplikasi
            $_SESSION['log'] = 'True';
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['nama'] = $user['nama_lengkap'];
            
            // --- INI BAGIAN TERPENTING UNTUK MEMPERBAIKI ERROR 403 ---
            $_SESSION['karyawan_id'] = $user['karyawan_id']; 

            // Redirect berdasarkan role
            if ($user['role'] === 'admin') {
                header('location:index.php');
                exit;
            } else if ($user['role'] === 'karyawan') {
                header('location:index_karyawan.php');
                exit;
            }

        } else {
            // Jika username atau password salah
            $error_message = 'Username atau Password Salah!';
        }

    } catch (PDOException $e) {
        $error_message = "Error Database: " . $e->getMessage();
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
                        <a href="index.php"><img src="assets/img/logo.png" alt=""></a>
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
