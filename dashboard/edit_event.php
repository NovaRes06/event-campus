<?php
session_start();
require '../config/koneksi.php';

// Cek Keamanan
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'ketua')) {
    header("Location: ../index.php"); exit;
}

// 1. Ambil ID Event
$id_event = $_GET['id'];
if (!$id_event) { header("Location: data_event.php"); exit; }

// --- LOGIKA BACKEND (CRUD) ---

// A. Update Info Utama Event
if (isset($_POST['update_event'])) {
    $nama  = mysqli_real_escape_string($conn, $_POST['nama_event']);
    $desk  = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $mulai = $_POST['tgl_mulai'];
    $selesai = $_POST['tgl_selesai'];
    $status = $_POST['status'];

    mysqli_query($conn, "UPDATE events SET nama_event='$nama', deskripsi='$desk', tanggal_mulai='$mulai', tanggal_selesai='$selesai', status='$status' WHERE event_id='$id_event'");
    echo "<script>alert('Info Event berhasil diperbarui!');</script>";
}

// B. Tambah Divisi Baru
if (isset($_POST['tambah_divisi'])) {
    $nama_divisi = mysqli_real_escape_string($conn, $_POST['nama_divisi']);
    mysqli_query($conn, "INSERT INTO divisi (event_id, nama_divisi) VALUES ('$id_event', '$nama_divisi')");
    // Update jumlah divisi di table events
    mysqli_query($conn, "UPDATE events SET jumlah_divisi = (SELECT COUNT(*) FROM divisi WHERE event_id='$id_event') WHERE event_id='$id_event'");
    echo "<script>window.location='edit_event.php?id=$id_event';</script>";
}

// C. Hapus Divisi
if (isset($_GET['hapus_divisi'])) {
    $id_div = $_GET['hapus_divisi'];
    mysqli_query($conn, "DELETE FROM divisi WHERE divisi_id='$id_div'");
    // Update jumlah divisi
    mysqli_query($conn, "UPDATE events SET jumlah_divisi = (SELECT COUNT(*) FROM divisi WHERE event_id='$id_event') WHERE event_id='$id_event'");
    echo "<script>window.location='edit_event.php?id=$id_event';</script>";
}

// D. Plotting Anggota (Tambah Anggota ke Divisi)
if (isset($_POST['tambah_anggota'])) {
    $user_id   = $_POST['user_id'];
    $divisi_id = $_POST['divisi_id'];
    $jabatan   = $_POST['jabatan'];

    // Cek duplikasi
    $cek = mysqli_query($conn, "SELECT * FROM anggota_divisi WHERE user_id='$user_id' AND divisi_id='$divisi_id'");
    if(mysqli_num_rows($cek) == 0){
        mysqli_query($conn, "INSERT INTO anggota_divisi (user_id, divisi_id, jabatan) VALUES ('$user_id', '$divisi_id', '$jabatan')");
    } else {
        echo "<script>alert('User tersebut sudah ada di divisi ini!');</script>";
    }
}

// E. Hapus Anggota dari Divisi
if (isset($_GET['hapus_anggota'])) {
    $id_ad = $_GET['hapus_anggota'];
    mysqli_query($conn, "DELETE FROM anggota_divisi WHERE anggota_id='$id_ad'");
    echo "<script>window.location='edit_event.php?id=$id_event';</script>";
}

// --- AMBIL DATA UNTUK DITAMPILKAN ---
$query_event = mysqli_query($conn, "SELECT * FROM events WHERE event_id='$id_event'");
$data = mysqli_fetch_assoc($query_event);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Event - <?= $data['nama_event'] ?></title>
    <link rel="stylesheet" href="../assets/css/style.css?v=115">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        .section-title { font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 15px; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; }
        .grid-layout { display: grid; grid-template-columns: 1fr 1.5fr; gap: 30px; align-items: start; }
        @media (max-width: 900px) { .grid-layout { grid-template-columns: 1fr; } }
        
        .mini-table { width: 100%; border-collapse: collapse; }
        .mini-table th { background: #f8fafc; padding: 10px; font-size: 12px; text-align: left; }
        .mini-table td { padding: 10px; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
        
        /* Fix Background */
        .bg-blob { pointer-events: none !important; z-index: 0 !important; }
        .dashboard-container { position: relative; z-index: 10 !important; }
    </style>
</head>
<body>

    <div class="bg-blob blob-2"></div>
    <div class="bg-blob blob-3"></div>

    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header"><h2 class="brand-title">E-PANITIA</h2></div>
            <nav>
                <a href="data_event.php" class="menu-item active"><i class="ph-bold ph-arrow-left"></i> Kembali</a>
            </nav>
        </aside>

        <main class="main-content">
            <h1 style="margin-bottom: 20px;">Control Center: <?= $data['nama_event'] ?> ⚙️</h1>
            
            <div class="grid-layout">
                <div class="login-card" style="margin: 0; text-align: left; width: 100%; max-width: 100%;">
                    <div class="section-title">1. Informasi Utama</div>
                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label">Nama Event</label>
                            <input type="text" name="nama_event" class="form-control" required value="<?= $data['nama_event'] ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" rows="3"><?= $data['deskripsi'] ?></textarea>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <div class="form-group">
                                <label class="form-label">Tgl Mulai</label>
                                <input type="date" name="tgl_mulai" class="form-control" value="<?= $data['tanggal_mulai'] ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Tgl Selesai</label>
                                <input type="date" name="tgl_selesai" class="form-control" value="<?= $data['tanggal_selesai'] ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control">
                                <option value="pending" <?= $data['status']=='pending'?'selected':'' ?>>Pending</option>
                                <option value="active" <?= $data['status']=='active'?'selected':'' ?>>Active</option>
                                <option value="completed" <?= $data['status']=='completed'?'selected':'' ?>>Completed</option>
                                <option value="cancelled" <?= $data['status']=='cancelled'?'selected':'' ?>>Cancelled</option>
                            </select>
                        </div>
                        <button type="submit" name="update_event" class="btn-login" style="padding: 12px;">Simpan Perubahan Info</button>
                    </form>
                </div>

                <div style="display: flex; flex-direction: column; gap: 30px;">
                    
                    <div class="content-card" style="padding: 25px;">
                        <div class="section-title">2. Manajemen Divisi</div>
                        
                        <form method="POST" style="display: flex; gap: 10px; margin-bottom: 20px;">
                            <input type="text" name="nama_divisi" class="form-control" placeholder="Nama Divisi Baru..." required>
                            <button type="submit" name="tambah_divisi" class="btn-kelola" style="border:none;">+ Tambah</button>
                        </form>

                        <table class="mini-table">
                            <thead><tr><th>Nama Divisi</th><th width="50">Aksi</th></tr></thead>
                            <tbody>
                                <?php
                                $q_div = mysqli_query($conn, "SELECT * FROM divisi WHERE event_id='$id_event'");
                                if(mysqli_num_rows($q_div) == 0) echo "<tr><td colspan='2'>Belum ada divisi.</td></tr>";
                                while($row_d = mysqli_fetch_assoc($q_div)):
                                ?>
                                <tr>
                                    <td><?= $row_d['nama_divisi'] ?></td>
                                    <td>
                                        <a href="edit_event.php?id=<?= $id_event ?>&hapus_divisi=<?= $row_d['divisi_id'] ?>" onclick="return confirm('Hapus divisi ini? Semua tugas di dalamnya akan hilang.')" style="color: #ef4444;"><i class="ph-bold ph-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="content-card" style="padding: 25px;">
                        <div class="section-title">3. Plotting Anggota Panitia</div>
                        
                        <form method="POST" style="background: #f8fafc; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                            <div style="display: grid; grid-template-columns: 1.5fr 1fr 1fr; gap: 10px; align-items: end;">
                                <div>
                                    <label class="form-label">Pilih Anggota</label>
                                    <select name="user_id" class="form-control" required>
                                        <?php
                                        // Ambil semua user kecuali admin
                                        $users = mysqli_query($conn, "SELECT * FROM users WHERE peran != 'admin' ORDER BY nama_lengkap ASC");
                                        while($u = mysqli_fetch_assoc($users)):
                                        ?>
                                        <option value="<?= $u['user_id'] ?>"><?= $u['nama_lengkap'] ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">Masuk Divisi</label>
                                    <select name="divisi_id" class="form-control" required>
                                        <?php
                                        // Reset pointer query divisi sebelumnya
                                        mysqli_data_seek($q_div, 0); 
                                        while($d = mysqli_fetch_assoc($q_div)):
                                        ?>
                                        <option value="<?= $d['divisi_id'] ?>"><?= $d['nama_divisi'] ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">Jabatan</label>
                                    <select name="jabatan" class="form-control">
                                        <option value="Staff">Staff</option>
                                        <option value="Koordinator">Koordinator</option>
                                        <option value="Sekretaris">Sekretaris</option>
                                        <option value="Bendahara">Bendahara</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" name="tambah_anggota" class="btn-login" style="margin-top: 15px; padding: 10px; font-size: 14px;">+ Masukkan Anggota</button>
                        </form>

                        <table class="mini-table">
                            <thead><tr><th>Nama</th><th>Divisi</th><th>Jabatan</th><th>Aksi</th></tr></thead>
                            <tbody>
                                <?php
                                $q_plot = mysqli_query($conn, "
                                    SELECT ad.anggota_id, u.nama_lengkap, d.nama_divisi, ad.jabatan 
                                    FROM anggota_divisi ad
                                    JOIN users u ON ad.user_id = u.user_id
                                    JOIN divisi d ON ad.divisi_id = d.divisi_id
                                    WHERE d.event_id = '$id_event'
                                    ORDER BY d.nama_divisi ASC
                                ");
                                if(mysqli_num_rows($q_plot) == 0) echo "<tr><td colspan='4'>Belum ada anggota diplot.</td></tr>";
                                while($plot = mysqli_fetch_assoc($q_plot)):
                                ?>
                                <tr>
                                    <td><?= $plot['nama_lengkap'] ?></td>
                                    <td><span class="badge-purple"><?= $plot['nama_divisi'] ?></span></td>
                                    <td><?= $plot['jabatan'] ?></td>
                                    <td>
                                        <a href="edit_event.php?id=<?= $id_event ?>&hapus_anggota=<?= $plot['anggota_id'] ?>" onclick="return confirm('Keluarkan anggota ini?')" style="color: #ef4444;"><i class="ph-bold ph-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </main>
    </div>
</body>
</html>