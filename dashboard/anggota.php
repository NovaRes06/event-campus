<?php
session_start();
require '../config/koneksi.php';

// Cek keamanan
if (!isset($_SESSION['role'])) { header("Location: ../index.php"); exit; }
$id_user = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Beranda Anggota - E-Panitia</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=110">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        .role-badge { background: #dbeafe; color: #1e40af; } 
        .bg-blob { pointer-events: none !important; z-index: 0 !important; }
        .dashboard-container { position: relative; z-index: 10 !important; }
        
        /* Card Event Custom */
        .event-card {
            background: white; border-radius: 16px; overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: 0.3s;
            border: 1px solid #f1f5f9; display: flex; flex-direction: column;
            height: 100%;
        }
        .event-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .event-header { height: 100px; background: linear-gradient(135deg, #6366f1, #8b5cf6); position: relative; }
        .event-body { padding: 20px; flex-grow: 1; display: flex; flex-direction: column; }
        .event-badge { 
            position: absolute; top: 15px; right: 15px; 
            background: rgba(255,255,255,0.2); backdrop-filter: blur(5px); color: white;
            padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: 700;
        }
        .divisi-tag {
            background: #eff6ff; color: #2563eb; padding: 5px 10px; border-radius: 6px;
            font-size: 11px; font-weight: 700; display: inline-block; margin-bottom: 10px;
        }
    </style>
</head>
<body>
    
    <div class="bg-blob blob-2"></div>
    <div class="bg-blob blob-4"></div>

    <div class="dashboard-container">
        
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2 class="brand-title">E-PANITIA</h2>
                <span class="role-badge">Anggota</span>
            </div>
            
            <nav>
                <a href="anggota.php" class="menu-item active">
                    <i class="ph-bold ph-house"></i> Beranda
                </a>
                <a href="profil_anggota.php" class="menu-item">
                    <i class="ph-bold ph-user"></i> Profil
                </a>
                <div class="menu-logout">
                    <a href="../logout.php" class="menu-item" style="color: #ef4444;"><i class="ph-bold ph-sign-out"></i> Logout</a>
                </div>
            </nav>
        </aside>

        <main class="main-content">
            
            <div class="welcome-section">
                <h1>Halo, <?= $_SESSION['nama']; ?>! 👋</h1>
                <p>Pilih event untuk melihat tugas dan notulensi.</p>
            </div>

            <h3 style="margin-bottom: 20px;">Event Aktif Saya 🚀</h3>
            <div class="stats-grid" style="grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));">
                <?php
                // Query Event Aktif yang diikuti user
                $qActive = mysqli_query($conn, "
                    SELECT e.*, d.nama_divisi, ad.jabatan 
                    FROM events e
                    JOIN divisi d ON e.event_id = d.event_id
                    JOIN anggota_divisi ad ON d.divisi_id = ad.divisi_id
                    WHERE ad.user_id = '$id_user' AND e.status IN ('active', 'pending')
                    ORDER BY e.tanggal_mulai ASC
                ");

                if (mysqli_num_rows($qActive) > 0) {
                    while ($row = mysqli_fetch_assoc($qActive)) {
                ?>
                <div class="event-card">
                    <div class="event-header">
                        <span class="event-badge">ACTIVE</span>
                    </div>
                    <div class="event-body">
                        <span class="divisi-tag"><?= $row['nama_divisi'] ?> - <?= $row['jabatan'] ?></span>
                        <h3 style="font-size: 18px; margin-bottom: 5px;"><?= $row['nama_event'] ?></h3>
                        <p style="color: #64748b; font-size: 13px; margin-bottom: 20px;">
                            <?= substr($row['deskripsi'], 0, 80) ?>...
                        </p>
                        <a href="detail_event.php?id=<?= $row['event_id'] ?>" class="btn-login" style="margin-top: auto; text-align: center; text-decoration: none;">
                            Masuk Event &rarr;
                        </a>
                    </div>
                </div>
                <?php 
                    } 
                } else {
                    echo "<p style='color: #94a3b8; font-style: italic;'>Kamu belum tergabung dalam event aktif apapun.</p>";
                }
                ?>
            </div>

            <h3 style="margin: 40px 0 20px;">Arsip Event Selesai 📂</h3>
            <div class="table-container" style="background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                <table style="width: 100%;">
                    <thead>
                        <tr>
                            <th width="40%" style="padding: 15px 20px;">NAMA EVENT</th>
                            <th width="20%">PERAN SAYA</th>
                            <th width="20%">TANGGAL</th>
                            <th width="20%">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $qArsip = mysqli_query($conn, "
                            SELECT e.*, d.nama_divisi, ad.jabatan 
                            FROM events e
                            JOIN divisi d ON e.event_id = d.event_id
                            JOIN anggota_divisi ad ON d.divisi_id = ad.divisi_id
                            WHERE ad.user_id = '$id_user' AND e.status IN ('completed', 'cancelled')
                            ORDER BY e.tanggal_mulai DESC
                        ");

                        if (mysqli_num_rows($qArsip) > 0) {
                            while ($hist = mysqli_fetch_assoc($qArsip)) {
                        ?>
                        <tr>
                            <td style="padding: 15px 20px; font-weight: 600;"><?= $hist['nama_event'] ?></td>
                            <td><?= $hist['nama_divisi'] ?> (<?= $hist['jabatan'] ?>)</td>
                            <td><?= date('d M Y', strtotime($hist['tanggal_mulai'])) ?></td>
                            <td>
                                <a href="detail_event.php?id=<?= $hist['event_id'] ?>" style="color: #6366f1; text-decoration: none; font-weight: 600;">Lihat Arsip</a>
                            </td>
                        </tr>
                        <?php 
                            }
                        } else {
                            echo "<tr><td colspan='4' style='text-align: center; padding: 20px; color: #94a3b8;'>Belum ada arsip event.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        </main>
    </div>
</body>
</html>