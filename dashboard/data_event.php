<?php
session_start();
require '../config/koneksi.php'; 

if (!isset($_SESSION['role'])) {
    header("Location: ../index.php");
    exit;
}

// --- 1. LOGIKA TAMBAH DIVISI ---
if (isset($_POST['tambah_divisi'])) {
    $event_id = $_POST['event_id'];
    $nama_divisi = mysqli_real_escape_string($conn, $_POST['nama_divisi']);
    if (!empty($nama_divisi)) {
        mysqli_query($conn, "INSERT INTO divisi (event_id, nama_divisi) VALUES ('$event_id', '$nama_divisi')");
        echo "<script>alert('Divisi berhasil ditambahkan!'); window.location='data_event.php';</script>";
    }
}

// --- 2. LOGIKA HAPUS DIVISI ---
if (isset($_GET['hapus_divisi'])) {
    $div_id = $_GET['hapus_divisi'];
    mysqli_query($conn, "DELETE FROM divisi WHERE divisi_id = '$div_id'");
    echo "<script>alert('Divisi dihapus!'); window.location='data_event.php';</script>";
}

// --- 3. LOGIKA HAPUS EVENT ---
if (isset($_GET['hapus_event'])) {
    $id_event = $_GET['hapus_event'];
    $query_hapus = "DELETE FROM events WHERE event_id = '$id_event'";
    if (mysqli_query($conn, $query_hapus)) {
        echo "<script>alert('Event berhasil dihapus!'); window.location='data_event.php';</script>";
    }
}

// --- 4. LOGIKA PLOTTING ANGGOTA ---
if (isset($_POST['simpan_anggota'])) {
    $divisi_id = $_POST['divisi_id'];
    $user_id   = $_POST['user_id'];
    $jabatan   = $_POST['jabatan'];
    $cek = mysqli_query($conn, "SELECT * FROM anggota_divisi WHERE user_id='$user_id' AND divisi_id='$divisi_id'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Anggota ini sudah ada di divisi tersebut!'); window.location='data_event.php';</script>";
    } else {
        mysqli_query($conn, "INSERT INTO anggota_divisi (user_id, divisi_id, jabatan) VALUES ('$user_id', '$divisi_id', '$jabatan')");
        echo "<script>alert('Anggota berhasil di-plotting!'); window.location='data_event.php';</script>";
    }
}

if (isset($_GET['hapus_anggota'])) {
    $anggota_id = $_GET['hapus_anggota'];
    mysqli_query($conn, "DELETE FROM anggota_divisi WHERE anggota_id = '$anggota_id'");
    echo "<script>alert('Anggota dihapus dari divisi!'); window.location='data_event.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Event</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=108">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        /* --- STYLE TABEL --- */
        .table-container { background: white; border-radius: 12px; box-shadow: 0 2px 15px rgba(0,0,0,0.03); border: 1px solid #f1f5f9; overflow: hidden; }
        thead tr { border-bottom: 1px solid #e2e8f0; }
        th { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.8px; padding: 20px 25px; }
        td { padding: 20px 25px; vertical-align: middle; color: #334155; font-size: 14px; border-bottom: 1px solid #f8fafc; }
        .event-name { font-weight: 700; color: #1e293b; font-size: 14px; display: block; }
        
        .badge-status { padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; display: inline-block; }
        .st-active { background: #dcfce7; color: #166534; }
        .st-completed { background: #f1f5f9; color: #475569; }
        .badge-divisi { background: #f3e8ff; color: #7e22ce; padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; }

        .btn-kelola-outline { background: white; border: 1px solid #8b5cf6; color: #8b5cf6; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: 0.2s; cursor: pointer; }
        .btn-kelola-outline:hover { background: #8b5cf6; color: white; }
        .btn-hapus-outline { background: white; border: 1px solid #ef4444; color: #ef4444; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: 0.2s; cursor: pointer; }
        .btn-hapus-outline:hover { background: #ef4444; color: white; }

        /* --- STYLE MODAL DIVISI (MENYESUAIKAN GAMBAR 1) --- */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; justify-content: center; align-items: center; backdrop-filter: blur(3px); }
        .modal-box { background: white; width: 95%; max-width: 650px; border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,0.2); overflow: hidden; animation: slideUp 0.3s ease-out; }
        
        .modal-header { padding: 20px 25px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; }
        .modal-title { font-size: 16px; font-weight: 700; color: #334155; }
        
        .modal-tabs { display: flex; background: #fff; padding: 0 25px; border-bottom: 1px solid #f1f5f9; }
        .tab-btn { padding: 15px 20px; border: none; background: none; font-size: 13px; font-weight: 600; color: #94a3b8; cursor: pointer; border-bottom: 2px solid transparent; display: flex; align-items: center; gap: 8px; transition: 0.2s; }
        .tab-btn.active { color: #6366f1; border-bottom: 2px solid #6366f1; }
        
        .tab-content { display: none; padding: 25px; min-height: 350px; }
        .tab-content.active { display: block; }

        /* INPUT DIVISI (Presisi Gambar 1) */
        .input-group-divisi { display: flex; gap: 12px; margin-bottom: 25px; }
        .form-control-divisi { 
            flex-grow: 1; 
            padding: 12px 15px; 
            border: 1px solid #e2e8f0; 
            border-radius: 8px; 
            outline: none; 
            font-size: 14px;
            color: #334155;
        }
        .form-control-divisi:focus { border-color: #6366f1; }
        
        .btn-plus-divisi { 
            background: #6366f1; 
            color: white; 
            border: none; 
            width: 45px; 
            border-radius: 8px; 
            cursor: pointer; 
            font-size: 20px; 
            display: flex; align-items: center; justify-content: center;
            transition: 0.2s;
        }
        .btn-plus-divisi:hover { background: #4f46e5; }

        /* LIST DIVISI (Presisi Gambar 1) */
        .label-list { font-size: 12px; color: #64748b; margin-bottom: 12px; display: block; font-weight: 600; }
        .divisi-list { list-style: none; padding: 0; margin: 0; }
        
        .divisi-item { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            padding: 12px 15px; 
            border: 1px solid #e2e8f0; 
            margin-bottom: 10px; 
            border-radius: 8px; 
            background: #fff; 
            transition: 0.2s;
        }
        .divisi-item:hover { border-color: #cbd5e1; box-shadow: 0 2px 8px rgba(0,0,0,0.02); }
        .divisi-text { font-weight: 500; color: #334155; font-size: 14px; }

        /* Tombol Hapus Outline Merah (Icon Sampah) */
        .btn-trash-outline { 
            background: white; 
            border: 1px solid #ef4444; /* Border Merah */
            color: #ef4444; 
            width: 32px; height: 32px; 
            border-radius: 6px; 
            cursor: pointer; 
            display: flex; align-items: center; justify-content: center;
            transition: 0.2s;
            text-decoration: none;
        }
        .btn-trash-outline:hover { background: #fef2f2; }

        /* Layout Fix */
        .bg-blob { pointer-events: none !important; z-index: 0 !important; }
        .dashboard-container { position: relative; z-index: 10 !important; }
    </style>
</head>
<body>

    <div class="bg-blob blob-1"></div>
    <div class="bg-blob blob-3"></div>

    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header"><h2 class="brand-title">E-PANITIA</h2><span class="role-badge">Administrator</span></div>
            <nav>
                <a href="admin.php" class="menu-item"><i class="ph-bold ph-squares-four"></i> Dashboard</a>
                <a href="data_event.php" class="menu-item active"><i class="ph-bold ph-calendar-plus"></i> Data Event</a>
                <a href="data_anggota.php" class="menu-item"><i class="ph-bold ph-users-three"></i> Data Anggota</a> 
                <div class="menu-logout"><a href="../logout.php" class="menu-item" style="color: #ef4444;"><i class="ph-bold ph-sign-out"></i> Logout</a></div>
            </nav>
        </aside>

        <main class="main-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <h1 style="font-size: 24px; color: #1e293b; font-weight: 700;">Daftar Event</h1>
                <a href="tambah_event.php" class="btn-login" style="width: auto; padding: 12px 20px; font-size: 14px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">+ Event Baru</a>
            </div>

            <div class="table-container">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th width="30%">NAMA EVENT</th>
                            <th width="20%">TANGGAL</th>
                            <th width="20%">STATUS</th>
                            <th width="15%" style="text-align: center;">JUMLAH DIVISI</th>
                            <th width="15%" style="text-align: center;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (isset($conn)) {
                            $cek = mysqli_query($conn, "SHOW COLUMNS FROM events LIKE 'tanggal_mulai'");
                            $sort = (mysqli_num_rows($cek) > 0) ? "tanggal_mulai" : "event_id";
                            $query = mysqli_query($conn, "SELECT * FROM events ORDER BY $sort DESC");

                            if ($query && mysqli_num_rows($query) > 0) {
                                while ($row = mysqli_fetch_assoc($query)) {
                                    $tgl = isset($row['tanggal_mulai']) ? date('d M Y', strtotime($row['tanggal_mulai'])) : '-';
                                    $eid = $row['event_id'];
                                    
                                    // Ambil data untuk JSON JS
                                    $q_div = mysqli_query($conn, "SELECT * FROM divisi WHERE event_id = '$eid'");
                                    $divisi_list = []; while($d = mysqli_fetch_assoc($q_div)){ $divisi_list[] = $d; }
                                    $jml_divisi = count($divisi_list);
                                    $json_divisi = htmlspecialchars(json_encode($divisi_list), ENT_QUOTES, 'UTF-8');

                                    $q_anggota = mysqli_query($conn, "SELECT ad.anggota_id, ad.jabatan, u.nama_lengkap, d.nama_divisi FROM anggota_divisi ad JOIN users u ON ad.user_id = u.user_id JOIN divisi d ON ad.divisi_id = d.divisi_id WHERE d.event_id = '$eid'");
                                    $anggota_list = []; while($a = mysqli_fetch_assoc($q_anggota)){ $anggota_list[] = $a; }
                                    $json_anggota = htmlspecialchars(json_encode($anggota_list), ENT_QUOTES, 'UTF-8');

                                    $st = strtolower($row['status']);
                                    $statusLabel = ($st == 'active' || $st == 'aktif') ? 'SEDANG BERJALAN' : 'SELESAI';
                                    $statusClass = ($st == 'active' || $st == 'aktif') ? 'st-active' : 'st-completed';
                        ?>
                        <tr>
                            <td><span class="event-name"><?= htmlspecialchars($row['nama_event']); ?></span></td>
                            <td style="color: #64748b; font-weight: 500;"><?= $tgl; ?></td>
                            <td><span class="badge-status <?= $statusClass; ?>"><?= $statusLabel; ?></span></td>
                            <td style="text-align: center;"><span class="badge-divisi"><?= $jml_divisi; ?> DIVISI</span></td>
                            <td style="text-align: center;">
                                <div style="display:flex; justify-content:center; gap:8px;">
                                    <button class="btn-kelola-outline" 
                                            onclick="openModal('<?= $row['event_id']; ?>', '<?= addslashes($row['nama_event']); ?>', '<?= $json_divisi; ?>', '<?= $json_anggota; ?>')">
                                        <i class="ph-bold ph-gear"></i> Kelola
                                    </button>
                                    <a href="data_event.php?hapus_event=<?= $row['event_id']; ?>" 
                                       class="btn-hapus-outline" 
                                       onclick="return confirm('⚠️ Yakin ingin menghapus event ini?')">
                                        <i class="ph-bold ph-trash"></i>
                                    </a>
                                </div>
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

    <div id="modalKelola" class="modal-overlay" style="display: none;">
        <div class="modal-box">
            
            <div class="modal-header">
                <div class="modal-title">Kelola: <span id="modalEventName">Nama Event</span></div>
                <button onclick="closeModal()" style="font-size: 24px; color: #94a3b8; border:none; background:none; cursor:pointer;">&times;</button>
            </div>

            <div class="modal-tabs">
                <button onclick="switchTab('divisi')" class="tab-btn active" id="btn-divisi"><i class="ph-bold ph-briefcase"></i> Divisi</button>
                <button onclick="switchTab('plotting')" class="tab-btn" id="btn-plotting"><i class="ph-bold ph-users"></i> Plotting Anggota</button>
            </div>

            <div id="tab-divisi" class="tab-content active">
                
                <form method="POST" class="input-group-divisi">
                    <input type="hidden" name="event_id" id="modalEventIdDivisi">
                    <input type="text" name="nama_divisi" class="form-control-divisi" placeholder="Nama Divisi Baru (Ex: Logistik)" required>
                    <button type="submit" name="tambah_divisi" class="btn-plus-divisi">
                        <i class="ph-bold ph-plus"></i>
                    </button>
                </form>

                <span class="label-list">Daftar Divisi:</span>
                <ul class="divisi-list" id="listDivisiContainer">
                    </ul>
            </div>

            <div id="tab-plotting" class="tab-content">
                <form method="POST" style="background: #f8fafc; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                    <div style="margin-bottom: 10px;">
                        <select class="form-control-divisi" name="user_id" style="width: 100%;" required>
                            <option value="">-- Pilih Anggota --</option>
                            <?php
                            $users = mysqli_query($conn, "SELECT user_id, nama_lengkap FROM users WHERE peran != 'admin' ORDER BY nama_lengkap ASC");
                            while($u = mysqli_fetch_assoc($users)){
                                echo "<option value='".$u['user_id']."'>".$u['nama_lengkap']."</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                        <select class="form-control-divisi" name="divisi_id" id="selectDivisiPlotting" required><option value="">-- Pilih Divisi --</option></select>
                        <select class="form-control-divisi" name="jabatan" required>
                            <option value="Staff">Staff</option>
                            <option value="Koordinator">Koordinator</option>
                            <option value="Ketua Pelaksana">Ketua Pelaksana</option>
                        </select>
                    </div>
                    <button type="submit" name="simpan_anggota" class="btn-login" style="padding: 10px; width:100%; font-size: 13px;">Simpan ke Divisi</button>
                </form>
                <span class="label-list">Anggota Terdaftar:</span>
                <ul class="divisi-list" id="listAnggotaContainer"></ul>
            </div>
        </div>
    </div>

    <script>
        function openModal(id, name, jsonDivisi, jsonAnggota) {
            document.getElementById('modalKelola').style.display = 'flex';
            document.getElementById('modalEventName').innerText = name;
            document.getElementById('modalEventIdDivisi').value = id;

            // --- RENDER LIST DIVISI (Presisi Gambar 1) ---
            const divisi = JSON.parse(jsonDivisi);
            const containerDiv = document.getElementById('listDivisiContainer');
            const selectDiv = document.getElementById('selectDivisiPlotting');
            
            containerDiv.innerHTML = ''; 
            selectDiv.innerHTML = '<option value="">-- Pilih Divisi --</option>';

            if (divisi.length > 0) {
                divisi.forEach(d => {
                    // Struktur HTML List Item
                    containerDiv.innerHTML += `
                        <li class="divisi-item">
                            <span class="divisi-text">${d.nama_divisi}</span>
                            <a href="data_event.php?hapus_divisi=${d.divisi_id}" class="btn-trash-outline" onclick="return confirm('Hapus divisi ini?')">
                                <i class="ph-bold ph-trash"></i>
                            </a>
                        </li>`;
                    
                    selectDiv.innerHTML += `<option value="${d.divisi_id}">${d.nama_divisi}</option>`;
                });
            } else {
                containerDiv.innerHTML = '<li style="text-align:center; color:#cbd5e1; padding:15px; font-size:13px; font-style:italic;">Belum ada divisi.</li>';
            }

            // --- RENDER LIST ANGGOTA ---
            const anggota = JSON.parse(jsonAnggota);
            const containerAng = document.getElementById('listAnggotaContainer');
            containerAng.innerHTML = '';

            if (anggota.length > 0) {
                anggota.forEach(a => {
                    containerAng.innerHTML += `
                        <li class="divisi-item">
                            <div>
                                <div style="font-weight:700; color:#334155; font-size:13px;">${a.nama_lengkap}</div>
                                <div style="font-size:11px; color:#64748b;">${a.nama_divisi} - ${a.jabatan}</div>
                            </div>
                            <a href="data_event.php?hapus_anggota=${a.anggota_id}" class="btn-trash-outline" onclick="return confirm('Hapus anggota ini?')">
                                <i class="ph-bold ph-trash"></i>
                            </a>
                        </li>`;
                });
            } else {
                containerAng.innerHTML = '<li style="text-align:center; color:#cbd5e1; padding:15px; font-size:13px; font-style:italic;">Belum ada anggota di-plotting.</li>';
            }
        }

        function closeModal() { document.getElementById('modalKelola').style.display = 'none'; }
        function switchTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
            document.getElementById('tab-' + tabName).classList.add('active');
            document.getElementById('btn-' + tabName).classList.add('active');
        }
    </script>

</body>
</html>