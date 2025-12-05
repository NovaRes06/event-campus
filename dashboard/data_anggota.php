<?php
session_start();
require '../config/koneksi.php'; 

// Cek keamanan
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'ketua')) {
    header("Location: ../index.php");
    exit;
}

// --- 1. LOGIKA SIMPAN DATA (TAMBAH & EDIT) ---
if (isset($_POST['simpan'])) {
    $id     = $_POST['user_id'];
    $nama   = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $email  = mysqli_real_escape_string($conn, $_POST['email']);
    $role   = $_POST['peran'];
    $status = $_POST['status']; 

    if (empty($id)) {
        // Mode Tambah
        $cek = mysqli_query($conn, "SELECT email FROM users WHERE email = '$email'");
        if (mysqli_num_rows($cek) > 0) {
            echo "<script>alert('Email sudah terdaftar!'); window.location='data_anggota.php';</script>";
        } else {
            $pass_hash = password_hash("mhs123", PASSWORD_DEFAULT);
            $query = "INSERT INTO users (nama_lengkap, email, password, peran, status) VALUES ('$nama', '$email', '$pass_hash', '$role', 'aktif')";
            
            if (mysqli_query($conn, $query)) {
                echo "<script>alert('Anggota berhasil ditambahkan!'); window.location='data_anggota.php';</script>";
            }
        }
    } else {
        // Mode Edit
        $query = "UPDATE users SET nama_lengkap='$nama', email='$email', peran='$role', status='$status' WHERE user_id='$id'";
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Data berhasil diperbarui!'); window.location='data_anggota.php';</script>";
        }
    }
}

// --- 2. LOGIKA HAPUS DATA ---
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    // Cegah admin menghapus dirinya sendiri atau sesama admin (Opsional, untuk keamanan)
    $cek_admin = mysqli_query($conn, "SELECT peran FROM users WHERE user_id='$id'");
    $data = mysqli_fetch_assoc($cek_admin);

    if ($data['peran'] == 'admin') {
        echo "<script>alert('DILARANG MENGHAPUS SUPER ADMIN!'); window.location='data_anggota.php';</script>";
    } else {
        $query = "DELETE FROM users WHERE user_id='$id'";
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Data berhasil dihapus!'); window.location='data_anggota.php';</script>";
        }
    }
}

// --- 3. LOGIKA RESET PASSWORD (BARU) ---
if (isset($_GET['reset'])) {
    $id = $_GET['reset'];
    // Password default: mhs123
    $pass_default = password_hash("mhs123", PASSWORD_DEFAULT);
    
    $query = "UPDATE users SET password='$pass_default' WHERE user_id='$id'";
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Password berhasil direset menjadi: mhs123'); window.location='data_anggota.php';</script>";
    } else {
        echo "<script>alert('Gagal reset password.'); window.location='data_anggota.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Anggota</title>
    
    <link rel="stylesheet" href="../assets/css/style.css?v=108">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <style>
        /* Badge Role */
        .role-badge { padding: 5px 12px; border-radius: 20px; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; }
        .role-admin { background: #fee2e2; color: #ef4444; }
        .role-anggota { background: #dbeafe; color: #3b82f6; }
        .role-ketua { background: #dcfce7; color: #166534; }

        /* Badge Status */
        .status-badge { padding: 5px 10px; border-radius: 6px; font-size: 10px; font-weight: 700; text-transform: uppercase; }
        .status-aktif { background: #dcfce7; color: #166534; } 
        .status-nonaktif { background: #f1f5f9; color: #64748b; }

        /* Header Tabel */
        .table-head-text { font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; }

        /* Dropdown Menu */
        .action-container { position: relative; display: inline-block; }
        .btn-dots { background: none; border: none; font-size: 20px; color: #94a3b8; cursor: pointer; padding: 5px; transition: 0.2s; }
        .btn-dots:hover { color: #1e293b; background: #f1f5f9; border-radius: 50%; }

        .dropdown-menu { display: none; position: absolute; right: 0; top: 30px; background: white; border: 1px solid #f1f5f9; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); z-index: 100; min-width: 150px; overflow: hidden; }
        .dropdown-menu.show { display: block; animation: fadeIn 0.2s; }
        
        .dropdown-item { display: flex; align-items: center; gap: 8px; padding: 10px 15px; color: #475569; text-decoration: none; font-size: 13px; transition: 0.2s; }
        .dropdown-item:hover { background: #f8fafc; color: #3b82f6; }
        .dropdown-item.danger:hover { background: #fef2f2; color: #ef4444; }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

    <div class="bg-blob blob-1"></div>
    <div class="bg-blob blob-3"></div>

    <div class="dashboard-container">
        
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2 class="brand-title">E-PANITIA</h2>
                <span class="role-badge" style="background: rgba(255,255,255,0.2); color:white;">Administrator</span>
            </div>
            <nav>
                <a href="admin.php" class="menu-item"><i class="ph-bold ph-squares-four"></i> Dashboard</a>
                <a href="data_event.php" class="menu-item"><i class="ph-bold ph-calendar-plus"></i> Data Event</a>
                <a href="data_anggota.php" class="menu-item active"><i class="ph-bold ph-users-three"></i> Data Anggota</a> 
                <div class="menu-logout">
                    <a href="../logout.php" class="menu-item" style="color: #ef4444;"><i class="ph-bold ph-sign-out"></i> Logout</a>
                </div>
            </nav>
        </aside>

        <main class="main-content">
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <div>
                    <h1 style="font-size: 24px; color: #1e293b; margin-bottom: 5px;">Manajemen Anggota 👥</h1>
                    <p style="color: #64748b; font-size: 14px;">Kelola akun mahasiswa dan panitia.</p>
                </div>
                <button onclick="openModal()" class="btn-login" style="width: auto; padding: 12px 20px; font-size: 14px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="ph-bold ph-user-plus"></i> Tambah Anggota
                </button>
            </div>

            <div class="table-container" style="background: white; border-radius: 16px; padding: 0; overflow: visible; box-shadow: 0 4px 25px rgba(0,0,0,0.03);">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="background: #fff; border-bottom: 2px solid #f1f5f9;">
                        <tr>
                            <th width="5%" style="padding: 20px;" class="table-head-text">NO</th>
                            <th width="25%" style="padding: 20px; text-align: left;" class="table-head-text">NAMA LENGKAP</th>
                            <th width="30%" style="padding: 20px; text-align: left;" class="table-head-text">EMAIL KAMPUS</th>
                            <th width="15%" style="padding: 20px; text-align: center;" class="table-head-text">ROLE</th>
                            <th width="15%" style="padding: 20px; text-align: center;" class="table-head-text">STATUS AKUN</th>
                            <th width="10%" style="padding: 20px; text-align: center;" class="table-head-text">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $query = mysqli_query($conn, "SELECT * FROM users ORDER BY user_id DESC");
                        
                        if(mysqli_num_rows($query) > 0){
                            while ($row = mysqli_fetch_assoc($query)) {
                                $roleClass = 'role-anggota';
                                if($row['peran'] == 'admin') $roleClass = 'role-admin';
                                if($row['peran'] == 'ketua') $roleClass = 'role-ketua';

                                $status = isset($row['status']) ? $row['status'] : 'aktif';
                                $statusClass = ($status == 'aktif') ? 'status-aktif' : 'status-nonaktif';
                        ?>
                        <tr style="border-bottom: 1px solid #f8fafc;">
                            <td style="padding: 20px; text-align: center; color: #64748b; font-weight: 600;"><?= $no++; ?></td>
                            <td style="padding: 20px; font-weight: 700; color: #1e293b;">
                                <?= htmlspecialchars($row['nama_lengkap']); ?>
                            </td>
                            <td style="padding: 20px; color: #475569; font-size: 13px;">
                                <?= htmlspecialchars($row['email']); ?>
                            </td>
                            <td style="padding: 20px; text-align: center;">
                                <span class="role-badge <?= $roleClass; ?>"><?= strtoupper($row['peran']); ?></span>
                            </td>
                            <td style="padding: 20px; text-align: center;">
                                <span class="status-badge <?= $statusClass; ?>"><?= strtoupper($status); ?></span>
                            </td>
                            <td style="padding: 20px; text-align: center; overflow: visible;">
                                
                                <div class="action-container">
                                    <button class="btn-dots" onclick="toggleDropdown(<?= $row['user_id']; ?>)">
                                        <i class="ph-bold ph-dots-three-vertical"></i>
                                    </button>
                                    
                                    <div id="dropdown-<?= $row['user_id']; ?>" class="dropdown-menu">
                                        <a href="#" class="dropdown-item" onclick="openEdit('<?= $row['user_id']; ?>', '<?= addslashes($row['nama_lengkap']); ?>', '<?= $row['email']; ?>', '<?= $row['peran']; ?>', '<?= $status; ?>')">
                                            <i class="ph-bold ph-pencil-simple"></i> Edit
                                        </a>
                                        
                                        <?php if($row['peran'] != 'admin') : ?>
                                            
                                            <a href="data_anggota.php?reset=<?= $row['user_id']; ?>" class="dropdown-item" onclick="return confirm('Yakin ingin mereset password menjadi mhs123?')">
                                                <i class="ph-bold ph-key"></i> Reset Pass
                                            </a>

                                            <a href="data_anggota.php?hapus=<?= $row['user_id']; ?>" class="dropdown-item danger" onclick="return confirm('Yakin ingin menghapus anggota ini?')">
                                                <i class="ph-bold ph-trash"></i> Hapus
                                            </a>

                                        <?php endif; ?>
                                    </div>
                                </div>

                            </td>
                        </tr>
                        <?php 
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center' style='padding:40px; color:#94a3b8;'>Belum ada data anggota.</td></tr>";
                        } 
                        ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <div id="modalUser" class="modal-overlay" style="display: none;">
        <div class="modal-box" style="max-width: 500px;">
            <div class="modal-header">
                <div class="modal-title" id="modalTitle">Registrasi Akun Baru</div>
                <button onclick="document.getElementById('modalUser').style.display='none'" class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <input type="hidden" name="user_id" id="user_id">

                    <div class="form-group">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" id="nama_lengkap" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Kampus</label>
                        <input type="email" name="email" id="email" class="form-control" required>
                    </div>
                    
                    <div class="form-group" id="pass_msg" style="display:block;">
                        <label class="form-label">Password Default</label>
                        <input type="text" class="form-control" value="mhs123" readonly style="background: #f8fafc; color: #94a3b8;">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Role</label>
                        <select name="peran" id="peran" class="form-control">
                            <option value="anggota">Anggota</option>
                            <option value="ketua">Ketua</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>

                    <div class="form-group" id="status_div" style="display:none;">
                        <label class="form-label">Status Akun</label>
                        <select name="status" id="status" class="form-control">
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                    </div>

                    <button type="submit" name="simpan" class="btn-login" style="margin-top: 10px;">Simpan Data</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleDropdown(id) {
            document.querySelectorAll('.dropdown-menu').forEach(el => el.classList.remove('show'));
            const menu = document.getElementById('dropdown-' + id);
            if (menu) menu.classList.toggle('show');
        }
        
        window.onclick = function(event) {
            if (!event.target.closest('.action-container')) {
                document.querySelectorAll('.dropdown-menu').forEach(el => el.classList.remove('show'));
            }
        }
        
        function openModal() {
            document.getElementById('modalUser').style.display = 'flex';
            document.getElementById('modalTitle').innerText = 'Registrasi Akun Baru';
            document.getElementById('user_id').value = ''; 
            document.getElementById('nama_lengkap').value = '';
            document.getElementById('email').value = '';
            document.getElementById('peran').value = 'anggota';
            document.getElementById('pass_msg').style.display = 'block'; 
            document.getElementById('status_div').style.display = 'none'; 
        }

        function openEdit(id, nama, email, peran, status) {
            document.getElementById('modalUser').style.display = 'flex';
            document.getElementById('modalTitle').innerText = 'Edit Data Anggota';
            document.getElementById('user_id').value = id; 
            document.getElementById('nama_lengkap').value = nama;
            document.getElementById('email').value = email;
            document.getElementById('peran').value = peran;
            document.getElementById('pass_msg').style.display = 'none'; 
            document.getElementById('status_div').style.display = 'block';
            document.getElementById('status').value = status;
        }
    </script>

</body>
</html>