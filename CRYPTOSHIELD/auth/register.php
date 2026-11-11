<?php
require_once __DIR__.'/../config/security.php';
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
 verify_csrf();
 $name=trim($_POST['name']??''); $email=trim($_POST['email']??''); $pass=$_POST['password']??'';
 if ($name==='' || !filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($pass)<8) $error='Lengkapi data. Password minimal 8 karakter.';
 else {
  try {
   $hash=password_hash($pass, defined('PASSWORD_ARGON2ID')?PASSWORD_ARGON2ID:PASSWORD_DEFAULT);
   $s=db()->prepare('INSERT INTO users(name,email,password_hash) VALUES(?,?,?)'); $s->execute([$name,$email,$hash]);
   header('Location: login.php?registered=1'); exit;
  } catch(PDOException $ex) { $error='Email sudah terdaftar atau terjadi kesalahan database.'; }
 }
}
?><!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Daftar | CRYPTOSHIELD</title><link rel="stylesheet" href="../assets/style.css"></head><body class="auth"><form class="auth-card" method="post"><div class="brand">⬡ CRYPTOSHIELD</div><h1>Buat akun</h1><p class="muted">Daftarkan akun untuk menggunakan aplikasi.</p><?php if($error):?><div class="alert"><?=$error?></div><?php endif;?><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><label>Nama</label><input name="name" required maxlength="100"><label>Email</label><input name="email" type="email" required><label>Password (minimal 8 karakter)</label><input name="password" type="password" minlength="8" required><button class="btn">Daftar</button><p class="muted">Sudah punya akun? <a href="login.php">Masuk</a></p></form></body></html>
