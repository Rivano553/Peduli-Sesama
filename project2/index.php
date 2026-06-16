<?php
require_once 'config/db.php';
require_once 'includes/auth.php';

$pageTitle = 'PeduliSesama - Crowdfunding Sosial';
$db        = getDB();

// -- Search parameters --
$keyword      = trim($_GET['keyword'] ?? '');
$kategori     = trim($_GET['kategori'] ?? '');
$lokasi       = trim($_GET['lokasi'] ?? '');
$penyelenggara = trim($_GET['penyelenggara'] ?? ''); // <-- TAMBAHKAN INI
$page         = max(1, (int)($_GET['page'] ?? 1));
$perPage      = 6;
$offset       = ($page - 1) * $perPage;

// -- Build query --
$where    = ['c.deadline >= CURDATE()'];
$params   = [];

if ($keyword !== '') {
    $where[]  = '(c.judul LIKE :keyword OR c.kategori LIKE :keyword2)';
    $params[':keyword']  = "%$keyword%";
    $params[':keyword2'] = "%$keyword%";
}
if ($kategori !== '') {
    $where[]  = 'c.kategori = :kategori';
    $params[':kategori'] = $kategori;
}
if ($lokasi !== '') {
    $where[]  = 'c.lokasi LIKE :lokasi';
    $params[':lokasi'] = "%$lokasi%";
}
// -- TAMBAHKAN KONDISI INI --
if ($penyelenggara !== '') {
    $where[]  = 'u.nama_kantor LIKE :penyelenggara';
    $params[':penyelenggara'] = "%$penyelenggara%";
}

$whereSQL = 'WHERE ' . implode(' AND ', $where);

// -- Count total --
// Karena kita memfilter menggunakan tabel users (u.nama_kantor), kita perlu melakukan JOIN juga pada count query
$countSQL  = "SELECT COUNT(*) FROM campaigns c JOIN users u ON c.pengelola_id = u.id $whereSQL"; // <-- DIUBAH (Ditambahkan JOIN)
$countStmt = $db->prepare($countSQL);
$countStmt->execute($params);
$totalRows  = (int)$countStmt->fetchColumn();
$totalPages = (int)ceil($totalRows / $perPage);

// -- Fetch campaigns: sorted by deadline ASC then dana_terkumpul ASC --
$sql  = "SELECT c.*, u.nama_kantor AS penyelenggara
         FROM campaigns c
         JOIN users u ON c.pengelola_id = u.id
         $whereSQL
         ORDER BY c.deadline ASC, c.dana_terkumpul ASC
         LIMIT :limit OFFSET :offset";
$stmt = $db->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
$stmt->execute();
$campaigns = $stmt->fetchAll();

// -- Kategori list --
$categories = ['Bencana Alam', 'Pendidikan', 'Kesehatan', 'Lingkungan', 'Fasilitas Umum', 'Lainnya'];

include 'includes/header.php';
?>
<main class="container">
    <section class="hero" style="text-align:center;padding:2rem 0 1rem;">
        <h1>Bantu Sesama, Ubah Dunia</h1>
        <p>Salurkan bantuan Anda untuk mereka yang membutuhkan melalui kampanye sosial terpercaya.</p>
    </section>

    <!-- Search Section -->
    <section class="search-section">
        <form method="GET" action="index.php">
            <div class="search-grid">
                <div class="form-group">
                    <label for="keyword">Cari Kampanye</label>
                    <input type="text" id="keyword" name="keyword"
                        placeholder="Judul atau kategori..."
                        value="<?= htmlspecialchars($keyword) ?>">
                </div>
                <div class="form-group">
                    <label for="kategori">Kategori</label>
                    <select id="kategori" name="kategori">
                        <option value="">Semua Kategori</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat) ?>"
                                <?= ($kategori === $cat) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="lokasi">Lokasi</label>
                    <input type="text" id="lokasi" name="lokasi"
                        placeholder="Kota atau provinsi..."
                        value="<?= htmlspecialchars($lokasi) ?>">
                </div>
                <div class="form-group">
                    <label for="penyelenggara">Penyelenggara</label>
                    <input type="text" id="penyelenggara" name="penyelenggara"
                        placeholder="Nama instansi/organisasi..."
                        value="<?= htmlspecialchars($penyelenggara) ?>">
                </div>
            </div>
            <br style="grid-column:1/-1;margin:0.5rem 0;">
            <div class="form-group" style="align-self:center;">
                <button type="submit" class="btn btn-primary" style="width:100%;">Cari</button>
            </div>
        </form>
    </section>

    <!-- Campaign List -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
        <h2>
            <?php if ($keyword || $kategori || $lokasi || $penyelenggara): ?>
                Hasil Pencarian
                <span style="font-size:1rem;color:#777;">(<?= $totalRows ?> kampanye ditemukan)</span>
            <?php else: ?>
                Kampanye Aktif
            <?php endif; ?>
        </h2>
        <?php if ($keyword || $kategori || $lokasi || $penyelenggara): ?>
            <a href="index.php" class="btn btn-outline" style="font-size:0.85rem;">✕ Reset Pencarian</a>
        <?php endif; ?>
    </div>

    <?php if (empty($campaigns)): ?>
        <div class="empty-state">
            <p> Tidak ada kampanye yang ditemukan.</p>
            <a href="index.php" class="btn btn-primary" style="margin-top:1rem;">Lihat Semua Kampanye</a>
        </div>
    <?php else: ?>
        <div class="campaign-grid">
            <?php foreach ($campaigns as $c): ?>
                <?php
                $progress  = hitungProgress($c['dana_terkumpul'], $c['target_dana']);
                $sisaHari  = sisaHari($c['deadline']);
                $posterUrl = getPosterUrlRoot($c);
                ?>
                <div class="campaign-card">
                    <a href="detail.php?id=<?= $c['id'] ?>">
                        <img src="<?= htmlspecialchars($posterUrl) ?>"
                            alt="<?= htmlspecialchars($c['judul']) ?>"
                            class="campaign-image"
                            referrerpolicy="no-referrer"
                            onerror="this.src='https://picsum.photos/seed/campaign<?= $c['id'] ?>/600/400'">
                        <div class="campaign-content">
                            <span class="campaign-category"><?= htmlspecialchars($c['kategori']) ?></span>
                            <h3 class="campaign-title"><?= htmlspecialchars($c['judul']) ?></h3>
                            <p class="campaign-organizer">Oleh: <?= htmlspecialchars($c['penyelenggara']) ?></p>
                            <p class="campaign-organizer" style="margin-top:-8px;">
                                <?= htmlspecialchars($c['lokasi']) ?>
                            </p>
                            <div class="campaign-stats">
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width:<?= $progress ?>%;"></div>
                                </div>
                                <div class="stats-row">
                                    <span>Terkumpul: <strong><?= formatRupiah($c['dana_terkumpul']) ?></strong></span>
                                    <span><?= $progress ?>%</span>
                                </div>
                                <div class="stats-row" style="margin-top:4px;">
                                    <span style="font-size:0.8rem;color:#777;">Target: <?= formatRupiah($c['target_dana']) ?></span>
                                </div>
                            </div>
                            <?php if ($sisaHari <= 3): ?>
                                <span class="campaign-deadline deadline-urgent"> Sisa <?= $sisaHari ?> hari lagi!</span>
                            <?php else: ?>
                                <span class="campaign-deadline"> Sisa <?= $sisaHari ?> hari lagi</span>
                            <?php endif; ?>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php
                $baseQuery = http_build_query(array_filter([
                    'keyword'       => $keyword,
                    'kategori'      => $kategori,
                    'lokasi'        => $lokasi,
                    'penyelenggara' => $penyelenggara, // <-- TAMBAHKAN INI
                ]));
                $baseQuery = $baseQuery ? "?$baseQuery&page=" : '?page=';
                ?>
                <?php if ($page > 1): ?>
                    <a href="<?= $baseQuery . ($page - 1) ?>" class="page-btn">&laquo; Sebelumnya</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="<?= $baseQuery . $i ?>"
                        class="page-btn <?= ($i === $page) ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="<?= $baseQuery . ($page + 1) ?>" class="page-btn">Selanjutnya &raquo;</a>
                <?php endif; ?>
            </div>
            <p style="text-align:center;color:#777;font-size:0.85rem;margin-top:0.5rem;">
                Halaman <?= $page ?> dari <?= $totalPages ?> (<?= $totalRows ?> kampanye)
            </p>
        <?php endif; ?>
    <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>