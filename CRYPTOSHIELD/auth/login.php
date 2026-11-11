<?php
require_once __DIR__.'/../config/security.php';
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
 verify_csrf(); $email=trim($_POST['email']??''); $pass=$_POST['password']??'';
 $s=db()->prepare('SELECT * FROM users WHERE email=?'); $s->execute([$email]); $u=$s->fetch();
 if ($u && password_verify($pass,$u['password_hash'])) {
  session_regenerate_id(true); $_SESSION['user_id']=$u['id']; $_SESSION['user_name']=$u['name']; $_SESSION['dongle_ok']=false;
  log_activity('LOGIN','Login password berhasil'); header('Location: ../pages/dashboard.php'); exit;
 } else $error='Email atau password tidak sesuai.';
}
?><!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Masuk | CRYPTOSHIELD</title><link rel="stylesheet" href="../assets/style.css"></head><body class="auth"><form class="auth-card" method="post"><div class="brand">⬡ CRYPTOSHIELD</div><h1>Selamat datang</h1><p class="muted">Secure Cryptography Application</p><?php if(isset($_GET['registered'])):?><div class="success">Akun berhasil dibuat. Silakan masuk.</div><?php endif;?><?php if($error):?><div class="alert"><?=$error?></div><?php endif;?><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><label>Email</label><input name="email" type="email" required><label>Password</label><input name="password" type="password" required><button class="btn">Masuk</button><p class="muted">Belum punya akun? <a href="register.php">Daftar</a></p></form></body></html>
