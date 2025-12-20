<?php
$current_page = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'] ?? '';
$id_event_uri = isset($_GET['id']) ? $_GET['id'] : '';

// Cek apakah sedang di halaman Workspace (Detail/Edit)
$is_workspace = ($current_page == 'detail_event.php' || $current_page == 'edit_event.php');
?>

<aside class="sidebar">
    <div class="sidebar-header">
        <h2 class="brand-title">E-PANITIA</h2>
        <span class="role-badge">
            <?= ($role == 'admin') ? 'Administrator' : 'Anggota' ?>
        </span>
    </div>
    
    <nav>
        <?php if ($is_workspace): ?>
            <a href="<?= ($role == 'admin') ? 'data_event.php' : 'anggota.php' ?>" class="btn-kelola" style="margin-bottom: 20px; background: #94a3b8; justify-content: center;">
                <i class="ph-bold ph-arrow-left"></i> Kembali
            </a>

            <?php if (isset($info)): // Jika variabel $info (data event) tersedia ?>
                <div style="padding: 15px; background: rgba(255,255,255,0.5); border-radius: 12px; border: 1px solid white; margin-bottom: 20px;">
                    <small style="text-transform: uppercase; font-weight: 700; color: #64748b; font-size: 10px;">Event Saat Ini</small>
                    <h3 style="font-size: 14px; margin: 5px 0; color: #1e293b;"><?= htmlspecialchars($info['nama_event']) ?></h3>
                    
                    <?php if ($role == 'admin'): ?>
                        <div style="background:#fcd34d; color:#92400e; font-weight:700; padding:4px 8px; border-radius:5px; font-size:10px; display:inline-block;">Mode Admin</div>
                    <?php elseif (isset($my_divisi_name)): ?>
                        <div style="background:#e0e7ff; color:#4338ca; font-weight:700; padding:4px 8px; border-radius:5px; font-size:10px; display:inline-block;">Divisi <?= htmlspecialchars($my_divisi_name) ?></div>
                    <?php endif; ?>
                </div>

                <?php if ($role == 'admin' || (isset($my_jabatan) && $my_jabatan == 'Ketua')): ?>
                    <a href="edit_event.php?id=<?= $id_event_uri ?>" class="menu-item <?= ($current_page == 'edit_event.php') ? 'active' : '' ?>">
                        <i class="ph-bold ph-gear"></i> Pengaturan
                    </a>
                <?php endif; ?>
            <?php endif; ?>

        <?php else: ?>
            <?php if ($role == 'admin'): ?>
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
            <?php else: ?>
                <a href="anggota.php" class="menu-item <?= ($current_page == 'anggota.php') ? 'active' : '' ?>">
                    <i class="ph-bold ph-house"></i> Beranda
                </a>
                <a href="arsip_event.php" class="menu-item <?= ($current_page == 'arsip_event.php') ? 'active' : '' ?>">
                    <i class="ph-bold ph-archive-box"></i> Arsip Event
                </a>
                <a href="profil_anggota.php" class="menu-item <?= ($current_page == 'profil_anggota.php') ? 'active' : '' ?>">
                    <i class="ph-bold ph-user"></i> Profil Saya
                </a>
            <?php endif; ?>
        <?php endif; ?>

        <div class="menu-logout">
            <a href="../logout.php" class="menu-item" style="color: #ef4444;">
                <i class="ph-bold ph-sign-out"></i> Logout
            </a>
        </div>
    </nav>
</aside>