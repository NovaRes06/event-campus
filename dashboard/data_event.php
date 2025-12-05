<?php
session_start();
require '../config/koneksi.php'; // Gunakan require agar stop jika file hilang

if (!isset($_SESSION['role'])) {
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
        .btn-kelola { background: #6366f1; color: white; padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; cursor: pointer; border: none; }
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; justify-content: center; align-items: center; backdrop-filter: blur(5px); }
        .modal-box { background: white; width: 90%; max-width: 600px; border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,0.2); overflow: hidden; animation: slideUp 0.3s ease-out; }
        .modal-tabs { display: flex; background: #f8fafc; padding: 10px 25px 0; border-bottom: 1px solid #e2e8f0; }
        .tab-btn { padding: 10px 20px; border: none; background: none; font-size: 14px; font-weight: 600; color: #64748b; cursor: pointer; border-bottom: 2px solid transparent; }
        .tab-btn.active { color: #6366f1; border-bottom: 2px solid #6366f1; }
        .tab-content { display: none; padding: 20px; }
        .tab-content.active { display: block; }
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
                <a href="data_anggota.php" class="menu-item"><i class="ph-bold ph-users-three"></i> Data Anggota</a> 
                <a href="data_laporan.php" class="menu-item"><i class="ph-bold ph-clipboard-text"></i> Laporan</a>
                <div class="menu-logout"><a href="../logout.php" class="menu-item" style="color: #ef4444;"><i class="ph-bold ph-sign-out"></i> Logout</a></div>
            </nav>
        </aside>

        <main class="main-content">
            <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                <h1>Daftar Event 📅</h1>
                <a href="tambah_event.php" class="btn-login" style="width: auto; padding: 10px 20px; font-size: 14px; text-decoration: none; display: inline-block;">+ Event Baru</a>
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
                            // Cek dulu apakah kolom 'tanggal_mulai' ada. Jika tidak, urutkan by ID.
                            $cekKolom = mysqli_query($conn, "SHOW COLUMNS FROM events LIKE 'tanggal_mulai'");
                            $orderBy = (mysqli_num_rows($cekKolom) > 0) ? "tanggal_mulai" : "event_id";

                            // Query SELECT
                            $query = mysqli_query($conn, "SELECT * FROM events ORDER BY $orderBy DESC");

                            if ($query && mysqli_num_rows($query) > 0) {
                                while ($row = mysqli_fetch_assoc($query)) {
                                    // Handle Data Kosong / Null agar tidak error
                                    $tgl = isset($row['tanggal_mulai']) ? date('d M Y', strtotime($row['tanggal_mulai'])) : '-';
                                    
                                    // Fix Deprecated substr null
                                    $deskripsiRaw = isset($row['deskripsi']) ? $row['deskripsi'] : ''; 
                                    $deskripsi = htmlspecialchars(substr($deskripsiRaw, 0, 45));

                                    // Fix Undefined array key jumlah_divisi
                                    $divisi = isset($row['jumlah_divisi']) ? $row['jumlah_divisi'] : 0; 
                                    
                                    // Badge Status
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
                                <a href="edit_event.php?id=<?= $row['event_id']; ?>" class="btn-kelola">
                                    <i class="ph-bold ph-gear"></i> Kelola Full
                                </a>
                            </td>
                        </tr>
                        <?php 
                                } 
                            } else { 
                                echo "<tr><td colspan='5' style='text-align:center; padding:50px; color:#94a3b8;'>Belum ada data event.</td></tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' style='text-align:center; color:red; padding:20px;'>Koneksi Database Gagal.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    
    <div id="modalKelola" class="modal-overlay" style="display: none;">
        <div class="modal-box">
             <div style="padding: 15px 25px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                <div style="font-size: 16px; font-weight: 700;">Kelola: <span id="modalEventName">Nama Event</span></div>
                <button onclick="closeModal()" style="font-size: 24px; color: #94a3b8; border:none; background:none; cursor:pointer;">&times;</button>
            </div>
            <div class="modal-tabs">
                <button onclick="switchTab('divisi')" class="tab-btn active" id="btn-divisi">Divisi</button>
                <button onclick="switchTab('plotting')" class="tab-btn" id="btn-plotting">Plotting Anggota</button>
            </div>
            <div id="tab-divisi" class="tab-content active"><div class="list-group"><div style="text-align: center; padding: 20px; color: #cbd5e1; font-size: 12px;">Belum ada divisi</div></div></div>
            <div id="tab-plotting" class="tab-content"></div>
        </div>
    </div>
    <script>
        function switchTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
            document.getElementById('tab-' + tabName).classList.add('active');
            document.getElementById('btn-' + tabName).classList.add('active');
        }
        function openModal(eventName) { document.getElementById('modalKelola').style.display = 'flex'; document.getElementById('modalEventName').innerText = eventName; }
        function closeModal() { document.getElementById('modalKelola').style.display = 'none'; }
    </script>
</body>
</html>