<?php
session_start(); 

// Cek keamanan
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'ketua')) {
    header("Location: ../index.php");
    exit;
}
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

                <a href="#" class="menu-item">
                    <i class="ph-bold ph-calendar-plus"></i> Data Event
                </a>

                <a href="#" class="menu-item">
                    <i class="ph-bold ph-users-three"></i> Data Anggota
                </a> 

                <a href="#" class="menu-item">
                    <i class="ph-bold ph-clipboard-text"></i> Laporan
                </a>
                
                <div class="menu-logout">
                    <a href="#" class="menu-item" style="color: #ef4444;">
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
                    <div class="stat-number">3</div>
                    <div class="stat-label">Event Aktif</div>
                </div>

                <div class="stat-card card-blue">
                    <i class="ph-bold ph-users stat-icon"></i>
                    <div class="stat-number">45</div>
                    <div class="stat-label">Total Anggota</div>
                </div>

                <div class="stat-card card-pink">
                    <i class="ph-bold ph-clock-countdown stat-icon"></i>
                    <div class="stat-number">12</div>
                    <div class="stat-label">Jobdesk Pending</div>
                </div>

                <div class="stat-card card-orange">
                    <i class="ph-bold ph-check-fat stat-icon"></i>
                    <div class="stat-number">24</div>
                    <div class="stat-label">Tugas Selesai</div>
                </div>
            </div>

            <div class="content-card">
                <h3 style="margin-bottom: 20px; font-size: 18px;">Jadwal Terbaru</h3>
                <div style="text-align: center; padding: 40px; color: #94a3b8;">
                    <i class="ph-duotone ph-calendar-slash" style="font-size: 48px; margin-bottom: 10px;"></i>
                    <p>Belum ada jadwal event yang akan datang.</p>
                </div>
            </div>

        </main>
    </div>

</body>
</html>