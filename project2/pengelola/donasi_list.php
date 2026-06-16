<?php
require_once '../config/db.php';
require_once '../includes/auth.php';

$isRoot = false;

if (!isLoggedIn() || $_SESSION['role'] !== 'pengelola') {
    header('Location: ../login.php');
    exit;
}

$db  = getDB();
$uid = $_SESSION['user_id'];
$cid = (int)($_GET['id'] ?? 0);

// Pastikan kampanye milik pengelola ini
$cstmt = $db->prepare('SELECT * FROM campaigns WHERE id=:id AND pengelola_id=:uid LIMIT 1');
$cstmt->execute([':id' => $cid, ':uid' => $uid]);
$campaign = $cstmt->fetch();

if (!$campaign) {
    $_SESSION['flash']['error'] = 'Kampanye tidak ditemukan.';
    header('Location: index.php');
    exit;
}

// Handle verifikasi / tolak
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['donasi_id'])) {
    $donasiId = (int)$_POST['donasi_id'];
    $action   = $_POST['action'];

    // Pastikan donasi ini memang milik kampanye pengelola ini
    $dstmt = $db->prepare(
        'SELECT d.* FROM donations d
         JOIN campaigns c ON d.campaign_id = c.id
         WHERE d.id=:did AND c.pengelola_id=:uid AND d.campaign_id=:cid
         LIMIT 1'
    );
    $dstmt->execute([':did' => $donasiId, ':uid' => $uid, ':cid' => $cid]);
    $donasi = $dstmt->fetch();

    if ($donasi && $donasi['status'] === 'pending') {
        if ($action === 'verify') {
            // Update status ke verified dan tambahkan dana ke campaign
            $db->prepare('UPDATE donations SET status="verified" WHERE id=:id')->execute([':id' => $donasiId]);
            $db->prepare('UPDATE campaigns SET dana_terkumpul = dana_terkumpul + :nom WHERE id=:cid')
                ->execute([':nom' => $donasi['nominal'], ':cid' => $cid]);
            $_SESSION['flash']['success'] = 'Donasi berhasil diverifikasi! Dana terkumpul telah diperbarui.';
        } elseif ($action === 'reject') {
            $db->prepare('UPDATE donations SET status="rejected" WHERE id=:id')->execute([':id' => $donasiId]);
            $_SESSION['flash']['success'] = 'Donasi telah ditolak.';
        }
    }

    header('Location: donasi_list.php?id=' . $cid);
    exit;
}

// Ambil semua donasi untuk kampanye ini
$stmt = $db->prepare(
    'SELECT d.*, u.nama AS nama_donatur, u.email AS email_donatur, u.phone AS phone_donatur
     FROM donations d
     JOIN users u ON d.donatur_id = u.id
     WHERE d.campaign_id = :cid
     ORDER BY d.created_at DESC'
);
$stmt->execute([':cid' => $cid]);
$donations = $stmt->fetchAll();

// Ringkasan dana
$danaVerified = 0;
$danaPending = 0;
$danaRejected = 0;
$countVerified = 0;
$countPending = 0;
$countRejected = 0;
foreach ($donations as $d) {
    if ($d['status'] === 'verified') {
        $danaVerified += $d['nominal'];
        $countVerified++;
    } elseif ($d['status'] === 'pending') {
        $danaPending  += $d['nominal'];
        $countPending++;
    } else {
        $danaRejected += $d['nominal'];
        $countRejected++;
    }
}

$pageTitle = 'Donasi Kampanye - PeduliSesama';
include '../includes/header.php';
?>
<main class="container" style="padding-top:2rem;padding-bottom:3rem;">
    <div style="margin-bottom:1.5rem;">
        <a href="index.php" class="btn btn-outline">&larr; Kembali ke Kampanye Saya</a>
    </div>

    <h2 style="margin-bottom:0.25rem;"> Donasi untuk Kampanye</h2>
    <p style="color:#27ae60;font-weight:600;margin-bottom:1.5rem;">
        <?= htmlspecialchars($campaign['judul']) ?>
    </p>

    <!-- Ringkasan Dana -->
    <div class="stats-summary" style="margin-bottom:1.5rem;">
        <div class="stat-card stat-verified">
            <div class="stat-info">
                <div class="stat-label">Dana Terkumpul</div>
                <div class="stat-amount"><?= formatRupiah($danaVerified) ?></div>
                <div class="stat-count"><?= $countVerified ?> donasi terverifikasi</div>
            </div>
        </div>
        <div class="stat-card stat-pending">
            <div class="stat-info">
                <div class="stat-label">Dana Pending</div>
                <div class="stat-amount"><?= formatRupiah($danaPending) ?></div>
                <div class="stat-count"><?= $countPending ?> menunggu verifikasi</div>
            </div>
        </div>
        <div class="stat-card stat-rejected">
            <div class="stat-info">
                <div class="stat-label">Dana Ditolak</div>
                <div class="stat-amount"><?= formatRupiah($danaRejected) ?></div>
                <div class="stat-count"><?= $countRejected ?> donasi ditolak</div>
            </div>
        </div>
    </div>

    <!-- Tabel Donasi -->
    <div class="table-card">
        <h3 style="margin-bottom:1rem;">Daftar Donatur</h3>

        <?php if (empty($donations)): ?>
            <div class="empty-state">
                <p>Belum ada donasi untuk kampanye ini.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Donatur</th>
                            <th>Tanggal</th>
                            <th>Nominal</th>
                            <th>Metode</th>
                            <th>Pesan</th>
                            <th>Bukti</th>
                            <th>Status</th>
                            <th>Aksi Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($donations as $i => $d): ?>
                            <tr class="row-<?= $d['status'] ?>">
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($d['nama_donatur']) ?></strong>
                                    <div style="font-size:0.78rem;color:#888;"><?= htmlspecialchars($d['email_donatur']) ?></div>
                                    <div style="font-size:0.78rem;color:#888;"><?= htmlspecialchars($d['phone_donatur']) ?></div>
                                </td>
                                <td style="font-size:0.85rem;"><?= date('d/m/Y H:i', strtotime($d['created_at'])) ?></td>
                                <td style="font-weight:700;color:#27ae60;"><?= formatRupiah($d['nominal']) ?></td>
                                <td style="font-size:0.85rem;"><?= htmlspecialchars($d['metode_pembayaran']) ?></td>
                                <td style="font-size:0.85rem;max-width:120px;">
                                    <?= $d['pesan'] ? htmlspecialchars(substr($d['pesan'], 0, 60)) : '<span style="color:#bbb;">-</span>' ?>
                                </td>
                                <td>
                                    <?php if ($d['bukti_path']): ?>
                                        <a href="../uploads/bukti/<?= htmlspecialchars($d['bukti_path']) ?>"
                                            target="_blank" class="btn-sm btn-view">
                                            Lihat Bukti
                                        </a>
                                    <?php else: ?>
                                        <span style="color:#bbb;font-size:0.85rem;">Tidak ada</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($d['status'] === 'verified'): ?>
                                        <span class="status-badge status-verified"> Terverifikasi</span>
                                    <?php elseif ($d['status'] === 'pending'): ?>
                                        <span class="status-badge status-pending"> Pending</span>
                                    <?php else: ?>
                                        <span class="status-badge status-rejected"> Ditolak</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($d['status'] === 'pending'): ?>
                                        <div style="display:flex;gap:4px;flex-wrap:wrap;">
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="donasi_id" value="<?= $d['id'] ?>">
                                                <input type="hidden" name="action" value="verify">
                                                <button type="submit" class="btn-sm btn-verify"
                                                    onclick="return confirm('Verifikasi donasi ini?')">
                                                    Terima
                                                </button>
                                            </form>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="donasi_id" value="<?= $d['id'] ?>">
                                                <input type="hidden" name="action" value="reject">
                                                <button type="submit" class="btn-sm btn-delete"
                                                    onclick="return confirm('Tolak donasi ini?')">
                                                    Tolak
                                                </button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <span style="color:#bbb;font-size:0.8rem;">Sudah diproses</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>
<?php include '../includes/footer.php'; ?>