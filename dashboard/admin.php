<?php
session_start(); 
require '../config/koneksi.php'; // 1. Panggil koneksi database

// Cek keamanan
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'ketua')) {
    header("Location: ../index.php");
    exit;
}

// --- LOGIKA MENGHITUNG STATISTIK DARI DATABASE ---

// 1. Hitung Event Aktif (Status = 'active')
$queryEvent = mysqli_query($conn, "SELECT COUNT(*) AS total FROM events WHERE status = 'active'");
$dataEvent = mysqli_fetch_assoc($queryEvent);
$totalEvent = $dataEvent['total'];

// 2. Hitung Total Anggota (Semua user kecuali admin)
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

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - E-PANITIA</title>
    
    <link rel="stylesheet" href="../assets/css/style.css?v=105">

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
                <a href="admin.php" class="menu-item active">
                    <i class="ph-bold ph-squares-four"></i> Dashboard
                </a>

                <a href="data_event.php" class="menu-item">
                    <i class="ph-bold ph-calendar-plus"></i> Data Event
                </a>

                <a href="data_anggota.php" class="menu-item">
                    <i class="ph-bold ph-users-three"></i> Data Anggota
                </a> 
                
                <div class="menu-logout">
                    <a href="../logout.php" class="menu-item" style="color: #ef4444;">
                        <i class="ph-bold ph-sign-out"></i> Logout
                    </a>
                </div>
            </nav>
        </aside>

        <main class="main-content">
            
            <div class="welcome-section">
                <h1>Halo, <?php echo $_SESSION['nama']; ?>! 👋</h1>
                <p>Berikut adalah ringkasan kegiatan kepanitiaan hari ini.</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card card-purple">
                    <i class="ph-bold ph-calendar-check stat-icon"></i>
                    <div class="stat-number"><?= $totalEvent; ?></div>
                    <div class="stat-label">Event Aktif</div>
                </div>

                <div class="stat-card card-blue">
                    <i class="ph-bold ph-users stat-icon"></i>
                    <div class="stat-number"><?= $totalAnggota; ?></div>
                    <div class="stat-label">Total Anggota</div>
                </div>
            </div>

            <div class="content-card">
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
            </div>

        </main>
    </div>

</body>
</html>