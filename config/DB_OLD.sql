CREATE DATABASE IF NOT EXISTS db_kampus_event;
USE db_kampus_event;

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    peran ENUM('admin', 'ketua', 'anggota') NOT NULL DEFAULT 'anggota',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    nama_event VARCHAR(200) NOT NULL,
    deskripsi TEXT,
    tanggal_mulai DATE,
    tanggal_selesai DATE,
    status ENUM('pending', 'active', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE divisi (
    divisi_id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    nama_divisi VARCHAR(100) NOT NULL,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE
);

CREATE TABLE anggota_divisi (
    anggota_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    divisi_id INT NOT NULL,
    jabatan VARCHAR(50) DEFAULT 'Staff',
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (divisi_id) REFERENCES divisi(divisi_id) ON DELETE CASCADE
);

CREATE TABLE jobdesk (
    jobdesk_id INT AUTO_INCREMENT PRIMARY KEY,
    divisi_id INT NOT NULL,
    user_id INT, -- Boleh NULL jika tugas belum diambil orang
    nama_tugas VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    deadline DATE,
    status ENUM('Pending', 'Process', 'Done') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (divisi_id) REFERENCES divisi(divisi_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

CREATE TABLE notulensi (
    notulensi_id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    divisi_id INT, -- Boleh NULL jika ini Rapat Akbar
    judul_notulen VARCHAR(200) NOT NULL,
    isi_pembahasan TEXT,
    foto_dokumentasi VARCHAR(255),
    tanggal_rapat DATETIME NOT NULL,
    jenis_rapat ENUM('Rapat Umum', 'Rapat Divisi', 'Rapat Koordinasi'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE
);


INSERT INTO users (email, password, nama_lengkap, peran) VALUES 
('admin@kampus.id', 'mhs123', 'Si Paling Admin', 'admin'),
('budi@kampus.id', 'mhs123', 'Budi Santoso', 'anggota'),
('ani@kampus.id', 'mhs123', 'Ani Wijaya', 'anggota');


INSERT INTO events (nama_event, tanggal_mulai, status) VALUES 
('INSPACE 2025', '2025-01-20', 'active'),
('SPIN ETAM 2024', '2024-12-15', 'completed');

INSERT INTO divisi (event_id, nama_divisi) VALUES 
(1, 'Acara'), (1, 'Humas'), (1, 'Perlengkapan');

INSERT INTO anggota_divisi (user_id, divisi_id, jabatan) VALUES 
(2, 1, 'Koordinator'); 

INSERT INTO jobdesk (divisi_id, user_id, nama_tugas, deadline, status) VALUES 
(1, 2, 'Konsep Rundown Acara', '2025-01-10', 'Process'),
(1, NULL, 'Booking Gedung Serbaguna', '2025-01-12', 'Pending');

ALTER TABLE events ADD COLUMN jumlah_divisi INT DEFAULT 0;
SHOW TABLES;

-- Mengubah semua password user saat ini menjadi MD5
UPDATE users SET password = MD5(password);

-- 1. Tambah fitur wajib ganti password
ALTER TABLE users ADD COLUMN perlu_ganti_pass BOOLEAN DEFAULT TRUE;

-- 2. Update user yang sudah ada agar tidak perlu ganti pass (opsional)
UPDATE users SET perlu_ganti_pass = FALSE;

-- 3. Sesuaikan Enum Jobdesk (Menambah Revision)
ALTER TABLE jobdesk MODIFY COLUMN status ENUM('Pending', 'Process', 'Revision', 'Done') DEFAULT 'Pending';

-- 4. Sesuaikan Enum Event (Menambah Upcoming & Archived jika mau)
ALTER TABLE events MODIFY COLUMN status ENUM('upcoming', 'active', 'completed', 'cancelled', 'archived') DEFAULT 'upcoming';

-- Masukkan divisi 'BPH' untuk semua event yang sudah ada
INSERT INTO divisi (event_id, nama_divisi)
SELECT event_id, 'Badan Pengurus Harian' FROM events;

-- Update jumlah divisi di tabel events agar sinkron
UPDATE events e 
SET jumlah_divisi = (SELECT COUNT(*) FROM divisi d WHERE d.event_id = e.event_id);

INSERT INTO users (nama_lengkap, email, password, peran, perlu_ganti_pass) VALUES
('Dimas Anggara', 'ketua_tf@kampus.id', MD5('mhs123'), 'anggota', 0),
('Siti Aminah', 'sekre_tf@kampus.id', MD5('mhs123'), 'anggota', 0),
('Budi Santoso', 'bendahara_tf@kampus.id', MD5('mhs123'), 'anggota', 0),

('Raka Pratama', 'koor_acara@kampus.id', MD5('mhs123'), 'anggota', 0),
('Dinda Kirana', 'staff_acara1@kampus.id', MD5('mhs123'), 'anggota', 0),
('Eko Prasetyo', 'staff_acara2@kampus.id', MD5('mhs123'), 'anggota', 0),

('Fara Quinn', 'koor_humas@kampus.id', MD5('mhs123'), 'anggota', 0),
('Gilang Dirga', 'staff_humas1@kampus.id', MD5('mhs123'), 'anggota', 0),
('Hesti Purwadinata', 'staff_humas2@kampus.id', MD5('mhs123'), 'anggota', 0),

('Indra Bekti', 'koor_perkap@kampus.id', MD5('mhs123'), 'anggota', 0),
('Joko Anwar', 'staff_perkap1@kampus.id', MD5('mhs123'), 'anggota', 0),
('Kiki Saputri', 'staff_perkap2@kampus.id', MD5('mhs123'), 'anggota', 0),

('Luna Maya', 'koor_dokum@kampus.id', MD5('mhs123'), 'anggota', 0),
('Maudy Ayunda', 'staff_dokum1@kampus.id', MD5('mhs123'), 'anggota', 0);


INSERT INTO events (nama_event, deskripsi, tanggal_mulai, tanggal_selesai, status, jumlah_divisi) 
VALUES ('INSPIRE 2025', 'Festival Teknologi Terbesar di Kampus', '2025-05-20', '2025-05-22', 'active', 5);

SET @id_event = LAST_INSERT_ID();


INSERT INTO divisi (event_id, nama_divisi) VALUES 
(@id_event, 'BPH'),
(@id_event, 'Acara'),
(@id_event, 'Humas'),
(@id_event, 'Perlengkapan'),
(@id_event, 'Dokumentasi');

SET @id_bph = (SELECT divisi_id FROM divisi WHERE event_id = @id_event AND nama_divisi = 'BPH');
SET @id_acara = (SELECT divisi_id FROM divisi WHERE event_id = @id_event AND nama_divisi = 'Acara');
SET @id_humas = (SELECT divisi_id FROM divisi WHERE event_id = @id_event AND nama_divisi = 'Humas');
SET @id_perkap = (SELECT divisi_id FROM divisi WHERE event_id = @id_event AND nama_divisi = 'Perlengkapan');
SET @id_dokum = (SELECT divisi_id FROM divisi WHERE event_id = @id_event AND nama_divisi = 'Dokumentasi');


INSERT INTO anggota_divisi (user_id, divisi_id, jabatan) VALUES
((SELECT user_id FROM users WHERE email='ketua_tf@kampus.id'), @id_bph, 'Ketua'),
((SELECT user_id FROM users WHERE email='sekre_tf@kampus.id'), @id_bph, 'Sekretaris'),
((SELECT user_id FROM users WHERE email='bendahara_tf@kampus.id'), @id_bph, 'Bendahara'),

((SELECT user_id FROM users WHERE email='koor_acara@kampus.id'), @id_acara, 'Koordinator'),
((SELECT user_id FROM users WHERE email='staff_acara1@kampus.id'), @id_acara, 'Staff'),
((SELECT user_id FROM users WHERE email='staff_acara2@kampus.id'), @id_acara, 'Staff'),

((SELECT user_id FROM users WHERE email='koor_humas@kampus.id'), @id_humas, 'Koordinator'),
((SELECT user_id FROM users WHERE email='staff_humas1@kampus.id'), @id_humas, 'Staff'),
((SELECT user_id FROM users WHERE email='staff_humas2@kampus.id'), @id_humas, 'Staff'),

((SELECT user_id FROM users WHERE email='koor_perkap@kampus.id'), @id_perkap, 'Koordinator'),
((SELECT user_id FROM users WHERE email='staff_perkap1@kampus.id'), @id_perkap, 'Staff'),
((SELECT user_id FROM users WHERE email='staff_perkap2@kampus.id'), @id_perkap, 'Staff'),

((SELECT user_id FROM users WHERE email='koor_dokum@kampus.id'), @id_dokum, 'Koordinator'),
((SELECT user_id FROM users WHERE email='staff_dokum1@kampus.id'), @id_dokum, 'Staff');


INSERT INTO jobdesk (divisi_id, user_id, nama_tugas, deskripsi, deadline, status) VALUES
(@id_bph, (SELECT user_id FROM users WHERE email='ketua_tf@kampus.id'), 'Memimpin Rapat Perdana', 'Koordinasi seluruh koordinator', '2025-02-01', 'Done'),
(@id_acara, (SELECT user_id FROM users WHERE email='koor_acara@kampus.id'), 'Menyusun Rundown Acara', 'Draft 1 harus selesai minggu ini', '2025-02-10', 'Process'),
(@id_humas, (SELECT user_id FROM users WHERE email='staff_humas1@kampus.id'), 'Menghubungi Media Partner', 'List media lokal dan nasional', '2025-02-15', 'Pending'),
(@id_perkap, NULL, 'Survey Panggung', 'Cari vendor panggung yang murah', '2025-02-20', 'Pending');