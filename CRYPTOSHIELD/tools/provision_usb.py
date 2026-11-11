import hashlib, secrets, sys
from pathlib import Path
root=Path(__file__).resolve().parents[1]
drive=input("Masukkan drive USB (contoh E:): ").strip()
if len(drive)<2 or drive[1] != ":":
    print("Format drive tidak valid."); sys.exit(1)
target=Path(drive+"\\\\")
if not target.exists():
    print("Drive tidak ditemukan."); sys.exit(1)
key=secrets.token_bytes(32)
(target/"cryptoshield.key").write_bytes(key)
fingerprint=hashlib.sha256(key).hexdigest()
(root/"config"/"usb_fingerprint.txt").write_text(fingerprint,encoding="utf-8")
print("Dongle berhasil dibuat.")
print("File cryptoshield.key tersimpan di USB.")
print("Fingerprint lokal tersimpan di config/usb_fingerprint.txt.")
print("Jangan membagikan atau menghapus file kunci tersebut.")
