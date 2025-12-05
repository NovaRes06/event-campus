<?php
session_start();
require '../config/koneksi.php'; 

// Cek keamanan
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'ketua')) {
    header("Location: ../index.php");
    exit;
}

// --- HITUNG DATA REAL-TIME ---
// 1. Event Aktif
$res_event = mysqli_query($conn, "SELECT COUNT(*) as total FROM events WHERE status = 'active'");
$data_event = mysqli_fetch_assoc($res_event);

// 2. Total Anggota
$res_user = mysqli_query($conn, "SELECT COUNT(*) as total FROM users");
$data_user = mysqli_fetch_assoc($res_user);

// 3. Jobdesk Pending (Semua tugas yang belum selesai)
$res_job = mysqli_query($conn, "SELECT COUNT(*) as total FROM jobdesk WHERE status = 'Pending'");
$data_job = mysqli_fetch_assoc($res_job);

// 4. Tugas Selesai
$res_done = mysqli_query($conn, "SELECT COUNT(*) as total FROM jobdesk WHERE status = 'Done'");
$data_done = mysqli_fetch_assoc($res_done);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - E-PANITIA</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=108">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body>

    <div class="bg-blob blob-1"></div>
    <div class="bg-blob blob-3"></div>

    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2 class="brand-title">E-PANITIA</h2>
                <span class="role-badge">Administrator</span>
            </div>
            <nav>
                <a href="admin.php" class="menu-item active"><i class="ph-bold ph-squares-four"></i> Dashboard</a>
                <a href="data_event.php" class="menu-item"><i class="ph-bold ph-calendar-plus"></i> Data Event</a>
                <a href="data_anggota.php" class="menu-item"><i class="ph-bold ph-users-three"></i> Data Anggota</a> 
                <div class="menu-logout">
                    <a href="../logout.php" class="menu-item" style="color: #ef4444;"><i class="ph-bold ph-sign-out"></i> Logout</a>
                </div>
            </nav>
        </aside>

        <main class="main-content">
            
            <div class="welcome-section">
                <h1>Halo, <?php echo htmlspecialchars($_SESSION['nama']); ?>! 👋</h1>
                <p>Berikut adalah ringkasan kegiatan kepanitiaan hari ini.</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card card-purple">
                    <i class="ph-bold ph-calendar-check stat-icon"></i>
                    <div class="stat-number"><?= $data_event['total']; ?></div>
                    <div class="stat-label">Event Aktif</div>
                </div>

                <div class="stat-card card-blue">
                    <i class="ph-bold ph-users stat-icon"></i>
                    <div class="stat-number"><?= $data_user['total']; ?></div>
                    <div class="stat-label">Total Anggota</div>
                </div>
                <div class="stat-card card-orange">
                    <i class="ph-bold ph-check-fat stat-icon"></i>
                    <div class="stat-number"><?= $data_done['total']; ?></div>
                    <div class="stat-label">Event Selesai</div>
                </div>
            </div>

            <div class="content-card">
                <h3 style="margin-bottom: 20px; font-size: 18px;">Jadwal Terbaru</h3>
                
                <?php
                $query_next = mysqli_query($conn, "SELECT * FROM events WHERE tanggal_mulai >= CURDATE() ORDER BY tanggal_mulai ASC LIMIT 1");
                
                if(mysqli_num_rows($query_next) > 0) {
                    $next = mysqli_fetch_assoc($query_next);
                    $tgl = date('d F Y', strtotime($next['tanggal_mulai']));
                ?>
                    <div style="display: flex; align-items: center; gap: 15px; padding: 15px; background: #f8fafc; border-radius: 10px;">
                        <div style="background: #e0e7ff; color: #4338ca; padding: 10px; border-radius: 8px;">
                            <i class="ph-bold ph-calendar" style="font-size: 24px;"></i>
                        </div>
                        <div>
                            <h4 style="margin: 0; color: #1e293b;"><?= htmlspecialchars($next['nama_event']); ?></h4>
                            <p style="margin: 5px 0 0; font-size: 13px; color: #64748b;">
                                <i class="ph-bold ph-clock"></i> <?= $tgl; ?>
                            </p>
                        </div>
                    </div>
                <?php } else { ?>
                    <div style="text-align: center; padding: 40px; color: #94a3b8;">
                        <i class="ph-duotone ph-calendar-slash" style="font-size: 48px; margin-bottom: 10px;"></i>
                        <p>Belum ada jadwal event yang akan datang.</p>
                    </div>
                <?php } ?>
            </div>

        </main>
    </div>
</body>
</html>