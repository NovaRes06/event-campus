<?php
session_start();
require '../config/koneksi.php'; // Hubungkan ke database

// Cek keamanan
if (!isset($_SESSION['role'])) {
    header("Location: ../index.php");
    exit;
}

// --- LOGIKA SIMPAN DATA (TAMBAH & EDIT) ---
if (isset($_POST['simpan'])) {
    $id    = $_POST['user_id']; // Tangkap ID (Hidden Input)
    $nama  = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $role  = $_POST['peran'];

    if (empty($id)) {
        // --- MODE TAMBAH (INSERT) ---
        $cek_email = mysqli_query($conn, "SELECT email FROM users WHERE email = '$email'");
        if (mysqli_num_rows($cek_email) > 0) {
            echo "<script>alert('Email sudah terdaftar! Gunakan email lain.');</script>";
        } else {
            // 1. Password default MD5
            $pass_input = !empty($_POST['password']) ? $_POST['password'] : 'mhs123';
            $password   = md5($pass_input);

            // 2. Insert Data (Set perlu_ganti_pass = 1)
            $query = "INSERT INTO users (nama_lengkap, email, password, peran, perlu_ganti_pass) 
                      VALUES ('$nama', '$email', '$password', '$role', 1)";
            
            if (mysqli_query($conn, $query)) {
                echo "<script>alert('Anggota berhasil ditambahkan!'); window.location='data_anggota.php';</script>";
            } else {
                echo "<script>alert('Gagal menambah anggota: " . mysqli_error($conn) . "');</script>";
            }
        }
    } else {
        // --- MODE EDIT (UPDATE) ---
        $query = "UPDATE users SET nama_lengkap='$nama', email='$email', peran='$role' WHERE user_id='$id'";
        
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Data anggota berhasil diupdate!'); window.location='data_anggota.php';</script>";
        } else {
            echo "<script>alert('Gagal update: " . mysqli_error($conn) . "');</script>";
        }
    }
}

// --- LOGIKA HAPUS DATA ---
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $query = "DELETE FROM users WHERE user_id='$id'";
    
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Data berhasil dihapus!'); window.location='data_anggota.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus: " . mysqli_error($conn) . "'); window.location='data_anggota.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Users - E-PANITIA</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=105">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <style>
        .text-center { text-align: center !important; }
        .text-left { text-align: left !important; }
        .user-profile { display: flex; align-items: center; }
        .role-tag { font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 20px; text-transform: uppercase; }
        .role-admin { background: #fee2e2; color: #991b1b; }
        .role-anggota { background: #dbeafe; color: #1e40af; }
        .action-container { position: relative; }
        .dropdown-menu { display: none; position: absolute; right: 0; top: 100%; background: white; border: 1px solid #eee; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); z-index: 100; min-width: 150px; overflow: hidden; }
        .show { display: block; }
        .dropdown-item { display: block; padding: 10px; color: #333; text-decoration: none; font-size: 13px; text-align: left; }
        .dropdown-item:hover { background: #f8fafc; }
        .modal-overlay { z-index: 9999 !important; }
        .bg-blob { pointer-events: none !important; z-index: 0 !important; }
        .dashboard-container { position: relative; z-index: 10 !important; }
        .role-badge { background: #fee2e2; color: #991b1b; }
        .role-badge-large {
            background: #fee2e2; color: #991b1b; 
            padding: 8px 20px; border-radius: 30px; 
            font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;
            display: inline-block; margin-top: 10px;
        }
    </style>
</head>
<body>

    <div class="bg-blob blob-1"></div>
    <div class="bg-blob blob-3"></div>

    <div class="dashboard-container">
        <?php include 'sidebar_common.php'; ?>

        <main class="main-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div>
                    <h1>Kelola Data Pengguna</h1>
                </div>
                <button onclick="openModal()" class="btn-login" style="width: auto; padding: auto;">
                    <i class="ph-bold ph-user-plus"></i> Tambah Akun
                </button>
            </div>

            <div class="table-container" style="background: white; border-radius: 12px; padding: 0;overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                <table style="width: 100%;">
                    <thead style="background: white; border-bottom: 2px solid #f1f5f9;">
                        <tr>
                            <th width="5%" class="text-center">NO</th>
                            <th width="35%" class="text-left">NAMA LENGKAP</th>
                            <th width="30%" class="text-left">EMAIL KAMPUS</th>
                            <th width="15%" class="text-center">ROLE</th>
                            <th width="15%" class="text-center">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $query = mysqli_query($conn, "SELECT * FROM users ORDER BY user_id DESC");
                        
                        if(mysqli_num_rows($query) > 0){
                            while ($row = mysqli_fetch_assoc($query)) {
                                $badge = ($row['peran'] == 'admin') ? 'role-admin' : 'role-anggota';
                        ?>
                        <tr>
                            <td class="text-center"><?= $no++; ?></td>
                            <td>
                                <div class="user-profile">
                                    <span style="font-weight: 600; color: #334155;"><?= htmlspecialchars($row['nama_lengkap']); ?></span>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($row['email']); ?></td>
                            <td class="text-center">
                                <span class="role-tag <?= $badge; ?>"><?= strtoupper($row['peran']); ?></span>
                            </td>
                            <td class="text-center" style="overflow: visible;">
                                <div class="action-container">
                                    <button class="btn-dots" onclick="toggleDropdown(<?= $row['user_id']; ?>)">
                                        <i class="ph-bold ph-dots-three-vertical"></i>
                                    </button>
                                    <div id="dropdown-<?= $row['user_id']; ?>" class="dropdown-menu">
                                        <a href="#" class="dropdown-item" onclick="openEdit('<?= $row['user_id']; ?>', '<?= addslashes($row['nama_lengkap']); ?>', '<?= $row['email']; ?>', '<?= $row['peran']; ?>')">
                                            <i class="ph-bold ph-pencil-simple"></i> Edit
                                        </a>
                                        <a href="data_anggota.php?hapus=<?= $row['user_id']; ?>" class="dropdown-item" style="color: red;" onclick="return confirm('Yakin ingin menghapus <?= addslashes($row['nama_lengkap']); ?>?')">
                                            <i class="ph-bold ph-trash"></i> Hapus
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php 
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center' style='padding:20px;'>Belum ada data anggota.</td></tr>";
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
                    
                    <div class="form-group" id="pass_div">
                        <label class="form-label">Password Default</label>
                        <input type="text" name="password" class="form-control" value="mhs123">
                        <small style="color: #64748b;">Akan di-hash otomatis (MD5). User baru wajib menggantinya nanti.</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Role</label>
                        <select name="peran" id="peran" class="form-control">
                            <option value="anggota">Anggota</option>
                            <option value="admin">Admin</option>
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
            document.getElementById('pass_div').style.display = 'block'; 
        }

        function openEdit(id, nama, email, peran) {
            document.getElementById('modalUser').style.display = 'flex';
            document.getElementById('modalTitle').innerText = 'Edit Data Anggota';
            document.getElementById('user_id').value = id; 
            document.getElementById('nama_lengkap').value = nama;
            document.getElementById('email').value = email;
            document.getElementById('peran').value = peran;
            document.getElementById('pass_div').style.display = 'none'; 
        }
    </script>

</body>
</html>