<?php $pageTitle='Dashboard'; require '_header.php';
$uid=$_SESSION['user_id']; $count=[];
foreach(['messages','encrypted_files','assets'] as $t){$s=db()->prepare("SELECT COUNT(*) FROM $t WHERE user_id=?");$s->execute([$uid]);$count[$t]=$s->fetchColumn();}
?><div class="welcome"><div><p class="eyebrow">SECURE WORKSPACE</p><h1>Halo, <?=e($_SESSION['user_name'])?> 👋</h1><p class="muted">Kelola pesan, file, dan aset digital dengan perlindungan kriptografi.</p></div><div class="shield">⬡</div></div>
<div class="stats"><div class="stat"><span>Pesan terenkripsi</span><b><?=$count['messages']?></b></div><div class="stat"><span>File terenkripsi</span><b><?=$count['encrypted_files']?></b></div><div class="stat"><span>Catatan aset</span><b><?=$count['assets']?></b></div></div>
<div class="panel"><div class="panelhead"><div><h2>Status Dongle USB</h2><p class="muted">Hubungkan flashdisk yang berisi cryptoshield.key, lalu jalankan USB Agent.</p></div><span id="dongleBadge" class="badge">Belum diperiksa</span></div><div class="actions"><button class="btn" onclick="checkDongle()">Periksa Dongle</button><span id="dongleMessage" class="muted">USB Agent: http://127.0.0.1:8765</span></div></div>
<div class="gridcards"><a class="feature" href="messages.php"><b>▤ Enkripsi Pesan</b><span>Vigenere + AES-256-GCM</span></a><a class="feature" href="files.php"><b>▧ Enkripsi File</b><span>Lindungi dokumen dengan AES-GCM</span></a><a class="feature" href="stego.php"><b>▨ Steganografi</b><span>Sisipkan pesan ke gambar PNG</span></a><a class="feature" href="blockchain.php"><b>⛓ Blockchain</b><span>Catat dan verifikasi integritas aset</span></a></div>
<script>window.CS_BASE='<?=BASE_URL?>';</script>
<?php require '_footer.php';?>
