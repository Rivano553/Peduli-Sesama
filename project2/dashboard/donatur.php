<?php
require_once '../config/db.php';
require_once '../includes/auth.php';

$isRoot = false; // untuk header path

if (!isLoggedIn() || $_SESSION['role'] !== 'donatur') {
    header('Location: ../login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$db  = getDB();
$uid = $_SESSION['user_id'];

// Riwayat donasi donatur
$stmt = $db->prepare(
    'SELECT d.*, c.judul AS judul_kampanye, c.id AS campaign_id,
            u.nama_kantor AS penyelenggara
     FROM donations d
     JOIN campaigns c ON d.campaign_id = c.id
     JOIN users u ON c.pengelola_id = u.id
     WHERE d.donatur_id = :uid
     ORDER BY d.created_at DESC'
);
$stmt->execute([':uid' => $uid]);
$donations = $stmt->fetchAll();

// Ringkasan BONUS
$ringkasan = ['verified' => ['total' => 0, 'count' => 0], 'pending' => ['total' => 0, 'count' => 0], 'rejected' => ['total' => 0, 'count' => 0]];
foreach ($donations as $d) {
    $ringkasan[$d['status']]['total'] += $d['nominal'];
    $ringkasan[$d['status']]['count']++;
}

$pageTitle = 'Riwayat Donasi - PeduliSesama';
include '../includes/header.php';
?>
<main class="container" style="padding-top:2rem;padding-bottom:3rem;">
    <h2 style="margin-bottom:0.5rem;">Riwayat Donasi Saya</h2>
    <p style="color:#777;margin-bottom:1.5rem;">Halo, <strong><?= htmlspecialchars($_SESSION['nama']) ?></strong>! Berikut riwayat donasi Anda.</p>

    <!-- BONUS: Ringkasan Donasi -->
    <div class="stats-summary">
        <div class="stat-card stat-verified">
            <div class="stat-info">
                <div class="stat-label">Terverifikasi</div>
                <div class="stat-amount"><?= formatRupiah($ringkasan['verified']['total']) ?></div>
                <div class="stat-count"><?= $ringkasan['verified']['count'] ?> donasi</div>
            </div>
        </div>
        <div class="stat-card stat-pending">
            <div class="stat-info">
                <div class="stat-label">Menunggu Verifikasi</div>
                <div class="stat-amount"><?= formatRupiah($ringkasan['pending']['total']) ?></div>
                <div class="stat-count"><?= $ringkasan['pending']['count'] ?> donasi</div>
            </div>
        </div>
        <div class="stat-card stat-rejected">
            <div class="stat-info">
                <div class="stat-label">Ditolak</div>
                <div class="stat-amount"><?= formatRupiah($ringkasan['rejected']['total']) ?></div>
                <div class="stat-count"><?= $ringkasan['rejected']['count'] ?> donasi</div>
            </div>
        </div>
    </div>

    <!-- Tabel Riwayat Donasi -->
    <div class="table-card">
        <h3 style="margin-bottom:1rem;"> Detail Riwayat Donasi</h3>
        <?php if (empty($donations)): ?>
            <div class="empty-state">
                <p>Anda belum pernah melakukan donasi.</p>
                <a href="../index.php" class="btn btn-primary" style="margin-top:1rem;">Cari Kampanye</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Kampanye</th>
                            <th>Tanggal</th>
                            <th>Nominal</th>
                            <th>Metode</th>
                            <th>Pesan</th>
                            <th>Status</th>
                            <th>Bukti</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($donations as $i => $d): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <a href="../detail.php?id=<?= $d['campaign_id'] ?>"
                                        style="color:#27ae60;">
                                        <?= htmlspecialchars($d['judul_kampanye']) ?>
                                    </a>
                                    <div style="font-size:0.78rem;color:#888;">
                                        <?= htmlspecialchars($d['penyelenggara']) ?>
                                    </div>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($d['created_at'])) ?></td>
                                <td style="font-weight:600;"><?= formatRupiah($d['nominal']) ?></td>
                                <td style="font-size:0.85rem;"><?= htmlspecialchars($d['metode_pembayaran']) ?></td>
                                <td style="font-size:0.85rem;max-width:150px;">
                                    <?= $d['pesan'] ? htmlspecialchars(substr($d['pesan'], 0, 50)) . (strlen($d['pesan']) > 50 ? '...' : '') : '<span style="color:#bbb;">-</span>' ?>
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
                                    <?php if ($d['bukti_path']): ?>
                                        <a href="../uploads/bukti/<?= htmlspecialchars($d['bukti_path']) ?>"
                                            target="_blank" class="btn btn-outline"
                                            style="padding:4px 10px;font-size:0.78rem;">
                                            Lihat
                                        </a>
                                    <?php else: ?>
                                        <span style="color:#bbb;font-size:0.85rem;">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div style="margin-top:1.5rem;">
        <a href="../index.php" class="btn btn-outline">&larr; Kembali ke Beranda</a>
    </div>
</main>
<?php include '../includes/footer.php'; ?>