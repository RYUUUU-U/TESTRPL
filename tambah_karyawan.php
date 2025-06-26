<?php
// File: tambah_karyawan.php
include("koneksi.php"); // Pastikan $conn terdefinisi di sini
// Mulai session jika diperlukan untuk pesan flash atau redirect
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_POST['karyawan'])) {
    $user_id = $_POST['user_id'] ?? null;
    $nama_lengkap = $_POST['nama_lengkap'] ?? null;
    $departemen = $_POST['departemen'] ?? null;
    $jabatan = $_POST['jabatan'] ?? null;
    $no_hp = $_POST['no_hp'] ?? null;
    $status_kepegawaian = $_POST['status_kepegawaian'] ?? null;

    // Validasi dasar (opsional, tapi baik untuk dilakukan)
    if (empty($user_id) || empty($nama_lengkap) || empty($jabatan) || empty($departemen) || empty($no_hp) || empty($status_kepegawaian)) {
        // Anda bisa menyimpan pesan error di session dan redirect kembali ke form
        $_SESSION['error_message'] = "Semua field wajib diisi.";
        header('Location: halaman_form_input_karyawan.php'); // Ganti dengan nama file form Anda
        exit;
    }

    // 1. Cek apakah user_id sudah terdaftar sebagai karyawan
    $stmt_check = $conn->prepare("SELECT karyawan_id FROM karyawan WHERE user_id = ?");
    if ($stmt_check) {
        $stmt_check->bind_param("i", $user_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            // User ID sudah ada, berikan pesan error
            // Simpan pesan error di session dan redirect kembali ke form input
            // atau tampilkan pesan error di halaman ini sebelum menghentikan skrip.
            // Contoh sederhana:
            echo "Gagal: User ID " . htmlspecialchars($user_id) . " sudah terdaftar sebagai karyawan.";
            // Jika ingin redirect dengan pesan:
            // $_SESSION['error_message'] = "User ID tersebut sudah terdaftar sebagai karyawan.";
            // header('Location: halaman_form_input_karyawan.php'); // Ganti dengan nama file form Anda
            // exit;
        } else {
            // 2. Jika belum ada, lakukan INSERT menggunakan prepared statement
            $query_insert = "INSERT INTO karyawan (user_id, nama_lengkap, departemen, jabatan, no_hp, status_kepegawaian) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($query_insert);

            if ($stmt_insert) {
                // 'isssss' -> i untuk integer (user_id), s untuk string (sisanya)
                $stmt_insert->bind_param("isssss", $user_id, $nama_lengkap, $departemen, $jabatan, $no_hp, $status_kepegawaian);

                if ($stmt_insert->execute()) {
                    // $_SESSION['success_message'] = "Data karyawan berhasil ditambahkan."; // Pesan sukses via session
                    header('Location: karyawan.php'); // Redirect ke halaman daftar karyawan
                    exit;
                } else {
                    echo "Gagal menambahkan data karyawan: " . $stmt_insert->error;
                }
                $stmt_insert->close();
            } else {
                echo "Gagal mempersiapkan statement insert: " . $conn->error;
            }
        }
        $stmt_check->close();
    } else {
        echo "Gagal mempersiapkan statement pengecekan: " . $conn->error;
    }
    $conn->close(); // Tutup koneksi jika sudah tidak digunakan lagi di skrip ini
} else {
    // Jika tidak ada data POST 'karyawan', redirect ke halaman form atau tampilkan pesan
    // header('Location: halaman_form_input_karyawan.php');
    // exit;
    echo "Akses tidak sah atau tidak ada data yang dikirim.";
}
?>