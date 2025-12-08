<?php
session_start();
require '../config/koneksi.php'; 

// Cek Login
if (!isset($_SESSION['role'])) {
    header("Location: ../index.php");
    exit;
}

if (isset($_POST['simpan'])) {
    // Cek Koneksi $conn dari koneksi.php
    if (!isset($conn)) {
        die("Error: Variabel koneksi (\$conn) tidak ditemukan.");
    }

    // Tangkap data dari form
    $nama_event = mysqli_real_escape_string($conn, $_POST['nama_event']);
    $deskripsi  = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    
    // Nama variabel PHP ini diambil dari name="" di form HTML
    $tgl_mulai_input  = $_POST['tgl_mulai']; 
    $tgl_selesai_input= $_POST['tgl_selesai'];
    
    $status = 'active'; // Sesuaikan dengan ENUM di database ('pending', 'active', etc)
    $jumlah_divisi = 0;

    // PERBAIKAN PENTING DISINI:
    // Kolom database: tanggal_mulai, tanggal_selesai (Sesuai file sql)
    // Nilai yg dimasukkan: $tgl_mulai_input, $tgl_selesai_input (Sesuai form)
    $query = "INSERT INTO events (nama_event, deskripsi, tanggal_mulai, tanggal_selesai, status, jumlah_divisi) 
              VALUES ('$nama_event', '$deskripsi', '$tgl_mulai_input', '$tgl_selesai_input', '$status', '$jumlah_divisi')";

    if (mysqli_query($conn, $query)) {
        // Ambil ID Event yang baru saja dibuat
        $last_id = mysqli_insert_id($conn);

        // OTOMATIS BUAT DIVISI 'BPH'
        $queryBPH = "INSERT INTO divisi (event_id, nama_divisi) VALUES ('$last_id', 'BPH')";
        mysqli_query($conn, $queryBPH);

        // Update jumlah divisi jadi 1
        mysqli_query($conn, "UPDATE events SET jumlah_divisi = 1 WHERE event_id = '$last_id'");

        echo "<script>
                alert('Event berhasil ditambahkan! Divisi Badan Pengurus Harian otomatis dibuat. ✨');
                window.location='data_event.php';
              </script>";
    } else {
        echo "<script>
                alert('Gagal menyimpan event: " . mysqli_error($conn) . "');
              </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Event</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header"><h2 class="brand-title">E-PANITIA</h2></div>
            <nav>
                <a href="data_event.php" class="menu-item active"><i class="ph-bold ph-arrow-left"></i> Kembali</a>
            </nav>
        </aside>

        <main class="main-content">
            <h1 style="margin-bottom: 20px;">Buat Event Baru ✨</h1>
            
            <div class="login-card" style="margin: 0; text-align: left; max-width: 600px;">
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Nama Event</label>
                        <input type="text" name="nama_event" class="form-control" required placeholder="Contoh: Dies Natalis 2025">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Deskripsi Singkat</label>
                        <textarea name="deskripsi" class="form-control" rows="3" placeholder="Jelaskan detail event..."></textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" name="tgl_mulai" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tanggal Selesai</label>
                            <input type="date" name="tgl_selesai" class="form-control" required>
                        </div>
                    </div>

                    <button type="submit" name="simpan" class="btn-login" style="margin-top: 20px;">
                        SIMPAN EVENT
                    </button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>