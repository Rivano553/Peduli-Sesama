<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // Ganti sesuai user MySQL Anda
define('DB_PASS', '');            // Ganti sesuai password MySQL Anda
define('DB_NAME', 'crowdfunding_sosial');

function getDB()
{
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die('<div style="font-family:sans-serif;padding:20px;color:red;">
                <h3>Database Connection Error</h3>
                <p>' . htmlspecialchars($e->getMessage()) . '</p>
                <p>Pastikan MySQL berjalan dan konfigurasi di <code>config/db.php</code> sudah benar.</p>
            </div>');
        }
    }
    return $pdo;
}
