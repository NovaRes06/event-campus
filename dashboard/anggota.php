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
</head>
<body>
    
    <div class="bg-blob blob-2"></div>
    <div class="bg-blob blob-4"></div>

    <div class="dashboard-container">
        <?php include 'sidebar_common.php'; ?>

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
                    SELECT DISTINCT e.*, d.nama_divisi, ad.jabatan 
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

            </main>
    </div>
</body>
</html>