<?php
<<<<<<< HEAD
session_start();
require '../config/koneksi.php'; 
=======
session_start(); 
require '../config/koneksi.php'; // 1. Panggil koneksi database
>>>>>>> 7f733da01298b851207f3834d6b4df65958cec78

// Cek keamanan
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'ketua')) {
    header("Location: ../index.php");
    exit;
}

<<<<<<< HEAD
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
=======
// --- LOGIKA MENGHITUNG STATISTIK DARI DATABASE ---

// 1. Hitung Event Aktif (Status = 'active')
$queryEvent = mysqli_query($conn, "SELECT COUNT(*) AS total FROM events WHERE status = 'active'");
$dataEvent = mysqli_fetch_assoc($queryEvent);
$totalEvent = $dataEvent['total'];

// 2. Hitung Total Anggota (Semua user kecuali admin, opsional bisa dihapus WHERE-nya jika ingin hitung semua)
$queryAnggota = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE peran != 'admin'");
$dataAnggota = mysqli_fetch_assoc($queryAnggota);
$totalAnggota = $dataAnggota['total'];

// 3. Hitung Jobdesk Pending (Status = 'Pending')
$queryPending = mysqli_query($conn, "SELECT COUNT(*) AS total FROM jobdesk WHERE status = 'Pending'");
$dataPending = mysqli_fetch_assoc($queryPending);
$totalPending = $dataPending['total'];

// 4. Hitung Tugas Selesai (Status = 'Done')
$queryDone = mysqli_query($conn, "SELECT COUNT(*) AS total FROM jobdesk WHERE status = 'Done'");
$dataDone = mysqli_fetch_assoc($queryDone);
$totalDone = $dataDone['total'];

>>>>>>> 7f733da01298b851207f3834d6b4df65958cec78
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
                <a href="data_anggota.php" class="menu-item"><i class="ph-bold ph-users-three"></i> Data Panitia</a> 
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
<<<<<<< HEAD
                    <div class="stat-number"><?= $data_event['total']; ?></div>
=======
                    <div class="stat-number"><?= $totalEvent; ?></div>
>>>>>>> 7f733da01298b851207f3834d6b4df65958cec78
                    <div class="stat-label">Event Aktif</div>
                </div>

                <div class="stat-card card-blue">
                    <i class="ph-bold ph-users stat-icon"></i>
<<<<<<< HEAD
                    <div class="stat-number"><?= $data_user['total']; ?></div>
                    <div class="stat-label">Total Panitia</div>
                </div>
                <div class="stat-card card-orange">
                    <i class="ph-bold ph-check-fat stat-icon"></i>
                    <div class="stat-number"><?= $data_done['total']; ?></div>
                    <div class="stat-label">Event Selesai</div>
=======
                    <div class="stat-number"><?= $totalAnggota; ?></div>
                    <div class="stat-label">Total Anggota</div>
                </div>

                <div class="stat-card card-pink">
                    <i class="ph-bold ph-clock-countdown stat-icon"></i>
                    <div class="stat-number"><?= $totalPending; ?></div>
                    <div class="stat-label">Jobdesk Pending</div>
                </div>

                <div class="stat-card card-orange">
                    <i class="ph-bold ph-check-fat stat-icon"></i>
                    <div class="stat-number"><?= $totalDone; ?></div>
                    <div class="stat-label">Tugas Selesai</div>
>>>>>>> 7f733da01298b851207f3834d6b4df65958cec78
                </div>
            </div>

            <div class="content-card">
<<<<<<< HEAD
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
=======
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3 style="margin: 0; font-size: 18px;">Jadwal Event Terdekat 📅</h3>
                    <a href="data_event.php" style="font-size: 13px; font-weight: 600; color: #6366f1; text-decoration: none;">Lihat Semua &rarr;</a>
                </div>

                <?php
                // 1. Query: Ambil 3 event terdekat (status active/pending)
                $queryJadwal = mysqli_query($conn, "SELECT * FROM events 
                                                    WHERE status IN ('active', 'pending') 
                                                    ORDER BY tanggal_mulai ASC 
                                                    LIMIT 3");

                // 2. Cek apakah ada data
                if (mysqli_num_rows($queryJadwal) > 0):
                ?>
                    <div class="schedule-list">
                        <?php while($row = mysqli_fetch_assoc($queryJadwal)): 
                            // Format Tanggal (Contoh: 20 Jan 2025)
                            $tgl = date('d M Y', strtotime($row['tanggal_mulai']));
                            
                            // Warna Badge Status
                            $bgStatus = ($row['status'] == 'active') ? '#e0e7ff' : '#fef3c7';
                            $colorStatus = ($row['status'] == 'active') ? '#4338ca' : '#d97706';
                        ?>
                        
                        <div style="display: flex; align-items: center; padding: 15px 0; border-bottom: 1px solid #f1f5f9;">
                            
                            <div style="background: #f8fafc; padding: 10px 15px; border-radius: 10px; text-align: center; margin-right: 15px; min-width: 70px;">
                                <div style="font-size: 18px; font-weight: 800; color: #6366f1;"><?= date('d', strtotime($row['tanggal_mulai'])); ?></div>
                                <div style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: #64748b;"><?= date('M', strtotime($row['tanggal_mulai'])); ?></div>
                            </div>

                            <div style="flex-grow: 1;">
                                <h4 style="margin: 0 0 5px 0; font-size: 15px; color: #1e293b;"><?= htmlspecialchars($row['nama_event']); ?></h4>
                                <p style="margin: 0; font-size: 13px; color: #94a3b8;">
                                    <?= htmlspecialchars(substr($row['deskripsi'], 0, 60)) . '...'; ?>
                                </p>
                            </div>

                            <div style="text-align: right; min-width: 80px;">
                                <span style="background: <?= $bgStatus ?>; color: <?= $colorStatus ?>; padding: 5px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase;">
                                    <?= $row['status'] ?>
                                </span>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>

                <?php else: ?>
                    
                    <div style="text-align: center; padding: 40px; color: #94a3b8;">
                        <i class="ph-duotone ph-calendar-slash" style="font-size: 48px; margin-bottom: 10px;"></i>
                        <p>Belum ada jadwal event yang akan datang.</p>
                        <a href="tambah_event.php" class="btn-login" style="display: inline-block; width: auto; padding: 8px 20px; margin-top: 10px; font-size: 13px;">+ Buat Event</a>
                    </div>

                <?php endif; ?>
>>>>>>> 7f733da01298b851207f3834d6b4df65958cec78
            </div>

        </main>
    </div>
</body>
</html>