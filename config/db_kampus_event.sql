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

SHOW TABLES;