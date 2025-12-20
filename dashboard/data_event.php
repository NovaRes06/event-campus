<?php
session_start();
require '../config/koneksi.php';

// Cek hanya Admin yang boleh masuk sini
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}

// --- LOGIKA HAPUS EVENT (BARU) ---
if (isset($_GET['hapus'])) {
    $id_hapus = intval($_GET['hapus']);
    // Database sudah ON DELETE CASCADE, jadi divisi, jobdesk, & notulensi terkait otomatis terhapus
    $qHapus = mysqli_query($conn, "DELETE FROM events WHERE event_id='$id_hapus'");
    if ($qHapus) {
        echo "<script>alert('Event berhasil dihapus permanen!'); window.location='data_event.php';</script>";
    } else {
        echo "<script>alert('Gagal hapus: " . mysqli_error($conn) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Event Aktif</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=126">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        .btn-pantau { background: #10b981; color: white; margin-right: 5px; }
        .btn-config { background: #6366f1; color: white; margin-right: 5px; }
        .btn-del { background: #fee2e2; color: #ef4444; } /* Warna Merah utk Hapus */
        .btn-del:hover { background: #fca5a5; color: #991b1b; }
    </style>
</head>
<body>
    <div class="bg-blob blob-1"></div>
    <div class="bg-blob blob-3"></div>

    <div class="dashboard-container">
        <?php include 'sidebar_common.php'; ?>

        <main class="main-content">
            <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                <h1>Event Aktif</h1>
                <a href="tambah_event.php" class="btn-login" style="width: auto; font-size: 14px; text-decoration: none; display: inline-block;">+ Event Baru</a>
            </div>

            <div class="table-container" style="background: white; border-radius: 12px; padding: 0; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                <table style="width: 100%; margin: 0;">
                    <thead style="background: white; border-bottom: 2px solid #f1f5f9;">
                        <tr>
                            <th width="30%" style="padding: 20px;">NAMA EVENT</th>
                            <th width="15%">TANGGAL</th>
                            <th width="15%">STATUS</th>
                            <th width="15%">DIVISI</th>
                            <th width="20%">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = mysqli_query($conn, "SELECT * FROM events WHERE status IN ('upcoming', 'active') ORDER BY tanggal_mulai DESC");

                        if ($query && mysqli_num_rows($query) > 0) {
                            while ($row = mysqli_fetch_assoc($query)) {
                                $tgl = isset($row['tanggal_mulai']) ? date('d M Y', strtotime($row['tanggal_mulai'])) : '-';
                                $statusClass = ($row['status'] == 'active') ? 'badge-purple' : 'badge-green';
                        ?>
                        <tr>
                            <td style="padding: 20px;">
                                <div style="font-weight: bold; font-size: 14px; color: #334155;"><?= htmlspecialchars($row['nama_event']); ?></div>
                                <div style="font-size: 12px; color: #64748b; margin-top: 5px;"><?= htmlspecialchars(substr($row['deskripsi'], 0, 40)); ?>...</div>
                            </td>
                            <td style="font-size: 13px; color: #475569;"><?= $tgl; ?></td>
                            <td><span class="<?= $statusClass; ?>"><?= strtoupper($row['status']); ?></span></td>
                            <td style="text-align: center; color: #475569;"><b><?= $row['jumlah_divisi']; ?></b> Divisi</td>
                            <td>
                                <a href="detail_event.php?id=<?= $row['event_id']; ?>" class="btn-kelola btn-pantau" title="Pantau"><i class="ph-bold ph-eye"></i></a>     
                                <a href="data_event.php?hapus=<?= $row['event_id']; ?>" class="btn-kelola btn-del" onclick="return confirm('⚠️ PERHATIAN: Menghapus event ini akan menghapus semua data divisi, jobdesk, dan notulensi di dalamnya. Lanjutkan?')" title="Hapus Permanen">
                                    <i class="ph-bold ph-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php 
                            } 
                        } else { 
                            echo "<tr><td colspan='5' style='text-align:center; padding:50px; color:#94a3b8;'>Tidak ada event berjalan.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>