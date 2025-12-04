<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Event</title>
    
    <link rel="stylesheet" href="../assets/css/style.css?v=106">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <style>
        /* Style Modal & Badge */
        .badge-purple { background: #e0e7ff; color: #4338ca; padding: 5px 12px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .badge-green { background: #dcfce7; color: #166534; padding: 5px 12px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        
        .btn-kelola { 
            background: #6366f1; color: white; padding: 8px 16px; border-radius: 6px; 
            font-size: 13px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; 
            cursor: pointer; position: relative; z-index: 100; border: none;
        }
        .btn-kelola:hover { background: #4f46e5; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3); }

        /* Modal Styles */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); z-index: 9999;
            display: flex; justify-content: center; align-items: center;
            backdrop-filter: blur(5px);
        }
        .modal-box {
            background: white; width: 90%; max-width: 600px;
            border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            overflow: hidden; animation: slideUp 0.3s ease-out;
        }
        
        .modal-tabs { display: flex; background: #f8fafc; padding: 10px 25px 0; border-bottom: 1px solid #e2e8f0; }
        .tab-btn {
            padding: 10px 20px; border: none; background: none;
            font-size: 14px; font-weight: 600; color: #64748b; cursor: pointer;
            border-bottom: 2px solid transparent;
        }
        .tab-btn.active { color: #6366f1; border-bottom: 2px solid #6366f1; }
        .tab-content { display: none; padding: 20px; }
        .tab-content.active { display: block; }
        
        .list-item { padding: 10px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; }
        
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
                <a href="admin.php" class="menu-item">
                    <i class="ph-bold ph-squares-four"></i> Dashboard
                </a>

                <a href="adm_data_event.php" class="menu-item active">
                    <i class="ph-bold ph-calendar-plus"></i> Data Event
                </a>

                <a href="data_anggota.php" class="menu-item">
                    <i class="ph-bold ph-users-three"></i> Data Anggota
                </a> 

                <a href="#" class="menu-item">
                    <i class="ph-bold ph-clipboard-text"></i> Laporan
                </a>
                
                <div class="menu-logout">
                    <a href="index.php" class="menu-item" style="color: #ef4444;">
                        <i class="ph-bold ph-sign-out"></i> Logout
                    </a>
                </div>
            </nav>
        </aside>

        <main class="main-content">
            <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                <h1>Daftar Event 📅</h1>
                <button class="btn-login" style="width: auto; padding: 10px 20px; font-size: 14px;">+ Event Baru</button>
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
                        
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 50px; color: #94a3b8;">
                                <i class="ph-duotone ph-folder-dashed" style="font-size: 48px; margin-bottom: 10px; display: block;"></i>
                                Belum ada data event yang ditambahkan.
                            </td>
                        </tr>

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

            <div id="tab-divisi" class="tab-content active">
                <form style="display: flex; gap: 10px; margin-bottom: 15px;">
                    <input type="text" class="form-control" placeholder="Nama Divisi Baru" required>
                    <button type="button" class="btn-login" style="width: auto;">+</button>
                </form>
                <div class="list-group">
                    <div style="text-align: center; padding: 20px; color: #cbd5e1; font-size: 12px;">Belum ada divisi</div>
                </div>
            </div>

            <div id="tab-plotting" class="tab-content">
                <form style="background: #f8fafc; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                    <div style="margin-bottom: 10px;">
                        <select class="form-control">
                            <option>Pilih Anggota...</option>
                        </select>
                    </div>
                    <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                        <select class="form-control">
                            <option>Pilih Divisi...</option>
                        </select>
                        <select class="form-control">
                            <option>Staff</option>
                            <option>Koordinator</option>
                        </select>
                    </div>
                    <button type="button" class="btn-login" style="padding: 10px; font-size: 13px;">Simpan</button>
                </form>
                <div class="list-group">
                    <div style="text-align: center; padding: 20px; color: #cbd5e1; font-size: 12px;">Belum ada anggota</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
            document.getElementById('tab-' + tabName).classList.add('active');
            document.getElementById('btn-' + tabName).classList.add('active');
        }
        function openModal(eventName) {
            document.getElementById('modalKelola').style.display = 'flex';
            document.getElementById('modalEventName').innerText = eventName;
        }
        function closeModal() {
            document.getElementById('modalKelola').style.display = 'none';
        }
    </script>
</body>
</html>