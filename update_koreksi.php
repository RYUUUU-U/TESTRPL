<?php
include ("koneksi.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : '';

    if ($id > 0 && in_array($status, ['Pending', 'Disetujui', 'Ditolak'])) {
        $query = "UPDATE koreksi_absensi SET status_koreksi = ? WHERE koreksi_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('si', $status, $id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Status berhasil diperbarui.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal memperbarui data.']);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Data tidak valid.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Metode tidak diperbolehkan.']);
}
