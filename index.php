<?php
session_start();
// Cek file koneksi
if (file_exists('config/koneksi.php')) {
    include 'config/koneksi.php';
} else {
    // Fallback jika path berbeda
    $conn = false; 
}

// Redirect jika sudah login
if (isset($_SESSION['status']) && $_SESSION['status'] == "login") {
    if ($_SESSION['role'] == 'admin') {
        header("Location: dashboard/admin.php");
    } else {
        header("Location: dashboard/anggota.php");
    }
    exit;
}

$error = "";

if (isset($_POST['login'])) {
    if (!$conn) {
        $error = "Error: Koneksi database tidak terhubung.";
    } else {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = $_POST['password'];
        
        // --- LOGIKA MD5 ---
        // Karena database sudah di-convert ke MD5, input user juga harus di-MD5
        $password_md5 = md5($password);

        $query = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' AND password='$password_md5'");
        
        if (mysqli_num_rows($query) > 0) {
            $data = mysqli_fetch_assoc($query);
            
            $_SESSION['user_id'] = $data['user_id'];
            $_SESSION['nama'] = $data['nama_lengkap'];
            $_SESSION['role'] = $data['peran'];
            $_SESSION['status'] = "login";

            if ($data['peran'] == 'admin') {
                header("Location: dashboard/admin.php");
            } else {
                // --- LOGIC BARU SESUAI ERD ---
                if ($data['perlu_ganti_pass'] == 1) {
                    // Jika user baru, paksa ke profil dan suruh ganti password
                    echo "<script>
                        alert('Demi keamanan, User Baru WAJIB mengganti password default!');
                        window.location='dashboard/profil_anggota.php?wajib_ganti=1';
                    </script>";
                } else {
                    header("Location: dashboard/anggota.php");
                }
            }
            exit;
        } else {
            $error = "Email atau Password salah.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - E-PANITIA</title>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <link rel="stylesheet" href="assets/css/style.css?v=105">
</head>
<body class="login-layout">
    
    <div class="bg-blob blob-1"></div>
    <div class="bg-blob blob-2"></div>
    <div class="bg-blob blob-3"></div>
    <div class="bg-blob blob-4"></div>

    <div class="login-card">
        
        <h1 class="brand-title">E-PANITIA</h1>
        <p class="welcome-text">Selamat Datang!</p>

        <?php if($error): ?>
            <div class="alert-error">
                <i class="ph-bold ph-warning-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label">Email Kampus</label>
                <div class="input-wrapper">
                    <input type="email" name="email" class="form-control" placeholder="contoh@kampus.ac.id" required autofocus>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="input-wrapper">
                    <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password" required>
                    <i class="ph ph-eye-slash toggle-password" id="togglePassword"></i>
                </div>
            </div>
            
            <button type="submit" name="login" class="btn-login">
                MASUK SEKARANG
            </button>
        </form>
    </div>

    <script>
        const toggleBtn = document.getElementById('togglePassword');
        const passInput = document.getElementById('password');

        toggleBtn.addEventListener('click', () => {
            const type = passInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passInput.setAttribute('type', type);
            
            if(type === 'text') {
                toggleBtn.classList.replace('ph-eye-slash', 'ph-eye');
                toggleBtn.style.color = '#c850c0'; 
            } else {
                toggleBtn.classList.replace('ph-eye', 'ph-eye-slash');
                toggleBtn.style.color = '#94a3b8'; 
            }
        });
    </script>

</body>
</html>