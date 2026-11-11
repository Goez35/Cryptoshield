import hashlib, json, os, string
from http.server import BaseHTTPRequestHandler, HTTPServer
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
FINGERPRINT_FILE = ROOT / "config" / "usb_fingerprint.txt"
KEY_FILENAME = "cryptoshield.key"

def check_usb():
    if not FINGERPRINT_FILE.exists():
        return False, "Fingerprint belum dibuat. Jalankan tools/provision_usb.py."
    expected = FINGERPRINT_FILE.read_text(encoding="utf-8").strip().lower()
    if not expected:
        return False, "Fingerprint kosong."
    if os.name == "nt":
        import ctypes
        drives = []
        mask = ctypes.windll.kernel32.GetLogicalDrives()
        for i, letter in enumerate(string.ascii_uppercase):
            if mask & (1 << i):
                root = f"{letter}:\\\\"
                if ctypes.windll.kernel32.GetDriveTypeW(root) == 2:
                    drives.append(Path(root))
    else:
        drives = [Path("/media") / os.getenv("USER", ""), Path("/mnt")]
    for drive in drives:
        try:
            for candidate in [drive / KEY_FILENAME]:
                if candidate.is_file():
                    actual = hashlib.sha256(candidate.read_bytes()).hexdigest()
                    if actual == expected:
                        return True, f"Dongle valid terdeteksi pada {drive}"
        except (OSError, PermissionError):
            pass
    return False, "Flashdisk dengan cryptoshield.key yang cocok tidak ditemukan."

class Handler(BaseHTTPRequestHandler):
    def do_GET(self):
        valid, message = check_usb()
        body = json.dumps({"valid": valid, "message": message}).encode()
        self.send_response(200)
        self.send_header("Content-Type", "application/json")
        self.send_header("Content-Length", str(len(body)))
        self.end_headers()
        self.wfile.write(body)
    def log_message(self, *_): pass

if __name__ == "__main__":
    print("CRYPTOSHIELD USB Agent aktif di http://127.0.0.1:8765/status")
    print("Biarkan terminal ini tetap terbuka. Tekan Ctrl+C untuk berhenti.")
    HTTPServer(("127.0.0.1", 8765), Handler).serve_forever()
