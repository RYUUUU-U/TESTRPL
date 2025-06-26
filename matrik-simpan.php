<?php
// matrik-simpan.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include("koneksi.php"); // Meng-include koneksi.php yang mendefinisikan $conn

// Pastikan koneksi berhasil dan $conn terdefinisi
if (!isset($conn) || !$conn instanceof mysqli) {
    $_SESSION['pesan_error'] = "Koneksi database gagal atau variabel koneksi tidak valid.";
    header("Location: matrik.php");
    exit();
}

// Cek jika form disubmit dengan metode POST dan tombol submit ditekan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {

    // Validasi input dasar
    if (!isset($_POST['id_alternative']) || empty($_POST['id_alternative'])) {
        $_SESSION['pesan_error'] = "Alternatif harus dipilih.";
        header("Location: matrik.php");
        exit();
    }
    if (!isset($_POST['id_criteria']) || empty($_POST['id_criteria'])) {
        $_SESSION['pesan_error'] = "Kriteria harus dipilih.";
        header("Location: matrik.php");
        exit();
    }
    if (!isset($_POST['value']) || $_POST['value'] === '') { // Memperbolehkan nilai 0, tapi tidak boleh kosong
        $_SESSION['pesan_error'] = "Nilai (Value) harus diisi.";
        header("Location: matrik.php");
        exit();
    }
    if (!is_numeric($_POST['value'])) {
        $_SESSION['pesan_error'] = "Nilai (Value) harus berupa angka.";
        header("Location: matrik.php");
        exit();
    }

    $id_alternative = (int)$_POST['id_alternative'];
    $id_criteria = (int)$_POST['id_criteria'];
    $value = (float)$_POST['value'];

    try {
        // Cek apakah data sudah ada untuk id_alternative dan id_criteria ini
        $stmt_check = $conn->prepare("SELECT id_evaluation FROM saw_evaluations WHERE id_alternative = ? AND id_criteria = ?");
        if ($stmt_check === false) {
            throw new Exception("Gagal mempersiapkan statement pengecekan: " . $conn->error);
        }
        $stmt_check->bind_param("ii", $id_alternative, $id_criteria);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        $existing_evaluation = $result_check->fetch_object();
        $stmt_check->close();

        if ($existing_evaluation) {
            // Data sudah ada, lakukan UPDATE
            $id_evaluation = $existing_evaluation->id_evaluation;
            $stmt_update = $conn->prepare("UPDATE saw_evaluations SET value = ? WHERE id_evaluation = ?");
            if ($stmt_update === false) {
                throw new Exception("Gagal mempersiapkan statement update: " . $conn->error);
            }
            $stmt_update->bind_param("di", $value, $id_evaluation); // 'd' for double/float, 'i' for integer

            if ($stmt_update->execute()) {
                $_SESSION['pesan_sukses'] = "Nilai evaluasi berhasil diperbarui.";
            } else {
                throw new Exception("Gagal memperbarui nilai evaluasi: " . $stmt_update->error);
            }
            $stmt_update->close();
        } else {
            // Data belum ada, lakukan INSERT
            $stmt_insert = $conn->prepare("INSERT INTO saw_evaluations (id_alternative, id_criteria, value) VALUES (?, ?, ?)");
            if ($stmt_insert === false) {
                throw new Exception("Gagal mempersiapkan statement insert: " . $conn->error);
            }
            $stmt_insert->bind_param("iid", $id_alternative, $id_criteria, $value); // 'i' integer, 'i' integer, 'd' double/float

            if ($stmt_insert->execute()) {
                $_SESSION['pesan_sukses'] = "Nilai evaluasi berhasil disimpan.";
            } else {
                throw new Exception("Gagal menyimpan nilai evaluasi: " . $stmt_insert->error);
            }
            $stmt_insert->close();
        }

    } catch (Exception $e) {
        $_SESSION['pesan_error'] = "Terjadi kesalahan: " . $e->getMessage();
    } catch (mysqli_sql_exception $e_sql) { // Menangkap error spesifik dari mysqli
        $_SESSION['pesan_error'] = "Terjadi kesalahan database: " . $e_sql->getMessage();
    }


    // Menutup koneksi $conn jika sudah selesai digunakan oleh script ini.
    if (isset($conn)) {
        $conn->close();
    }

    header("Location: matrik.php"); // Redirect kembali ke halaman matrik
    exit();

} else {
    // Jika bukan metode POST atau tombol submit tidak ditekan
    $_SESSION['pesan_error'] = "Akses tidak sah atau metode pengiriman tidak valid.";
    header("Location: matrik.php");
    exit();
}
?>