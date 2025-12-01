<?php
session_start();
include '../config/koneksi.php'; 

// Cek Role Admin
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'ketua')) {
    header("Location: ../index.php"); exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Arsip Laporan Rapat</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=110">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <style>
        /* Style Khusus Laporan */
        .badge-type { background: #f0e6ff; color: #6d28d9; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; }
        .list-table-container { 
            background: white; border-radius: 12px; padding: 0; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            min-height: 300px;
        }
        /* Style Sidebar */
        .bg-blob { pointer-events: none !important; z-index: 0 !important; }
        .dashboard-container { position: relative; z-index: 10 !important; }
    </style>
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
                <a href="admin.php" class="menu-item"><i class="ph-bold ph-squares-four"></i> Dashboard</a>
                <a href="data_event.php" class="menu-item"><i class="ph-bold ph-calendar-plus"></i> Data Event</a>
                <a href="data_anggota.php" class="menu-item"><i class="ph-bold ph-users-three"></i> Data Anggota</a>
                
                <a href="data_laporan.php" class="menu-item active"><i class="ph-bold ph-clipboard-text"></i> Laporan</a>
                
                <div class="menu-logout">
                    <a href="../logout.php" class="menu-item" style="color: #ef4444;"><i class="ph-bold ph-sign-out"></i> Logout</a>
                </div>
            </nav>
        </aside>

        <main class="main-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h1>Arsip Notulensi Rapat 📝</h1>
                <a href="tambah_notulensi.php" class="btn-login" style="width: auto; padding: 12px 25px;">
                    <i class="ph-bold ph-plus"></i> Buat Notulensi
                </a>
            </div>

            <div class="list-table-container">
                <table style="width: 100%;">
                    <thead>
                        <tr>
                            <th width="30%">JUDUL NOTULENSI</th>
                            <th width="20%">EVENT</th>
                            <th width="15%">DIVISI</th>
                            <th width="15%">JENIS RAPAT</th>
                            <th width="10%">TANGGAL</th>
                            <th width="10%">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = mysqli_query($conn, "
                            SELECT n.*, e.nama_event, d.nama_divisi 
                            FROM notulensi n
                            JOIN events e ON n.event_id = e.event_id
                            LEFT JOIN divisi d ON n.divisi_id = d.divisi_id
                            ORDER BY n.tanggal_rapat DESC
                        ");

                        if (mysqli_num_rows($query) == 0) {
                            echo '<tr><td colspan="6" style="text-align: center; padding: 40px; color: #94a3b8;">Belum ada arsip notulensi rapat.</td></tr>';
                        }

                        while ($row = mysqli_fetch_assoc($query)) :
                            $jenis_rapat = str_replace(' ', '', $row['jenis_rapat']); // Untuk class CSS
                        ?>
                        <tr>
                            <td style="font-weight: 600; color: #334155;"><?= htmlspecialchars($row['judul_notulen']); ?></td>
                            <td><?= htmlspecialchars($row['nama_event']); ?></td>
                            <td><?= $row['nama_divisi'] ?? 'Umum'; ?></td>
                            <td><span class="badge-type"><?= $row['jenis_rapat']; ?></span></td>
                            <td><?= date('d M Y', strtotime($row['tanggal_rapat'])); ?></td>
                            <td>
                                <a href="#" style="color: #4158d0; margin-right: 5px;" title="Lihat Detail"><i class="ph-bold ph-eye"></i></a>
                                <a href="#" style="color: #ef4444;" onclick="return confirm('Hapus notulensi?')" title="Hapus"><i class="ph-bold ph-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

</body>
</html>