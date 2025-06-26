<?php
// koneksi.php sudah berisi koneksi $pdo dan memulai sesi
require 'koneksi.php';

// Jika sudah login, redirect
if (isset($_SESSION['log']) && $_SESSION['log'] == 'True') {
    header('location:index.php');
    exit;
}

$error_message = '';
$success_message = '';

if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = 'karyawan'; // Default role

    if (empty($username) || empty($password)) {
        $error_message = 'Username dan password tidak boleh kosong!';
    } else {
        try {
            $pdo->beginTransaction();

            // 1. Cek username
            $stmt_check = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
            $stmt_check->execute([$username]);

            if ($stmt_check->fetch()) {
                $error_message = 'Username sudah terdaftar!';
                $pdo->rollBack();
            } else {
                // 2. Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // 3. Insert ke tabel 'users'
                $stmt_user = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
                $stmt_user->execute([$username, $hashed_password, $role]);
                $new_user_id = $pdo->lastInsertId();

                // 4. Insert ke tabel 'karyawan', menghubungkan dengan user_id
                $stmt_karyawan = $pdo->prepare("INSERT INTO karyawan (user_id, nama_lengkap, status_kepegawaian) VALUES (?, ?, 'Kontrak')");
                $stmt_karyawan->execute([$new_user_id, $username]); // Nama lengkap diisi dgn username sebagai default

                $pdo->commit();
                $success_message = 'Registrasi berhasil! Akun dan profil karyawan telah dibuat. Silakan <a href="login.php" class="alert-link">login di sini</a>.';
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error_message = 'Registrasi gagal! Error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0" />
    <title>WEB KP - Registrasi Akun</title>
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="assets/css/font-awesome.min.css" />
    <link rel="stylesheet" type="text/css" href="assets/css/style.css" />
</head>
<body>
    <div class="main-wrapper account-wrapper">
        <div class="account-page">
            <div class="account-center">
                <div class="account-box">
                    <div class="account-logo">
                        <a href="index.php"><img src="assets/img/logo.png" alt="" /></a>
                    </div>

                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger text-center" role="alert"><?php echo $error_message; ?></div>
                    <?php endif; ?>
                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success text-center" role="alert"><?php echo $success_message; ?></div>
                    <?php endif; ?>

                    <form method="post" action="register.php">
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" autofocus class="form-control" name="username" required />
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" class="form-control" name="password" required />
                        </div>
                        <div class="form-group checkbox">
                            <label><input type="checkbox" required /> Saya menyetujui Syarat & Ketentuan</label>
                        </div>
                        <div class="form-group text-center">
                            <button class="btn btn-primary account-btn" type="submit" name="register">Daftar</button>
                        </div>
                        <div class="text-center login-link">
                            Sudah Punya Akun? <a href="login.php">Login</a>
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
