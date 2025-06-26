<?php
// File: R.php
// Diasumsikan koneksi.php (yang mendefinisikan $conn) sudah di-include sebelumnya.

// BAGIAN 1: Membangun Matriks Keputusan X
$sql_X = "SELECT
            sa.id_alternative,
            k.nama_lengkap AS alternative_name, /* Mengambil nama_lengkap dari tabel karyawan */
            SUM(IF(se.id_criteria=1, se.value, 0)) AS C1,
            SUM(IF(se.id_criteria=2, se.value, 0)) AS C2,
            SUM(IF(se.id_criteria=3, se.value, 0)) AS C3,
            SUM(IF(se.id_criteria=4, se.value, 0)) AS C4,
            SUM(IF(se.id_criteria=5, se.value, 0)) AS C5
          FROM
            saw_evaluations se
          JOIN saw_alternatives sa ON se.id_alternative = sa.id_alternative
          JOIN kontrak_karyawan kk ON sa.kontrak_id = kk.kontrak_id /* Join ke kontrak_karyawan */
          JOIN karyawan k ON kk.karyawan_id = k.karyawan_id       /* Join ke karyawan untuk nama */
          GROUP BY sa.id_alternative, k.nama_lengkap
          ORDER BY sa.id_alternative";

$result_X = $conn->query($sql_X);

if (!$result_X) {
    die("Error mengambil matriks keputusan X: " . $conn->error . "<br>Query:<br><pre>" . htmlspecialchars($sql_X) . "</pre>");
}

$X = array(1 => array(), 2 => array(), 3 => array(), 4 => array(), 5 => array());
// Anda mungkin juga ingin menyimpan nama alternatif bersamaan dengan ID-nya
$alternative_details = array(); // Untuk menyimpan nama alternatif jika diperlukan

while ($row_X = $result_X->fetch_object()) {
    array_push($X[1], round((float)$row_X->C1, 2));
    array_push($X[2], round((float)$row_X->C2, 2));
    array_push($X[3], round((float)$row_X->C3, 2));
    array_push($X[4], round((float)$row_X->C4, 2));
    array_push($X[5], round((float)$row_X->C5, 2));

    // Menyimpan detail alternatif jika diperlukan nanti
    if (isset($row_X->id_alternative) && isset($row_X->alternative_name)) {
        $alternative_details[$row_X->id_alternative] = $row_X->alternative_name;
    }
}
$result_X->free();

// BAGIAN 2: Membangun Matriks Ternormalisasi R

// Pastikan $X[i] tidak kosong sebelum memanggil max() atau min()
// untuk menghindari warning jika tidak ada data untuk kriteria tertentu.
$max_C1 = !empty($X[1]) ? max($X[1]) : 0;
$min_C1 = !empty($X[1]) ? min($X[1]) : 0;
$max_C2 = !empty($X[2]) ? max($X[2]) : 0;
$min_C2 = !empty($X[2]) ? min($X[2]) : 0;
$max_C3 = !empty($X[3]) ? max($X[3]) : 0;
$min_C3 = !empty($X[3]) ? min($X[3]) : 0;
$max_C4 = !empty($X[4]) ? max($X[4]) : 0;
$min_C4 = !empty($X[4]) ? min($X[4]) : 0;
$max_C5 = !empty($X[5]) ? max($X[5]) : 0;
$min_C5 = !empty($X[5]) ? min($X[5]) : 0;

/*
PERHATIAN PENTING untuk query SQL R:
Penanganan pembagian dengan nol menggunakan ($variabel ?: 1) atau IF(a.value=0,1,a.value)
adalah cara untuk MENGHINDARI ERROR SQL, tetapi mungkin tidak selalu menghasilkan
normalisasi yang secara matematis paling tepat menurut aturan SAW jika nilai pembagi
benar-benar 0 dan bukan seharusnya 1. Anda mungkin perlu menyesuaikan logika ini
jika kasus seperti itu sering terjadi dan membutuhkan penanganan khusus.
*/
$sql_R = "SELECT
            a.id_alternative,
            SUM(IF(a.id_criteria=1, IF(b.type='benefit', a.value/" . ($max_C1 ?: 1) . ", " . $min_C1 . "/IF(a.value=0,1,a.value)), 0)) AS C1,
            SUM(IF(a.id_criteria=2, IF(b.type='benefit', a.value/" . ($max_C2 ?: 1) . ", " . $min_C2 . "/IF(a.value=0,1,a.value)), 0)) AS C2,
            SUM(IF(a.id_criteria=3, IF(b.type='benefit', a.value/" . ($max_C3 ?: 1) . ", " . $min_C3 . "/IF(a.value=0,1,a.value)), 0)) AS C3,
            SUM(IF(a.id_criteria=4, IF(b.type='benefit', a.value/" . ($max_C4 ?: 1) . ", " . $min_C4 . "/IF(a.value=0,1,a.value)), 0)) AS C4,
            SUM(IF(a.id_criteria=5, IF(b.type='benefit', a.value/" . ($max_C5 ?: 1) . ", " . $min_C5 . "/IF(a.value=0,1,a.value)), 0)) AS C5
          FROM
            saw_evaluations a
          JOIN saw_criterias b ON a.id_criteria = b.id_criteria
          GROUP BY a.id_alternative
          ORDER BY a.id_alternative";

$result_R = $conn->query($sql_R);

if (!$result_R) {
    die("Error mengambil matriks ternormalisasi R: " . $conn->error . "<br>Query:<br><pre>" . htmlspecialchars($sql_R) . "</pre>");
}

$R = array();
while ($row_R = $result_R->fetch_object()) {
    $R[$row_R->id_alternative] = array(
        round((float)$row_R->C1, 4), // Presisi 4 angka di belakang koma
        round((float)$row_R->C2, 4),
        round((float)$row_R->C3, 4),
        round((float)$row_R->C4, 4),
        round((float)$row_R->C5, 4)
    );
}
$result_R->free();
?>