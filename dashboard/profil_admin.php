<?php
session_start();
require '../config/koneksi.php';

$current_page = basename($_SERVER['PHP_SELF']);

// Cek hanya Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}

$id_user = $_SESSION['user_id'];

// --- LETAKKAN LOGIC UPDATE DISINI ---
if (isset($_POST['update_profil'])) {
    $nama = $_POST['nama_lengkap'];
    $email = $_POST['email'];
    $pass_baru = $_POST['password_baru'];
    
    // Cek duplikat email
    $stmtCek = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
    $stmtCek->bind_param("si", $email, $id_user);
    $stmtCek->execute();
    if ($stmtCek->get_result()->num_rows > 0) {
        echo "<script>alert('Email sudah digunakan!');</script>";
    } else {
        if (!empty($pass_baru)) {
            $hash = md5($pass_baru);
            // Reset flag ganti pass otomatis jadi 0 saat password diubah
            $stmt = $conn->prepare("UPDATE users SET nama_lengkap=?, email=?, password=?, perlu_ganti_pass=0 WHERE user_id=?");
            $stmt->bind_param("sssi", $nama, $email, $hash, $id_user);
        } else {
            // Jika dia sedang mode wajib ganti tapi tidak isi password
            if (isset($_GET['wajib_ganti'])) {
                echo "<script>alert('Anda HARUS mengisi password baru!'); window.location='profil_admin.php?wajib_ganti=1';</script>";
                exit;
            }
            $stmt = $conn->prepare("UPDATE users SET nama_lengkap=?, email=? WHERE user_id=?");
            $stmt->bind_param("ssi", $nama, $email, $id_user);
        }
        
        if ($stmt->execute()) {
            $_SESSION['nama'] = $nama;
            echo "<script>alert('Profil berhasil diperbarui!'); window.location='profil_admin.php';</script>";
        }
    }
}

// Ambil Data Admin Terbaru untuk ditampilkan di form
$qUser = mysqli_query($conn, "SELECT * FROM users WHERE user_id='$id_user'");
$user = mysqli_fetch_assoc($qUser);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil Admin - E-PANITIA</title>
    
    <link rel="stylesheet" href="../assets/css/style.css?v=110">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <style>
        /* Menggunakan style yang sama persis dengan profil_anggota.php */
        .role-badge { background: #fee2e2; color: #991b1b; } /* Merah untuk Admin */
        
        .profile-container {
            background: white; border-radius: 20px; padding: 40px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            display: grid; grid-template-columns: 300px 1fr; gap: 50px; align-items: start;
        }
        .profile-left { text-align: center; padding-right: 50px; border-right: 1px solid #f1f5f9; }
        
        .avatar-large {
            width: 150px; height: 150px;
            /* Gradient Admin sedikit beda biar keren (Merah-Orange) */
            background: linear-gradient(135deg, #f43f5e, #fb923c); 
            color: white; font-size: 60px; font-weight: 700;
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px; box-shadow: 0 15px 30px rgba(244, 63, 94, 0.3); border: 5px solid #fff;
        }
        
        .role-badge-large {
            background: #fee2e2; color: #991b1b; 
            padding: 8px 20px; border-radius: 30px; 
            font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;
            display: inline-block; margin-top: 10px;
        }
        
        .profile-right h3 { margin-bottom: 25px; color: #1e293b; font-size: 18px; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; }
        .bg-blob { pointer-events: none !important; z-index: 0 !important; }
        .dashboard-container { position: relative; z-index: 10 !important; }
        
        /* Helper input password eye */
        .input-wrapper { position: relative; }
        .toggle-password { position: absolute; right: 15px; top: 12px; cursor: pointer; color: #94a3b8; font-size: 20px; }
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
                <a href="admin.php" class="menu-item <?= ($current_page == 'admin.php') ? 'active' : '' ?>">
                    <i class="ph-bold ph-squares-four"></i> Dashboard
                </a>
                <a href="data_event.php" class="menu-item <?= ($current_page == 'data_event.php') ? 'active' : '' ?>">
                    <i class="ph-bold ph-calendar-plus"></i> Data Event
                </a>
                <a href="arsip_event.php" class="menu-item <?= ($current_page == 'arsip_event.php') ? 'active' : '' ?>">
                    <i class="ph-bold ph-archive-box"></i> Arsip Event
                </a>
                <a href="data_anggota.php" class="menu-item <?= ($current_page == 'data_anggota.php') ? 'active' : '' ?>">
                    <i class="ph-bold ph-users-three"></i> Users
                </a>
                <a href="profil_admin.php" class="menu-item <?= ($current_page == 'profil_admin.php') ? 'active' : '' ?>">
                    <i class="ph-bold ph-user-gear"></i> Profil Saya
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
                    <strong>⚠️ PERHATIAN:</strong> Akun Admin Anda masih menggunakan password default. Silakan buat password baru di bawah ini untuk mengaktifkan menu lainnya.
                </div>
                <style>.sidebar nav { pointer-events: none; opacity: 0.5; }</style>
            <?php endif; ?>
            
            <h1 style="margin-bottom: 30px;">Profil Saya</h1>

            <div class="profile-container">
                
                <div class="profile-left">
                    <div class="avatar-large"><?= strtoupper(substr($user['nama_lengkap'], 0, 1)) ?></div>
                    <h2 style="margin: 10px 0 5px; color: #1e293b;"><?= htmlspecialchars($user['nama_lengkap']) ?></h2>
                    <p style="color: #64748b; font-size: 14px; margin-bottom: 15px;"><?= htmlspecialchars($user['email']) ?></p>
                    <span class="role-badge-large">Administrator</span>
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
                                <label class="form-label">Email Login</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                        </div>

                        <h3>Keamanan</h3>
                        <div class="form-group">
                            <label class="form-label">Password Baru</label>
                            <div class="input-wrapper">
                                <input type="password" name="password_baru" id="passBaru" class="form-control" placeholder="Biarkan kosong jika tidak ingin mengganti">
                                <i class="ph ph-eye-slash toggle-password" onclick="togglePass('passBaru', this)"></i>
                            </div>
                            <small style="color: #64748b; margin-top: 5px; display: block;">*Gunakan password yang kuat demi keamanan sistem.</small>
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
                icon.style.color = '#f43f5e'; // Warna mata merah utk admin
            } else {
                icon.classList.replace('ph-eye', 'ph-eye-slash');
                icon.style.color = '#94a3b8';
            }
        }
    </script>
</body>
</html>