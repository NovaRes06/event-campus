<?php
session_start();
include '../config/koneksi.php';

// Cek Role Admin
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'ketua')) {
    header("Location: ../index.php"); exit;
}

// Menangkap Event ID yang dipilih untuk filtering divisi
$selected_evt = isset($_GET['evt_id']) ? $_GET['evt_id'] : '';

// --- LOGIC: SIMPAN NOTULENSI ---
if (isset($_POST['simpan_notulen'])) {
    $event_id = $_POST['event_id'];
    $divisi_id = !empty($_POST['divisi_id']) ? "'".$_POST['divisi_id']."'" : "NULL"; // Handle NULL if Rapat Umum
    $judul = mysqli_real_escape_string($conn, $_POST['judul_notulen']);
    $isi = mysqli_real_escape_string($conn, $_POST['isi_pembahasan']);
    $tgl_rapat = $_POST['tanggal_rapat'] . ' ' . $_POST['jam_rapat'] . ':00';
    $jenis = $_POST['jenis_rapat'];

    $query = "INSERT INTO notulensi (event_id, divisi_id, judul_notulen, isi_pembahasan, tanggal_rapat, jenis_rapat) 
              VALUES ('$event_id', $divisi_id, '$judul', '$isi', '$tgl_rapat', '$jenis')";
    
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Notulensi Rapat berhasil disimpan!'); window.location='data_laporan.php';</script>";
    } else {
        echo "<script>alert('Gagal menyimpan notulensi! Error: " . mysqli_error($conn) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buat Notulensi Global</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=111">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        .bg-blob { pointer-events: none !important; z-index: 0 !important; }
        .dashboard-container { position: relative; z-index: 10 !important; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2 class="brand-title">E-PANITIA</h2>
                <span class="role-badge">Administrator</span>
            </div>
            <nav>
                <a href="data_laporan.php" class="btn-kelola" style="margin-bottom: 20px;">
                    <i class="ph-bold ph-arrow-left"></i> Kembali ke Arsip
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <h1 style="margin-bottom: 20px;">Catat Hasil Rapat (Admin) 📝</h1>
            
            <div class="login-card" style="margin: 0 auto; text-align: left; width: 95%; max-width: 1100px;">
                <form method="POST">
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        
                        <div class="form-group">
                            <label class="form-label">Event Terkait</label>
                            <select name="event_id" class="form-control" required onchange="window.location.href='tambah_notulensi.php?evt_id='+this.value">
                                <option value="">-- Pilih Event Dahulu --</option>
                                <?php
                                $events = mysqli_query($conn, "SELECT event_id, nama_event FROM events WHERE status IN ('active', 'pending') ORDER BY nama_event");
                                while($evt = mysqli_fetch_assoc($events)):
                                    $selected = ($selected_evt == $evt['event_id']) ? 'selected' : '';
                                ?>
                                <option value="<?= $evt['event_id'] ?>" <?= $selected ?>><?= $evt['nama_event'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Divisi Penyelenggara</label>
                            <select name="divisi_id" class="form-control" <?= empty($selected_evt) ? 'disabled' : '' ?>>
                                <option value="">-- Rapat Umum / Semua Divisi --</option>
                                <?php
                                if (!empty($selected_evt)) {
                                    // Query Divisi HANYA untuk event yang dipilih
                                    $divs = mysqli_query($conn, "SELECT divisi_id, nama_divisi FROM divisi WHERE event_id='$selected_evt' ORDER BY nama_divisi");
                                    while($div = mysqli_fetch_assoc($divs)):
                                ?>
                                <option value="<?= $div['divisi_id'] ?>"><?= $div['nama_divisi'] ?></option>
                                <?php 
                                    endwhile; 
                                }
                                ?>
                            </select>
                            <?php if(empty($selected_evt)): ?>
                                <small style="color: #ef4444;">*Pilih event dulu untuk memunculkan divisi.</small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Judul Notulensi</label>
                        <input type="text" name="judul_notulen" class="form-control" required placeholder="Contoh: Rapat Koordinasi Pembukaan Event">
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label class="form-label">Tanggal Rapat</label>
                            <input type="date" name="tanggal_rapat" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Waktu (Jam)</label>
                            <input type="time" name="jam_rapat" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Jenis Rapat</label>
                            <select name="jenis_rapat" class="form-control" required>
                                <option value="Rapat Umum">Rapat Umum</option>
                                <option value="Rapat Divisi">Rapat Divisi</option>
                                <option value="Rapat Koordinasi">Rapat Koordinasi</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Isi Pembahasan / Keputusan</label>
                        <textarea name="isi_pembahasan" class="form-control" rows="8" required placeholder="Tuliskan poin-poin keputusan dan hal yang dibahas..."></textarea>
                    </div>

                    <button type="submit" name="simpan_notulen" class="btn-login" style="margin-top: 20px;">
                        SIMPAN NOTULENSI
                    </button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>