-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 23 Jun 2025 pada 11.21
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `absensi_qr`
--

DELIMITER $$
--
-- Prosedur
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `ValidateAndRecordAttendance` (IN `p_token_value` VARCHAR(255), IN `p_karyawan_id` INT, IN `p_waktu_scan` DATETIME, OUT `p_result_message` VARCHAR(50))   BEGIN
    DECLARE v_id_token INT;
    DECLARE v_token_status ENUM('aktif', 'digunakan', 'kedaluwarsa');
    DECLARE v_expires_at DATETIME;
    DECLARE v_absensi_id_today INT;
    DECLARE v_jam_keluar_today TIME;
    DECLARE v_waktu_efektif DATETIME;

    -- Tentukan waktu efektif: gunakan waktu debug jika ada, jika tidak gunakan waktu server asli
    IF p_waktu_scan IS NULL THEN
        SET time_zone = '+07:00';
        SET v_waktu_efektif = NOW();
    ELSE
        SET v_waktu_efektif = p_waktu_scan;
    END IF;

    START TRANSACTION;

    SELECT id_token, status, expires_at INTO v_id_token, v_token_status, v_expires_at
    FROM qr_tokens WHERE token_value = p_token_value LIMIT 1;

    -- === PERBAIKAN DI SINI ===
    -- Bandingkan waktu kedaluwarsa dengan WAKTU EFEKTIF, bukan NOW()
    IF v_id_token IS NULL THEN SET p_result_message = 'TOKEN_TIDAK_VALID';
    ELSEIF v_token_status = 'digunakan' THEN SET p_result_message = 'TOKEN_SUDAH_DIGUNAKAN';
    ELSEIF v_token_status = 'kedaluwarsa' OR v_expires_at < v_waktu_efektif THEN SET p_result_message = 'TOKEN_KEDALUWARSA';
    ELSE
        -- Logika absensi tetap sama
        SELECT absensi_id, jam_keluar INTO v_absensi_id_today, v_jam_keluar_today
        FROM absensi WHERE karyawan_id = p_karyawan_id AND tanggal = DATE(v_waktu_efektif) LIMIT 1;

        IF v_absensi_id_today IS NULL THEN
            IF TIME(v_waktu_efektif) >= '07:00:00' AND TIME(v_waktu_efektif) <= '09:00:00' THEN
                UPDATE qr_tokens SET status = 'digunakan' WHERE id_token = v_id_token;
                INSERT INTO absensi (karyawan_id, tanggal, jam_masuk, status, id_token)
                VALUES (p_karyawan_id, DATE(v_waktu_efektif), TIME(v_waktu_efektif), 'hadir', v_id_token);
                SET p_result_message = 'ABSENSI_MASUK_BERHASIL';
            ELSE SET p_result_message = 'BUKAN_WAKTU_MASUK';
            END IF;
        ELSEIF v_jam_keluar_today IS NULL THEN
            IF TIME(v_waktu_efektif) >= '16:00:00' AND TIME(v_waktu_efektif) <= '18:00:00' THEN
                UPDATE qr_tokens SET status = 'digunakan' WHERE id_token = v_id_token;
                UPDATE absensi SET jam_keluar = TIME(v_waktu_efektif) WHERE absensi_id = v_absensi_id_today;
                SET p_result_message = 'ABSENSI_KELUAR_BERHASIL';
            ELSE SET p_result_message = 'BUKAN_WAKTU_KELUAR';
            END IF;
        ELSE SET p_result_message = 'SUDAH_ABSEN_LENGKAP';
        END IF;
    END IF;
    COMMIT;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `absensi`
--

CREATE TABLE `absensi` (
  `absensi_id` int(11) NOT NULL,
  `karyawan_id` int(11) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `jam_masuk` time DEFAULT NULL,
  `jam_keluar` time DEFAULT NULL,
  `status` enum('hadir','terlambat','alpha','izin','sakit') DEFAULT NULL,
  `id_token` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `absensi`
--

INSERT INTO `absensi` (`absensi_id`, `karyawan_id`, `tanggal`, `jam_masuk`, `jam_keluar`, `status`, `id_token`) VALUES
(5, 15, '2025-06-22', '07:21:30', '17:22:27', 'hadir', NULL),
(6, 13, '2025-06-23', '07:00:00', '07:00:00', 'izin', NULL);

--
-- Trigger `absensi`
--
DELIMITER $$
CREATE TRIGGER `after_absensi_insert_update_saw_evaluation` AFTER INSERT ON `absensi` FOR EACH ROW BEGIN
    -- Deklarasi variabel untuk menampung id_criteria dan id_alternative
    DECLARE v_id_criteria INT;
    DECLARE v_id_alternative INT;

    -- 1. Tentukan id_criteria berdasarkan status absensi yang baru masuk.
    SET v_id_criteria = CASE LOWER(NEW.status)
        WHEN 'hadir' THEN 1      -- Sesuai kriteria 'Jumlah Hadir'
        WHEN 'alpha' THEN 3      -- Sesuai kriteria 'Jumlah Alpha'
        WHEN 'terlambat' THEN 4  -- Sesuai kriteria 'Jumlah Terlambat'
        ELSE NULL                -- Abaikan status lain seperti 'sakit' atau 'izin'
    END;

    -- 2. Hanya lanjutkan jika statusnya relevan untuk perhitungan SAW
    IF v_id_criteria IS NOT NULL THEN

        -- 3. Cari id_alternative untuk karyawan yang bersangkutan dari kontrak aktif mereka
        SELECT sa.id_alternative INTO v_id_alternative
        FROM `saw_alternatives` sa
        JOIN `kontrak_karyawan` kk ON sa.kontrak_id = kk.kontrak_id
        WHERE kk.karyawan_id = NEW.karyawan_id
          AND kk.status_kontrak = 'aktif'
        LIMIT 1;

        -- 4. Hanya lanjutkan jika karyawan tersebut terdaftar sebagai 'alternatif' di sistem SAW
        IF v_id_alternative IS NOT NULL THEN

            -- 5. Masukkan/Update data ke tabel evaluasi SAW.
            INSERT INTO `saw_evaluations` (id_alternative, id_criteria, value)
            VALUES (v_id_alternative, v_id_criteria, 1)
            ON DUPLICATE KEY UPDATE
                `value` = `value` + 1;

        END IF;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `izin_cuti`
--

CREATE TABLE `izin_cuti` (
  `cuti_id` int(11) NOT NULL,
  `karyawan_id` int(11) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `jenis_cuti` varchar(50) DEFAULT 'Cuti Tahunan',
  `alasan` text DEFAULT NULL,
  `status_cuti` enum('pending','disetujui','ditolak') DEFAULT 'pending',
  `tanggal_pengajuan` date DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `izin_cuti`
--

INSERT INTO `izin_cuti` (`cuti_id`, `karyawan_id`, `tanggal_mulai`, `tanggal_selesai`, `jenis_cuti`, `alasan`, `status_cuti`, `tanggal_pengajuan`) VALUES
(2, 13, '2025-06-27', '2025-06-28', 'Cuti Tahunan', 'pulang kampung', 'pending', '2025-06-23'),
(3, 13, '2025-06-23', '2025-06-25', 'Izin Khusus', 'test', 'pending', '2025-06-23');

-- --------------------------------------------------------

--
-- Struktur dari tabel `karyawan`
--

CREATE TABLE `karyawan` (
  `karyawan_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `departemen` varchar(100) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `status_kepegawaian` enum('tetap','kontrak') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `karyawan`
--

INSERT INTO `karyawan` (`karyawan_id`, `user_id`, `nama_lengkap`, `jabatan`, `departemen`, `no_hp`, `status_kepegawaian`) VALUES
(10, 4, 'Ujang', 'Staff Produksi', 'Produksi', '0833333333', 'kontrak'),
(11, 5, 'RYU', 'Staff Produksi', 'Produksi', '084444444', 'kontrak'),
(12, 6, 'ocaa', 'Staff Produksi', 'Produksi', '085555555', 'tetap'),
(13, 8, 'cecep', 'Staff', 'Produksi', '6514568416894', 'tetap'),
(14, 7, 'Admin Utama', 'Administrator', NULL, NULL, 'tetap'),
(15, 9, 'Cia', NULL, NULL, NULL, 'kontrak');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kontrak_karyawan`
--

CREATE TABLE `kontrak_karyawan` (
  `kontrak_id` int(11) NOT NULL,
  `karyawan_id` int(11) DEFAULT NULL,
  `tanggal_mulai` date DEFAULT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `status_kontrak` enum('aktif','berakhir','diperpanjang') DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kontrak_karyawan`
--

INSERT INTO `kontrak_karyawan` (`kontrak_id`, `karyawan_id`, `tanggal_mulai`, `tanggal_selesai`, `status_kontrak`) VALUES
(5, 10, '2025-05-01', '2025-06-30', 'aktif'),
(6, 11, '2025-06-01', '2025-06-30', 'aktif');

-- --------------------------------------------------------

--
-- Struktur dari tabel `koreksi_absensi`
--

CREATE TABLE `koreksi_absensi` (
  `koreksi_id` int(11) NOT NULL,
  `absensi_id` int(11) DEFAULT NULL,
  `karyawan_id` int(11) DEFAULT NULL,
  `tanggal_pengajuan` date DEFAULT NULL,
  `alasan` text DEFAULT NULL,
  `status_koreksi` enum('pending','disetujui','ditolak') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `koreksi_absensi`
--

INSERT INTO `koreksi_absensi` (`koreksi_id`, `absensi_id`, `karyawan_id`, `tanggal_pengajuan`, `alasan`, `status_koreksi`) VALUES
(6, 5, 13, '2025-06-23', 'mis info\r\n', 'disetujui');

-- --------------------------------------------------------

--
-- Struktur dari tabel `qr_tokens`
--

CREATE TABLE `qr_tokens` (
  `id_token` int(11) NOT NULL,
  `token_value` varchar(255) NOT NULL COMMENT 'Nilai unik yang ada di dalam QR code',
  `generated_by` int(11) NOT NULL COMMENT 'FK ke karyawan.karyawan_id (Admin yg generate)',
  `created_at` datetime NOT NULL COMMENT 'Waktu token dibuat',
  `expires_at` datetime NOT NULL COMMENT 'Waktu kedaluwarsa jika tidak ada yg scan',
  `status` enum('aktif','digunakan','kedaluwarsa') NOT NULL DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Menyimpan token sementara untuk validasi QR Code';

--
-- Dumping data untuk tabel `qr_tokens`
--

INSERT INTO `qr_tokens` (`id_token`, `token_value`, `generated_by`, `created_at`, `expires_at`, `status`) VALUES
(137, 'e0cc7c295b111fbc876691178f4977113ad835a4def0c61a854f342ffb8db735', 14, '2025-06-23 13:22:59', '2025-06-23 13:23:04', 'aktif'),
(138, '7d5868cc54f7b3f2a08bd522b37b59df0d97961d465afc2dd723da3db7d5d296', 14, '2025-06-23 13:23:02', '2025-06-23 13:23:07', 'aktif'),
(139, '65ac25770163e1f87ba23f03e5ce1585368edba0e5089ceeb6c90dd872099519', 14, '2025-06-23 13:23:09', '2025-06-23 13:23:14', 'aktif'),
(140, '44255c810016dbb943b3a1b8ba95ad7a5651333c2517b3dc9ee221f5197699db', 14, '2025-06-23 14:10:30', '2025-06-23 14:10:35', 'aktif'),
(141, '19698b427812b716b34697ccee3efcc4cf3c80a59f851fc5127bac5bfbf4ed9e', 14, '2025-06-23 14:10:38', '2025-06-23 14:10:43', 'aktif'),
(142, 'c266b40a0bd120ac2d0a6525fb46ef4f4c3ce8154085a20fa023d0011ba99a55', 14, '2025-06-23 14:10:46', '2025-06-23 14:10:51', 'aktif'),
(143, 'df263e4f47c825fb55c18cb7c714de09deb1557c3dc7383d4b3f16ba1738723e', 14, '2025-06-23 14:53:49', '2025-06-23 14:53:54', 'aktif'),
(144, '3ed891a800331ce223ed742642e199f4141917d1214f1a7bb9ca9a6efcb41f80', 14, '2025-06-23 14:53:56', '2025-06-23 14:54:01', 'aktif'),
(145, '2f5389b5bbc7813a6f7f44c2f17dd639e9f4366bbf149bae5861a880cbccd236', 14, '2025-06-23 14:54:04', '2025-06-23 14:54:09', 'aktif'),
(146, '37e6b0bd1d69b56b8dd96de9bcac42af3390019da5d46546ad8d56240c1f6f67', 14, '2025-06-23 14:54:12', '2025-06-23 14:54:17', 'aktif'),
(147, '488bcbd6625a261e397eb95696cbd84becc2550b126f244feade2aa62b2b81ec', 14, '2025-06-23 15:28:09', '2025-06-23 15:28:14', 'aktif'),
(148, '2989beeb1fe06367ff9d2f1ebe3428550ea96f86feb8d549754891c3fd923776', 14, '2025-06-23 15:53:15', '2025-06-23 15:53:20', 'aktif'),
(149, '320f0a8a9d0c11d987ae421fd816162cf98bd9f2bcbf032adee9d13d06c7283f', 14, '2025-06-23 15:53:22', '2025-06-23 15:53:27', 'aktif');

-- --------------------------------------------------------

--
-- Struktur dari tabel `saw_alternatives`
--

CREATE TABLE `saw_alternatives` (
  `id_alternative` int(11) NOT NULL,
  `kontrak_id` int(11) NOT NULL,
  `periode_bulan` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `saw_alternatives`
--

INSERT INTO `saw_alternatives` (`id_alternative`, `kontrak_id`, `periode_bulan`) VALUES
(3, 5, 'Mei 2025'),
(4, 6, 'Juni 2025');

-- --------------------------------------------------------

--
-- Struktur dari tabel `saw_criterias`
--

CREATE TABLE `saw_criterias` (
  `id_criteria` int(11) NOT NULL,
  `criteria_name` varchar(100) NOT NULL,
  `weight` float NOT NULL,
  `type` enum('benefit','cost') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `saw_criterias`
--

INSERT INTO `saw_criterias` (`id_criteria`, `criteria_name`, `weight`, `type`) VALUES
(1, 'Jumlah Hadir', 4, 'benefit'),
(2, 'Penilaian Kinerja', 3, 'benefit'),
(3, 'Jumlah Alpha', 2, 'cost'),
(4, 'Jumlah Terlambat', 1, 'cost');

-- --------------------------------------------------------

--
-- Struktur dari tabel `saw_evaluations`
--

CREATE TABLE `saw_evaluations` (
  `id_evaluation` int(11) NOT NULL,
  `id_alternative` int(11) NOT NULL,
  `id_criteria` int(11) NOT NULL,
  `value` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `saw_evaluations`
--

INSERT INTO `saw_evaluations` (`id_evaluation`, `id_alternative`, `id_criteria`, `value`) VALUES
(11, 3, 1, 10),
(15, 3, 3, 1),
(16, 3, 4, 1),
(17, 3, 2, 10),
(18, 4, 1, 10),
(19, 4, 2, 10),
(20, 4, 3, 2),
(21, 4, 4, 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) NOT NULL COMMENT 'Diubah untuk menyimpan password hasil hash dari password_hash() PHP',
  `role` enum('admin','karyawan') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `role`) VALUES
(4, 'ujang', '123', 'karyawan'),
(5, 'RYU', '123', 'karyawan'),
(6, 'ocaa', '123', 'karyawan'),
(7, 'admin', '$2y$10$bh7EE5hJpq3FePKUzm9WsenOpu0m7JN/jRpYAC7ugZOpLtGCRjQxS', 'admin'),
(8, 'cecep', '$2y$10$kQXxvtgKLVOrSDWEJNbA.uRxpNQwDoMJmknXdeNhc4paz9HvwgmtG', 'karyawan'),
(9, 'Cia', '$2y$10$nrBhaicQsK7K7qbveAH9OO9elf0b2tU5E8Q5lk4mgiRbqdeNu2QZy', 'karyawan');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `absensi`
--
ALTER TABLE `absensi`
  ADD PRIMARY KEY (`absensi_id`),
  ADD UNIQUE KEY `id_token_unique` (`id_token`),
  ADD KEY `karyawan_id` (`karyawan_id`);

--
-- Indeks untuk tabel `izin_cuti`
--
ALTER TABLE `izin_cuti`
  ADD PRIMARY KEY (`cuti_id`),
  ADD KEY `karyawan_id` (`karyawan_id`);

--
-- Indeks untuk tabel `karyawan`
--
ALTER TABLE `karyawan`
  ADD PRIMARY KEY (`karyawan_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `kontrak_karyawan`
--
ALTER TABLE `kontrak_karyawan`
  ADD PRIMARY KEY (`kontrak_id`),
  ADD KEY `karyawan_id` (`karyawan_id`);

--
-- Indeks untuk tabel `koreksi_absensi`
--
ALTER TABLE `koreksi_absensi`
  ADD PRIMARY KEY (`koreksi_id`),
  ADD KEY `absensi_id` (`absensi_id`),
  ADD KEY `karyawan_id` (`karyawan_id`);

--
-- Indeks untuk tabel `qr_tokens`
--
ALTER TABLE `qr_tokens`
  ADD PRIMARY KEY (`id_token`),
  ADD UNIQUE KEY `token_value_unique` (`token_value`),
  ADD KEY `status_index` (`status`),
  ADD KEY `generated_by` (`generated_by`);

--
-- Indeks untuk tabel `saw_alternatives`
--
ALTER TABLE `saw_alternatives`
  ADD PRIMARY KEY (`id_alternative`),
  ADD KEY `kontrak_id` (`kontrak_id`);

--
-- Indeks untuk tabel `saw_criterias`
--
ALTER TABLE `saw_criterias`
  ADD PRIMARY KEY (`id_criteria`);

--
-- Indeks untuk tabel `saw_evaluations`
--
ALTER TABLE `saw_evaluations`
  ADD PRIMARY KEY (`id_evaluation`),
  ADD UNIQUE KEY `alternative_criteria_unique` (`id_alternative`,`id_criteria`),
  ADD KEY `id_alternative` (`id_alternative`),
  ADD KEY `id_criteria` (`id_criteria`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `absensi`
--
ALTER TABLE `absensi`
  MODIFY `absensi_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `izin_cuti`
--
ALTER TABLE `izin_cuti`
  MODIFY `cuti_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `karyawan`
--
ALTER TABLE `karyawan`
  MODIFY `karyawan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `kontrak_karyawan`
--
ALTER TABLE `kontrak_karyawan`
  MODIFY `kontrak_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `koreksi_absensi`
--
ALTER TABLE `koreksi_absensi`
  MODIFY `koreksi_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `qr_tokens`
--
ALTER TABLE `qr_tokens`
  MODIFY `id_token` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=150;

--
-- AUTO_INCREMENT untuk tabel `saw_alternatives`
--
ALTER TABLE `saw_alternatives`
  MODIFY `id_alternative` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `saw_criterias`
--
ALTER TABLE `saw_criterias`
  MODIFY `id_criteria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `saw_evaluations`
--
ALTER TABLE `saw_evaluations`
  MODIFY `id_evaluation` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `absensi`
--
ALTER TABLE `absensi`
  ADD CONSTRAINT `absensi_ibfk_1` FOREIGN KEY (`karyawan_id`) REFERENCES `karyawan` (`karyawan_id`),
  ADD CONSTRAINT `absensi_ibfk_2` FOREIGN KEY (`id_token`) REFERENCES `qr_tokens` (`id_token`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `izin_cuti`
--
ALTER TABLE `izin_cuti`
  ADD CONSTRAINT `izin_cuti_ibfk_1` FOREIGN KEY (`karyawan_id`) REFERENCES `karyawan` (`karyawan_id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `karyawan`
--
ALTER TABLE `karyawan`
  ADD CONSTRAINT `karyawan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `kontrak_karyawan`
--
ALTER TABLE `kontrak_karyawan`
  ADD CONSTRAINT `kontrak_karyawan_ibfk_1` FOREIGN KEY (`karyawan_id`) REFERENCES `karyawan` (`karyawan_id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `koreksi_absensi`
--
ALTER TABLE `koreksi_absensi`
  ADD CONSTRAINT `koreksi_absensi_ibfk_1` FOREIGN KEY (`absensi_id`) REFERENCES `absensi` (`absensi_id`),
  ADD CONSTRAINT `koreksi_absensi_ibfk_2` FOREIGN KEY (`karyawan_id`) REFERENCES `karyawan` (`karyawan_id`);

--
-- Ketidakleluasaan untuk tabel `qr_tokens`
--
ALTER TABLE `qr_tokens`
  ADD CONSTRAINT `qr_tokens_ibfk_1` FOREIGN KEY (`generated_by`) REFERENCES `karyawan` (`karyawan_id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `saw_alternatives`
--
ALTER TABLE `saw_alternatives`
  ADD CONSTRAINT `saw_alternatives_ibfk_1` FOREIGN KEY (`kontrak_id`) REFERENCES `kontrak_karyawan` (`kontrak_id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `saw_evaluations`
--
ALTER TABLE `saw_evaluations`
  ADD CONSTRAINT `saw_evaluations_ibfk_1` FOREIGN KEY (`id_alternative`) REFERENCES `saw_alternatives` (`id_alternative`) ON DELETE CASCADE,
  ADD CONSTRAINT `saw_evaluations_ibfk_2` FOREIGN KEY (`id_criteria`) REFERENCES `saw_criterias` (`id_criteria`) ON DELETE CASCADE;

DELIMITER $$
--
-- Event
--
CREATE DEFINER=`root`@`localhost` EVENT `CleanExpiredTokens` ON SCHEDULE EVERY 5 MINUTE STARTS '2025-06-21 22:02:20' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
    UPDATE qr_tokens SET status = 'kedaluwarsa'
    WHERE expires_at < NOW() AND status = 'aktif';
END$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
