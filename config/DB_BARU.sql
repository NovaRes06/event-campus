
CREATE DATABASE IF NOT EXISTS `db_kampus_event` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `db_kampus_event`;

-- Dumping structure for table db_kampus_event.anggota_divisi
CREATE TABLE IF NOT EXISTS `anggota_divisi` (
  `anggota_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `divisi_id` int NOT NULL,
  `jabatan` varchar(50) DEFAULT 'Staff',
  PRIMARY KEY (`anggota_id`),
  KEY `user_id` (`user_id`),
  KEY `divisi_id` (`divisi_id`),
  CONSTRAINT `anggota_divisi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `anggota_divisi_ibfk_2` FOREIGN KEY (`divisi_id`) REFERENCES `divisi` (`divisi_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_kampus_event.anggota_divisi: ~19 rows (approximately)
INSERT INTO `anggota_divisi` (`anggota_id`, `user_id`, `divisi_id`, `jabatan`) VALUES
	(1, 2, 1, 'Koordinator'),
	(2, 3, 1, 'Sekretaris'),
	(3, 4, 1, 'Staff'),
	(5, 7, 9, 'Ketua'),
	(6, 9, 10, 'Koordinator'),
	(7, 11, 11, 'Ketua'),
	(8, 12, 11, 'Sekretaris'),
	(9, 13, 11, 'Bendahara'),
	(10, 14, 12, 'Koordinator'),
	(11, 15, 12, 'Staff'),
	(12, 16, 12, 'Staff'),
	(13, 17, 13, 'Koordinator'),
	(14, 18, 13, 'Staff'),
	(15, 19, 13, 'Staff'),
	(16, 20, 14, 'Koordinator'),
	(17, 21, 14, 'Staff'),
	(18, 22, 14, 'Staff'),
	(19, 23, 15, 'Koordinator'),
	(20, 24, 15, 'Staff');

-- Dumping structure for table db_kampus_event.divisi
CREATE TABLE IF NOT EXISTS `divisi` (
  `divisi_id` int NOT NULL AUTO_INCREMENT,
  `event_id` int NOT NULL,
  `nama_divisi` varchar(100) NOT NULL,
  PRIMARY KEY (`divisi_id`),
  KEY `event_id` (`event_id`),
  CONSTRAINT `divisi_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_kampus_event.divisi: ~11 rows (approximately)
INSERT INTO `divisi` (`divisi_id`, `event_id`, `nama_divisi`) VALUES
	(1, 1, 'Acara'),
	(2, 1, 'Humas'),
	(3, 1, 'Perlengkapan'),
	(6, 2, 'Badan Pengurus Harian'),
	(9, 1, 'BPH'),
	(10, 1, 'Pubdekdok'),
	(11, 3, 'BPH'),
	(12, 3, 'Acara'),
	(13, 3, 'Humas'),
	(14, 3, 'Perlengkapan'),
	(15, 3, 'Dokumentasi');

-- Dumping structure for table db_kampus_event.events
CREATE TABLE IF NOT EXISTS `events` (
  `event_id` int NOT NULL AUTO_INCREMENT,
  `nama_event` varchar(200) NOT NULL,
  `deskripsi` text,
  `tanggal_mulai` date DEFAULT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `status` enum('upcoming','active','completed','cancelled','archived') DEFAULT 'upcoming',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `jumlah_divisi` int DEFAULT '0',
  PRIMARY KEY (`event_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_kampus_event.events: ~3 rows (approximately)
INSERT INTO `events` (`event_id`, `nama_event`, `deskripsi`, `tanggal_mulai`, `tanggal_selesai`, `status`, `created_at`, `jumlah_divisi`) VALUES
	(1, 'INSPACE 2025', '', '2026-01-20', '2026-01-30', 'active', '2025-12-05 11:22:03', 5),
	(2, 'SPIN ETAM 2024', NULL, '2024-12-15', NULL, 'completed', '2025-12-05 11:22:03', 1),
	(3, 'INSPIRE 2025', 'Festival Teknologi Terbesar di Kampus', '2025-05-20', '2025-05-22', 'active', '2025-12-08 12:17:10', 5);

-- Dumping structure for table db_kampus_event.jobdesk
CREATE TABLE IF NOT EXISTS `jobdesk` (
  `jobdesk_id` int NOT NULL AUTO_INCREMENT,
  `divisi_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `nama_tugas` varchar(255) NOT NULL,
  `deskripsi` text,
  `deadline` date DEFAULT NULL,
  `status` enum('Pending','Process','Revision','Done') DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`jobdesk_id`),
  KEY `divisi_id` (`divisi_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `jobdesk_ibfk_1` FOREIGN KEY (`divisi_id`) REFERENCES `divisi` (`divisi_id`) ON DELETE CASCADE,
  CONSTRAINT `jobdesk_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_kampus_event.jobdesk: ~6 rows (approximately)
INSERT INTO `jobdesk` (`jobdesk_id`, `divisi_id`, `user_id`, `nama_tugas`, `deskripsi`, `deadline`, `status`, `created_at`) VALUES
	(1, 1, 4, 'Konsep Rundown Acara', NULL, '2025-01-10', 'Done', '2025-12-05 11:22:04'),
	(2, 1, 4, 'Booking Gedung Serbaguna', NULL, '2025-01-12', 'Process', '2025-12-05 11:22:04'),
	(3, 11, 11, 'Memimpin Rapat Perdana', 'Koordinasi seluruh koordinator', '2025-02-01', 'Done', '2025-12-08 12:17:10'),
	(4, 12, 14, 'Menyusun Rundown Acara', 'Draft 1 harus selesai minggu ini', '2025-02-10', 'Process', '2025-12-08 12:17:10'),
	(5, 13, 18, 'Menghubungi Media Partner', 'List media lokal dan nasional', '2025-02-15', 'Pending', '2025-12-08 12:17:10'),
	(6, 14, NULL, 'Survey Panggung', 'Cari vendor panggung yang murah', '2025-02-20', 'Pending', '2025-12-08 12:17:10');

-- Dumping structure for table db_kampus_event.notulensi
CREATE TABLE IF NOT EXISTS `notulensi` (
  `notulensi_id` int NOT NULL AUTO_INCREMENT,
  `event_id` int NOT NULL,
  `divisi_id` int DEFAULT NULL,
  `judul_notulen` varchar(200) NOT NULL,
  `isi_pembahasan` text,
  `foto_dokumentasi` varchar(255) DEFAULT NULL,
  `tanggal_rapat` datetime NOT NULL,
  `jenis_rapat` enum('Rapat Umum','Rapat Divisi','Rapat Koordinasi') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notulensi_id`),
  KEY `event_id` (`event_id`),
  CONSTRAINT `notulensi_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_kampus_event.notulensi: ~1 rows (approximately)
INSERT INTO `notulensi` (`notulensi_id`, `event_id`, `divisi_id`, `judul_notulen`, `isi_pembahasan`, `foto_dokumentasi`, `tanggal_rapat`, `jenis_rapat`, `created_at`) VALUES
	(1, 1, 1, 'Rapat Perdana', 'Lorem ipsum dll', NULL, '2025-12-08 19:09:00', 'Rapat Divisi', '2025-12-08 11:09:50');

-- Dumping structure for table db_kampus_event.users
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `peran` enum('admin','ketua','anggota') NOT NULL DEFAULT 'anggota',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `perlu_ganti_pass` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_kampus_event.users: ~23 rows (approximately)
INSERT INTO `users` (`user_id`, `email`, `password`, `nama_lengkap`, `peran`, `created_at`, `perlu_ganti_pass`) VALUES
	(1, 'admin@kampus.id', '39f55dd65ead9c938fa93a765983bff0', 'Si Paling Admin', 'admin', '2025-12-05 11:22:03', 0),
	(2, 'budi@kampus.id', '39f55dd65ead9c938fa93a765983bff0', 'Budi Santoso', 'anggota', '2025-12-05 11:22:03', 0),
	(3, 'ani@kampus.id', '39f55dd65ead9c938fa93a765983bff0', 'Ani Wijaya', 'anggota', '2025-12-05 11:22:03', 0),
	(4, 'tes1@admin.id', '39f55dd65ead9c938fa93a765983bff0', 'Rifa', 'anggota', '2025-12-05 12:44:09', 0),
	(6, 'adel@kampus.id', '746c18723874cc0f855e0cc84d4020f9', 'Test MHS', 'anggota', '2025-12-08 11:15:10', 0),
	(7, 'adelia.isra@itk.ac.id', '738f395106b983b401eb7982c81bfcf7', 'Adelia Isra Ekaputri', 'anggota', '2025-12-08 11:36:10', 0),
	(8, 'nova@kampus.id', '39f55dd65ead9c938fa93a765983bff0', 'Nova', 'anggota', '2025-12-08 11:36:22', 1),
	(9, 'zaydan@kampus.id', '6b37bc7fbe2183a9826becd100a07e66', 'Zaydan', 'anggota', '2025-12-08 11:36:41', 0),
	(10, 'adel@blabla.id', '39f55dd65ead9c938fa93a765983bff0', 'Adelia', 'anggota', '2025-12-08 11:59:42', 1),
	(11, 'ketua_tf@kampus.id', '39f55dd65ead9c938fa93a765983bff0', 'Dimas Anggara', 'anggota', '2025-12-08 12:17:10', 0),
	(12, 'sekre_tf@kampus.id', '39f55dd65ead9c938fa93a765983bff0', 'Siti Aminah', 'anggota', '2025-12-08 12:17:10', 0),
	(13, 'bendahara_tf@kampus.id', '39f55dd65ead9c938fa93a765983bff0', 'Budi Santoso', 'anggota', '2025-12-08 12:17:10', 0),
	(14, 'koor_acara@kampus.id', '39f55dd65ead9c938fa93a765983bff0', 'Raka Pratama', 'anggota', '2025-12-08 12:17:10', 0),
	(15, 'staff_acara1@kampus.id', '39f55dd65ead9c938fa93a765983bff0', 'Dinda Kirana', 'anggota', '2025-12-08 12:17:10', 0),
	(16, 'staff_acara2@kampus.id', '39f55dd65ead9c938fa93a765983bff0', 'Eko Prasetyo', 'anggota', '2025-12-08 12:17:10', 0),
	(17, 'koor_humas@kampus.id', '39f55dd65ead9c938fa93a765983bff0', 'Fara Quinn', 'anggota', '2025-12-08 12:17:10', 0),
	(18, 'staff_humas1@kampus.id', '39f55dd65ead9c938fa93a765983bff0', 'Gilang Dirga', 'anggota', '2025-12-08 12:17:10', 0),
	(19, 'staff_humas2@kampus.id', '39f55dd65ead9c938fa93a765983bff0', 'Hesti Purwadinata', 'anggota', '2025-12-08 12:17:10', 0),
	(20, 'koor_perkap@kampus.id', '39f55dd65ead9c938fa93a765983bff0', 'Indra Bekti', 'anggota', '2025-12-08 12:17:10', 0),
	(21, 'staff_perkap1@kampus.id', '39f55dd65ead9c938fa93a765983bff0', 'Joko Anwar', 'anggota', '2025-12-08 12:17:10', 0),
	(22, 'staff_perkap2@kampus.id', '39f55dd65ead9c938fa93a765983bff0', 'Kiki Saputri', 'anggota', '2025-12-08 12:17:10', 0),
	(23, 'koor_dokum@kampus.id', '39f55dd65ead9c938fa93a765983bff0', 'Luna Maya', 'anggota', '2025-12-08 12:17:10', 0),
	(24, 'staff_dokum1@kampus.id', '39f55dd65ead9c938fa93a765983bff0', 'Maudy Ayunda', 'anggota', '2025-12-08 12:17:10', 0);


ALTER TABLE jobdesk ADD COLUMN keterangan TEXT DEFAULT NULL AFTER status;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
