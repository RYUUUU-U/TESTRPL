<?php
// session_start() sudah ada di koneksi.php dan dicek,
// jadi tidak wajib di sini jika koneksi.php di-include pertama.
// Namun, jika ada penggunaan session sebelum include koneksi.php,
// maka session_start() di sini tetap penting.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include("koneksi.php"); // Meng-include koneksi.php yang mendefinisikan $conn

// Fungsi untuk mendapatkan nama bulan dalam Bahasa Indonesia
function getNamaBulanIndonesia($monthNumber) {
    $bulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    return $bulan[(int)$monthNumber] ?? 'Bulan Tidak Valid';
}

// Cek jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil kontrak_id dari form
    if (!isset($_POST['kontrak_id']) || empty($_POST['kontrak_id'])) {
        $_SESSION['pesan_error'] = "Karyawan kontrak harus dipilih.";
        header("Location: alternatif.php");
        exit();
    }
    $kontrak_id = $_POST['kontrak_id'];
    $periode_bulan_server = "";

    // Pastikan $conn sudah terdefinisi setelah include koneksi.php
    if (!isset($conn) || !$conn) { // $conn adalah variabel dari koneksi.php Anda
        $_SESSION['pesan_error'] = "Koneksi database tidak berhasil dibuat atau variabel koneksi tidak ditemukan.";
        header("Location: alternatif.php");
        exit();
    }

    try {
        // 1. Ambil tanggal_mulai dari tabel kontrak_karyawan berdasarkan kontrak_id
        // Menggunakan $conn dari koneksi.php
        $stmt_get_tanggal = $conn->prepare("SELECT tanggal_mulai FROM kontrak_karyawan WHERE kontrak_id = ?");
        if ($stmt_get_tanggal === false) {
            throw new Exception("Gagal mempersiapkan statement untuk mengambil tanggal mulai: " . $conn->error);
        }
        $stmt_get_tanggal->bind_param("i", $kontrak_id);
        $stmt_get_tanggal->execute();
        $result_tanggal = $stmt_get_tanggal->get_result();

        if ($result_tanggal->num_rows > 0) {
            $row_tanggal = $result_tanggal->fetch_assoc();
            $tanggal_mulai_str = $row_tanggal['tanggal_mulai'];

            $tanggal_mulai_dt = new DateTime($tanggal_mulai_str);
            $nama_bulan = getNamaBulanIndonesia($tanggal_mulai_dt->format('n'));
            $tahun = $tanggal_mulai_dt->format('Y');
            $periode_bulan_server = $nama_bulan . " " . $tahun;
        } else {
            throw new Exception("Data kontrak karyawan dengan ID " . htmlspecialchars($kontrak_id) . " tidak ditemukan.");
        }
        $stmt_get_tanggal->close();

        if (empty($periode_bulan_server) || strpos($periode_bulan_server, 'Bulan Tidak Valid') !== false) {
            throw new Exception("Gagal menghasilkan periode bulan yang valid dari tanggal mulai kontrak.");
        }

        // 3. Cek apakah alternatif sudah ada DENGAN PERIODE_BULAN DARI SERVER
        // Menggunakan $conn
        $stmt_check = $conn->prepare("SELECT id_alternative FROM saw_alternatives WHERE kontrak_id = ? AND periode_bulan = ?");
        if ($stmt_check === false) {
            throw new Exception("Gagal mempersiapkan statement pengecekan duplikat: " . $conn->error);
        }
        $stmt_check->bind_param("is", $kontrak_id, $periode_bulan_server);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $_SESSION['pesan_error'] = "Alternatif untuk karyawan dan periode (" . htmlspecialchars($periode_bulan_server) . ") tersebut sudah ada.";
            header("Location: alternatif.php");
            exit();
        }
        $stmt_check->close();

        // 4. Insert data alternatif baru DENGAN PERIODE_BULAN DARI SERVER
        // Menggunakan $conn
        $stmt_insert = $conn->prepare("INSERT INTO saw_alternatives (kontrak_id, periode_bulan) VALUES (?, ?)");
        if ($stmt_insert === false) {
            throw new Exception("Gagal mempersiapkan statement insert: " . $conn->error);
        }
        $stmt_insert->bind_param("is", $kontrak_id, $periode_bulan_server);

        if ($stmt_insert->execute()) {
            $_SESSION['pesan_sukses'] = "Data alternatif berhasil ditambahkan untuk periode: " . htmlspecialchars($periode_bulan_server) . ".";
        } else {
            throw new Exception("Gagal menyimpan data alternatif: " . $stmt_insert->error);
        }
        $stmt_insert->close();

    } catch (Exception $e) {
        $_SESSION['pesan_error'] = "Terjadi kesalahan: " . $e->getMessage();
    }

    // Menutup koneksi $conn jika sudah selesai digunakan oleh script ini.
    // Namun, jika ada proses lain setelah ini yang mungkin butuh $conn, penutupan bisa ditunda.
    // PHP akan otomatis menutup koneksi di akhir eksekusi script jika tidak ditutup manual.
    if (isset($conn)) {
        $conn->close();
    }

    header("Location: alternatif.php");
    exit();

} else {
    $_SESSION['pesan_error'] = "Metode pengiriman tidak valid.";
    header("Location: alternatif.php");
    exit();
}
?>