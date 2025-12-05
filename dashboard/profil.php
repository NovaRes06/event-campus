<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil Saya - E-PANITIA</title>
    
    <link rel="stylesheet" href="../assets/css/style.css?v=109">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <style>
        /* --- STYLE KHUSUS HALAMAN PROFIL (Full Layout) --- */
        
        .profile-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            display: grid;
            grid-template-columns: 300px 1fr; /* Kiri 300px, Kanan sisanya */
            gap: 50px;
            align-items: start;
        }

        /* Bagian Kiri (Foto) */
        .profile-left {
            text-align: center;
            padding-right: 50px;
            border-right: 1px solid #f1f5f9;
        }

        .avatar-large {
            width: 150px; height: 150px;
            background: linear-gradient(135deg, #6366f1, #a855f7);
            color: white; font-size: 60px; font-weight: 700;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 15px 30px rgba(99, 102, 241, 0.3);
            border: 5px solid #fff;
        }

        .role-badge-large {
            background: #e0e7ff; color: #4338ca; 
            padding: 8px 20px; border-radius: 30px; 
            font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;
            display: inline-block; margin-top: 10px;
        }

        /* Bagian Kanan (Form) */
        .profile-right h3 {
            margin-bottom: 25px; color: #1e293b; font-size: 18px; 
            border-bottom: 2px solid #f1f5f9; padding-bottom: 10px;
        }

        /* Global Fix */
        .bg-blob { pointer-events: none !important; z-index: 0 !important; }
        .dashboard-container { position: relative; z-index: 10 !important; }
    </style>
</head>
<body>

    <div class="bg-blob blob-2"></div>
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
                <a href="data_laporan.php" class="menu-item"><i class="ph-bold ph-clipboard-text"></i> Laporan</a>
                
                <a href="profil.php" class="menu-item active">
                    <i class="ph-bold ph-user-gear"></i> Profil Saya
                </a>
                
                <div class="menu-logout">
                    <a href="../logout.php" class="menu-item" style="color: #ef4444;"><i class="ph-bold ph-sign-out"></i> Logout</a>
                </div>
            </nav>
        </aside>

        <main class="main-content">
            <h1 style="margin-bottom: 30px;">Pengaturan Akun ⚙️</h1>

            <div class="profile-container">
                
                <div class="profile-left">
                    <div class="avatar-large">A</div>
                    <h2 style="margin: 10px 0 5px; color: #1e293b;">Si Paling Admin</h2>
                    <p style="color: #64748b; font-size: 14px; margin-bottom: 15px;">admin@kampus.id</p>
                    <span class="role-badge-large">Administrator</span>
                    
                    <button class="btn-kelola" style="margin-top: 30px; background: #fff; color: #ef4444; border: 1px solid #fee2e2; width: 100%; justify-content: center;">
                        <i class="ph-bold ph-trash"></i> Hapus Akun
                    </button>
                </div>

                <div class="profile-right">
                    <form onsubmit="event.preventDefault(); alert('Perubahan berhasil disimpan!');">
                        
                        <h3>Data Diri</h3>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
                            <div class="form-group">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" value="Si Paling Admin">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email Kampus</label>
                                <input type="email" class="form-control" value="admin@kampus.id" style="background: #f8fafc; color: #94a3b8;" readonly>
                            </div>
                        </div>

                        <h3>Keamanan</h3>
                        <div class="form-group">
                            <label class="form-label">Password Baru</label>
                            <div class="input-wrapper">
                                <input type="password" id="passBaru" class="form-control" placeholder="Biarkan kosong jika tidak ingin mengganti">
                                <i class="ph ph-eye-slash toggle-password" onclick="togglePass('passBaru', this)"></i>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Ulangi Password Baru</label>
                            <div class="input-wrapper">
                                <input type="password" id="passKonfirm" class="form-control" placeholder="Ketik ulang password baru">
                                <i class="ph ph-eye-slash toggle-password" onclick="togglePass('passKonfirm', this)"></i>
                            </div>
                        </div>

                        <div style="text-align: right; margin-top: 30px;">
                            <button type="submit" class="btn-login" style="width: auto; padding: 12px 40px;">
                                Simpan Perubahan
                            </button>
                        </div>

                    </form>
                </div>

            </div>
        </main>
    </div>

    <script>
        function togglePass(inputId, icon) {
            const input = document.getElementById(inputId);
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            
            if(type === 'text') {
                icon.classList.replace('ph-eye-slash', 'ph-eye');
                icon.style.color = '#c850c0';
            } else {
                icon.classList.replace('ph-eye', 'ph-eye-slash');
                icon.style.color = '#94a3b8';
            }
        }
    </script>

</body>
</html>