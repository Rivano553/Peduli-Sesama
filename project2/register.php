<?php
require_once 'config/db.php';
require_once 'includes/auth.php';

// Jika sudah login, langsung redirect
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$errors  = [];
$success = false;
$post    = $_POST;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role       = trim($post['role'] ?? '');
    $nama       = trim($post['nama'] ?? '');
    $email      = trim($post['email'] ?? '');
    $phone      = trim($post['phone'] ?? '');
    $password   = $post['password'] ?? '';
    $konfirmasi = $post['konfirmasi'] ?? '';
    // Khusus pengelola
    $nama_kantor = trim($post['nama_kantor'] ?? '');
    $alamat      = trim($post['alamat'] ?? '');

    if (!in_array($role, ['donatur', 'pengelola'])) {
        $errors[] = 'Jenis akun tidak valid.';
    }
    if (empty($nama)) {
        $errors[] = 'Nama lengkap tidak boleh kosong.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }
    if (empty($phone) || !preg_match('/^[0-9+\-\s]{8,20}$/', $phone)) {
        $errors[] = 'Nomor telepon tidak valid (8–20 digit).';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password minimal 6 karakter.';
    }
    if ($password !== $konfirmasi) {
        $errors[] = 'Konfirmasi password tidak cocok.';
    }
    if ($role === 'pengelola') {
        if (empty($nama_kantor)) {
            $errors[] = 'Nama kantor / yayasan tidak boleh kosong.';
        }
        if (empty($alamat)) {
            $errors[] = 'Alamat kantor tidak boleh kosong.';
        }
    }
    if (empty($errors)) {
        $db   = getDB();
        $chk  = $db->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $chk->execute([':email' => $email]);
        if ($chk->fetch()) {
            $errors[] = 'Email sudah terdaftar. Gunakan email lain atau langsung masuk.';
        }
    }
    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $ins    = $db->prepare(
            'INSERT INTO users (nama, email, phone, password, role, nama_kantor, alamat)
             VALUES (:nama, :email, :phone, :pass, :role, :kantor, :alamat)'
        );
        $ins->execute([
            ':nama'   => $nama,
            ':email'  => $email,
            ':phone'  => $phone,
            ':pass'   => $hashed,
            ':role'   => $role,
            ':kantor' => ($role === 'pengelola') ? $nama_kantor : null,
            ':alamat' => ($role === 'pengelola') ? $alamat      : null,
        ]);

        $success = true;
        $post    = []; // bersihkan form
    }
}

$pageTitle = 'Daftar Akun - PeduliSesama';
include 'includes/header.php';
?>

<main class="container">
    <div class="register-wrapper">
        <div class="register-side">
            <img src="logo.png" alt="PeduliSesama" class="reg-logo">
            <h1>Bergabung bersama PeduliSesama</h1>
            <p>Daftarkan diri Anda dan mulai berkontribusi untuk sesama.</p>

            <div class="reg-feature">
                <div>
                    <strong>Donatur</strong>
                    <p>Temukan & dukung kampanye sosial yang Anda pedulikan.</p>
                </div>
            </div>
            <div class="reg-feature">
                <div>
                    <strong>Pengelola Kampanye</strong>
                    <p>Buat dan kelola kampanye penggalangan dana Anda.</p>
                </div>
            </div>

            <p class="reg-login-link">Sudah punya akun?
                <a href="login.php">Masuk di sini</a>
            </p>
        </div>

        <div class="register-form-card">
            <h3>Buat Akun Baru</h3>

            <?php if ($success): ?>
                <div class="reg-success">
                    <h4>Registrasi Berhasil!</h4>
                    <p>Akun Anda telah dibuat. Silakan masuk dengan email dan password yang sudah didaftarkan.</p>
                    <a href="login.php" class="btn btn-primary" style="margin-top:1rem;display:inline-block;">
                        Masuk Sekarang
                    </a>
                </div>
            <?php else: ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <ul style="margin:0;padding-left:1.2rem;">
                            <?php foreach ($errors as $e): ?>
                                <li><?= htmlspecialchars($e) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="register.php" id="regForm">

                    <!-- Pilih jenis akun -->
                    <div class="role-selector">
                        <label class="role-option <?= (($post['role'] ?? 'donatur') === 'donatur') ? 'active' : '' ?>">
                            <input type="radio" name="role" value="donatur"
                                <?= (($post['role'] ?? 'donatur') === 'donatur') ? 'checked' : '' ?>>
                            <span class="role-label">Donatur</span>
                        </label>
                        <label class="role-option <?= (($post['role'] ?? '') === 'pengelola') ? 'active' : '' ?>">
                            <input type="radio" name="role" value="pengelola"
                                <?= (($post['role'] ?? '') === 'pengelola') ? 'checked' : '' ?>>
                            <span class="role-label">Pengelola</span>
                        </label>
                    </div>

                    <!-- Data Umum -->
                    <div class="form-group">
                        <label>Nama Lengkap <span class="req">*</span></label>
                        <input type="text" name="nama" required
                            placeholder="Masukkan nama lengkap Anda"
                            value="<?= htmlspecialchars($post['nama'] ?? '') ?>">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Email <span class="req">*</span></label>
                            <input type="email" name="email" required
                                placeholder="contoh@email.com"
                                value="<?= htmlspecialchars($post['email'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>No. Telepon <span class="req">*</span></label>
                            <input type="tel" name="phone" required
                                placeholder="08xx-xxxx-xxxx"
                                value="<?= htmlspecialchars($post['phone'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Password <span class="req">*</span></label>
                            <div class="input-eye">
                                <input type="password" name="password" id="pwd" required
                                    placeholder="Minimal 6 karakter">
                                <button type="button" class="eye-btn" onclick="togglePwd('pwd', this)">👁</button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Konfirmasi Password <span class="req">*</span></label>
                            <div class="input-eye">
                                <input type="password" name="konfirmasi" id="pwd2" required
                                    placeholder="Ulangi password">
                                <button type="button" class="eye-btn" onclick="togglePwd('pwd2', this)">👁</button>
                            </div>
                        </div>
                    </div>

                    <!-- Password strength indicator -->
                    <div class="pwd-strength" id="pwdStrength" style="display:none;">
                        <div class="pwd-bar">
                            <div class="pwd-fill" id="pwdFill"></div>
                        </div>
                        <span id="pwdLabel" class="pwd-label"></span>
                    </div>

                    <!-- Section tambahan untuk Pengelola -->
                    <div id="sectionPengelola" style="display:none;">
                        <div class="reg-divider">
                            <span>Informasi Organisasi / Yayasan</span>
                        </div>
                        <div class="form-group">
                            <label>Nama Kantor / Yayasan <span class="req">*</span></label>
                            <input type="text" name="nama_kantor"
                                placeholder="Contoh: Yayasan Peduli Bangsa"
                                value="<?= htmlspecialchars($post['nama_kantor'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Alamat Kantor <span class="req">*</span></label>
                            <textarea name="alamat" rows="3"
                                placeholder="Jl. Contoh No.1, Kota, Provinsi"><?= htmlspecialchars($post['alamat'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary"
                        style="width:100%;font-size:1rem;margin-top:0.5rem;">
                        Daftar Sekarang
                    </button>

                    <p style="text-align:center;margin-top:1rem;font-size:0.85rem;color:#888;">
                        Sudah punya akun? <a href="login.php" style="color:#27ae60;font-weight:600;">Masuk</a>
                    </p>

                </form>

            <?php endif; ?>
        </div>
    </div>
</main>

<script>
    document.querySelectorAll('input[name="role"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            const isPengelola = this.value === 'pengelola';
            document.getElementById('sectionPengelola').style.display = isPengelola ? 'block' : 'none';

            // required attributes
            ['nama_kantor', 'alamat'].forEach(function(name) {
                const el = document.querySelector('[name="' + name + '"]');
                if (el) el.required = isPengelola;
            });

            // highlight role option
            document.querySelectorAll('.role-option').forEach(function(opt) {
                opt.classList.remove('active');
            });
            radio.closest('.role-option').classList.add('active');
        });
    });

    // Init on load (jika POST error & role = pengelola)
    (function() {
        const checked = document.querySelector('input[name="role"]:checked');
        if (checked && checked.value === 'pengelola') {
            document.getElementById('sectionPengelola').style.display = 'block';
            ['nama_kantor', 'alamat'].forEach(function(name) {
                const el = document.querySelector('[name="' + name + '"]');
                if (el) el.required = true;
            });
        }
    })();

    function togglePwd(id, btn) {
        const input = document.getElementById(id);
        if (input.type === 'password') {
            input.type = 'text';
            btn.textContent = '👁';
        } else {
            input.type = 'password';
            btn.textContent = '👁';
        }
    }

    document.getElementById('pwd').addEventListener('input', function() {
        const val = this.value;
        const bar = document.getElementById('pwdFill');
        const lbl = document.getElementById('pwdLabel');
        const wrap = document.getElementById('pwdStrength');

        if (!val) {
            wrap.style.display = 'none';
            return;
        }
        wrap.style.display = 'flex';

        let strength = 0;
        if (val.length >= 6) strength++;
        if (val.length >= 10) strength++;
        if (/[A-Z]/.test(val) && /[a-z]/.test(val)) strength++;
        if (/[0-9]/.test(val)) strength++;
        if (/[^A-Za-z0-9]/.test(val)) strength++;

        const levels = [{
                pct: '20%',
                color: '#e74c3c',
                text: 'Sangat Lemah'
            },
            {
                pct: '40%',
                color: '#e67e22',
                text: 'Lemah'
            },
            {
                pct: '60%',
                color: '#f1c40f',
                text: 'Cukup'
            },
            {
                pct: '80%',
                color: '#2ecc71',
                text: 'Kuat'
            },
            {
                pct: '100%',
                color: '#27ae60',
                text: 'Sangat Kuat'
            },
        ];
        const lvl = levels[Math.min(strength - 1, 4)] || levels[0];
        bar.style.width = lvl.pct;
        bar.style.background = lvl.color;
        lbl.textContent = lvl.text;
        lbl.style.color = lvl.color;
    });
</script>

<?php include 'includes/footer.php'; ?>