<?php
require_once '../config/db.php';
require_once '../includes/auth.php';

$isRoot = false;

if (!isLoggedIn() || $_SESSION['role'] !== 'pengelola') {
    header('Location: ../login.php');
    exit;
}

$errors = [];
$db     = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul      = trim($_POST['judul'] ?? '');
    $kategori   = trim($_POST['kategori'] ?? '');
    $lokasi     = trim($_POST['lokasi'] ?? '');
    $deskripsi  = trim($_POST['deskripsi'] ?? '');
    $target     = (float)($_POST['target_dana'] ?? 0);
    $deadline   = trim($_POST['deadline'] ?? '');
    $rekening   = trim($_POST['info_rekening'] ?? '');

    if (empty($judul))     $errors[] = 'Judul kampanye tidak boleh kosong.';
    if (empty($kategori))  $errors[] = 'Kategori harus dipilih.';
    if (empty($lokasi))    $errors[] = 'Lokasi tidak boleh kosong.';
    if (empty($deskripsi)) $errors[] = 'Deskripsi tidak boleh kosong.';
    if ($target < 1000)    $errors[] = 'Target dana minimal Rp 1.000.';
    if (empty($deadline))  $errors[] = 'Batas waktu harus diisi.';
    elseif ($deadline <= date('Y-m-d')) $errors[] = 'Batas waktu harus setelah hari ini.';
    if (empty($rekening))  $errors[] = 'Informasi rekening/pembayaran tidak boleh kosong.';

    // Upload poster (opsional)
    $posterPath = null;
    if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
        $file     = $_FILES['poster'];
        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowExt = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($ext, $allowExt)) {
            $errors[] = 'Format poster harus JPG, PNG, atau WEBP.';
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $errors[] = 'Ukuran poster maksimal 5MB.';
        } else {
            $posterName = 'poster_' . time() . '_' . uniqid() . '.' . $ext;
            $uploadDir  = '../uploads/kampanye/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            if (move_uploaded_file($file['tmp_name'], $uploadDir . $posterName)) {
                $posterPath = $posterName;
            } else {
                $errors[] = 'Gagal mengupload poster.';
            }
        }
    }

    if (empty($errors)) {
        $ins = $db->prepare(
            'INSERT INTO campaigns (pengelola_id, judul, kategori, lokasi, deskripsi, target_dana, deadline, poster_path, info_rekening)
             VALUES (:pid, :judul, :kat, :lok, :desk, :target, :dl, :poster, :rek)'
        );
        $ins->execute([
            ':pid'    => $_SESSION['user_id'],
            ':judul'  => $judul,
            ':kat'    => $kategori,
            ':lok'    => $lokasi,
            ':desk'   => $deskripsi,
            ':target' => $target,
            ':dl'     => $deadline,
            ':poster' => $posterPath,
            ':rek'    => $rekening,
        ]);
        $_SESSION['flash']['success'] = 'Kampanye berhasil ditambahkan!';
        header('Location: index.php');
        exit;
    }
}

$categories = ['Bencana Alam', 'Pendidikan', 'Kesehatan', 'Lingkungan', 'Fasilitas Umum', 'Lainnya'];
$pageTitle  = 'Tambah Kampanye - PeduliSesama';
include '../includes/header.php';
?>
<main class="container">
    <div class="form-container">
        <div style="margin-bottom:1.5rem;">
            <a href="index.php" class="btn btn-outline">&larr; Kembali</a>
        </div>
        <h2 style="margin-bottom:1.5rem;">Tambah Kampanye Baru</h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul style="margin:0;padding-left:1.2rem;">
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="donation-form">
            <div class="form-group">
                <label>Judul Kampanye <span style="color:red;">*</span></label>
                <input type="text" name="judul" required placeholder="Contoh: Bantuan Korban Gempa Cianjur"
                    value="<?= htmlspecialchars($_POST['judul'] ?? '') ?>">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group">
                    <label>Kategori <span style="color:red;">*</span></label>
                    <select name="kategori" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat ?>"
                                <?= (($_POST['kategori'] ?? '') === $cat) ? 'selected' : '' ?>>
                                <?= $cat ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Lokasi <span style="color:red;">*</span></label>
                    <input type="text" name="lokasi" required placeholder="Contoh: Bandung, Jawa Barat"
                        value="<?= htmlspecialchars($_POST['lokasi'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Deskripsi Kampanye <span style="color:red;">*</span></label>
                <textarea name="deskripsi" rows="6" required
                    placeholder="Ceritakan latar belakang, tujuan, dan kebutuhan kampanye ini..."><?= htmlspecialchars($_POST['deskripsi'] ?? '') ?></textarea>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group">
                    <label>Target Dana (Rp) <span style="color:red;">*</span></label>
                    <input type="number" name="target_dana" required min="1000" step="1000"
                        placeholder="Contoh: 50000000"
                        value="<?= htmlspecialchars($_POST['target_dana'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Batas Waktu <span style="color:red;">*</span></label>
                    <input type="date" name="deadline" required
                        min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                        value="<?= htmlspecialchars($_POST['deadline'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Informasi Rekening / Metode Donasi <span style="color:red;">*</span></label>
                <textarea name="info_rekening" rows="4" required
                    placeholder="Contoh:&#10;Bank BCA: 123-456-7890 a.n Nama Yayasan&#10;GoPay/OVO: 0812-3456-7890"><?= htmlspecialchars($_POST['info_rekening'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label>Poster Kampanye (Opsional)</label>
                <input type="file" name="poster" accept=".jpg,.jpeg,.png,.webp">
                <small style="color:#888;">Format: JPG, PNG, WEBP. Maksimal 5MB. Jika tidak diupload, akan menggunakan gambar default.</small>
            </div>

            <div style="margin-top:1.5rem;display:flex;gap:12px;">
                <button type="submit" class="btn btn-primary" style="flex:1;"> Simpan Kampanye</button>
                <a href="index.php" class="btn btn-outline" style="flex:1;text-align:center;">Batal</a>
            </div>
        </form>
    </div>
</main>
<?php include '../includes/footer.php'; ?>