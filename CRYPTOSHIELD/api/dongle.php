<?php
require_once __DIR__.'/../config/security.php'; require_login();
header('Content-Type: application/json');
$res=false;
if (function_exists('curl_init')) {
    $ch=curl_init(USB_AGENT_URL); curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>2,CURLOPT_TIMEOUT=>3]);
    $res=curl_exec($ch); curl_close($ch);
} else {
    $ctx=stream_context_create(['http'=>['timeout'=>3]]); $res=@file_get_contents(USB_AGENT_URL,false,$ctx);
}
$data=$res?json_decode($res,true):null;
$ok=is_array($data)&&!empty($data['valid']);
$_SESSION['dongle_ok']=$ok;
if($ok) log_activity('DONGLE_OK','USB key valid');
echo json_encode(['valid'=>$ok,'message'=>$ok?'Dongle USB valid. Fitur terlindungi dapat digunakan.':($data['message']??'USB Agent tidak aktif atau dongle tidak valid. Jalankan usb_agent.py.')]);
