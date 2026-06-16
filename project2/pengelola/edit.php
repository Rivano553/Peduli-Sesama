<?php
require_once '../config/db.php';
require_once '../includes/auth.php';

$isRoot = false;

if (!isLoggedIn() || $_SESSION['role'] !== 'pengelola') {
    header('Location: ../login.php');
    exit;
}

$db  = getDB();
$id  = (int)($_GET['id'] ?? 0);
$uid = $_SESSION['user_id'];

// Ambil kampanye, pastikan milik pengelola ini
$stmt = $db->prepare('SELECT * FROM campaigns WHERE id=:id AND pengelola_id=:uid LIMIT 1');
$stmt->execute([':id' => $id, ':uid' => $uid]);
$c = $stmt->fetch();

if (!$c) {
    $_SESSION['flash']['error'] = 'Kampanye tidak ditemukan.';
    header('Location: index.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul     = trim($_POST['judul'] ?? '');
    $kategori  = trim($_POST['kategori'] ?? '');
    $lokasi    = trim($_POST['lokasi'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $target    = (float)($_POST['target_dana'] ?? 0);
    $deadline  = trim($_POST['deadline'] ?? '');
    $rekening  = trim($_POST['info_rekening'] ?? '');

    if (empty($judul))     $errors[] = 'Judul kampanye tidak boleh kosong.';
    if (empty($kategori))  $errors[] = 'Kategori harus dipilih.';
    if (empty($lokasi))    $errors[] = 'Lokasi tidak boleh kosong.';
    if (empty($deskripsi)) $errors[] = 'Deskripsi tidak boleh kosong.';
    if ($target < 1000)    $errors[] = 'Target dana minimal Rp 1.000.';
    if (empty($deadline))  $errors[] = 'Batas waktu harus diisi.';
    if (empty($rekening))  $errors[] = 'Informasi rekening tidak boleh kosong.';

    // Upload poster baru (opsional)
    $posterPath = $c['poster_path']; // tetap pakai yang lama
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
                // Hapus poster lama jika ada
                if ($c['poster_path'] && file_exists($uploadDir . $c['poster_path'])) {
                    unlink($uploadDir . $c['poster_path']);
                }
                $posterPath = $posterName;
            }
        }
    }

    if (empty($errors)) {
        $upd = $db->prepare(
            'UPDATE campaigns SET judul=:judul, kategori=:kat, lokasi=:lok, deskripsi=:desk,
             target_dana=:target, deadline=:dl, poster_path=:poster, info_rekening=:rek
             WHERE id=:id AND pengelola_id=:uid'
        );
        $upd->execute([
            ':judul'  => $judul,
            ':kat'    => $kategori,
            ':lok'    => $lokasi,
            ':desk'   => $deskripsi,
            ':target' => $target,
            ':dl'     => $deadline,
            ':poster' => $posterPath,
            ':rek'    => $rekening,
            ':id'     => $id,
            ':uid'    => $uid,
        ]);
        $_SESSION['flash']['success'] = 'Kampanye berhasil diperbarui!';
        header('Location: index.php');
        exit;
    }
    // Jika ada error, isi form dengan nilai POST
    $c = array_merge($c, $_POST);
}

$categories = ['Bencana Alam', 'Pendidikan', 'Kesehatan', 'Lingkungan', 'Fasilitas Umum', 'Lainnya'];
$pageTitle  = 'Edit Kampanye - PeduliSesama';
include '../includes/header.php';
?>
<main class="container">
    <div class="form-container">
        <div style="margin-bottom:1.5rem;">
            <a href="index.php" class="btn btn-outline">&larr; Kembali</a>
        </div>
        <h2 style="margin-bottom:1.5rem;"> Edit Kampanye</h2>

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
                <input type="text" name="judul" required
                    value="<?= htmlspecialchars($c['judul']) ?>">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group">
                    <label>Kategori <span style="color:red;">*</span></label>
                    <select name="kategori" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat ?>"
                                <?= ($c['kategori'] === $cat) ? 'selected' : '' ?>>
                                <?= $cat ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Lokasi <span style="color:red;">*</span></label>
                    <input type="text" name="lokasi" required
                        value="<?= htmlspecialchars($c['lokasi']) ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Deskripsi Kampanye <span style="color:red;">*</span></label>
                <textarea name="deskripsi" rows="6" required><?= htmlspecialchars($c['deskripsi']) ?></textarea>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group">
                    <label>Target Dana (Rp) <span style="color:red;">*</span></label>
                    <input type="number" name="target_dana" required min="1000" step="1000"
                        value="<?= htmlspecialchars($c['target_dana']) ?>">
                </div>
                <div class="form-group">
                    <label>Batas Waktu <span style="color:red;">*</span></label>
                    <input type="date" name="deadline" required
                        value="<?= htmlspecialchars($c['deadline']) ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Informasi Rekening / Metode Donasi <span style="color:red;">*</span></label>
                <textarea name="info_rekening" rows="4" required><?= htmlspecialchars($c['info_rekening']) ?></textarea>
            </div>

            <div class="form-group">
                <label>Poster Kampanye (Opsional - kosongkan jika tidak ingin mengubah)</label>
                <?php if ($c['poster_path'] && file_exists('../uploads/kampanye/' . $c['poster_path'])): ?>
                    <div style="margin-bottom:8px;">
                        <img src="../uploads/kampanye/<?= htmlspecialchars($c['poster_path']) ?>"
                            style="height:100px;border-radius:6px;border:1px solid #ddd;">
                        <p style="font-size:0.8rem;color:#888;margin-top:4px;">Poster saat ini</p>
                    </div>
                <?php endif; ?>
                <input type="file" name="poster" accept=".jpg,.jpeg,.png,.webp">
                <small style="color:#888;">Format: JPG, PNG, WEBP. Maksimal 5MB.</small>
            </div>

            <div style="margin-top:1.5rem;display:flex;gap:12px;">
                <button type="submit" class="btn btn-primary" style="flex:1;"> Simpan Perubahan</button>
                <a href="index.php" class="btn btn-outline" style="flex:1;text-align:center;">Batal</a>
            </div>
        </form>
    </div>
</main>
<?php include '../includes/footer.php'; ?>