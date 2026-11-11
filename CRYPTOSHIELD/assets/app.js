async function checkDongle(){
 const b=document.getElementById('dongleBadge'),m=document.getElementById('dongleMessage');
 if(b)b.textContent='Memeriksa...';
 try{const r=await fetch('../api/dongle.php');const d=await r.json();if(b){b.textContent=d.valid?'Dongle valid':'Dongle tidak valid';b.className='badge '+(d.valid?'good':'bad');}if(m)m.textContent=d.message;}
 catch(e){if(m)m.textContent='Tidak dapat menghubungi USB Agent. Jalankan usb_agent.py.';}
}
function loadImage(file,cb){const img=new Image(),url=URL.createObjectURL(file);img.onload=()=>{cb(img);URL.revokeObjectURL(url)};img.onerror=()=>alert('Gambar tidak dapat dibaca.');img.src=url;}
function encodeStego(){
 const f=document.getElementById('cover').files[0],msg=document.getElementById('secret').value;
 if(!f||!msg)return alert('Pilih gambar dan isi pesan.');
 loadImage(f,img=>{const c=document.getElementById('stegoCanvas'),x=c.getContext('2d');c.width=img.naturalWidth;c.height=img.naturalHeight;x.drawImage(img,0,0);
 const bytes=new TextEncoder().encode(msg),payload=new Uint8Array(4+bytes.length);new DataView(payload.buffer).setUint32(0,bytes.length);payload.set(bytes,4);
 const im=x.getImageData(0,0,c.width,c.height),capacity=Math.floor(im.data.length/4)*3;if(payload.length*8>capacity)return alert('Gambar terlalu kecil untuk pesan ini.');
 let bit=0;for(const byte of payload){for(let k=7;k>=0;k--){const v=(byte>>k)&1,px=Math.floor(bit/3)*4+(bit%3);im.data[px]=(im.data[px]&254)|v;bit++;}}
 x.putImageData(im,0,0);c.toBlob(blob=>{const a=document.createElement('a');a.href=URL.createObjectURL(blob);a.download='cryptoshield-stego.png';a.click();document.getElementById('stegoStatus').textContent='Berhasil. Simpan PNG hasil unduhan.'},'image/png');
 });
}
function decodeStego(){
 const f=document.getElementById('stegoInput').files[0];if(!f)return alert('Pilih gambar PNG.');
 loadImage(f,img=>{const c=document.createElement('canvas');c.width=img.naturalWidth;c.height=img.naturalHeight;const x=c.getContext('2d');x.drawImage(img,0,0);const d=x.getImageData(0,0,c.width,c.height).data;let bit=0;
 function readByte(){let n=0;for(let i=0;i<8;i++){const px=Math.floor(bit/3)*4+(bit%3);n=(n<<1)|(d[px]&1);bit++;}return n;}
 const len=(readByte()<<24)|(readByte()<<16)|(readByte()<<8)|readByte();if(len<0||len>Math.floor(d.length/12))return alert('Tidak ditemukan pesan atau format tidak valid.');
 const out=new Uint8Array(len);for(let j=0;j<len;j++)out[j]=readByte();document.getElementById('decoded').textContent=new TextDecoder().decode(out);
 });
}
async function biometricDemo(){
 const out=document.getElementById('bioStatus');if(!window.PublicKeyCredential||!navigator.credentials){out.textContent='Browser tidak mendukung WebAuthn.';return;}
 try{const available=await PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable();if(!available){out.textContent='Authenticator biometrik platform tidak tersedia. Periksa Windows Hello/passkey.';return;}
 const challenge=crypto.getRandomValues(new Uint8Array(32));const cred=await navigator.credentials.create({publicKey:{challenge,rp:{name:'CRYPTOSHIELD',id:location.hostname},user:{id:new TextEncoder().encode('cryptoshield-demo'),name:'demo@cryptoshield.local',displayName:'Demo CRYPTOSHIELD'},pubKeyCredParams:[{type:'public-key',alg:-7},{type:'public-key',alg:-257}],authenticatorSelection:{authenticatorAttachment:'platform',userVerification:'required'},timeout:60000,attestation:'none'}});
 out.textContent=cred?'Authenticator merespons. Ini hanya uji browser; server belum memverifikasi signature.':'Tidak ada respons.';
 }catch(e){out.textContent='Proses dibatalkan/gagal: '+e.message;}
}
