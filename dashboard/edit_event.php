
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Event</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header"><h2 class="brand-title">E-PANITIA</h2></div>
            <nav>
                <a href="data_event.php" class="menu-item active"><i class="ph-bold ph-arrow-left"></i> Kembali</a>
            </nav>
        </aside>

        <main class="main-content">
            <h1 style="margin-bottom: 20px;">Edit Event ✏️</h1>
            
            <div class="login-card" style="margin: 0; text-align: left; max-width: 600px;">
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Nama Event</label>
                        <input type="text" name="nama_event" class="form-control" required value="<?= $data['nama_event'] ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Deskripsi Singkat</label>
                        <textarea name="deskripsi" class="form-control" rows="3"><?= $data['deskripsi'] ?></textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" name="tgl_mulai" class="form-control" required value="<?= $data['tanggal_mulai'] ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tanggal Selesai</label>
                            <input type="date" name="tgl_selesai" class="form-control" required value="<?= $data['tanggal_selesai'] ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Status Event</label>
                        <select name="status" class="form-control">
                            <option value="pending" <?= $data['status']=='pending'?'selected':'' ?>>Pending (Persiapan)</option>
                            <option value="active" <?= $data['status']=='active'?'selected':'' ?>>Active (Sedang Berjalan)</option>
                            <option value="completed" <?= $data['status']=='completed'?'selected':'' ?>>Completed (Selesai)</option>
                            <option value="cancelled" <?= $data['status']=='cancelled'?'selected':'' ?>>Cancelled (Batal)</option>
                        </select>
                    </div>

                    <button type="submit" name="update" class="btn-login" style="margin-top: 20px;">
                        SIMPAN PERUBAHAN
                    </button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>