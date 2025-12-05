<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Anggota - E-Panitia</title>
    
    <link rel="stylesheet" href="../assets/css/style.css?v=107">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <style>
        /* Warna Sidebar Khusus Anggota (Biru Muda) */
        .role-badge { background: #dbeafe; color: #1e40af; } 
        
        /* Tombol Detail Kecil */
        .btn-detail { 
            background: #6366f1; color: white; border: none; 
            padding: 8px 15px; border-radius: 8px; cursor: pointer; font-size: 12px; 
            text-decoration: none; display: inline-block;
        }
        .btn-detail:hover { background: #4f46e5; }

        /* Item List Tugas */
        .task-item {
            display: flex; align-items: center; justify-content: space-between; 
            padding: 15px; border-bottom: 1px solid #f1f5f9;
            transition: 0.2s;
        }
        .task-item:hover { background-color: #f8fafc; }

        /* Badge Status */
        .status-badge { padding: 4px 10px; border-radius: 4px; font-size: 11px; font-weight: 600; }
        .status-pending { background: #fef3c7; color: #d97706; } /* Kuning */
        .status-process { background: #e0e7ff; color: #3b82f6; } /* Biru */
        
        /* Fix Background & Modal */
        .bg-blob { pointer-events: none !important; z-index: 0 !important; }
        .dashboard-container { position: relative; z-index: 10 !important; }
        .modal-overlay { z-index: 9999 !important; }
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
                    <i class="ph-bold ph-check-square-offset"></i> Tugas Saya
                </a>
                
                <a href="view_notulensi.php" class="menu-item">
                    <i class="ph-bold ph-chats-circle"></i> Notulensi
                </a>
                
                <a href="profil_anggota.php" class="menu-item">
                    <i class="ph-bold ph-user"></i> Profil
                </a>
                
                <div class="menu-logout">
                    <a href="../logout.php" class="menu-item" style="color: #ef4444;">
                        <i class="ph-bold ph-sign-out"></i> Logout
                    </a>
                </div>
            </nav>
        </aside>

        <main class="main-content">
            
            <div class="welcome-section">
                <h1>Semangat, Budi! 🚀</h1>
                <p>Kamu punya 2 tugas yang harus diselesaikan.</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card card-orange">
                    <i class="ph-bold ph-clock stat-icon"></i>
                    <div class="stat-number">2</div>
                    <div class="stat-label">Tugas Pending</div>
                </div>
                <div class="stat-card card-blue">
                    <i class="ph-bold ph-check-circle stat-icon"></i>
                    <div class="stat-number">5</div>
                    <div class="stat-label">Tugas Selesai</div>
                </div>
            </div>

            <div class="content-card">
                <h3 style="margin-bottom: 20px;">Jobdesk Saya 📝</h3>
                
                <div class="task-item">
                    <div>
                        <div style="font-size: 11px; color: #94a3b8; margin-bottom: 2px;">INSPACE 2025 / Acara</div>
                        <h4 style="margin: 0; font-size: 14px; color: #334155;">Konsep Rundown Acara</h4>
                        <div style="margin-top: 5px;">
                            <span class="status-badge status-process">Sedang Dikerjakan</span>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 12px; color: #ef4444; font-weight: 600; margin-bottom: 5px;">20 Jan 2025</div>
                        <button class="btn-detail" onclick="openModal('Konsep Rundown Acara', 'Membuat susunan acara lengkap dari pembukaan sampai penutupan.', 'Process')">Detail</button>
                    </div>
                </div>

                <div class="task-item">
                    <div>
                        <div style="font-size: 11px; color: #94a3b8; margin-bottom: 2px;">INSPACE 2025 / Humas</div>
                        <h4 style="margin: 0; font-size: 14px; color: #334155;">Hubungi Pemateri Seminar</h4>
                        <div style="margin-top: 5px;">
                            <span class="status-badge status-pending">Pending</span>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 12px; color: #ef4444; font-weight: 600; margin-bottom: 5px;">25 Jan 2025</div>
                        <button class="btn-detail" onclick="openModal('Hubungi Pemateri Seminar', 'Menghubungi Pak Sandiaga Uno untuk konfirmasi kehadiran.', 'Pending')">Detail</button>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <div id="modalDetail" class="modal-overlay" style="display: none;">
        <div class="modal-box" style="max-width: 500px;">
            <div class="modal-header">
                <div class="modal-title">Detail Tugas</div>
                <button onclick="closeModal()" class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <h3 id="taskTitle" style="color: #6366f1; margin-bottom: 10px;">Judul Tugas</h3>
                
                <div style="background: #f8fafc; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <p id="taskDesc" style="color: #475569; font-size: 14px; line-height: 1.6;">Deskripsi tugas...</p>
                </div>

                <div class="form-group">
                    <label class="form-label">Update Status</label>
                    <select class="form-control" id="taskStatus">
                        <option value="Pending">Pending (Belum Mulai)</option>
                        <option value="Process">On Progress (Sedang Dikerjakan)</option>
                        <option value="Done">Selesai</option>
                    </select>
                </div>

                <button class="btn-login" onclick="closeModal()" style="margin-top: 10px;">Simpan Perubahan</button>
            </div>
        </div>
    </div>

    <script>
        function openModal(title, desc, status) {
            document.getElementById('modalDetail').style.display = 'flex';
            document.getElementById('taskTitle').innerText = title;
            document.getElementById('taskDesc').innerText = desc;
            document.getElementById('taskStatus').value = status;
        }

        function closeModal() {
            document.getElementById('modalDetail').style.display = 'none';
        }
    </script>

</body>
</html>