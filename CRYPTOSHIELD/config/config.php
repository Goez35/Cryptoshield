<?php
declare(strict_types=1);
define('APP_NAME', 'CRYPTOSHIELD');
define('BASE_URL', '/CRYPTOSHIELD');
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'cryptoshield');
define('DB_USER', 'root');
define('DB_PASS', '');
// Ganti dengan 64 karakter hex acak (32 byte). Jangan gunakan contoh ini untuk deployment.
define('APP_KEY', 'e971ef2dc5d2a15993c97c2b10e901cad8b0fa9e14c9311bf373a540a217eb54');
define('USB_AGENT_URL', 'http://127.0.0.1:8765/status');
define('MAX_UPLOAD_BYTES', 10 * 1024 * 1024);
date_default_timezone_set('Asia/Jakarta');
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly'=>true, 'samesite'=>'Lax', 'secure'=>false]);
    session_start();
}
