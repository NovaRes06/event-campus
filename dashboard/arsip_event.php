<?php
session_start();
require '../config/koneksi.php';

if (!isset($_SESSION['role'])) { header("Location: ../index.php"); exit; }
$id_user = $_SESSION['user_id'];
$my_role = $_SESSION['role'];
$is_admin = ($my_role == 'admin');

// --- LOGIC HAPUS EVENT (KHUSUS ADMIN) ---
if ($is_admin && isset($_GET['hapus'])) {
    $id_hapus = $_GET['hapus'];
    $qHapus = mysqli_query($conn, "DELETE FROM events WHERE event_id='$id_hapus'");
    if ($qHapus) {
        echo "<script>alert('Event berhasil dihapus permanen!'); window.location='arsip_event.php';</script>";
    } else {
        echo "<script>alert('Gagal hapus event: " . mysqli_error($conn) . "');</script>";
    }
}
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
        .badge-gray { background: #f1f5f9; color: #64748b; padding: 5px 12px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .badge-red { background: #fee2e2; color: #991b1b; padding: 5px 12px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .btn-del { color: #ef4444; background: #fee2e2; padding: 5px 10px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600; margin-left: 10px; }
        .btn-del:hover { background: #fca5a5; }
    </style>
</head>
<body>
    <div class="bg-blob blob-2"></div>
    <div class="bg-blob blob-4"></div>

    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2 class="brand-title">E-PANITIA</h2>
                <span class="role-badge"><?= $is_admin ? 'Administrator' : 'Anggota' ?></span>
            </div>
            
            <nav>
                <?php if ($is_admin): ?>
                    <a href="admin.php" class="menu-item"><i class="ph-bold ph-squares-four"></i> Dashboard</a>
                    <a href="data_event.php" class="menu-item"><i class="ph-bold ph-calendar-plus"></i> Data Event</a>
                    <a href="arsip_event.php" class="menu-item active"><i class="ph-bold ph-archive-box"></i> Arsip Event</a>
                    <a href="data_anggota.php" class="menu-item"><i class="ph-bold ph-users-three"></i> Data Anggota</a>
                    <a href="profil_admin.php" class="menu-item"><i class="ph-bold ph-user-gear"></i> Profil Saya</a>
                <?php else: ?>
                    <a href="anggota.php" class="menu-item"><i class="ph-bold ph-house"></i> Beranda</a>
                    <a href="arsip_event.php" class="menu-item active"><i class="ph-bold ph-archive-box"></i> Arsip Event</a>
                    <a href="profil_anggota.php" class="menu-item"><i class="ph-bold ph-user"></i> Profil</a>
                <?php endif; ?>

                <div class="menu-logout">
                    <a href="../logout.php" class="menu-item" style="color: #ef4444;"><i class="ph-bold ph-sign-out"></i> Logout</a>
                </div>
            </nav>
        </aside>

        <main class="main-content">
            <div style="margin-bottom: 30px;">
                <h1 style="margin:0;">Arsip Event 📂</h1>
                <p style="color: #64748b;"><?= $is_admin ? 'Manajemen seluruh riwayat event kampus.' : 'Riwayat event yang pernah kamu ikuti.' ?></p>
            </div>

            <div class="table-container" style="background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <th width="35%" style="padding: 15px 20px; text-align: left; color: #64748b; font-size: 12px;">NAMA EVENT</th>
                            <th width="20%" style="text-align: left; color: #64748b; font-size: 12px;">STATUS</th>
                            <th width="25%" style="text-align: left; color: #64748b; font-size: 12px;">TANGGAL SELESAI</th>
                            <th width="20%" style="text-align: left; color: #64748b; font-size: 12px;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($is_admin) {
                            // Admin: Lihat semua event yang selesai/batal/arsip
                            $sql = "SELECT * FROM events WHERE status IN ('completed', 'cancelled', 'archived') ORDER BY tanggal_selesai DESC";
                        } else {
                            // Anggota: Hanya yang ia ikuti
                            $sql = "SELECT e.* FROM events e 
                                    JOIN divisi d ON e.event_id = d.event_id 
                                    JOIN anggota_divisi ad ON d.divisi_id = ad.divisi_id 
                                    WHERE ad.user_id = '$id_user' AND e.status IN ('completed', 'cancelled') 
                                    ORDER BY e.tanggal_selesai DESC";
                        }
                        $qArsip = mysqli_query($conn, $sql);

                        if (mysqli_num_rows($qArsip) > 0) {
                            while ($row = mysqli_fetch_assoc($qArsip)) {
                                $badge = ($row['status'] == 'completed') ? 'badge-gray' : 'badge-red';
                        ?>
                        <tr style="border-bottom: 1px solid #f8fafc;">
                            <td style="padding: 15px 20px; font-weight: 600; color: #334155;"><?= $row['nama_event'] ?></td>
                            <td><span class="<?= $badge ?>"><?= strtoupper($row['status']) ?></span></td>
                            <td style="color: #64748b; font-size: 13px;"><?= date('d M Y', strtotime($row['tanggal_selesai'])) ?></td>
                            <td>
                                <a href="detail_event.php?id=<?= $row['event_id'] ?>" style="color: #6366f1; text-decoration: none; font-weight: 600; font-size: 13px;">Lihat</a>
                                
                                <?php if($is_admin): ?>
                                    <a href="arsip_event.php?hapus=<?= $row['event_id'] ?>" class="btn-del" onclick="return confirm('⚠️ Hapus permanen event ini? Seluruh data jobdesk & divisi akan hilang!')">Hapus</a>
                                <?php endif; ?>
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