<?php
// matrik-hapus-alternatif.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require 'koneksi.php'; // Pastikan path ini benar dan $conn terdefinisi di sana
require 'cek.php';     // Untuk pengecekan sesi atau otentikasi lainnya

// Pastikan koneksi berhasil dan $conn (atau $db) terdefinisi
if (isset($conn) && $conn instanceof mysqli) {
    $db_connection = $conn; // Menggunakan $conn dari koneksi.php
} elseif (isset($db) && $db instanceof mysqli) {
    $db_connection = $db; // Fallback jika $db yang digunakan di matrik.php
} else {
    $_SESSION['pesan_error'] = "Koneksi database tidak valid atau variabel koneksi tidak ditemukan.";
    header("Location: matrik.php");
    exit();
}

// Cek apakah id_alternative ada di URL
if (isset($_GET['id_alternative']) && !empty($_GET['id_alternative'])) {
    $id_alternative = (int)$_GET['id_alternative'];

    // Sebaiknya tambahkan validasi apakah id_alternative ini benar-benar ada di tabel saw_alternatives
    // sebelum melakukan penghapusan, untuk keamanan tambahan (opsional tapi direkomendasikan).

    try {
        // Mulai transaksi jika Anda ingin memastikan semua query berhasil atau tidak sama sekali
        // $db_connection->begin_transaction(); // Opsional

        // Query untuk menghapus semua entri di saw_evaluations untuk id_alternative tertentu
        $stmt_delete = $db_connection->prepare("DELETE FROM saw_evaluations WHERE id_alternative = ?");

        if ($stmt_delete === false) {
            throw new Exception("Gagal mempersiapkan statement hapus: " . $db_connection->error);
        }

        $stmt_delete->bind_param("i", $id_alternative);

        if ($stmt_delete->execute()) {
            $affected_rows = $stmt_delete->affected_rows;
            if ($affected_rows > 0) {
                $_SESSION['pesan_sukses'] = "Semua nilai evaluasi untuk alternatif A<sub>{$id_alternative}</sub> berhasil dihapus ({$affected_rows} data kriteria dihapus).";
            } else {
                $_SESSION['pesan_info'] = "Tidak ada data evaluasi yang ditemukan atau sudah terhapus untuk alternatif A<sub>{$id_alternative}</sub>.";
            }
            // $db_connection->commit(); // Opsional, jika menggunakan transaksi
        } else {
            // $db_connection->rollback(); // Opsional, jika menggunakan transaksi
            throw new Exception("Gagal menghapus nilai evaluasi: " . $stmt_delete->error);
        }
        $stmt_delete->close();

    } catch (Exception $e) {
        // $db_connection->rollback(); // Opsional, jika menggunakan transaksi
        $_SESSION['pesan_error'] = "Terjadi kesalahan: " . $e->getMessage();
    } catch (mysqli_sql_exception $e_sql) {
        // $db_connection->rollback(); // Opsional, jika menggunakan transaksi
        $_SESSION['pesan_error'] = "Terjadi kesalahan database saat menghapus: " . $e_sql->getMessage();
    }

    // Tutup koneksi jika sudah tidak digunakan lagi oleh skrip ini
    // (meskipun PHP akan menutupnya di akhir, ini praktik yang baik)
    // if (isset($db_connection)) {
    // $db_connection->close();
    // }
    // Lebih baik koneksi ditutup oleh skrip utama atau di akhir jika tidak ada lagi proses.
    // Untuk skrip yang hanya melakukan satu aksi dan redirect, penutupan di sini bisa dilakukan.

} else {
    $_SESSION['pesan_error'] = "ID Alternatif tidak valid atau tidak ditemukan untuk dihapus.";
}

// Redirect kembali ke halaman matrik.php
header("Location: matrik.php");
exit();
?>