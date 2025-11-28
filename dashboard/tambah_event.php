<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Event</title>
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
            <h1 style="margin-bottom: 20px;">Buat Event Baru ✨</h1>
            
            <div class="login-card" style="margin: 0; text-align: left; max-width: 600px;">
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Nama Event</label>
                        <input type="text" name="nama_event" class="form-control" required placeholder="Contoh: Dies Natalis 2025">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Deskripsi Singkat</label>
                        <textarea name="deskripsi" class="form-control" rows="3" placeholder="Jelaskan detail event..."></textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" name="tgl_mulai" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tanggal Selesai</label>
                            <input type="date" name="tgl_selesai" class="form-control" required>
                        </div>
                    </div>

                    <button type="submit" name="simpan" class="btn-login" style="margin-top: 20px;">
                        SIMPAN EVENT
                    </button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>