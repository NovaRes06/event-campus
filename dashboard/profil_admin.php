<?php
session_start();
require '../config/koneksi.php';

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
</head>
<body>

    <div class="bg-blob blob-1"></div>
    <div class="bg-blob blob-3"></div>

    <div class="dashboard-container">
        <?php include 'sidebar_common.php'; ?>

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