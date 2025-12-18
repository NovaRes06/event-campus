<?php
session_start();
require '../config/koneksi.php';

if (!isset($_SESSION['role'])) { header("Location: ../index.php"); exit; }

$id_user = $_SESSION['user_id'];
$id_event = isset($_GET['id']) ? $_GET['id'] : '';
$my_role_global = $_SESSION['role']; 

// 1. Ambil Info Event & Role User
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

// Cek Hak Akses
$is_super_admin = ($my_role_global == 'admin');
$is_ketua_event = ($my_jabatan == 'Ketua');
$is_koordinator = ($my_jabatan == 'Koordinator');
$is_sekretaris  = ($my_jabatan == 'Sekretaris');

if (!$is_super_admin && $my_jabatan == null) {
    echo "<script>alert('Akses ditolak! Kamu bukan panitia event ini.'); window.location='anggota.php';</script>";
    exit;
}

$view_all_divisions = ($is_super_admin || $is_ketua_event);


// --- LOGIC CRUD JOBDESK (BARU & UPDATE) ---

// 1. TAMBAH JOBDESK (BARU)
if (isset($_POST['tambah_jobdesk'])) {
    $divisi_id = $_POST['divisi_id'];
    $nama_tugas = mysqli_real_escape_string($conn, $_POST['nama_tugas']);
    $deadline = $_POST['deadline'];
    
    // Validasi: Ketua/Admin boleh semua, Koor hanya divisinya
    $allow = false;
    if ($view_all_divisions) $allow = true;
    elseif ($is_koordinator && $divisi_id == $my_divisi) $allow = true;

    if ($allow) {
        mysqli_query($conn, "INSERT INTO jobdesk (divisi_id, nama_tugas, deadline, status) VALUES ('$divisi_id', '$nama_tugas', '$deadline', 'Pending')");
        echo "<script>window.location='detail_event.php?id=$id_event&msg=job_added';</script>";
    } else {
        echo "<script>alert('Anda tidak berhak menambah tugas di divisi ini.');</script>";
    }
}

// 2. EDIT JOBDESK UTAMA (Nama & Deadline) (BARU)
if (isset($_POST['edit_jobdesk_full'])) {
    $jid = $_POST['job_id'];
    $nama_tugas = mysqli_real_escape_string($conn, $_POST['nama_tugas']);
    $deadline = $_POST['deadline'];
    
    mysqli_query($conn, "UPDATE jobdesk SET nama_tugas='$nama_tugas', deadline='$deadline' WHERE jobdesk_id='$jid'");
    echo "<script>window.location='detail_event.php?id=$id_event&msg=job_edited';</script>";
}

// 3. HAPUS JOBDESK (BARU)
if (isset($_GET['hapus_jobdesk'])) {
    $jid = $_GET['hapus_jobdesk'];
    mysqli_query($conn, "DELETE FROM jobdesk WHERE jobdesk_id='$jid'");
    echo "<script>window.location='detail_event.php?id=$id_event&msg=job_deleted';</script>";
}

// 4. UPDATE STATUS/PIC (LAMA - Tetap dipertahankan)
if (isset($_POST['update_jobdesk'])) {
    $jid = $_POST['job_id'];
    $stat = $_POST['status'];
    $pic = !empty($_POST['pic_id']) ? $_POST['pic_id'] : "NULL"; 
    $ket = isset($_POST['keterangan']) ? mysqli_real_escape_string($conn, $_POST['keterangan']) : '';

    $allow_update = false;
    if ($view_all_divisions) {
        $allow_update = true; 
    } elseif ($is_koordinator) {
        $cekDiv = mysqli_query($conn, "SELECT divisi_id FROM jobdesk WHERE jobdesk_id='$jid'");
        $d = mysqli_fetch_assoc($cekDiv);
        if ($d['divisi_id'] == $my_divisi) $allow_update = true;
    } else {
        $cekOwner = mysqli_query($conn, "SELECT user_id FROM jobdesk WHERE jobdesk_id='$jid'");
        $o = mysqli_fetch_assoc($cekOwner);
        if ($o['user_id'] == $id_user) {
            $allow_update = true;
            $pic = $id_user; 
        }
    }

    if ($allow_update) {
        $queryUpdate = "UPDATE jobdesk SET status='$stat', user_id=$pic WHERE jobdesk_id='$jid'";
        mysqli_query($conn, $queryUpdate);
        echo "<script>window.location='detail_event.php?id=$id_event&msg=job_status_updated';</script>";
    }
}


// --- LOGIC NOTULENSI ---
if (isset($_POST['simpan_notulensi'])) {
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $isi = mysqli_real_escape_string($conn, $_POST['isi']);
    $tgl = $_POST['tanggal'];
    $jenis = $_POST['jenis']; 
    $div_target = "NULL";
    if ($jenis == 'Rapat Divisi' && !$view_all_divisions) $div_target = "'$my_divisi'";
    
    mysqli_query($conn, "INSERT INTO notulensi (event_id, divisi_id, judul_notulen, isi_pembahasan, tanggal_rapat, jenis_rapat) VALUES ('$id_event', $div_target, '$judul', '$isi', '$tgl', '$jenis')");
    echo "<script>window.location='detail_event.php?id=$id_event&tab=notulensi&msg=notulensi_saved';</script>";
}
// Update Notulensi
if (isset($_POST['update_notulensi'])) {
    $id_not = $_POST['notulensi_id'];
    $judul = mysqli_real_escape_string($conn, $_POST['judul_notulen']);
    $isi = mysqli_real_escape_string($conn, $_POST['isi_pembahasan']);
    $tgl_rapat = $_POST['tanggal_rapat'];
    $jenis = $_POST['jenis_rapat'];

    mysqli_query($conn, "UPDATE notulensi SET judul_notulen='$judul', isi_pembahasan='$isi', tanggal_rapat='$tgl_rapat', jenis_rapat='$jenis' WHERE notulensi_id='$id_not'");
    echo "<script>window.location='detail_event.php?id=$id_event&tab=notulensi&msg=notulensi_updated';</script>";
}
// Hapus Notulensi
if (isset($_GET['hapus_notulensi'])) {
    $id_not = $_GET['hapus_notulensi'];
    mysqli_query($conn, "DELETE FROM notulensi WHERE notulensi_id='$id_not'");
    echo "<script>window.location='detail_event.php?id=$id_event&tab=notulensi&msg=notulensi_deleted';</script>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard <?= htmlspecialchars($info['nama_event']) ?></title>
    <link rel="stylesheet" href="../assets/css/style.css?v=116">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        .role-badge { background: #dbeafe; color: #1e40af; }
        .bg-blob { pointer-events: none !important; z-index: 0 !important; }
        .dashboard-container { position: relative; z-index: 10 !important; }

        .tabs { display: flex; gap: 20px; border-bottom: 2px solid #f1f5f9; margin-bottom: 20px; }
        .tab-item { padding: 15px 20px; font-weight: 600; color: #64748b; cursor: pointer; border-bottom: 3px solid transparent; transition: 0.3s; }
        .tab-item.active { color: #6366f1; border-bottom-color: #6366f1; }
        .tab-content { display: none; animation: fadeIn 0.3s; }
        .tab-content.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        
        .select-xs { padding: 6px 8px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 12px; background: white; max-width: 100%; width: 100%; cursor: pointer;}
        .input-xs { padding: 6px 10px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 12px; width: 100%; transition: 0.2s; }
        .input-xs:focus { border-color: #6366f1; outline: none; background: #f8fafc; }
        
        .badge-divisi { background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 4px; display: inline-block; }
        
        .status-Pending { color: #d97706; font-weight: 700; }
        .status-Process { color: #2563eb; font-weight: 700; }
        .status-Revision { color: #ea580c; font-weight: 700; }
        .status-Done { color: #166534; font-weight: 700; }

        .notulensi-card { background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 15px; transition: 0.2s; }
        .notulensi-card:hover { border-color: #6366f1; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }

        .action-icon { color: #94a3b8; cursor: pointer; text-decoration: none; margin-left: 8px; font-size: 14px; }
        .action-icon:hover { color: #6366f1; }
        .action-icon.trash:hover { color: #ef4444; }
    </style>
</head>
<body>
    
    <div class="bg-blob blob-1"></div>
    <div class="bg-blob blob-3"></div>

    <div class="dashboard-container">
        
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2 class="brand-title">E-PANITIA</h2>
                <span class="role-badge"><?= $my_role_global == 'admin' ? 'SYSTEM ADMIN' : 'ANGGOTA' ?></span>
            </div>
            
            <a href="<?= $my_role_global == 'admin' ? 'admin.php' : 'anggota.php' ?>" class="btn-kelola" style="margin-bottom: 20px; background: #94a3b8;">
                <i class="ph-bold ph-arrow-left"></i> Kembali
            </a>

            <?php if($is_super_admin || $is_ketua_event): ?>
            <a href="edit_event.php?id=<?= $id_event ?>" class="btn-kelola" style="margin-bottom: 20px; background: #6366f1; width: 100%; justify-content: center;">
                <i class="ph-bold ph-gear"></i> Pengaturan Event
            </a>
            <?php endif; ?>

            <div style="padding: 20px; background: rgba(255,255,255,0.5); border-radius: 12px; border: 1px solid white;">
                <small style="text-transform: uppercase; font-weight: 700; color: #64748b;">Event Saat Ini</small>
                <h3 style="font-size: 16px; margin: 5px 0; color: #1e293b;"><?= htmlspecialchars($info['nama_event']) ?></h3>
                
                <?php if($is_super_admin): ?>
                    <div class="divisi-tag" style="background:#fcd34d; color:#92400e; font-weight:700; padding:5px; border-radius:5px; font-size:11px;">Mode Pantau (Admin)</div>
                <?php elseif($is_ketua_event): ?>
                    <div class="divisi-tag" style="background:#c084fc; color:#fff; font-weight:700; padding:5px; border-radius:5px; font-size:11px;">Ketua Pelaksana</div>
                <?php else: ?>
                    <div class="divisi-tag" style="background:#e0e7ff; color:#4338ca; font-weight:700; padding:5px; border-radius:5px; font-size:11px;">Divisi <?= htmlspecialchars($my_divisi_name) ?></div>
                <?php endif; ?>
            </div>
        </aside>

        <main class="main-content">
            
            <div class="tabs">
                <div class="tab-item active" onclick="bukaTab('jobdesk')"><i class="ph-bold ph-check-square"></i> Jobdesk</div>
                <div class="tab-item" onclick="bukaTab('notulensi')"><i class="ph-bold ph-book-open"></i> Notulensi</div>
            </div>

            <div id="jobdesk" class="tab-content active">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3>
                        <?php if($view_all_divisions): ?>
                            Master Jobdesk (Semua Divisi) 🌐
                        <?php else: ?>
                            Tugas Divisi <?= $my_divisi_name ?> 📌
                        <?php endif; ?>
                    </h3>
                    
                    <?php if($view_all_divisions || $is_koordinator): ?>
                    <button onclick="document.getElementById('modalTambahJob').style.display='flex'" class="btn-login" style="width: auto; padding: 10px 20px; font-size: 13px;">
                        + Buat Tugas Baru
                    </button>
                    <?php endif; ?>
                </div>

                <div class="table-container" style="background: white; border-radius: 12px; padding: 0; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                    <table style="width: 100%;">
                        <thead style="background: white; border-bottom: 2px solid #f1f5f9;">
                            <tr>
                                <th width="25%" style="padding: 20px;">TUGAS</th>
                                <th width="20%">PIC</th>
                                <th width="15%">DEADLINE</th>
                                <th width="15%">STATUS</th>
                                <th width="20%">KETERANGAN</th> 
                                <th width="5%"></th> </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $qJobSql = "SELECT j.*, u.nama_lengkap, d.nama_divisi 
                                        FROM jobdesk j 
                                        LEFT JOIN users u ON j.user_id = u.user_id 
                                        JOIN divisi d ON j.divisi_id = d.divisi_id
                                        WHERE d.event_id='$id_event'";
                            
                            if (!$view_all_divisions) {
                                $qJobSql .= " AND j.divisi_id='$my_divisi'";
                            }

                            $qJobSql .= " ORDER BY j.deadline ASC";
                            $qJob = mysqli_query($conn, $qJobSql);
                            
                            if(mysqli_num_rows($qJob) == 0) echo "<tr><td colspan='6' style='text-align:center; padding:30px; color:#94a3b8;'>Belum ada tugas.</td></tr>";

                            while($row = mysqli_fetch_assoc($qJob)):
                                $is_mine = ($row['user_id'] == $id_user);
                                // Permission Logic
                                $can_manage = ($view_all_divisions || ($is_koordinator && $row['divisi_id'] == $my_divisi)); // Edit nama/hapus
                                $can_update_status = ($can_manage || $is_mine); // Update status/progress

                                $current_keterangan = htmlspecialchars($row['keterangan'] ?? '');
                                $current_status = $row['status'];
                                $current_pic = $row['user_id'];
                            ?>
                            <tr style="<?= $is_mine ? 'background:#f0f9ff;' : '' ?>">
                                <td style="padding: 15px 20px;">
                                    <?php if($view_all_divisions): ?>
                                        <span class="badge-divisi"><?= $row['nama_divisi'] ?></span><br>
                                    <?php endif; ?>
                                    <span style="font-weight: 600; color: #334155;"><?= htmlspecialchars($row['nama_tugas']) ?></span>
                                </td>
                                
                                <td>
                                    <?php if ($can_manage): ?>
                                        <form method="POST" style="margin:0;">
                                            <input type="hidden" name="job_id" value="<?= $row['jobdesk_id'] ?>">
                                            <input type="hidden" name="status" value="<?= $current_status ?>">
                                            <input type="hidden" name="keterangan" value="<?= $current_keterangan ?>"> 
                                            <input type="hidden" name="update_jobdesk" value="1">

                                            <select name="pic_id" class="select-xs" onchange="this.form.submit()">
                                                <option value="" style="color:red;">-- Belum Ada --</option>
                                                <?php
                                                $div_target = $row['divisi_id'];
                                                $qMember = mysqli_query($conn, "SELECT u.user_id, u.nama_lengkap FROM anggota_divisi ad JOIN users u ON ad.user_id = u.user_id WHERE ad.divisi_id='$div_target'");
                                                while($m = mysqli_fetch_assoc($qMember)):
                                                ?>
                                                <option value="<?= $m['user_id'] ?>" <?= $row['user_id'] == $m['user_id'] ? 'selected' : '' ?>><?= $m['nama_lengkap'] ?></option>
                                                <?php endwhile; ?>
                                            </select>
                                        </form>
                                    <?php else: ?>
                                        <?= $row['nama_lengkap'] ?? '<span style="color:#ef4444; font-size:11px;">Belum ada PIC</span>' ?>
                                    <?php endif; ?>
                                </td>

                                <td><?= date('d M', strtotime($row['deadline'])) ?></td>

                                <td>
                                    <?php if ($can_update_status): ?>
                                        <form method="POST" style="margin:0;">
                                            <input type="hidden" name="job_id" value="<?= $row['jobdesk_id'] ?>">
                                            <input type="hidden" name="pic_id" value="<?= $current_pic ?>">
                                            <input type="hidden" name="keterangan" value="<?= $current_keterangan ?>"> 
                                            <input type="hidden" name="update_jobdesk" value="1">
                                            
                                            <select name="status" class="select-xs status-<?= $row['status'] ?>" onchange="this.form.submit()">
                                                <option value="Pending" <?= $row['status']=='Pending'?'selected':'' ?>>Pending</option>
                                                <option value="Process" <?= $row['status']=='Process'?'selected':'' ?>>On Progress</option>
                                                <option value="Revision" <?= $row['status']=='Revision'?'selected':'' ?>>Perlu Revisi</option>
                                                <option value="Done" <?= $row['status']=='Done'?'selected':'' ?>>Done</option>
                                            </select>
                                        </form>
                                    <?php else: ?>
                                        <span class="status-<?= $row['status'] ?>" style="font-size: 11px;"><?= $row['status'] ?></span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?php if ($can_update_status): ?>
                                        <form method="POST" style="margin:0;">
                                            <input type="hidden" name="job_id" value="<?= $row['jobdesk_id'] ?>">
                                            <input type="hidden" name="pic_id" value="<?= $current_pic ?>">
                                            <input type="hidden" name="status" value="<?= $current_status ?>">
                                            <input type="hidden" name="update_jobdesk" value="1">
                                            
                                            <input type="text" name="keterangan" class="input-xs" 
                                                   value="<?= $current_keterangan ?>" 
                                                   placeholder="Tulis ket..." 
                                                   onchange="this.form.submit()">
                                        </form>
                                    <?php else: ?>
                                        <span style="font-size: 12px; color: #64748b; font-style: italic;">
                                            <?= $current_keterangan ?: '-' ?>
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td style="text-align: right; padding-right: 20px;">
                                    <?php if($can_manage): ?>
                                        <div style="display: flex; gap: 5px; justify-content: flex-end;">
                                            <a href="#" class="action-icon" title="Edit Tugas" onclick="openEditJob('<?= $row['jobdesk_id'] ?>', '<?= addslashes($row['nama_tugas']) ?>', '<?= $row['deadline'] ?>')">
                                                <i class="ph-bold ph-pencil-simple"></i>
                                            </a>
                                            <a href="?id=<?= $id_event ?>&hapus_jobdesk=<?= $row['jobdesk_id'] ?>" class="action-icon trash" title="Hapus Tugas" onclick="return confirm('Hapus tugas ini?')">
                                                <i class="ph-bold ph-trash"></i>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="notulensi" class="tab-content">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3>Arsip Notulensi 📖</h3>
                    <?php if ($view_all_divisions || $is_sekretaris || $is_koordinator): ?>
                    <button onclick="document.getElementById('modalNotulen').style.display='flex'" class="btn-login" style="width: auto; padding: 10px 20px;">+ Buat Notulensi</button>
                    <?php endif; ?>
                </div>

                <?php
                $sqlNote = "SELECT * FROM notulensi WHERE event_id='$id_event'";
                if (!$view_all_divisions) {
                    $sqlNote .= " AND (jenis_rapat = 'Rapat Umum' OR divisi_id = '$my_divisi')";
                }
                $sqlNote .= " ORDER BY tanggal_rapat DESC";
                $qNote = mysqli_query($conn, $sqlNote);

                if(mysqli_num_rows($qNote) == 0) echo "<p style='color:#94a3b8; text-align:center;'>Belum ada notulensi.</p>";

                while($note = mysqli_fetch_assoc($qNote)):
                    $badgeType = ($note['jenis_rapat'] == 'Rapat Umum') ? 'background:#e0e7ff; color:#4338ca;' : 'background:#dcfce7; color:#166534;';
                    $can_manage_note = ($view_all_divisions || (($is_koordinator || $is_sekretaris) && $note['jenis_rapat'] != 'Rapat Umum'));
                    $tgl_edit = date('Y-m-d\TH:i', strtotime($note['tanggal_rapat']));
                ?>
                <div class="notulensi-card">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span style="<?= $badgeType ?> padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase;"><?= $note['jenis_rapat'] ?></span>
                        <span style="font-size: 12px; color: #64748b;"><?= date('d M Y, H:i', strtotime($note['tanggal_rapat'])) ?></span>
                    </div>
                    <h4 style="margin-bottom: 10px;"><?= htmlspecialchars($note['judul_notulen']) ?></h4>
                    <p style="color: #475569; font-size: 14px; line-height: 1.6; white-space: pre-line;"><?= htmlspecialchars($note['isi_pembahasan']) ?></p>
                    
                    <?php if($can_manage_note): ?>
                    <div style="margin-top: 15px; border-top: 1px solid #f1f5f9; padding-top: 10px; text-align: right; display: flex; justify-content: flex-end; gap: 15px;">
                        <a href="#" onclick="openEditNotulen('<?= $note['notulensi_id'] ?>', '<?= addslashes($note['judul_notulen']) ?>', '<?= $tgl_edit ?>', '<?= addslashes($note['jenis_rapat']) ?>', `<?= addslashes($note['isi_pembahasan']) ?>`)" style="font-size: 13px; color: #6366f1; text-decoration: none; font-weight: 600;"><i class="ph-bold ph-pencil-simple"></i> Edit</a>
                        <a href="?id=<?= $id_event ?>&hapus_notulensi=<?= $note['notulensi_id'] ?>&tab=notulensi" onclick="return confirm('Hapus notulensi ini?')" style="font-size: 13px; color: #ef4444; text-decoration: none; font-weight: 600;"><i class="ph-bold ph-trash"></i> Hapus</a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endwhile; ?>
            </div>

        </main>
    </div>

    <div id="modalTambahJob" class="modal-overlay" style="display: none;">
        <div class="modal-box" style="max-width: 500px;">
            <div class="modal-header">
                <div class="modal-title">Buat Tugas Baru</div>
                <button onclick="document.getElementById('modalTambahJob').style.display='none'" class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <input type="hidden" name="tambah_jobdesk" value="1">
                    
                    <div class="form-group">
                        <label class="form-label">Untuk Divisi</label>
                        <select name="divisi_id" class="form-control" required>
                            <?php if($view_all_divisions): ?>
                                <?php 
                                $qDivs = mysqli_query($conn, "SELECT * FROM divisi WHERE event_id='$id_event'");
                                while($d = mysqli_fetch_assoc($qDivs)): ?>
                                    <option value="<?= $d['divisi_id'] ?>"><?= $d['nama_divisi'] ?></option>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <option value="<?= $my_divisi ?>" selected><?= $my_divisi_name ?></option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nama Tugas</label>
                        <input type="text" name="nama_tugas" class="form-control" placeholder="Contoh: Survey Lokasi" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Deadline</label>
                        <input type="date" name="deadline" class="form-control" required>
                    </div>
                    <button type="submit" class="btn-login">Simpan Tugas</button>
                </form>
            </div>
        </div>
    </div>

    <div id="modalEditJob" class="modal-overlay" style="display: none;">
        <div class="modal-box" style="max-width: 500px;">
            <div class="modal-header">
                <div class="modal-title">Edit Tugas</div>
                <button onclick="document.getElementById('modalEditJob').style.display='none'" class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <input type="hidden" name="edit_jobdesk_full" value="1">
                    <input type="hidden" name="job_id" id="edit_job_id">
                    
                    <div class="form-group">
                        <label class="form-label">Nama Tugas</label>
                        <input type="text" name="nama_tugas" id="edit_job_nama" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Deadline</label>
                        <input type="date" name="deadline" id="edit_job_deadline" class="form-control" required>
                    </div>
                    <button type="submit" class="btn-login">Simpan Perubahan</button>
                </form>
            </div>
        </div>
    </div>

    <div id="modalNotulen" class="modal-overlay" style="display: none;">
        <div class="modal-box" style="max-width: 600px;">
            <div class="modal-header">
                <div class="modal-title">Tulis Notulensi Baru</div>
                <button onclick="document.getElementById('modalNotulen').style.display='none'" class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <input type="hidden" name="simpan_notulensi" value="1">
                    <div class="form-group">
                        <label class="form-label">Jenis Rapat</label>
                        <select name="jenis" class="form-control" required>
                            <?php if($view_all_divisions): ?>
                                <option value="Rapat Umum">Rapat Umum (Semua Divisi)</option>
                            <?php endif; ?>
                            <?php if($my_divisi_name): ?>
                                <option value="Rapat Divisi">Rapat Internal Divisi <?= htmlspecialchars($my_divisi_name) ?></option>
                            <?php endif; ?>
                            <option value="Rapat Koordinasi">Rapat Koordinasi</option>
                        </select>
                    </div>
                    <div class="form-group"><label class="form-label">Judul Rapat</label><input type="text" name="judul" class="form-control" required placeholder="Cth: Koordinasi Teknis"></div>
                    <div class="form-group"><label class="form-label">Tanggal</label><input type="datetime-local" name="tanggal" class="form-control" required></div>
                    <div class="form-group"><label class="form-label">Isi Pembahasan</label><textarea name="isi" class="form-control" rows="6" required></textarea></div>
                    <button type="submit" class="btn-login">Simpan</button>
                </form>
            </div>
        </div>
    </div>

    <div id="modalEditNotulen" class="modal-overlay" style="display: none;">
        <div class="modal-box" style="max-width: 600px;">
            <div class="modal-header">
                <div class="modal-title">Edit Notulensi</div>
                <button onclick="document.getElementById('modalEditNotulen').style.display='none'" class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <input type="hidden" name="update_notulensi" value="1">
                    <input type="hidden" name="notulensi_id" id="edit_id">

                    <div class="form-group">
                        <label class="form-label">Jenis Rapat</label>
                        <select name="jenis_rapat" id="edit_jenis" class="form-control" required>
                            <?php if($view_all_divisions): ?>
                                <option value="Rapat Umum">Rapat Umum (Semua Divisi)</option>
                            <?php endif; ?>
                            <?php if($my_divisi_name): ?>
                                <option value="Rapat Divisi">Rapat Internal Divisi <?= htmlspecialchars($my_divisi_name) ?></option>
                            <?php endif; ?>
                            <option value="Rapat Koordinasi">Rapat Koordinasi</option>
                        </select>
                    </div>
                    <div class="form-group"><label class="form-label">Judul Rapat</label><input type="text" name="judul_notulen" id="edit_judul" class="form-control" required></div>
                    <div class="form-group"><label class="form-label">Tanggal & Waktu</label><input type="datetime-local" name="tanggal_rapat" id="edit_tanggal" class="form-control" required></div>
                    <div class="form-group"><label class="form-label">Isi Pembahasan / Keputusan</label><textarea name="isi_pembahasan" id="edit_isi" class="form-control" rows="6" required></textarea></div>
                    <button type="submit" class="btn-login">Simpan Perubahan</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function bukaTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-item').forEach(el => el.classList.remove('active'));
            document.getElementById(tabName).classList.add('active');
            const btns = document.querySelectorAll('.tab-item');
            if(tabName == 'jobdesk') btns[0].classList.add('active'); else btns[1].classList.add('active');
        }
        
        const urlParams = new URLSearchParams(window.location.search);
        if(urlParams.get('tab') === 'notulensi') bukaTab('notulensi');

        // JS MODAL NOTULENSI
        function openEditNotulen(id, judul, tanggal, jenis, isi) {
            document.getElementById('modalEditNotulen').style.display = 'flex';
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_judul').value = judul;
            document.getElementById('edit_tanggal').value = tanggal;
            document.getElementById('edit_jenis').value = jenis;
            document.getElementById('edit_isi').value = isi;
        }

        // JS MODAL JOBDESK (BARU)
        function openEditJob(id, nama, deadline) {
            document.getElementById('modalEditJob').style.display = 'flex';
            document.getElementById('edit_job_id').value = id;
            document.getElementById('edit_job_nama').value = nama;
            document.getElementById('edit_job_deadline').value = deadline;
        }

        // FEEDBACK ALERTS
        if(urlParams.get('msg') === 'job_added') alert('✅ Tugas baru berhasil ditambahkan!');
        if(urlParams.get('msg') === 'job_edited') alert('✅ Info tugas berhasil diperbarui!');
        if(urlParams.get('msg') === 'job_deleted') alert('🗑️ Tugas berhasil dihapus.');
        if(urlParams.get('msg') === 'job_status_updated') alert('✅ Status/Keterangan berhasil diperbarui!');
        if(urlParams.get('msg') === 'notulensi_saved') alert('✅ Notulensi baru berhasil disimpan!');
        if(urlParams.get('msg') === 'notulensi_updated') alert('✅ Notulensi berhasil diperbarui!');
        if(urlParams.get('msg') === 'notulensi_deleted') alert('🗑️ Notulensi berhasil dihapus.');
    </script>
</body>
</html>