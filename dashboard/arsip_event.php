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
    <title>Arsip Event - E-Panitia</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=110">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        .role-badge { background: #dbeafe; color: #1e40af; } 
        .bg-blob { pointer-events: none !important; z-index: 0 !important; }
        .dashboard-container { position: relative; z-index: 10 !important; }
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
                <a href="anggota.php" class="menu-item">
                    <i class="ph-bold ph-house"></i> Beranda
                </a>
                
                <a href="arsip_event.php" class="menu-item active">
                    <i class="ph-bold ph-archive-box"></i> Arsip Event
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
            
            <div style="margin-bottom: 30px;">
                <h1 style="margin:0;">Arsip Event Selesai 📂</h1>
                <p style="color: #64748b;">Riwayat event yang pernah kamu ikuti.</p>
            </div>

            <div class="table-container" style="background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <th width="40%" style="padding: 15px 20px; text-align: left; color: #64748b; font-size: 12px;">NAMA EVENT</th>
                            <th width="20%" style="text-align: left; color: #64748b; font-size: 12px;">PERAN SAYA</th>
                            <th width="20%" style="text-align: left; color: #64748b; font-size: 12px;">TANGGAL</th>
                            <th width="20%" style="text-align: left; color: #64748b; font-size: 12px;">AKSI</th>
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
                        <tr style="border-bottom: 1px solid #f8fafc;">
                            <td style="padding: 15px 20px; font-weight: 600; color: #334155;"><?= $hist['nama_event'] ?></td>
                            <td style="color: #64748b; font-size: 13px;"><?= $hist['nama_divisi'] ?> (<?= $hist['jabatan'] ?>)</td>
                            <td style="color: #64748b; font-size: 13px;"><?= date('d M Y', strtotime($hist['tanggal_mulai'])) ?></td>
                            <td>
                                <a href="detail_event.php?id=<?= $hist['event_id'] ?>" style="color: #6366f1; text-decoration: none; font-weight: 600; font-size: 13px;">
                                    Lihat Detail &rarr;
                                </a>
                            </td>
                        </tr>
                        <?php 
                            }
                        } else {
                            echo "<tr><td colspan='4' style='text-align: center; padding: 40px; color: #94a3b8;'>Belum ada arsip event.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        </main>
    </div>
</body>
</html>