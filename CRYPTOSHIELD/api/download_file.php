<?php
require_once __DIR__.'/../config/security.php';require_login();require_dongle();
$s=db()->prepare('SELECT * FROM encrypted_files WHERE id=? AND user_id=?');$s->execute([$_GET['id']??0,$_SESSION['user_id']]);$r=$s->fetch();if(!$r)exit('File tidak ditemukan.');
$path=__DIR__.'/../storage/files/'.$r['stored_name'];try{$plain=decrypt_data(file_get_contents($path));log_activity('FILE_DECRYPTED',$r['original_name']);}catch(Throwable $e){http_response_code(500);exit('Dekripsi gagal.');}
header('Content-Type: application/octet-stream');header('Content-Disposition: attachment; filename="'.str_replace('"','',basename($r['original_name'])).'"');header('Content-Length: '.strlen($plain));echo $plain;
