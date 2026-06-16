<?php
require_once 'config/db.php';
require_once 'includes/auth.php';

// Jika sudah login, redirect
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error    = '';
$redirect = $_GET['redirect'] ?? 'index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email dan password tidak boleh kosong.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama']    = $user['nama'];
            $_SESSION['email']   = $user['email'];
            $_SESSION['role']    = $user['role'];
            $_SESSION['user']    = $user;

            // Redirect
            $dest = $_POST['redirect'] ?? 'index.php';
            // Sanitasi redirect agar tidak kemana-mana
            if (strpos($dest, 'http') === 0 || strpos($dest, '//') === 0) {
                $dest = 'index.php';
            }
            header('Location: ' . $dest);
            exit;
        } else {
            $error = 'Email atau password salah. Silakan coba lagi.';
        }
    }
}

$pageTitle = 'Masuk - PeduliSesama';
include 'includes/header.php';
?>
<main class="container">
    <div class="login-container">
        <div style="text-align:center;margin-bottom:1.5rem;">
            <img src="logo.jpeg" alt="PeduliSesama" style="height:60px;margin-bottom:0.5rem;">
            <h2>Masuk ke Akun Anda</h2>
            <p style="color:#666;font-size:0.9rem;">Silakan masuk untuk mulai berdonasi atau mengelola kampanye.</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">

            <div class="form-group" style="text-align:left;">
                <label for="email">Email</label>
                <input type="email" id="email" name="email"
                    placeholder="contoh@email.com" required
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <div class="form-group" style="text-align:left;margin-top:15px;">
                <label for="password">Kata Sandi</label>
                <input type="password" id="password" name="password"
                    placeholder="Masukkan kata sandi" required>
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top:20px;">
                Masuk Sekarang
            </button>
        </form>

        <div class="login-footer">
            <!-- Link ke Register -->
            <div style="margin-top:18px;padding:14px;background:#f0fff4;border-radius:8px;border:1px solid #c3e6cb;">
                <p style="font-size:0.92rem;color:#333;">Belum punya akun?</p>
                <a href="register.php" class="btn btn-primary"
                    style="display:block;margin-top:8px;font-size:0.95rem;">
                    Daftar Akun Baru
                </a>
            </div>

            <div style="margin-top:16px;padding-top:16px;border-top:1px solid #eee;">
                <p style="font-size:0.82rem;color:#999;margin-bottom:6px;">
                    <strong>Akun Demo (password: password):</strong>
                </p>
                <div style="font-size:0.78rem;color:#555;background:#f9f9f9;padding:10px;border-radius:6px;text-align:left;">
                    <p><strong>Donatur:</strong> budi@gmail.com</p>
                    <p><strong>Donatur 2:</strong> siti@gmail.com</p>
                    <p style="margin-top:4px;"><strong>Pengelola:</strong> yayasan@kemanusiaan.org</p>
                    <p><strong>Pengelola 2:</strong> guru@pedulisesama.org</p>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include 'includes/footer.php'; ?>