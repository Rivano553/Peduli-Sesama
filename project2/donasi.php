<?php
require_once 'config/db.php';
require_once 'includes/auth.php';

// Wajib login dan harus donatur
if (!isLoggedIn()) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}
if ($_SESSION['role'] !== 'donatur') {
    header('Location: index.php');
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$db   = getDB();
$stmt = $db->prepare(
    'SELECT c.*, u.nama_kantor AS penyelenggara
     FROM campaigns c
     JOIN users u ON c.pengelola_id = u.id
     WHERE c.id = :id AND c.deadline >= CURDATE()
     LIMIT 1'
);
$stmt->execute([':id' => $id]);
$campaign = $stmt->fetch();

if (!$campaign) {
    $_SESSION['flash']['error'] = 'Kampanye tidak ditemukan atau sudah berakhir.';
    header('Location: index.php');
    exit;
}

// Data donatur dari DB (paling akurat)
$userStmt = $db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
$userStmt->execute([':id' => $_SESSION['user_id']]);
$donatur = $userStmt->fetch();

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nominal  = (int)($_POST['nominal'] ?? 0);
    $metode   = trim($_POST['metode'] ?? '');
    $pesan    = trim($_POST['pesan'] ?? '');

    // Validasi nominal
    if ($nominal < 10000) {
        $errors[] = 'Nominal donasi minimal Rp 10.000.';
    }
    // Validasi metode
    if (empty($metode)) {
        $errors[] = 'Metode pembayaran harus dipilih.';
    }
    // Validasi bukti transfer
    $buktiPath = null;
    if (!isset($_FILES['bukti']) || $_FILES['bukti']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Bukti transfer wajib diupload.';
    } else {
        $file     = $_FILES['bukti'];
        $allowExt = ['jpg', 'jpeg', 'png', 'pdf'];
        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowExt)) {
            $errors[] = 'Format bukti transfer harus JPG, PNG, atau PDF.';
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Ukuran file bukti transfer maksimal 2MB.';
        } else {
            $fileName  = 'bukti_' . time() . '_' . uniqid() . '.' . $ext;
            $uploadDir = 'uploads/bukti/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            if (move_uploaded_file($file['tmp_name'], $uploadDir . $fileName)) {
                $buktiPath = $fileName;
            } else {
                $errors[] = 'Gagal mengupload file. Silakan coba lagi.';
            }
        }
    }

    if (empty($errors)) {
        $ins = $db->prepare(
            'INSERT INTO donations (campaign_id, donatur_id, nominal, metode_pembayaran, pesan, bukti_path, status)
             VALUES (:cid, :did, :nominal, :metode, :pesan, :bukti, "pending")'
        );
        $ins->execute([
            ':cid'    => $campaign['id'],
            ':did'    => $_SESSION['user_id'],
            ':nominal' => $nominal,
            ':metode' => $metode,
            ':pesan'  => $pesan ?: null,
            ':bukti'  => $buktiPath,
        ]);

        $success = true;
    }
}

$posterUrl = getPosterUrlRoot($campaign);
$pageTitle = 'Form Donasi - PeduliSesama';
include 'includes/header.php';
?>
<main class="container">
    <?php if ($success): ?>
        <div class="form-container" style="text-align:center;">
            <div style="font-size:4rem;margin-bottom:1rem;"></div>
            <h2 style="color:#27ae60;">Terima Kasih!</h2>
            <p style="margin:1rem 0;">Donasi Anda sebesar <strong><?= formatRupiah((int)$_POST['nominal']) ?></strong> untuk kampanye
                <strong><?= htmlspecialchars($campaign['judul']) ?></strong> telah berhasil diajukan.
            </p>
            <div class="alert alert-info" style="text-align:left;">
                ⏳ Donasi Anda sedang dalam status <strong>PENDING</strong> dan akan diverifikasi oleh penyelenggara.
                Dana akan terakumulasi setelah verifikasi diterima.
            </div>
            <div style="margin-top:1.5rem;display:flex;gap:12px;justify-content:center;">
                <a href="detail.php?id=<?= $campaign['id'] ?>" class="btn btn-outline">Kembali ke Kampanye</a>
                <a href="dashboard/donatur.php" class="btn btn-primary">Lihat Riwayat Donasi</a>
            </div>
        </div>
    <?php else: ?>
        <div class="form-container">
            <h2 style="margin-bottom:20px;text-align:center;"> Formulir Donasi</h2>

            <!-- Ringkasan Kampanye -->
            <div class="summary-card">
                <img src="<?= htmlspecialchars($posterUrl) ?>"
                    alt="<?= htmlspecialchars($campaign['judul']) ?>"
                    referrerpolicy="no-referrer"
                    onerror="this.src='https://picsum.photos/seed/camp<?= $campaign['id'] ?>/200/200'">
                <div>
                    <p style="font-size:0.85rem;color:#555;margin-bottom:4px;">Anda akan berdonasi untuk:</p>
                    <h3 style="color:#27ae60;font-size:1.05rem;"><?= htmlspecialchars($campaign['judul']) ?></h3>
                    <p style="font-size:0.85rem;color:#777;margin-top:4px;">
                        Oleh: <?= htmlspecialchars($campaign['penyelenggara']) ?>
                    </p>
                    <p style="font-size:0.85rem;margin-top:4px;">
                        Target: <?= formatRupiah($campaign['target_dana']) ?> &nbsp;|&nbsp;
                        Terkumpul: <strong><?= formatRupiah($campaign['dana_terkumpul']) ?></strong>
                    </p>
                </div>
            </div>

            <!-- Error Messages -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul style="margin:0;padding-left:1.2rem;">
                        <?php foreach ($errors as $err): ?>
                            <li><?= htmlspecialchars($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form class="donation-form" method="POST" enctype="multipart/form-data">
                <h3 style="margin-bottom:1rem;color:#555;font-size:1rem;border-bottom:1px solid #eee;padding-bottom:8px;">
                    Data Donatur
                </h3>

                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" value="<?= htmlspecialchars($donatur['nama']) ?>" readonly
                        style="background:#f0f0f0;cursor:not-allowed;">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" value="<?= htmlspecialchars($donatur['email']) ?>" readonly
                        style="background:#f0f0f0;cursor:not-allowed;">
                </div>

                <h3 style="margin:1.5rem 0 1rem;color:#555;font-size:1rem;border-bottom:1px solid #eee;padding-bottom:8px;">
                    Detail Donasi
                </h3>

                <div class="form-group">
                    <label for="nominal">Nominal Donasi (Rp) <span style="color:red;">*</span></label>
                    <input type="number" id="nominal" name="nominal"
                        placeholder="Minimal Rp 10.000"
                        min="10000" step="1000" required
                        value="<?= htmlspecialchars($_POST['nominal'] ?? '') ?>">
                    <small style="color:#888;">Minimal donasi Rp 10.000</small>
                    <!-- Quick amount buttons -->
                    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px;">
                        <?php foreach ([25000, 50000, 100000, 250000, 500000] as $amt): ?>
                            <button type="button" class="btn btn-outline"
                                style="padding:4px 12px;font-size:0.8rem;"
                                onclick="document.getElementById('nominal').value=<?= $amt ?>">
                                <?= formatRupiah($amt) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php
                $lines = array_filter(array_map('trim', explode("\n", $campaign['info_rekening'])));
                ?>
                <div class="bank-info">
                    <h3> Metode Donasi</h3>
                    <p style="margin-bottom:8px;color:#666;font-size:0.9rem;">
                        Anda dapat menyalurkan bantuan melalui:
                    </p>
                    <ul style="margin-top:8px;">
                        <?php foreach ($lines as $line): ?>
                            <li style="margin-bottom:6px;"> <?= htmlspecialchars($line) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="form-group">
                    <label for="metode">Metode Pembayaran <span style="color:red;">*</span></label>
                    <select id="metode" name="metode" required>
                        <option value="">-- Pilih Metode Pembayaran --</option>
                        <?php foreach ($lines as $line): ?>
                            <?php
                            // Menggunakan teks baris rekening sebagai value dan label agar sinkron
                            $selected = (($_POST['metode'] ?? '') === $line) ? 'selected' : '';
                            ?>
                            <option value="<?= htmlspecialchars($line) ?>" <?= $selected ?>>
                                <?= htmlspecialchars($line) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="pesan">Pesan Dukungan (Opsional)</label>
                    <textarea id="pesan" name="pesan" rows="3"
                        placeholder="Tuliskan kata-kata penyemangat..."><?= htmlspecialchars($_POST['pesan'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="bukti">Upload Bukti Transfer <span style="color:red;">*</span></label>
                    <input type="file" id="bukti" name="bukti" accept=".jpg,.jpeg,.png,.pdf" required>
                    <small style="color:#888;">Format: JPG, PNG, atau PDF. Maksimal 2MB.</small>
                </div>

                <div style="margin-top:30px;">
                    <button type="submit" class="btn btn-primary"
                        style="width:100%;font-size:1.1rem;">
                        Kirim Donasi
                    </button>
                    <a href="detail.php?id=<?= $campaign['id'] ?>" class="btn btn-outline"
                        style="width:100%;text-align:center;margin-top:10px;display:block;">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    <?php endif; ?>
</main>
<?php include 'includes/footer.php'; ?>