<?php
require_once '../config/db.php';
require_once '../includes/auth.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'pengelola') {
    header('Location: ../login.php');
    exit;
}

$db  = getDB();
$id  = (int)($_GET['id'] ?? 0);
$uid = $_SESSION['user_id'];

// Pastikan kampanye milik pengelola ini
$stmt = $db->prepare('SELECT * FROM campaigns WHERE id=:id AND pengelola_id=:uid LIMIT 1');
$stmt->execute([':id' => $id, ':uid' => $uid]);
$c = $stmt->fetch();

if (!$c) {
    $_SESSION['flash']['error'] = 'Kampanye tidak ditemukan.';
    header('Location: index.php');
    exit;
}

// Cek: kampanye dengan dana terkumpul >= 10.000 tidak dapat dihapus
if ($c['dana_terkumpul'] >= 10000) {
    $_SESSION['flash']['error'] = 'Kampanye yang sudah memiliki dana terkumpul minimal Rp 10.000 tidak dapat dihapus.';
    header('Location: index.php');
    exit;
}

// Hapus poster jika ada
if ($c['poster_path'] && file_exists('../uploads/kampanye/' . $c['poster_path'])) {
    unlink('../uploads/kampanye/' . $c['poster_path']);
}

// Hapus dari DB (donations akan terhapus karena ON DELETE CASCADE)
$del = $db->prepare('DELETE FROM campaigns WHERE id=:id AND pengelola_id=:uid');
$del->execute([':id' => $id, ':uid' => $uid]);

$_SESSION['flash']['success'] = 'Kampanye berhasil dihapus.';
header('Location: index.php');
exit;
