<?php
require_once __DIR__ . '/db.php';
function e(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
function require_login(): void {
    if (empty($_SESSION['user_id'])) { header('Location: '.BASE_URL.'/auth/login.php'); exit; }
}
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function verify_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
        !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(419); exit('Token keamanan tidak valid. Muat ulang halaman.');
    }
}
function app_key(): string {
    if (!preg_match('/^[a-f0-9]{64}$/i', APP_KEY)) throw new RuntimeException('APP_KEY belum diatur. Baca README.md.');
    return hex2bin(APP_KEY);
}
function encrypt_data(string $plain): string {
    $iv = random_bytes(12); $tag = '';
    $cipher = openssl_encrypt($plain, 'aes-256-gcm', app_key(), OPENSSL_RAW_DATA, $iv, $tag);
    if ($cipher === false) throw new RuntimeException('Enkripsi gagal.');
    return base64_encode($iv.$tag.$cipher);
}
function decrypt_data(string $encoded): string {
    $raw = base64_decode($encoded, true);
    if ($raw === false || strlen($raw) < 28) throw new RuntimeException('Ciphertext tidak valid.');
    $plain = openssl_decrypt(substr($raw,28), 'aes-256-gcm', app_key(), OPENSSL_RAW_DATA, substr($raw,0,12), substr($raw,12,16));
    if ($plain === false) throw new RuntimeException('Dekripsi gagal: kunci salah atau data berubah.');
    return $plain;
}
function log_activity(string $action, string $detail=''): void {
    $s=db()->prepare('INSERT INTO activity_logs(user_id,action,detail) VALUES(?,?,?)');
    $s->execute([$_SESSION['user_id'] ?? null, $action, $detail]);
}
function require_dongle(): void {
    if (empty($_SESSION['dongle_ok'])) { http_response_code(403); exit('Dongle belum diverifikasi. Kembali ke dashboard dan periksa USB.'); }
}
