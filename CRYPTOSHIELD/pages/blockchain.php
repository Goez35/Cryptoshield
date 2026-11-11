
<?php
$pageTitle = 'Blockchain Aset';
require '_header.php';
require_dongle();

$uid = $_SESSION['user_id'];
$msg = '';
$msgType = 'success';

// Fungsi untuk membuat hash blok
function createAssetHash(
    string $previousHash,
    string $name,
    string $description,
    string $timestamp
): string {
    return hash(
        'sha256',
        $previousHash . '|' .
        $name . '|' .
        $description . '|' .
        $timestamp
    );
}

// Menambahkan aset baru ke hash chain
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name = trim($_POST['asset_name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($name !== '') {
        // Mengambil hash blok terakhir
        $s = db()->prepare(
            'SELECT record_hash FROM assets
             WHERE user_id = ?
             ORDER BY id DESC LIMIT 1'
        );
        $s->execute([$uid]);

        $previousHash = $s->fetchColumn();

        if (!$previousHash) {
            $previousHash = str_repeat('0', 64);
        }

        // Membuat timestamp untuk blok baru
        $timestamp = date('c');

        // Membuat hash berdasarkan data blok
        $hash = createAssetHash(
            $previousHash,
            $name,
            $description,
            $timestamp
        );

        // Menyimpan aset beserta timestamp
        $i = db()->prepare(
            'INSERT INTO assets
            (user_id, asset_name, description,
             previous_hash, record_hash, block_timestamp)
            VALUES (?, ?, ?, ?, ?, ?)'
        );

        $i->execute([
            $uid,
            $name,
            $description,
            $previousHash,
            $hash,
            $timestamp
        ]);

        log_activity('ASSET_BLOCK_ADDED', $name);

        $msg = 'Catatan aset berhasil ditambahkan ke hash chain.';
    } else {
        $msg = 'Nama aset wajib diisi.';
        $msgType = 'error';
    }
}

// Mengambil seluruh data aset
$s = db()->prepare(
    'SELECT * FROM assets
     WHERE user_id = ?
     ORDER BY id ASC'
);
$s->execute([$uid]);

$rows = $s->fetchAll();

// Verifikasi hash chain
$previousHash = str_repeat('0', 64);
$valid = true;
$legacyCount = 0;

foreach ($rows as $r) {

    // Memeriksa hubungan dengan blok sebelumnya
    if (!hash_equals($previousHash, $r['previous_hash'])) {
        $valid = false;
    }

    // Memeriksa format hash
    if (!preg_match('/^[a-f0-9]{64}$/', $r['record_hash'])) {
        $valid = false;
    }

    // Memeriksa hash blok baru
    if (empty($r['block_timestamp'])) {

        // Data lama belum memiliki timestamp khusus
        $legacyCount++;

    } else {

        $expectedHash = createAssetHash(
            $r['previous_hash'],
            $r['asset_name'],
            $r['description'] ?? '',
            $r['block_timestamp']
        );

        // Membandingkan hash asli dengan hasil perhitungan
        if (!hash_equals($expectedHash, $r['record_hash'])) {
            $valid = false;
        }
    }

    $previousHash = $r['record_hash'];
}
?>

<div class="panel">
    <h2>Catat aset penting</h2>

    <p class="muted">
        Setiap catatan terhubung dengan hash catatan sebelumnya.
        Sistem memeriksa hubungan antarblok dan integritas hash
        berdasarkan data aset yang tersimpan.
    </p>

    <?php if ($msg): ?>
        <div class="<?= $msgType === 'success' ? 'success' : 'error' ?>">
            <?= e($msg) ?>
        </div>
    <?php endif; ?>

    <div class="badge <?= $valid ? 'good' : 'bad' ?>">
        <?= $valid
            ? 'Rantai hash konsisten'
            : 'Rantai hash bermasalah' ?>
    </div>

    <?php if ($legacyCount > 0): ?>
        <p class="muted">
            <?= e((string)$legacyCount) ?>
            blok lama belum memiliki timestamp untuk verifikasi hash penuh.
        </p>
    <?php endif; ?>

    <form method="post">
        <input
            type="hidden"
            name="csrf"
            value="<?= e(csrf_token()) ?>"
        >

        <label>Nama aset</label>
        <input name="asset_name" required>

        <label>Deskripsi</label>
        <textarea name="description" rows="2"></textarea>

        <button class="btn">
            Tambah ke Blockchain
        </button>
    </form>
</div>

<div class="panel">
    <h2>Ledger</h2>

    <?php foreach ($rows as $r): ?>
        <div class="record vertical">

            <b>
                Block #<?= e((string)$r['id']) ?>
                · <?= e($r['asset_name']) ?>
            </b>

            <small>
                <?= e($r['description'] ?? '') ?>
            </small>

            <code>
                Prev: <?= e($r['previous_hash']) ?>
            </code>

            <code>
                Hash: <?= e($r['record_hash']) ?>
            </code>

            <small>
                Waktu:
                <?= e($r['block_timestamp'] ?: 'Blok lama') ?>
            </small>

        </div>
    <?php endforeach; ?>
</div>

<?php require '_footer.php'; ?>
