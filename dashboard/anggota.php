<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Anggota - E-Panitia</title>
    
    <link rel="stylesheet" href="../assets/css/style.css?v=105">
    
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <style>
        /* Warna Sidebar Khusus Anggota (Biru Muda) */
        .role-badge { background: #dbeafe; color: #1e40af; } 
        
        /* Tombol Detail */
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
        
        /* Fix Background */
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
                <a href="anggota.html" class="menu-item active">
                    <i class="ph-bold ph-check-square-offset"></i> Tugas Saya
                </a>
                <a href="#" class="menu-item">
                    <i class="ph-bold ph-chats-circle"></i> Notulensi
                </a>
                <a href="#" class="menu-item">
                    <i class="ph-bold ph-user"></i> Profil
                </a>
                
                <div class="menu-logout">
                    <a href="../index.html" class="menu-item" style="color: #ef4444;">
                        <i class="ph-bold ph-sign-out"></i> Logout
                    </a>
                </div>
            </nav>
        </aside>

        <main class="main-content">
            
            <div class="welcome-section">
                <h1>Semangat, Budi Anggota! 🚀</h1>
                <p>Cek tugas yang harus kamu selesaikan hari ini.</p>
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
                <h3 style="margin-bottom: 20px;">Daftar Tugas (Jobdesk)</h3>
                
                <div class="task-item">
                    <div>
                        <h4 style="margin-bottom: 5px; font-size: 14px; color: #334155;">Desain Poster Instagram</h4>
                        <span style="font-size: 11px; background: #fee2e2; color: #ef4444; padding: 4px 10px; border-radius: 20px; font-weight: 600;">
                            Deadline: Besok
                        </span>
                    </div>
                    <a href="#" class="btn-detail">Detail</a>
                </div>

                <div class="task-item">
                    <div>
                        <h4 style="margin-bottom: 5px; font-size: 14px; color: #334155;">Hubungi Pemateri Seminar</h4>
                        <span style="font-size: 11px; background: #fef3c7; color: #d97706; padding: 4px 10px; border-radius: 20px; font-weight: 600;">
                            Deadline: 20 Nov 2025
                        </span>
                    </div>
                    <a href="#" class="btn-detail">Detail</a>
                </div>

                <div class="task-item">
                    <div>
                        <h4 style="margin-bottom: 5px; font-size: 14px; color: #94a3b8; text-decoration: line-through;">
                            Booking Gedung Serbaguna
                        </h4>
                        <span style="font-size: 11px; background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 20px; font-weight: 600;">
                            Selesai
                        </span>
                    </div>
                    <span style="font-size: 24px; color: #10b981;"><i class="ph-fill ph-check-circle"></i></span>
                </div>

            </div>
        </main>
    </div>

</body>
</html>