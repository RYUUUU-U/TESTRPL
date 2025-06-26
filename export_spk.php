<?php
// File: export_spk.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require 'koneksi.php';
require 'cek.php';
require "W.php";
require "R.php"; // Ini akan mendefinisikan $R dan $alternative_details

// Pastikan PhpSpreadsheet sudah diinstal melalui Composer
// dan autoload.php dapat diakses
$phpSpreadsheetAutoloadPath = __DIR__ . '/vendor/autoload.php'; // Asumsi direktori vendor ada di root proyek
if (file_exists($phpSpreadsheetAutoloadPath)) {
    require $phpSpreadsheetAutoloadPath;
} else {
    die("PhpSpreadsheet library not found. Please install via Composer or check the path to autoload.php. Path checked: " . $phpSpreadsheetAutoloadPath);
}


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

// Hitung Nilai Preferensi (P) untuk ekspor
$P_export = array();
$ranking_export = array();

if (isset($R) && isset($W) && is_array($R) && is_array($W) && !empty($R) && !empty($W)) {
    $m = count($W);
    foreach ($R as $id_alternative => $r_values) {
        if (!is_array($r_values) || count($r_values) !== $m) continue;

        $nilai_p = 0;
        for ($j = 0; $j < $m; $j++) {
            if (isset($r_values[$j]) && isset($W[$j])) {
                $nilai_p += $r_values[$j] * $W[$j];
            }
        }
        $P_export[$id_alternative] = $nilai_p;
        $nama_alternatif = isset($alternative_details[$id_alternative]) ? $alternative_details[$id_alternative] : 'ID ' . $id_alternative;
        $ranking_export[] = [
            'id' => $id_alternative,
            'nama' => $nama_alternatif,
            'nilai_p' => $nilai_p
        ];
    }
    if(!empty($ranking_export)){
        usort($ranking_export, function ($a, $b) {
            return $b['nilai_p'] <=> $a['nilai_p'];
        });
    }
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Hasil Perhitungan SPK');

// --- Header Global ---
$sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
$sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
$sheet->getPageMargins()->setTop(0.75)->setRight(0.7)->setLeft(0.7)->setBottom(0.75);

// Judul Laporan
$sheet->mergeCells('A1:G1'); // Sesuaikan range merge jika jumlah kolom berbeda
$sheet->setCellValue('A1', 'LAPORAN HASIL PERHITUNGAN SISTEM PENDUKUNG KEPUTUSAN');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
$sheet->getRowDimension(1)->setRowHeight(25);

$baris = 3;

// --- 1. Data Bobot Kriteria (W) ---
$sheet->setCellValue('A'.$baris, 'BOBOT KRITERIA (W)');
$sheet->getStyle('A'.$baris)->getFont()->setBold(true)->setSize(12);
$baris++;
$headerBobot = [];
$dataBobot = [];
if (!empty($W)) {
    for ($i=0; $i < count($W); $i++) {
        $headerBobot[] = 'C'.($i+1);
        $dataBobot[] = round($W[$i], 4);
    }
    $sheet->fromArray([$headerBobot], NULL, 'A'.$baris);
    $sheet->getStyle('A'.$baris.':'. $sheet->getHighestColumn() . $baris)->getFont()->setBold(true);
    $sheet->getStyle('A'.$baris.':'. $sheet->getHighestColumn() . $baris)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $baris++;
    $sheet->fromArray([$dataBobot], NULL, 'A'.$baris);
    $sheet->getStyle('A'.$baris.':'. $sheet->getHighestColumn() . $baris)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

} else {
    $sheet->setCellValue('A'.$baris, 'Data Bobot Tidak Tersedia');
}
$baris += 3;


// --- 2. Matriks Ternormalisasi (R) ---
$sheet->setCellValue('A'.$baris, 'MATRIKS TERNORMALISASI (R)');
$sheet->getStyle('A'.$baris)->getFont()->setBold(true)->setSize(12);
$baris++;
$headerR = ['Alternatif'];
if (!empty($W)) {
    for ($i=0; $i < count($W); $i++) { $headerR[] = 'C'.($i+1); }
}
$sheet->fromArray($headerR, NULL, 'A'.$baris);
$sheet->getStyle('A'.$baris.':'. $sheet->getHighestColumn() . $baris)->getFont()->setBold(true);
$sheet->getStyle('A'.$baris.':'. $sheet->getHighestColumn() . $baris)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$baris++;

if (!empty($R)) {
    $startRowR = $baris;
    foreach($R as $id_alt => $r_vals) {
        $nama_alt = isset($alternative_details[$id_alt]) ? $alternative_details[$id_alt] : 'A'.$id_alt;
        $rowData = [$nama_alt];
        foreach($r_vals as $val) { $rowData[] = round($val, 4); }
        $sheet->fromArray($rowData, NULL, 'A'.$baris);
        $sheet->getStyle('B'.$baris.':'. $sheet->getHighestColumn() . $baris)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $baris++;
    }
    $endRowR = $baris - 1;
} else {
    $sheet->setCellValue('A'.$baris, 'Data Matriks R Tidak Tersedia');
    $baris++;
}
$baris += 2;

// --- 3. Nilai Preferensi (P) dan Perangkingan ---
$sheet->setCellValue('A'.$baris, 'NILAI PREFERENSI (P) DAN PERANGKINGAN');
$sheet->getStyle('A'.$baris)->getFont()->setBold(true)->setSize(12);
$baris++;
$headerP = ['Ranking', 'ID Alternatif', 'Nama Alternatif', 'Nilai Preferensi (P)'];
$sheet->fromArray($headerP, NULL, 'A'.$baris);
$sheet->getStyle('A'.$baris.':D'.$baris)->getFont()->setBold(true);
$sheet->getStyle('A'.$baris.':D'.$baris)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$baris++;

if (!empty($ranking_export)) {
    $no_rank = 1;
    $startRowP = $baris;
    foreach ($ranking_export as $item) {
        $rowDataP = [
            $no_rank,
            'A'.$item['id'],
            $item['nama'],
            round($item['nilai_p'], 4)
        ];
        $sheet->fromArray($rowDataP, NULL, 'A'.$baris);
        $sheet->getStyle('A'.$baris)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B'.$baris)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D'.$baris)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $baris++;
        $no_rank++;
    }
    $endRowP = $baris - 1;
} else {
    $sheet->setCellValue('A'.$baris, 'Data Nilai Preferensi Tidak Tersedia');
}

// Auto size kolom
foreach (range('A', $sheet->getHighestDataColumn()) as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Border untuk semua tabel data (opsional, contoh untuk tabel P)
$styleArrayBorders = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['argb' => 'FF000000'],
        ],
    ],
];
// Contoh penerapan border ke tabel P, sesuaikan jika perlu
if (!empty($ranking_export) && isset($startRowP) && isset($endRowP)) {
    $sheet->getStyle('A' . ($startRowP-1) . ':D' . $endRowP)->applyFromArray($styleArrayBorders);
}


$filename = 'Laporan_Hasil_SPK_'.date('Y-m-d_H-i-s').'.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.$filename.'"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>