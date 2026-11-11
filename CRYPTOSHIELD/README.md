# CRYPTOSHIELD — Secure Cryptography Application

Proyek pembelajaran berbasis PHP, MySQL, JavaScript, dan XAMPP.

## Fitur
- Registrasi dan login dengan password_hash (Argon2id jika tersedia).
- Aktivasi sesi menggunakan USB flashdisk sebagai dongle melalui companion Python lokal.
- Enkripsi pesan Super Encryption: Vigenere (klasik) lalu AES-256-GCM (modern).
- Penyimpanan pesan dalam database dalam bentuk ciphertext.
- Enkripsi/dekripsi file AES-256-GCM.
- Steganografi LSB pada gambar PNG melalui Canvas (fitur browser).
- Ledger blockchain sederhana berbasis SHA-256 hash chain untuk catatan aset.
- Riwayat aktivitas.

## Persyaratan
- Windows 10/11
- XAMPP dengan Apache, PHP 8.1+ (ekstensi OpenSSL, PDO MySQL)
- Python 3.9+ untuk USB companion
- Browser modern

## Instalasi
1. Ekstrak folder `CRYPTOSHIELD` ke `C:\xampp\htdocs\`.
2. Jalankan Apache dan MySQL dari XAMPP.
3. Buka phpMyAdmin, buat/import database dengan mengimpor `database/cryptoshield.sql`.
4. Buka `config/config.php`, sesuaikan `BASE_URL` jika nama folder berbeda.
5. Buat master key aplikasi: jalankan `php tools/generate_app_key.php` dari terminal PHP, lalu salin hasilnya ke `APP_KEY` pada `config/config.php`.
   Alternatif: gunakan 32-byte hex acak dari password manager. Jangan bagikan APP_KEY.
6. Buat dongle:
   - Jalankan `python tools/provision_usb.py`.
   - Masukkan huruf drive flashdisk saat diminta (contoh `E:`).
   - Skrip menulis `cryptoshield.key` ke flashdisk dan fingerprint ke `config/usb_fingerprint.txt`.
7. Jalankan companion:
   - Buka terminal pada folder proyek.
   - Jalankan `python usb_agent/usb_agent.py`.
   - Biarkan terminal tersebut tetap berjalan saat aplikasi digunakan.
8. Buka `http://localhost/CRYPTOSHIELD/`.
9. Daftar akun pertama, lalu login. Klik **Periksa Dongle** pada dashboard sebelum menggunakan fitur terlindungi.

## Catatan penting tentang USB
Browser tidak bisa membaca USB secara diam-diam. Karena itu proyek memakai program pendamping lokal Python. Program memeriksa drive removable Windows dan mencocokkan SHA-256 fingerprint file kunci. Ini demonstrasi dongle fisik; jangan menganggapnya sebagai proteksi anti-tamper tingkat produksi.

## Catatan penting tentang biometrik
Menu biometrik menggunakan WebAuthn API browser untuk memanggil authenticator/Windows Hello bila tersedia. Paket ini adalah demonstrasi alur sisi browser, bukan implementasi passkey produksi: verifikasi attestation/assertion di server dan penyimpanan credential perlu ditambahkan sebelum dipakai untuk keamanan nyata. Browser biasanya mensyaratkan `localhost` atau HTTPS dan perangkat yang mendukung Windows Hello/passkey.

## Keamanan dan batasan
- `APP_KEY` wajib diganti dan dirahasiakan. Kehilangan APP_KEY berarti data terenkripsi tidak dapat dibuka.
- Jangan mengunggah `config/usb_fingerprint.txt`, `APP_KEY`, atau file `cryptoshield.key`.
- File upload dibatasi 10 MB. Folder `storage/` harus tidak dapat diakses langsung; `.htaccess` disediakan untuk Apache.
- Blockchain pada proyek ini adalah hash-chain lokal sederhana, bukan jaringan blockchain publik.
- Untuk demo kelas, gunakan data uji, bukan data pribadi/rahasia sungguhan.

## Struktur
- `index.php`: halaman utama/router sederhana
- `auth/`: login, registrasi, logout
- `pages/`: dashboard dan fitur
- `api/`: endpoint aplikasi
- `config/`: koneksi DB dan konfigurasi
- `usb_agent/`: companion untuk deteksi USB
- `tools/`: pembuatan kunci dan provisioning USB
- `database/`: skema SQL
