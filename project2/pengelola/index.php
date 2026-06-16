<?php
require_once '../config/db.php';
require_once '../includes/auth.php';

$isRoot = false;

if (!isLoggedIn() || $_SESSION['role'] !== 'pengelola') {
    header('Location: ../login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$db  = getDB();
$uid = $_SESSION['user_id'];

// Ambil semua kampanye milik pengelola ini
$stmt = $db->prepare(
    'SELECT c.*,
        (SELECT COUNT(*) FROM donations d WHERE d.campaign_id=c.id) AS total_donasi,
        (SELECT COALESCE(SUM(d.nominal),0) FROM donations d WHERE d.campaign_id=c.id AND d.status="verified") AS dana_verified,
        (SELECT COALESCE(SUM(d.nominal),0) FROM donations d WHERE d.campaign_id=c.id AND d.status="pending") AS dana_pending
     FROM campaigns c
     WHERE c.pengelola_id = :uid
     ORDER BY c.created_at DESC'
);
$stmt->execute([':uid' => $uid]);
$campaigns = $stmt->fetchAll();

$pageTitle = 'Kelola Kampanye - PeduliSesama';
include '../includes/header.php';
?>
<main class="container" style="padding-top:2rem;padding-bottom:3rem;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:10px;">
        <div>
            <h2> Kampanye Saya</h2>
            <p style="color:#777;">Kelola kampanye penggalangan dana Anda</p>
        </div>
        <a href="tambah.php" class="btn btn-primary"> Tambah Kampanye Baru</a>
    </div>

    <?php if (empty($campaigns)): ?>
        <div class="empty-state">
            <p>Anda belum memiliki kampanye. Mulai buat kampanye pertama Anda!</p>
            <a href="tambah.php" class="btn btn-primary" style="margin-top:1rem;">Buat Kampanye</a>
        </div>
    <?php else: ?>
        <div class="table-card">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Kampanye</th>
                            <th>Kategori</th>
                            <th>Target</th>
                            <th>Terkumpul</th>
                            <th>Pending</th>
                            <th>Progress</th>
                            <th>Deadline</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($campaigns as $i => $c):
                            $progress  = hitungProgress($c['dana_terkumpul'], $c['target_dana']);
                            $sisaHari  = sisaHari($c['deadline']);
                            $isActive  = $sisaHari > 0;
                        ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <a href="../detail.php?id=<?= $c['id'] ?>" style="color:#27ae60;font-weight:600;">
                                        <?= htmlspecialchars(substr($c['judul'], 0, 40)) ?>
                                        <?= strlen($c['judul']) > 40 ? '...' : '' ?>
                                    </a>
                                    <div style="font-size:0.78rem;color:#888;">
                                        📍 <?= htmlspecialchars($c['lokasi']) ?>
                                    </div>
                                </td>
                                <td><span class="campaign-category" style="font-size:0.75rem;"><?= htmlspecialchars($c['kategori']) ?></span></td>
                                <td><?= formatRupiah($c['target_dana']) ?></td>
                                <td style="color:#27ae60;font-weight:600;"><?= formatRupiah($c['dana_terkumpul']) ?></td>
                                <td style="color:#f39c12;"><?= formatRupiah($c['dana_pending']) ?></td>
                                <td>
                                    <div class="progress-bar" style="height:8px;min-width:80px;">
                                        <div class="progress-fill" style="width:<?= $progress ?>%;"></div>
                                    </div>
                                    <small><?= $progress ?>%</small>
                                </td>
                                <td>
                                    <?= date('d/m/Y', strtotime($c['deadline'])) ?>
                                    <?php if ($isActive): ?>
                                        <div style="font-size:0.75rem;color:#e67e22;"><?= $sisaHari ?> hari lagi</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($isActive): ?>
                                        <span class="status-badge status-verified">Aktif</span>
                                    <?php else: ?>
                                        <span class="status-badge status-rejected">Berakhir</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display:flex;gap:4px;flex-wrap:wrap;">
                                        <a href="edit.php?id=<?= $c['id'] ?>" class="btn-sm btn-edit"> Edit</a>
                                        <hr>
                                        <a href="donasi_list.php?id=<?= $c['id'] ?>" class="btn-sm btn-view"> Donasi</a>
                                        <hr>
                                        <a href="hapus.php?id=<?= $c['id'] ?>"
                                            class="btn-sm btn-delete"
                                            onclick="return confirm('Yakin hapus kampanye ini?')"> Hapus</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <div style="margin-top:1.5rem;">
        <a href="../index.php" class="btn btn-outline">&larr; Kembali ke Beranda</a>
    </div>
</main>
<?php include '../includes/footer.php'; ?>