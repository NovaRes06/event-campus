<?php
session_start();
require '../config/koneksi.php';

// Cek hanya Admin yang boleh masuk sini
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Event</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=106">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        .badge-purple { background: #e0e7ff; color: #4338ca; padding: 5px 12px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .badge-green { background: #dcfce7; color: #166534; padding: 5px 12px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .btn-kelola { 
            padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; 
            text-decoration: none; display: inline-flex; align-items: center; gap: 5px; 
            cursor: pointer; border: none; transition: 0.2s;
        }
        .btn-pantau { background: #10b981; color: white; margin-right: 5px; }
        .btn-pantau:hover { background: #059669; }
        .btn-config { background: #6366f1; color: white; }
        .btn-config:hover { background: #4f46e5; }
        
        .bg-blob { pointer-events: none !important; z-index: 0 !important; }
        .dashboard-container { position: relative; z-index: 10 !important; }
    </style>
</head>
<body>
    <div class="bg-blob blob-1"></div>
    <div class="bg-blob blob-3"></div>

    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header"><h2 class="brand-title">E-PANITIA</h2></div>
            <nav>
                <a href="admin.php" class="menu-item"><i class="ph-bold ph-squares-four"></i> Dashboard</a>
                <a href="data_event.php" class="menu-item active"><i class="ph-bold ph-calendar-plus"></i> Data Event</a>
                <a href="arsip_event.php" class="menu-item"><i class="ph-bold ph-archive-box"></i> Arsip Event</a>
                <a href="data_anggota.php" class="menu-item"><i class="ph-bold ph-users-three"></i> Data Anggota</a>
                <a href="profil_admin.php" class="menu-item"><i class="ph-bold ph-user-gear"></i> Profil Saya</a>
                <div class="menu-logout"><a href="../logout.php" class="menu-item" style="color: #ef4444;"><i class="ph-bold ph-sign-out"></i> Logout</a></div>
            </nav>
        </aside>

        <main class="main-content">
            <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                <h1>Daftar Event 📅</h1>
                <a href="tambah_event.php" class="btn-login" style="width: auto; padding: 15px 20px; font-size: 14px; text-decoration: none; display: inline-block;">+ Event Baru</a>
            </div>

            <div class="table-container" style="background: white; border-radius: 12px; padding: 0; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                <table style="width: 100%; margin: 0;">
                    <thead style="background: white; border-bottom: 2px solid #f1f5f9;">
                        <tr>
                            <th width="30%" style="padding: 20px;">NAMA EVENT</th>
                            <th width="20%">TANGGAL</th>
                            <th width="15%">STATUS</th>
                            <th width="15%">JUMLAH DIVISI</th>
                            <th width="20%">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (isset($conn)) {
                            $cekKolom = mysqli_query($conn, "SHOW COLUMNS FROM events LIKE 'tanggal_mulai'");
                            $orderBy = (mysqli_num_rows($cekKolom) > 0) ? "tanggal_mulai" : "event_id";

                            $query = mysqli_query($conn, "SELECT * FROM events WHERE status IN ('upcoming', 'active') ORDER BY $orderBy DESC");

                            if ($query && mysqli_num_rows($query) > 0) {
                                while ($row = mysqli_fetch_assoc($query)) {
                                    $tgl = isset($row['tanggal_mulai']) ? date('d M Y', strtotime($row['tanggal_mulai'])) : '-';
                                    $deskripsi = htmlspecialchars(substr($row['deskripsi'] ?? '', 0, 45));
                                    $divisi = isset($row['jumlah_divisi']) ? $row['jumlah_divisi'] : 0; 
                                    $statusClass = ($row['status'] == 'active') ? 'badge-purple' : 'badge-green';
                        ?>
                        <tr>
                            <td style="padding: 20px;">
                                <div style="font-weight: bold; font-size: 14px; color: #334155;">
                                    <?= htmlspecialchars($row['nama_event']); ?>
                                </div>
                                <div style="font-size: 12px; color: #64748b; margin-top: 5px;">
                                    <?= $deskripsi; ?>...
                                </div>
                            </td>
                            <td style="font-size: 13px; color: #475569;"><?= $tgl; ?></td>
                            <td><span class="<?= $statusClass; ?>"><?= $row['status']; ?></span></td>
                            <td style="text-align: center; color: #475569;"><b><?= $divisi; ?></b> Divisi</td>
                            <td>
                                <a href="detail_event.php?id=<?= $row['event_id']; ?>" class="btn-kelola btn-pantau" title="Lihat Dashboard Event">
                                    <i class="ph-bold ph-eye"></i> Pantau
                                </a>
                                <a href="edit_event.php?id=<?= $row['event_id']; ?>" class="btn-kelola btn-config" title="Edit Pengaturan Event">
                                    <i class="ph-bold ph-gear"></i> Kelola
                                </a>
                            </td>
                        </tr>
                        <?php 
                                } 
                            } else { 
                                echo "<tr><td colspan='5' style='text-align:center; padding:50px; color:#94a3b8;'>Belum ada data event.</td></tr>";
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>