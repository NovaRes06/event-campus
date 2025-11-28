<?php
// --- 1. BAGIAN BACKEND (OTAK) ---
session_start();

// Cek file koneksi (Debug)
if (!file_exists('../config/koneksi.php')) {
    die("Error Fatal: File config/koneksi.php tidak ditemukan! Pastikan struktur folder benar.");
}
include '../config/koneksi.php';

// Pastikan variabel $conn ada
if (!isset($conn)) {
    die("Error Fatal: Koneksi database gagal! Variabel \$conn tidak terbaca.");
}

// 1. Validasi ID Event dari URL
if (!isset($_GET['id']) || empty($_GET['id'])) { 
    // Kalau ga ada ID, balikin ke daftar event
    echo "<script>alert('Pilih event dulu!'); window.location='data_event.php';</script>";
    exit; 
}
$id_event = $_GET['id'];

// 2. Ambil Data Event (Judul, Tanggal, dll)
$query_event = mysqli_query($conn, "SELECT * FROM events WHERE event_id='$id_event'");
$event = mysqli_fetch_assoc($query_event);

// Kalau event tidak ditemukan di database
if (!$event) {
    die("Error: Data Event tidak ditemukan di database.");
}

// 3. LOGIC: TAMBAH TUGAS BARU
if (isset($_POST['add_task'])) {
    $nama = $_POST['nama_tugas'];
    $divisi = $_POST['divisi_id'];
    $user = !empty($_POST['user_id']) ? $_POST['user_id'] : "NULL"; 
    $deadline = $_POST['deadline'];
    
    $q = "INSERT INTO jobdesk (divisi_id, user_id, nama_tugas, deadline, status) 
          VALUES ('$divisi', $user, '$nama', '$deadline', 'Pending')";
    
    if(mysqli_query($conn, $q)){
        header("Location: jobdesk.php?id=$id_event");
    } else {
        echo "<script>alert('Gagal: " . mysqli_error($conn) . "');</script>";
    }
}

// 4. LOGIC: PINDAH STATUS
if (isset($_GET['move']) && isset($_GET['status'])) {
    $jid = $_GET['move'];
    $stat = $_GET['status'];
    mysqli_query($conn, "UPDATE jobdesk SET status='$stat' WHERE jobdesk_id='$jid'");
    header("Location: jobdesk.php?id=$id_event");
}

// 5. LOGIC: HAPUS TUGAS
if (isset($_GET['del'])) {
    $jid = $_GET['del'];
    mysqli_query($conn, "DELETE FROM jobdesk WHERE jobdesk_id='$jid'");
    header("Location: jobdesk.php?id=$id_event");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jobdesk - <?= $event['nama_event'] ?></title>
    <link rel="stylesheet" href="../assets/css/style.css?v=105">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <style>
        .bg-blob { pointer-events: none !important; z-index: 0 !important; }
        .dashboard-container { position: relative; z-index: 10 !important; }
        
        /* Style Sidebar Tombol Kembali */
        .btn-back {
            background: linear-gradient(135deg, #6366f1, #8b5cf6); 
            color: white; padding: 12px; border-radius: 8px; 
            text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 8px;
            font-weight: 600; margin-bottom: 20px; text-align: center;
        }
        .btn-back:hover { opacity: 0.9; transform: translateY(-2px); transition: 0.3s; }
    </style>
</head>
<body>

    <div class="bg-blob blob-2"></div>
    <div class="bg-blob blob-4"></div>

    <div class="dashboard-container">
        
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2 class="brand-title">E-PANITIA</h2>
                <span class="role-badge">Administrator</span>
            </div>
            
            <a href="data_event.php" class="btn-back">
                <i class="ph-bold ph-arrow-left"></i> Kembali ke Event
            </a>

            <nav>
                <a href="admin.php" class="menu-item"><i class="ph-bold ph-squares-four"></i> Dashboard</a>
                <div class="menu-logout">
                    <a href="../logout.php" class="menu-item" style="color: #ef4444;"><i class="ph-bold ph-sign-out"></i> Logout</a>
                </div>
            </nav>
        </aside>

        <main class="main-content">
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div>
                    <h1 style="font-size: 24px; margin-bottom: 5px;"><?= $event['nama_event'] ?></h1>
                    <p style="color: #64748b;">Papan Tugas & Monitoring Progress</p>
                </div>
                
                <button onclick="document.getElementById('modalTask').style.display='flex'" class="btn-login" style="width: auto; padding: 12px 25px;">
                    <i class="ph-bold ph-plus"></i> Buat Tugas
                </button>
            </div>

            <div class="kanban-board">
                
                <div class="kanban-col">
                    <div class="col-header h-pending">
                        <span><i class="ph-bold ph-clock"></i> Pending</span>
                    </div>
                    <?php
                    $q = mysqli_query($conn, "SELECT j.*, d.nama_divisi, u.nama_lengkap 
                        FROM jobdesk j 
                        JOIN divisi d ON j.divisi_id = d.divisi_id 
                        LEFT JOIN users u ON j.user_id = u.user_id 
                        WHERE d.event_id='$id_event' AND j.status='Pending' ORDER BY j.deadline ASC");
                    
                    while($row = mysqli_fetch_assoc($q)):
                    ?>
                    <div class="task-card b-pending">
                        <a href="?id=<?= $id_event ?>&del=<?= $row['jobdesk_id'] ?>" onclick="return confirm('Hapus?')" class="btn-trash-task"><i class="ph-bold ph-trash"></i></a>
                        <span class="task-divisi"><?= $row['nama_divisi'] ?></span>
                        <div class="task-title"><?= $row['nama_tugas'] ?></div>
                        <div class="task-footer">
                            <div style="display: flex; align-items: center; gap: 5px;">
                                <i class="ph-bold ph-calendar-blank"></i> <?= date('d M', strtotime($row['deadline'])) ?>
                            </div>
                            <a href="?id=<?= $id_event ?>&move=<?= $row['jobdesk_id'] ?>&status=Process" class="btn-next-status">
                                <i class="ph-fill ph-arrow-circle-right" style="color: #3b82f6;"></i>
                            </a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>

                <div class="kanban-col">
                    <div class="col-header h-process">
                        <span><i class="ph-bold ph-spinner"></i> On Progress</span>
                    </div>
                    <?php
                    $q = mysqli_query($conn, "SELECT j.*, d.nama_divisi, u.nama_lengkap FROM jobdesk j JOIN divisi d ON j.divisi_id = d.divisi_id LEFT JOIN users u ON j.user_id = u.user_id WHERE d.event_id='$id_event' AND j.status='Process'");
                    while($row = mysqli_fetch_assoc($q)):
                    ?>
                    <div class="task-card b-process">
                        <span class="task-divisi"><?= $row['nama_divisi'] ?></span>
                        <div class="task-title"><?= $row['nama_tugas'] ?></div>
                        <div class="task-footer">
                            <span style="color: #2563eb; font-weight: 600; font-size: 12px;"><?= $row['nama_lengkap'] ?? 'Unassigned' ?></span>
                            <a href="?id=<?= $id_event ?>&move=<?= $row['jobdesk_id'] ?>&status=Done" class="btn-next-status">
                                <i class="ph-fill ph-check-circle" style="color: #10b981;"></i>
                            </a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>

                <div class="kanban-col">
                    <div class="col-header h-done">
                        <span><i class="ph-bold ph-check-fat"></i> Selesai</span>
                    </div>
                    <?php
                    $q = mysqli_query($conn, "SELECT j.*, d.nama_divisi FROM jobdesk j JOIN divisi d ON j.divisi_id = d.divisi_id WHERE d.event_id='$id_event' AND j.status='Done'");
                    while($row = mysqli_fetch_assoc($q)):
                    ?>
                    <div class="task-card b-done">
                        <div class="task-title" style="text-decoration: line-through; color: #94a3b8;"><?= $row['nama_tugas'] ?></div>
                        <div class="task-footer">
                            <span style="color: #10b981; font-weight: 600;">Selesai</span>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>

            </div>
        </main>
    </div>

    <div id="modalTask" class="modal-overlay" style="display: none;">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-title">Buat Tugas Baru</div>
                <button onclick="document.getElementById('modalTask').style.display='none'" class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Judul Tugas</label>
                        <input type="text" name="nama_tugas" class="form-control" required>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                        <div>
                            <label class="form-label">Deadline</label>
                            <input type="date" name="deadline" class="form-control" required>
                        </div>
                        <div>
                            <label class="form-label">Divisi</label>
                            <select name="divisi_id" class="form-control" required>
                                <?php
                                $divs = mysqli_query($conn, "SELECT * FROM divisi WHERE event_id='$id_event'");
                                while($d = mysqli_fetch_assoc($divs)):
                                ?>
                                <option value="<?= $d['divisi_id'] ?>"><?= $d['nama_divisi'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Assign User</label>
                        <select name="user_id" class="form-control">
                            <option value="">-- Unassigned --</option>
                            <?php
                            $usrs = mysqli_query($conn, "SELECT u.user_id, u.nama_lengkap FROM anggota_divisi ad JOIN users u ON ad.user_id = u.user_id JOIN divisi d ON ad.divisi_id = d.divisi_id WHERE d.event_id='$id_event'");
                            while($u = mysqli_fetch_assoc($usrs)):
                            ?>
                            <option value="<?= $u['user_id'] ?>"><?= $u['nama_lengkap'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button type="submit" name="add_task" class="btn-login">Simpan</button>
                </form>
            </div>
        </div>
    </div>

</body>
</html>