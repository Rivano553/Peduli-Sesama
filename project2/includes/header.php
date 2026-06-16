<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) session_start();

$isRoot   = (strpos($_SERVER['PHP_SELF'], '/pengelola/') === false &&
             strpos($_SERVER['PHP_SELF'], '/dashboard/') === false);
$basePath = $isRoot ? '' : '../';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'PeduliSesama - Crowdfunding Sosial' ?></title>
    <link rel="stylesheet" href="<?= $basePath ?>style.css">
</head>
<body>
<header>
    <div class="container">
        <a href="<?= $basePath ?>index.php" class="logo">
            <img src="<?= $basePath ?>logo.jpeg" class="imgTitle" alt="PeduliSesama">
        </a>
        <nav>
            <ul>
                <li><a href="<?= $basePath ?>index.php">Beranda</a></li>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'pengelola'): ?>
                    <li><a href="<?= $basePath ?>pengelola/index.php">Kelola Kampanye</a></li>
                <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'donatur'): ?>
                    <li><a href="<?= $basePath ?>dashboard/donatur.php">Riwayat Donasi</a></li>
                <?php endif; ?>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><span style="color:#555;font-size:0.9rem;">Halo, <strong><?= htmlspecialchars($_SESSION['nama']) ?></strong></span></li>
                    <li><a href="<?= $basePath ?>logout.php" class="btn-login btn-logout">Keluar</a></li>
                <?php else: ?>
                    <li><a href="<?= $basePath ?>login.php" class="btn-login">Masuk</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>
<?php
// Tampilkan flash message jika ada
if (isset($_SESSION['flash'])):
    foreach ($_SESSION['flash'] as $type => $msg):
        $cls = ($type === 'success') ? 'alert-success' : (($type === 'error') ? 'alert-error' : 'alert-info');
?>
<div class="alert <?= $cls ?>">
    <div class="container"><?= htmlspecialchars($msg) ?></div>
</div>
<?php
    endforeach;
    unset($_SESSION['flash']);
endif;
?>
