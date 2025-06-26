<?php
// File: W.php
// Diasumsikan koneksi.php (yang mendefinisikan $conn) sudah di-include sebelumnya
// oleh file yang memanggil W.php (misalnya percetakan_spk.php).

$sql = "SELECT weight FROM saw_criterias ORDER BY id_criteria";
// Menggunakan $conn untuk query database
$result = $conn->query($sql);

// Penanganan error jika query gagal
if (!$result) {
    // Menggunakan $conn->error untuk pesan error dari koneksi mysqli
    die("Error mengambil data bobot: " . $conn->error);
}

$W = array();
while ($row = $result->fetch_object()) {
    // (float) untuk memastikan tipe data numerik dan konsisten
    $W[] = (float)$row->weight;
}

// Opsional: penanganan jika tidak ada bobot yang ditemukan
if (empty($W)) {
    // Anda bisa menambahkan log atau memberikan nilai default jika diperlukan
    // error_log("Peringatan: Tidak ada data bobot ditemukan di tabel saw_criterias.");
    // $W = [0.25, 0.25, 0.25, 0.25]; // Contoh bobot default jika tabel kosong
}
?>