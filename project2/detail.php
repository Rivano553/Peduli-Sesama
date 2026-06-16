<?php
require_once 'config/db.php';
require_once 'includes/auth.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$db   = getDB();
$stmt = $db->prepare(
    'SELECT c.*, u.nama_kantor AS penyelenggara, u.email AS email_pengelola, u.phone AS phone_pengelola
     FROM campaigns c
     JOIN users u ON c.pengelola_id = u.id
     WHERE c.id = :id
     LIMIT 1'
);
$stmt->execute([':id' => $id]);
$c = $stmt->fetch();

if (!$c) {
    header('Location: index.php');
    exit;
}

$progress = hitungProgress($c['dana_terkumpul'], $c['target_dana']);
$sisaHari = sisaHari($c['deadline']);
$isActive = $sisaHari > 0;
$posterUrl = getPosterUrlRoot($c);

// Hitung donasi pending untuk kampanye ini
$pendingStmt = $db->prepare(
    'SELECT COALESCE(SUM(nominal),0) FROM donations WHERE campaign_id=:id AND status="pending"'
);
$pendingStmt->execute([':id' => $id]);
$danaPending = (float)$pendingStmt->fetchColumn();

$pageTitle = htmlspecialchars($c['judul']) . ' - PeduliSesama';
include 'includes/header.php';
?>
<main class="container">
    <div style="margin-top:2rem;">
        <a href="index.php" class="btn btn-outline">&larr; Kembali ke Beranda</a>
    </div>

    <div class="detail-container">
        <!-- Kolom Kiri: Gambar + Info Rekening -->
        <div class="detail-image">
            <img src="<?= htmlspecialchars($posterUrl) ?>"
                alt="<?= htmlspecialchars($c['judul']) ?>"
                referrerpolicy="no-referrer"
                onerror="this.src='https://picsum.photos/seed/campaign<?= $c['id'] ?>/800/600'">

            <div class="bank-info">
                <h3> Metode Donasi</h3>
                <p style="margin-bottom:8px;color:#666;font-size:0.9rem;">
                    Anda dapat menyalurkan bantuan melalui:
                </p>
                <?php
                $lines = array_filter(array_map('trim', explode("\n", $c['info_rekening'])));
                ?>
                <ul style="margin-top:8px;">
                    <?php foreach ($lines as $line): ?>
                        <li style="margin-bottom:6px;"> <?= htmlspecialchars($line) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <!-- Kolom Kanan: Info + Donasi -->
        <div class="detail-info">
            <span class="campaign-category"><?= htmlspecialchars($c['kategori']) ?></span>
            <h1><?= htmlspecialchars($c['judul']) ?></h1>

            <div class="detail-meta">
                <span> <strong>Penyelenggara:</strong> <?= htmlspecialchars($c['penyelenggara']) ?></span>
                <span> <strong>Lokasi:</strong> <?= htmlspecialchars($c['lokasi']) ?></span>
                <span> <strong>Batas Waktu:</strong> <?= date('d F Y', strtotime($c['deadline'])) ?></span>
                <span>
                    <?php if ($isActive): ?>
                        <span class="status-badge status-pending"><?= $sisaHari ?> hari lagi</span>
                    <?php else: ?>
                        <span class="status-badge status-rejected"> Kampanye berakhir</span>
                    <?php endif; ?>
                </span>
            </div>

            <!-- Progress & Stats -->
            <div class="donation-box">
                <div class="campaign-stats">
                    <div class="stats-row" style="margin-bottom:10px;font-size:1.05rem;">
                        <span>Terkumpul: <strong style="color:#27ae60;"><?= formatRupiah($c['dana_terkumpul']) ?></strong></span>
                        <span>Target: <strong><?= formatRupiah($c['target_dana']) ?></strong></span>
                    </div>
                    <div class="progress-bar" style="height:15px;">
                        <div class="progress-fill" style="width:<?= $progress ?>%;"></div>
                    </div>
                    <p style="text-align:right;font-weight:bold;color:#27ae60;margin-top:5px;">
                        <?= $progress ?>% Tercapai
                    </p>
                    <?php if ($danaPending > 0): ?>
                        <p style="font-size:0.85rem;color:#f39c12;margin-top:4px;">
                            Menunggu verifikasi: <?= formatRupiah($danaPending) ?>
                        </p>
                    <?php endif; ?>
                </div>

                <?php if ($isActive): ?>
                    <?php if (isLoggedIn() && $_SESSION['role'] === 'donatur'): ?>
                        <a href="donasi.php?id=<?= $c['id'] ?>" class="btn btn-primary"
                            style="width:100%;text-align:center;font-size:1.1rem;display:block;margin-top:12px;">
                            Donasi Sekarang
                        </a>
                    <?php elseif (!isLoggedIn()): ?>
                        <a href="login.php?redirect=<?= urlencode('donasi.php?id=' . $c['id']) ?>"
                            class="btn btn-primary"
                            style="width:100%;text-align:center;font-size:1.1rem;display:block;margin-top:12px;">
                            Login untuk Donasi
                        </a>
                        <p style="text-align:center;font-size:0.85rem;color:#888;margin-top:8px;">
                            Harap login sebagai donatur untuk berdonasi
                        </p>
                    <?php else: ?>
                        <p style="text-align:center;padding:12px;background:#fff3cd;border-radius:6px;margin-top:12px;font-size:0.9rem;">
                            Pengelola tidak dapat melakukan donasi.
                        </p>
                    <?php endif; ?>
                <?php else: ?>
                    <p style="text-align:center;padding:12px;background:#f8d7da;border-radius:6px;margin-top:12px;color:#721c24;">
                        Kampanye ini telah berakhir dan tidak menerima donasi baru.
                    </p>
                <?php endif; ?>
            </div>

            <!-- Deskripsi -->
            <div class="detail-description">
                <h3> Deskripsi Kampanye</h3>
                <?php
                $paragraphs = explode("\n", nl2br(htmlspecialchars($c['deskripsi'])));
                foreach ($paragraphs as $p) {
                    if (trim(strip_tags($p))) {
                        echo "<p>$p</p>";
                    }
                }
                ?>
            </div>
        </div>
    </div>
</main>
<?php include 'includes/footer.php'; ?>