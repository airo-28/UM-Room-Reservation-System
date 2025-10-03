<?php
function app_config(){ static $cfg=null; if(!$cfg){ $cfg=require __DIR__.'/../config/app.php'; } return $cfg; }
function base_url($path=''){ $cfg=app_config(); return rtrim($cfg['BASE_URL'],'/').($path?'/'.ltrim($path,'/'):''); }
function redirect($path){ header('Location: '.base_url($path)); exit; }
function is_post(){ return $_SERVER['REQUEST_METHOD']==='POST'; }
function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function set_flash($key,$msg,$type='success'){ $_SESSION['flash'][$key]=['m'=>$msg,'t'=>$type]; }
function get_flash($key){ if(isset($_SESSION['flash'][$key])){ $x=$_SESSION['flash'][$key]; unset($_SESSION['flash'][$key]); return $x; } return null; }
