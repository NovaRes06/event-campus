<?php
session_start();
require '../config/koneksi.php';

// Cek Login Dasar
if (!isset($_SESSION['role'])) { header("Location: ../index.php"); exit; }

$id_user = $_SESSION['user_id'];
$id_event = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_event === 0) { header("Location: data_event.php"); exit; }

// --- LOGIKA HAK AKSES ---
$is_admin_global = ($_SESSION['role'] == 'admin');
$is_ketua_lokal = false;

$qCekKetua = mysqli_query($conn, "
    SELECT ad.jabatan 
    FROM anggota_divisi ad
    JOIN divisi d ON ad.divisi_id = d.divisi_id
    WHERE d.event_id = '$id_event' AND ad.user_id = '$id_user' AND ad.jabatan = 'Ketua'
");
if (mysqli_num_rows($qCekKetua) > 0) $is_ketua_lokal = true;

if (!$is_admin_global && !$is_ketua_lokal) {
    echo "<script>alert('Akses Ditolak!'); window.location='anggota.php';</script>";
    exit;
}

// --- LOGIKA BACKEND (CRUD) ---

// A. Update Info Utama
if (isset($_POST['update_event'])) {
    $nama  = mysqli_real_escape_string($conn, $_POST['nama_event']);
    $desk  = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $mulai = $_POST['tgl_mulai'];
    $selesai = $_POST['tgl_selesai'];
    $status = $_POST['status'];

    mysqli_query($conn, "UPDATE events SET nama_event='$nama', deskripsi='$desk', tanggal_mulai='$mulai', tanggal_selesai='$selesai', status='$status' WHERE event_id='$id_event'");
    echo "<script>alert('Info Event berhasil diperbarui!');</script>";
}

// B. Tambah Divisi
if (isset($_POST['tambah_divisi'])) {
    $nama_divisi = mysqli_real_escape_string($conn, $_POST['nama_divisi']);
    mysqli_query($conn, "INSERT INTO divisi (event_id, nama_divisi) VALUES ('$id_event', '$nama_divisi')");
    mysqli_query($conn, "UPDATE events SET jumlah_divisi = (SELECT COUNT(*) FROM divisi WHERE event_id='$id_event') WHERE event_id='$id_event'");
    echo "<script>window.location='edit_event.php?id=$id_event';</script>";
}

// C. Hapus Divisi
if (isset($_GET['hapus_divisi'])) {
    $id_div = $_GET['hapus_divisi'];
    mysqli_query($conn, "DELETE FROM divisi WHERE divisi_id='$id_div'");
    mysqli_query($conn, "UPDATE events SET jumlah_divisi = (SELECT COUNT(*) FROM divisi WHERE event_id='$id_event') WHERE event_id='$id_event'");
    echo "<script>window.location='edit_event.php?id=$id_event';</script>";
}

// D. Plotting Anggota (DENGAN VALIDASI DOUBLE JOB)
if (isset($_POST['tambah_anggota'])) {
    $user_id   = $_POST['user_id'];
    $divisi_id = $_POST['divisi_id']; 
    $jabatan   = $_POST['jabatan'];

    // 1. CEK APAKAH USER SUDAH TERDAFTAR DI EVENT INI (Lintas Divisi)
    $stmtCek = $conn->prepare("
        SELECT d.nama_divisi, ad.jabatan 
        FROM anggota_divisi ad
        JOIN divisi d ON ad.divisi_id = d.divisi_id
        WHERE ad.user_id = ? AND d.event_id = ?
    ");
    $stmtCek->bind_param("ii", $user_id, $id_event);
    $stmtCek->execute();
    $resDouble = $stmtCek->get_result();

    if ($resDouble->num_rows > 0) {
        $existing = $resDouble->fetch_assoc();
        echo "<script>alert('GAGAL: User ini sudah terdaftar sebagai {$existing['jabatan']} di Divisi {$existing['nama_divisi']}. Satu orang hanya boleh satu jabatan per event!');</script>";
    } else {
        // 2. LOGIC BPH: Jika Ketua/Wakil, Paksa Masuk Divisi BPH
        if ($jabatan == 'Ketua' || $jabatan == 'Wakil Ketua') {
            $qBPH = mysqli_query($conn, "SELECT divisi_id FROM divisi WHERE event_id='$id_event' AND nama_divisi='BPH'");
            $rowBPH = mysqli_fetch_assoc($qBPH);
            $divisi_id = $rowBPH['divisi_id']; 
        }

        $stmtIns = $conn->prepare("INSERT INTO anggota_divisi (user_id, divisi_id, jabatan) VALUES (?, ?, ?)");
        $stmtIns->bind_param("iis", $user_id, $divisi_id, $jabatan);
        $stmtIns->execute();
        echo "<script>window.location='edit_event.php?id=$id_event&msg=member_added';</script>";
    }
}

// E. Hapus Anggota
if (isset($_GET['hapus_anggota'])) {
    $id_ad = $_GET['hapus_anggota'];
    mysqli_query($conn, "DELETE FROM anggota_divisi WHERE anggota_id='$id_ad'");
    echo "<script>window.location='edit_event.php?id=$id_event';</script>";
}

// --- AMBIL DATA ---
// --- GANTIKAN BLOK INI ---
// --- AMBIL DATA EVENT & ROLE USER (Sama seperti detail_event agar Sidebar sinkron) ---
$qCek = mysqli_query($conn, "
    SELECT e.*, d.divisi_id, d.nama_divisi, ad.jabatan 
    FROM events e
    JOIN divisi d ON e.event_id = d.event_id
    LEFT JOIN anggota_divisi ad ON d.divisi_id = ad.divisi_id AND ad.user_id = '$id_user'
    WHERE e.event_id = '$id_event'
");

$info = null;
$my_divisi = null;
$my_jabatan = null;
$my_divisi_name = "";

while ($row = mysqli_fetch_assoc($qCek)) {
    if ($info === null) $info = $row; 
    if ($row['jabatan'] != null) {
        $my_divisi = $row['divisi_id'];
        $my_jabatan = $row['jabatan'];
        $my_divisi_name = $row['nama_divisi'];
    }
}
// -------------------------
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Event - <?= htmlspecialchars($info['nama_event']) ?></title>
    <link rel="stylesheet" href="../assets/css/style.css?v=115">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body>

    <div class="bg-blob blob-1"></div>
    <div class="bg-blob blob-3"></div>

    <div class="dashboard-container">
        <?php include 'sidebar_common.php'; ?>

        <main class="main-content">
            <h1 style="margin-bottom: 20px;">Control Center: <?= $info['nama_event'] ?> ⚙️</h1>
            
            <div class="grid-layout">
                <div class="login-card" style="margin: 0; text-align: left; width: 100%; max-width: 100%;">
                    <div class="section-title">1. Informasi Utama</div>
                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label">Nama Event</label>
                            <input type="text" name="nama_event" class="form-control" required value="<?= $info['nama_event'] ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" rows="3"><?= $info['deskripsi'] ?></textarea>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <div class="form-group"><label class="form-label">Tgl Mulai</label><input type="date" name="tgl_mulai" class="form-control" value="<?= $info['tanggal_mulai'] ?>"></div>
                            <div class="form-group"><label class="form-label">Tgl Selesai</label><input type="date" name="tgl_selesai" class="form-control" value="<?= $info['tanggal_selesai'] ?>"></div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control">
                                <option value="upcoming" <?= $info['status']=='upcoming'?'selected':'' ?>>Upcoming</option>
                                <option value="active" <?= $info['status']=='active'?'selected':'' ?>>Active</option>
                                <option value="completed" <?= $info['status']=='completed'?'selected':'' ?>>Completed</option>
                                <option value="cancelled" <?= $info['status']=='cancelled'?'selected':'' ?>>Cancelled</option>
                                <option value="archived" <?= $info['status']=='archived'?'selected':'' ?>>Archived</option>
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
                                $id_bph_js = ""; 
                                
                                if(mysqli_num_rows($q_div) > 0) {
                                    mysqli_data_seek($q_div, 0);
                                } else {
                                    echo "<tr><td colspan='2'>Belum ada divisi.</td></tr>";
                                }
                                while($row_d = mysqli_fetch_assoc($q_div)):
                                    if($row_d['nama_divisi'] == 'BPH') $id_bph_js = $row_d['divisi_id'];
                                ?>
                                <tr>
                                    <td>
                                        <?= $row_d['nama_divisi'] ?>
                                        <?php if($row_d['nama_divisi'] == 'BPH') echo '<span style="font-size:10px; background:#fcd34d; padding:2px 5px; border-radius:4px; margin-left:5px; font-weight:bold;">INTI</span>'; ?>
                                    </td>
                                    <td>
                                        <?php if($row_d['nama_divisi'] != 'BPH'): ?>
                                            <a href="edit_event.php?id=<?= $id_event ?>&hapus_divisi=<?= $row_d['divisi_id'] ?>" onclick="return confirm('Hapus divisi?')" style="color: #ef4444;"><i class="ph-bold ph-trash"></i></a>
                                        <?php endif; ?>
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
                                        // Menampilkan Nama (Email) di dropdown
                                        $users = mysqli_query($conn, "SELECT * FROM users WHERE peran != 'admin' ORDER BY nama_lengkap ASC");
                                        while($u = mysqli_fetch_assoc($users)):
                                        ?>
                                        <option value="<?= $u['user_id'] ?>">
                                            <?= htmlspecialchars($u['nama_lengkap']) ?> 
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">Masuk Divisi</label>
                                    <select name="divisi_id" id="selectDivisi" class="form-control" required>
                                        <?php
                                        mysqli_data_seek($q_div, 0); 
                                        while($d = mysqli_fetch_assoc($q_div)):
                                            $isBPH = ($d['nama_divisi'] == 'BPH');
                                        ?>
                                        <option value="<?= $d['divisi_id'] ?>" class="<?= $isBPH?'opt-bph':'' ?>">
                                            <?= $d['nama_divisi'] ?>
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">Jabatan</label>
                                    <select name="jabatan" id="selectJabatan" class="form-control" onchange="autoSelectBPH()">
                                        <option value="Staff">Staff</option>
                                        <option value="Koordinator">Koordinator</option>
                                        <option value="Sekretaris">Sekretaris</option>
                                        <option value="Bendahara">Bendahara</option>
                                        <option value="Wakil Ketua" style="font-weight:bold; color:#d97706;">Wakil Ketua</option>
                                        <option value="Ketua" style="font-weight:bold; color:#d97706;">Ketua</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" name="tambah_anggota" class="btn-login" style="margin-top: 15px; padding: 10px; font-size: 14px;">+ Masukkan Anggota</button>
                        </form>

                        <table class="mini-table">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Email</th> <th>Divisi</th>
                                    <th>Jabatan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Tambahkan u.email pada query SELECT
                                $q_plot = mysqli_query($conn, "
                                    SELECT ad.anggota_id, u.nama_lengkap, u.email, d.nama_divisi, ad.jabatan 
                                    FROM anggota_divisi ad
                                    JOIN users u ON ad.user_id = u.user_id
                                    JOIN divisi d ON ad.divisi_id = d.divisi_id
                                    WHERE d.event_id = '$id_event'
                                    ORDER BY FIELD(ad.jabatan, 'Ketua', 'Wakil Ketua', 'Sekretaris', 'Bendahara', 'Koordinator', 'Staff'), d.nama_divisi ASC
                                ");
                                if(mysqli_num_rows($q_plot) == 0) echo "<tr><td colspan='5'>Belum ada anggota diplot.</td></tr>";
                                while($plot = mysqli_fetch_assoc($q_plot)):
                                ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($plot['nama_lengkap']) ?></strong></td>
                                    <td style="color: #64748b; font-size: 12px;"><?= htmlspecialchars($plot['email']) ?></td> <td><span class="badge-purple"><?= $plot['nama_divisi'] ?></span></td>
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

    <script>
        function autoSelectBPH() {
            const jabatansInti = ['Ketua', 'Wakil Ketua', 'Sekretaris', 'Bendahara'];
            const jabatan = document.getElementById('selectJabatan').value;
            const selectDivisi = document.getElementById('selectDivisi');
            const idBPH = "<?= $id_bph_js ?>"; 

            if (jabatansInti.includes(jabatan)) {
                if (idBPH) selectDivisi.value = idBPH;
            }
        }
    </script>

</body>
</html>