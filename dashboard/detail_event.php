<?php
session_start();
require '../config/koneksi.php';

if (!isset($_SESSION['role'])) { header("Location: ../index.php"); exit; }

$id_user = $_SESSION['user_id'];
$id_event = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_event === 0) {
    echo "<script>alert('ID Event tidak valid!'); window.location='anggota.php';</script>";
    exit;
}
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
$is_bendahara = ($my_jabatan == 'Bendahara');

if (!$is_super_admin && $my_jabatan == null) {
    echo "<script>alert('Akses ditolak!'); window.location='anggota.php';</script>";
    exit;
}

$view_all_divisions = ($is_super_admin || $is_ketua_event || $is_sekretaris || $is_bendahara);


// --- LOGIC CRUD JOBDESK ---

// 1. TAMBAH JOBDESK (FITUR REQUEST - UPDATED)
if (isset($_POST['tambah_jobdesk'])) {
    $target_divisi = $_POST['target_divisi']; 
    $nama_tugas = mysqli_real_escape_string($conn, $_POST['nama_tugas']);
    $deadline = $_POST['deadline'];
    // Keterangan opsional saat buat tugas
    $keterangan = isset($_POST['keterangan']) ? mysqli_real_escape_string($conn, $_POST['keterangan']) : '';
    
    // Logic Request: Jika saya bukan Admin/Ketua, DAN target bukan divisi saya -> Request
    $req_dari = "NULL";
    if (!$view_all_divisions && $target_divisi != $my_divisi) {
        $req_dari = "'$my_divisi'";
    }

    $qInsert = "INSERT INTO jobdesk (divisi_id, nama_tugas, deadline, status, keterangan, request_dari_divisi_id) 
                VALUES ('$target_divisi', '$nama_tugas', '$deadline', 'Pending', '$keterangan', $req_dari)";
    
    if(mysqli_query($conn, $qInsert)) {
        echo "<script>window.location='detail_event.php?id=$id_event&msg=job_added';</script>";
    } else {
        echo "<script>alert('Gagal: ".mysqli_error($conn)."');</script>";
    }
}

// 2. EDIT JOBDESK UTAMA (Nama & Deadline)
if (isset($_POST['edit_jobdesk_full'])) {
    $jid = $_POST['job_id'];
    $nama_tugas = mysqli_real_escape_string($conn, $_POST['nama_tugas']);
    $deadline = $_POST['deadline'];
    
    mysqli_query($conn, "UPDATE jobdesk SET nama_tugas='$nama_tugas', deadline='$deadline' WHERE jobdesk_id='$jid'");
    echo "<script>window.location='detail_event.php?id=$id_event&msg=job_edited';</script>";
}

// 3. HAPUS JOBDESK
if (isset($_GET['hapus_jobdesk'])) {
    $jid = $_GET['hapus_jobdesk'];
    mysqli_query($conn, "DELETE FROM jobdesk WHERE jobdesk_id='$jid'");
    echo "<script>window.location='detail_event.php?id=$id_event&msg=job_deleted';</script>";
}

// 4. UPDATE STATUS/PIC (LENGKAP dengan KETERANGAN)
if (isset($_POST['update_jobdesk'])) {
    $jid = $_POST['job_id'];
    $stat = $_POST['status'];
    $pic = !empty($_POST['pic_id']) ? $_POST['pic_id'] : "NULL"; 
    $ket = isset($_POST['keterangan']) ? mysqli_real_escape_string($conn, $_POST['keterangan']) : '';

    // Logic Hak Edit: Pengirim (Requestor) & Penerima (Owner) & PIC bisa edit
    $allow_update = false;
    if ($view_all_divisions) {
        $allow_update = true;
    } else {
        $cekJob = mysqli_query($conn, "SELECT divisi_id, user_id, request_dari_divisi_id FROM jobdesk WHERE jobdesk_id='$jid'");
        $jobData = mysqli_fetch_assoc($cekJob);
        
        // Cek kepemilikan atau request
        if ($jobData['divisi_id'] == $my_divisi || $jobData['request_dari_divisi_id'] == $my_divisi || $jobData['user_id'] == $id_user) {
            $allow_update = true;
        }
    }

    if ($allow_update) {
        $queryUpdate = "UPDATE jobdesk SET status='$stat', user_id=$pic, keterangan='$ket' WHERE jobdesk_id='$jid'";
        mysqli_query($conn, $queryUpdate);
        echo "<script>window.location='detail_event.php?id=$id_event&msg=job_status_updated';</script>";
    }
}

// 5. UPDATE KETERANGAN SAJA (VIA POPUP)
if (isset($_POST['update_keterangan_job'])) {
    $jid = intval($_POST['job_id']);
    $ket = mysqli_real_escape_string($conn, $_POST['keterangan']);
    
    mysqli_query($conn, "UPDATE jobdesk SET keterangan='$ket' WHERE jobdesk_id='$jid'");
    echo "<script>window.location='detail_event.php?id=$id_event&msg=job_status_updated';</script>";
}

// --- LOGIC CRUD NOTULENSI (LENGKAP) ---

// 1. Simpan Notulensi
if (isset($_POST['simpan_notulensi'])) {
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $isi = mysqli_real_escape_string($conn, $_POST['isi']);
    $tgl = $_POST['tanggal'];
    $jenis = $_POST['jenis']; 
    $div_target = ($jenis == 'Rapat Divisi' && !$view_all_divisions) ? "'$my_divisi'" : "NULL";
    
    mysqli_query($conn, "INSERT INTO notulensi (event_id, divisi_id, judul_notulen, isi_pembahasan, tanggal_rapat, jenis_rapat) VALUES ('$id_event', $div_target, '$judul', '$isi', '$tgl', '$jenis')");
    echo "<script>window.location='detail_event.php?id=$id_event&tab=notulensi&msg=notulensi_saved';</script>";
}

// 2. Update Notulensi (YANG HILANG TADI SAYA KEMBALIKAN DISINI)
if (isset($_POST['update_notulensi'])) {
    $id_not = $_POST['notulensi_id'];
    $judul = mysqli_real_escape_string($conn, $_POST['judul_notulen']);
    $isi = mysqli_real_escape_string($conn, $_POST['isi_pembahasan']);
    $tgl_rapat = $_POST['tanggal_rapat'];
    $jenis = $_POST['jenis_rapat'];

    mysqli_query($conn, "UPDATE notulensi SET judul_notulen='$judul', isi_pembahasan='$isi', tanggal_rapat='$tgl_rapat', jenis_rapat='$jenis' WHERE notulensi_id='$id_not'");
    echo "<script>window.location='detail_event.php?id=$id_event&tab=notulensi&msg=notulensi_updated';</script>";
}

// 3. Hapus Notulensi
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
    <link rel="stylesheet" href="../assets/css/style.css?v=119">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body>
    
    <div class="bg-blob blob-1"></div>
    <div class="bg-blob blob-3"></div>

    <div class="dashboard-container">
        <?php include 'sidebar_common.php'; ?>

        <main class="main-content">
            
            <div class="tabs">
                <div class="tab-item active" onclick="bukaTab('jobdesk')"><i class="ph-bold ph-check-square"></i> Jobdesk</div>
                <div class="tab-item" onclick="bukaTab('notulensi')"><i class="ph-bold ph-book-open"></i> Notulensi</div>
            </div>

            <div id="jobdesk" class="tab-content active">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3>
                        <?php if($view_all_divisions): ?>
                            Master Jobdesk (Semua Divisi)
                        <?php else: ?>
                            Tugas Divisi <?= $my_divisi_name ?>
                        <?php endif; ?>
                    </h3>
                    
                    <button onclick="document.getElementById('modalTambahJob').style.display='flex'" class="btn-login" style="width: auto; padding: 10px 20px; font-size: 13px;">
                        + Buat Jobdesk
                    </button>
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
                                <th width="5%"></th> 
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Query: Ambil tugas Divisi Sendiri OR Request Masuk OR Request Keluar
                            $qJobSql = "SELECT j.*, u.nama_lengkap, d.nama_divisi, req_div.nama_divisi AS nama_divisi_req
                                        FROM jobdesk j 
                                        LEFT JOIN users u ON j.user_id = u.user_id 
                                        JOIN divisi d ON j.divisi_id = d.divisi_id
                                        LEFT JOIN divisi req_div ON j.request_dari_divisi_id = req_div.divisi_id
                                        WHERE d.event_id='$id_event'";
                            
                            if (!$view_all_divisions) {
                                $qJobSql .= " AND (j.divisi_id='$my_divisi' OR j.request_dari_divisi_id='$my_divisi')";
                            }

                            $qJobSql .= " ORDER BY j.deadline ASC";
                            $qJob = mysqli_query($conn, $qJobSql);
                            
                            if(mysqli_num_rows($qJob) == 0) echo "<tr><td colspan='6' style='text-align:center; padding:30px; color:#94a3b8;'>Belum ada tugas.</td></tr>";

                            while($row = mysqli_fetch_assoc($qJob)):
                                $is_mine = ($row['user_id'] == $id_user);
                                // Permission Logic: Bisa edit jika Requestor OR Receiver OR SuperUser
                                $can_manage = ($view_all_divisions || ($row['divisi_id'] == $my_divisi || $row['request_dari_divisi_id'] == $my_divisi));
                                $can_update_status = ($can_manage || $is_mine);

                                $current_keterangan = isset($row['keterangan']) ? $row['keterangan'] : '';
                                $current_status = $row['status'];
                                $current_pic = $row['user_id'];
                                
                                // Deteksi Request
                                $is_incoming_req = (!empty($row['request_dari_divisi_id']) && $row['divisi_id'] == $my_divisi);
                                $is_outgoing_req = (!empty($row['request_dari_divisi_id']) && $row['request_dari_divisi_id'] == $my_divisi);
                            ?>
                            <tr style="<?= $is_mine ? 'background:#f0f9ff;' : '' ?>">
                                <td style="padding: 15px 20px;">
                                    <?php if($view_all_divisions): ?>
                                        <span class="badge-divisi"><?= $row['nama_divisi'] ?></span><br>
                                    <?php endif; ?>

                                    <?php if($is_incoming_req): ?>
                                        <span class="badge-req" title="Tugas ini diminta oleh divisi lain">
                                            🔔 Req dari: <?= htmlspecialchars($row['nama_divisi_req']) ?>
                                        </span><br>
                                    <?php elseif($is_outgoing_req): ?>
                                        <span class="badge-req" style="background:#dbeafe; color:#1e40af; border-color:#93c5fd;">
                                            📤 Req ke: <?= htmlspecialchars($row['nama_divisi']) ?>
                                        </span><br>
                                    <?php endif; ?>

                                    <span style="font-weight: 600; color: #334155;"><?= htmlspecialchars($row['nama_tugas']) ?></span>
                                </td>
                                
                                <td>
                                    <?php if ($can_manage): ?>
                                        <form method="POST" style="margin:0;">
                                            <input type="hidden" name="job_id" value="<?= $row['jobdesk_id'] ?>">
                                            <input type="hidden" name="status" value="<?= $current_status ?>">
                                            <input type="hidden" name="keterangan" value="<?= htmlspecialchars($current_keterangan) ?>"> 
                                            <input type="hidden" name="update_jobdesk" value="1">

                                            <select name="pic_id" class="select-xs" onchange="this.form.submit()">
                                                <option value="" style="color:red;">-- Belum Ada --</option>
                                                <?php
                                                // Dropdown PIC = Anggota Divisi TARGET (Penerima Tugas)
                                                $target_div = $row['divisi_id'];
                                                $qMember = mysqli_query($conn, "SELECT u.user_id, u.nama_lengkap FROM anggota_divisi ad JOIN users u ON ad.user_id = u.user_id WHERE ad.divisi_id='$target_div'");
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

                                <td><?= date('d M Y', strtotime($row['deadline'])) ?></td>

                                <td>
                                    <?php if ($can_update_status): ?>
                                        <form method="POST" style="margin:0;">
                                            <input type="hidden" name="job_id" value="<?= $row['jobdesk_id'] ?>">
                                            <input type="hidden" name="pic_id" value="<?= $current_pic ?>">
                                            <input type="hidden" name="keterangan" value="<?= htmlspecialchars($current_keterangan) ?>"> 
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

                                <td style="padding-right: 20px;">
                                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                                        <span style="font-size: 12px; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 120px;" title="<?= htmlspecialchars($current_keterangan) ?>">
                                            <?= htmlspecialchars($current_keterangan) ?: '<span style="opacity:0.5;">-</span>' ?>
                                        </span>
                                        
                                        <?php if ($can_update_status): ?>
                                            <button type="button" class="action-icon" style="border:none; background:none; color: #6366f1;" 
                                                    onclick="openUpdateKeterangan('<?= $row['jobdesk_id'] ?>', `<?= addslashes($current_keterangan) ?>`)">
                                                <i class="ph-bold ph-eye"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
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
                // Menggunakan n.* untuk data notulensi dan d.nama_divisi untuk nama divisinya
                $sqlNote = "SELECT n.*, d.nama_divisi 
                            FROM notulensi n 
                            LEFT JOIN divisi d ON n.divisi_id = d.divisi_id 
                            WHERE n.event_id='$id_event'";
                if (!$view_all_divisions) {
                    $sqlNote .= " AND (n.jenis_rapat = 'Rapat Umum' OR n.divisi_id = '$my_divisi')";
                }
                $sqlNote .= " ORDER BY n.tanggal_rapat DESC";
                $qNote = mysqli_query($conn, $sqlNote);

                if(mysqli_num_rows($qNote) == 0) echo "<p style='color:#94a3b8; text-align:center;'>Belum ada notulensi.</p>";

                while($note = mysqli_fetch_assoc($qNote)):
                    $badgeType = ($note['jenis_rapat'] == 'Rapat Umum') ? 'background:#e0e7ff; color:#4338ca;' : 'background:#dcfce7; color:#166534;';
                    $can_manage_note = ($view_all_divisions || (($is_koordinator || $is_sekretaris) && $note['jenis_rapat'] != 'Rapat Umum'));
                    $tgl_edit = date('Y-m-d\TH:i', strtotime($note['tanggal_rapat']));
                ?>
                <div class="notulensi-card">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <span style="<?= $badgeType ?> padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase;">
                                <?= $note['jenis_rapat'] ?>
                            </span>
                            
                            <?php if ($note['nama_divisi']): ?>
                                <span class="badge-divisi" style="margin-bottom: 0; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase;">
                                    Divisi <?= htmlspecialchars($note['nama_divisi']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
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
                <div class="modal-title">Buat Jobdesk</div>
                <button onclick="document.getElementById('modalTambahJob').style.display='none'" class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <input type="hidden" name="tambah_jobdesk" value="1">
                    
                    <div class="form-group">
                        <label class="form-label">Divisi Tujuan</label>
                        <select name="target_divisi" class="form-control" required>
                            <?php 
                            // Tampilkan semua divisi agar bisa request ke lain divisi
                            $qDivModal = mysqli_query($conn, "SELECT * FROM divisi WHERE event_id='$id_event' ORDER BY nama_divisi ASC");
                            while($d = mysqli_fetch_assoc($qDivModal)):
                                $selected = ($d['divisi_id'] == $my_divisi) ? 'selected' : '';
                                $label = ($d['divisi_id'] == $my_divisi) ?' (Divisi Saya)' : '';
                            ?>
                            <option value="<?= $d['divisi_id'] ?>" <?= $selected ?>><?= $d['nama_divisi'] . $label ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group"><label class="form-label">Nama Tugas</label><input type="text" name="nama_tugas" class="form-control" placeholder="Contoh: Desain Banner" required></div>
                    <div class="form-group"><label class="form-label">Deadline</label><input type="date" name="deadline" class="form-control" required></div>
                    <div class="form-group"><label class="form-label">Keterangan Awal (Opsional)</label><textarea name="keterangan" class="form-control" rows="2" placeholder="Detail request..."></textarea></div>
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
                    <div class="form-group"><label class="form-label">Nama Tugas</label><input type="text" name="nama_tugas" id="edit_job_nama" class="form-control" required></div>
                    <div class="form-group"><label class="form-label">Deadline</label><input type="date" name="deadline" id="edit_job_deadline" class="form-control" required></div>
                    <button type="submit" class="btn-login">Simpan Perubahan</button>
                </form>
            </div>
        </div>
    </div>

    <div id="modalKetJob" class="modal-overlay" style="display: none;">
        <div class="modal-box" style="max-width: 500px;">
            <div class="modal-header">
                <div class="modal-title">Update Keterangan Tugas</div>
                <button onclick="document.getElementById('modalKetJob').style.display='none'" class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <input type="hidden" name="update_keterangan_job" value="1">
                    <input type="hidden" name="job_id" id="ket_job_id">
                    <div class="form-group">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" id="ket_job_text" class="form-control" rows="6" placeholder="Tulis detail progress atau catatan di sini..."></textarea>
                    </div>
                    <button type="submit" class="btn-login">Simpan Keterangan</button>
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
                    <div class="form-group"><label class="form-label">Jenis Rapat</label>
                        <select name="jenis" class="form-control" required>
                            <?php if($view_all_divisions): ?><option value="Rapat Umum">Rapat Umum (Semua Divisi)</option><?php endif; ?>
                            <?php if($my_divisi_name): ?><option value="Rapat Divisi">Rapat Internal Divisi <?= htmlspecialchars($my_divisi_name) ?></option><?php endif; ?>
                        </select>
                    </div>
                    <div class="form-group"><label class="form-label">Judul Rapat</label><input type="text" name="judul" class="form-control" required></div>
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

        // JS MODAL JOBDESK
        function openEditJob(id, nama, deadline) {
            document.getElementById('modalEditJob').style.display = 'flex';
            document.getElementById('edit_job_id').value = id;
            document.getElementById('edit_job_nama').value = nama;
            document.getElementById('edit_job_deadline').value = deadline;
        }

        // JS MODAL KETERANGAN JOB
        function openUpdateKeterangan(id, keterangan) {
            document.getElementById('modalKetJob').style.display = 'flex';
            document.getElementById('ket_job_id').value = id;
            document.getElementById('ket_job_text').value = keterangan;
        }

        // JS MODAL NOTULENSI
        function openEditNotulen(id, judul, tanggal, jenis, isi) {
            document.getElementById('modalEditNotulen').style.display = 'flex';
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_judul').value = judul;
            document.getElementById('edit_tanggal').value = tanggal;
            document.getElementById('edit_jenis').value = jenis;
            document.getElementById('edit_isi').value = isi;
        }

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