<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Anggota - E-PANITIA</title>
    
    <link rel="stylesheet" href="../assets/css/style.css?v=105">
    
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <style>
        .text-center { text-align: center !important; }
        .text-left { text-align: left !important; }
        
        /* Container Nama (Sekarang cuma teks) */
        .user-profile { display: flex; align-items: center; }
        
        .role-tag { font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 20px; text-transform: uppercase; }
        .role-admin { background: #fee2e2; color: #991b1b; }
        .role-anggota { background: #dbeafe; color: #1e40af; }
        
        /* Dropdown Styles */
        .action-container { position: relative; }
        .dropdown-menu { 
            display: none; position: absolute; right: 0; top: 100%; 
            background: white; border: 1px solid #eee; border-radius: 8px; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.1); z-index: 100; min-width: 150px; overflow: hidden;
        }
        .show { display: block; }
        .dropdown-item { display: block; padding: 10px; color: #333; text-decoration: none; font-size: 13px; text-align: left; }
        .dropdown-item:hover { background: #f8fafc; }
        
        /* Global Styles fix */
        .modal-overlay { z-index: 9999 !important; }
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
                <a href="data_event.php" class="menu-item">
                    <i class="ph-bold ph-calendar-plus"></i> Data Event
                </a>
                <a href="data_anggota.php" class="menu-item active">
                    <i class="ph-bold ph-users-three"></i> Data Anggota
                </a>
                <a href="data_laporan.php" class="menu-item">
                    <i class="ph-bold ph-clipboard-text"></i> Laporan
                </a>
                
                <div class="menu-logout">
                    <a href="#" class="menu-item" style="color: #ef4444;">
                        <i class="ph-bold ph-sign-out"></i> Logout
                    </a>
                </div>
            </nav>
        </aside>

        <main class="main-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div>
                    <h1>Manajemen Anggota 👥</h1>
                    <p style="color: #64748b;">Kelola akun mahasiswa dan panitia.</p>
                </div>
                <button onclick="openModal()" class="btn-login" style="width: auto; padding: 12px 25px;">
                    <i class="ph-bold ph-user-plus"></i> Tambah Anggota
                </button>
            </div>

            <div class="table-container" style="background: white; border-radius: 12px; overflow: visible; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
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
                        
                        <tr>
                            <td class="text-center">1</td>
                            <td>
                                <div class="user-profile">
                                    <span style="font-weight: 600; color: #334155;">Si Paling Admin</span>
                                </div>
                            </td>
                            <td>admin@kampus.id</td>
                            <td class="text-center"><span class="role-tag role-admin">ADMIN</span></td>
                            <td class="text-center" style="overflow: visible;">
                                <div class="action-container">
                                    <button class="btn-dots" onclick="toggleDropdown(1)">
                                        <i class="ph-bold ph-dots-three-vertical"></i>
                                    </button>
                                    <div id="dropdown-1" class="dropdown-menu">
                                        <a href="#" class="dropdown-item" onclick="openEdit('1', 'Si Paling Admin', 'admin@kampus.id', 'admin')">
                                            <i class="ph-bold ph-pencil-simple"></i> Edit
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td class="text-center">2</td>
                            <td>
                                <div class="user-profile">
                                    <span style="font-weight: 600; color: #334155;">Budi Anggota</span>
                                </div>
                            </td>
                            <td>budi@kampus.id</td>
                            <td class="text-center"><span class="role-tag role-anggota">ANGGOTA</span></td>
                            <td class="text-center" style="overflow: visible;">
                                <div class="action-container">
                                    <button class="btn-dots" onclick="toggleDropdown(2)">
                                        <i class="ph-bold ph-dots-three-vertical"></i>
                                    </button>
                                    <div id="dropdown-2" class="dropdown-menu">
                                        <a href="#" class="dropdown-item" onclick="openEdit('2', 'Budi Santoso', 'budi@kampus.id', 'anggota')">
                                            <i class="ph-bold ph-pencil-simple"></i> Edit
                                        </a>
                                        <a href="#" class="dropdown-item">
                                            <i class="ph-bold ph-key"></i> Reset Pass
                                        </a>
                                        <a href="#" class="dropdown-item" style="color: red;">
                                            <i class="ph-bold ph-trash"></i> Hapus
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>

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
                <form>
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" id="nama_lengkap" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Kampus</label>
                        <input type="email" id="email" class="form-control" required>
                    </div>
                    <div class="form-group" id="pass_div">
                        <label class="form-label">Password Default</label>
                        <input type="text" class="form-control" value="mhs123">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Role</label>
                        <select id="peran" class="form-control">
                            <option value="anggota">Anggota</option>
                            <option value="ketua">Ketua</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <button type="button" class="btn-login" style="margin-top: 10px;">Simpan Data</button>
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
            if (!event.target.matches('.btn-dots') && !event.target.matches('.ph-dots-three-vertical')) {
                document.querySelectorAll('.dropdown-menu').forEach(el => el.classList.remove('show'));
            }
        }
        function openModal() {
            document.getElementById('modalUser').style.display = 'flex';
            document.getElementById('modalTitle').innerText = 'Registrasi Akun Baru';
            document.getElementById('nama_lengkap').value = '';
            document.getElementById('email').value = '';
            document.getElementById('pass_div').style.display = 'block';
        }
        function openEdit(id, nama, email, peran) {
            document.getElementById('modalUser').style.display = 'flex';
            document.getElementById('modalTitle').innerText = 'Edit Data Anggota';
            document.getElementById('nama_lengkap').value = nama;
            document.getElementById('email').value = email;
            document.getElementById('peran').value = peran;
            document.getElementById('pass_div').style.display = 'none';
        }
    </script>

</body>
</html>