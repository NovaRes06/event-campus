<?php
session_start();
require '../config/koneksi.php';

// Cek keamanan
if (!isset($_SESSION['role'])) {
    header("Location: ../index.php");
    exit;
}

$id_user = $_SESSION['user_id'];

// --- LOGIC UPDATE PROFIL (UPDATED) ---
if (isset($_POST['update_profil'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $pass_baru = $_POST['password_baru'];
    
    // Default Query (Ganti Nama Saja)
    $query = "UPDATE users SET nama_lengkap='$nama' WHERE user_id='$id_user'";
    
    // Jika Password Diisi
    if (!empty($pass_baru)) {
        $hash = md5($pass_baru); // Sesuai request MD5
        
        // UPDATE PASSWORD + MATIKAN perlu_ganti_pass
        $query = "UPDATE users SET nama_lengkap='$nama', password='$hash', perlu_ganti_pass=0 WHERE user_id='$id_user'";
    } else {
        // Cek jika dia sedang dalam mode wajib ganti tapi tidak ngisi password
        if (isset($_GET['wajib_ganti'])) {
            echo "<script>alert('Anda HARUS mengisi password baru!');</script>";
            // Stop eksekusi agar tidak lanjut
            exit; // Atau redirect balik
        }
    }
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['nama'] = $nama;
        echo "<script>alert('Profil berhasil diperbarui!'); window.location='profil_anggota.php';</script>";
    } else {
        echo "<script>alert('Gagal update: " . mysqli_error($conn) . "');</script>";
    }
}

// Ambil Data User Terbaru
$qUser = mysqli_query($conn, "SELECT * FROM users WHERE user_id='$id_user'");
$user = mysqli_fetch_assoc($qUser);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil Saya - E-PANITIA</title>
    
    <link rel="stylesheet" href="../assets/css/style.css?v=110">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <style>
        .role-badge { background: #dbeafe; color: #1e40af; } 
        .profile-container {
            background: white; border-radius: 20px; padding: 40px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            display: grid; grid-template-columns: 300px 1fr; gap: 50px; align-items: start;
        }
        .profile-left { text-align: center; padding-right: 50px; border-right: 1px solid #f1f5f9; }
        .avatar-large {
            width: 150px; height: 150px;
            background: linear-gradient(135deg, #3b82f6, #06b6d4); 
            color: white; font-size: 60px; font-weight: 700;
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px; box-shadow: 0 15px 30px rgba(59, 130, 246, 0.3); border: 5px solid #fff;
        }
        .role-badge-large {
            background: #dbeafe; color: #1e40af; 
            padding: 8px 20px; border-radius: 30px; 
            font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;
            display: inline-block; margin-top: 10px;
        }
        .profile-right h3 { margin-bottom: 25px; color: #1e293b; font-size: 18px; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; }
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
                <a href="anggota.php" class="menu-item">
                    <i class="ph-bold ph-house"></i> Beranda
                </a>
                
                <a href="arsip_event.php" class="menu-item">
                    <i class="ph-bold ph-archive-box"></i> Arsip Event
                </a>

                <a href="profil_anggota.php" class="menu-item active">
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
            <?php if(isset($_GET['wajib_ganti'])): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #f87171;">
                <strong>⚠️ PERHATIAN:</strong> Akun Anda masih menggunakan password default. Silakan buat password baru di bawah ini untuk bisa mengakses menu lainnya.
            </div>
            <style>.sidebar nav { pointer-events: none; opacity: 0.5; }</style>
            <?php endif; ?>
            
            <h1 style="margin-bottom: 30px;">Profil Saya</h1>

            <div class="profile-container">
                
                <div class="profile-left">
                    <div class="avatar-large"><?= strtoupper(substr($user['nama_lengkap'], 0, 1)) ?></div>
                    <h2 style="margin: 10px 0 5px; color: #1e293b;"><?= htmlspecialchars($user['nama_lengkap']) ?></h2>
                    <p style="color: #64748b; font-size: 14px; margin-bottom: 15px;"><?= htmlspecialchars($user['email']) ?></p>
                    <span class="role-badge-large"><?= ucfirst($user['peran']) ?></span>
                </div>

                <div class="profile-right">
                    <form method="POST">
                        
                        <h3>Data Diri</h3>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
                            <div class="form-group">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($user['nama_lengkap']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email Kampus</label>
                                <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" style="background: #f8fafc; color: #94a3b8;" readonly>
                            </div>
                        </div>

                        <h3>Keamanan</h3>
                        <div class="form-group">
                            <label class="form-label">Password Baru</label>
                            <div class="input-wrapper">
                                <input type="password" name="password_baru" id="passBaru" class="form-control" placeholder="Biarkan kosong jika tidak ingin mengganti">
                                <i class="ph ph-eye-slash toggle-password" onclick="togglePass('passBaru', this)"></i>
                            </div>
                        </div>

                        <div style="text-align: right; margin-top: 30px;">
                            <button type="submit" name="update_profil" class="btn-login" style="width: auto; padding: 12px 40px;">
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