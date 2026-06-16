<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function getUser()
{
    return $_SESSION['user'] ?? null;
}

function requireLogin($redirect = '../login.php')
{
    if (!isLoggedIn()) {
        header('Location: ' . $redirect . '?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

function requirePengelola($redirect = '../login.php')
{
    if (!isLoggedIn() || $_SESSION['role'] !== 'pengelola') {
        header('Location: ' . $redirect);
        exit;
    }
}

function requireDonatur($redirect = '../login.php')
{
    if (!isLoggedIn() || $_SESSION['role'] !== 'donatur') {
        header('Location: ' . $redirect);
        exit;
    }
}

function formatRupiah($amount)
{
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function hitungProgress($collected, $target)
{
    if ($target <= 0) return 0;
    $pct = ($collected / $target) * 100;
    return min(100, round($pct, 1));
}

function sisaHari($deadline)
{
    $now  = new DateTime();
    $end  = new DateTime($deadline);
    $diff = $now->diff($end);
    if ($end < $now) return 0;
    return $diff->days;
}

function getPosters()
{
    // Mapping kategori ke seed picsum yang konsisten
    return [
        'Bencana Alam'  => 'flood',
        'Pendidikan'    => 'school',
        'Kesehatan'     => 'health',
        'Lingkungan'    => 'forest',
        'Fasilitas Umum' => 'library',
        'Lainnya'       => 'charity',
    ];
}

function getPosterUrl($campaign)
{
    if (!empty($campaign['poster_path']) && file_exists('../uploads/kampanye/' . $campaign['poster_path'])) {
        return '../uploads/kampanye/' . $campaign['poster_path'];
    }
    $seeds = getPosters();
    $seed  = $seeds[$campaign['kategori']] ?? 'campaign';
    return "https://picsum.photos/seed/{$seed}{$campaign['id']}/600/400";
}

function getPosterUrlRoot($campaign)
{
    if (!empty($campaign['poster_path']) && file_exists('uploads/kampanye/' . $campaign['poster_path'])) {
        return 'uploads/kampanye/' . $campaign['poster_path'];
    }
    $seeds = getPosters();
    $seed  = $seeds[$campaign['kategori']] ?? 'campaign';
    return "https://picsum.photos/seed/{$seed}{$campaign['id']}/600/400";
}

function flash($key, $msg = null)
{
    if ($msg !== null) {
        $_SESSION['flash'][$key] = $msg;
    } else {
        $val = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $val;
    }
}
