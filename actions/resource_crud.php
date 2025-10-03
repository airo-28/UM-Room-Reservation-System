<?php require_once __DIR__.'/../lib/auth.php'; start_session(); require_role(['admin']); require_once __DIR__.'/../lib/csrf.php'; verify_csrf(); require_once __DIR__.'/../config/db.php'; require_once __DIR__.'/../lib/helpers.php';
if(isset($_POST['delete_id'])){ $id=(int)$_POST['delete_id']; $pdo->prepare('DELETE FROM resources WHERE id=?')->execute([$id]); set_flash('ok','Resource deleted'); redirect('resources.php'); }
$name=trim($_POST['name']??''); $active=isset($_POST['is_active'])?1:0; if(!$name){ set_flash('err','Name required','danger'); redirect('resources.php'); }
$pdo->prepare('INSERT INTO resources(name,is_active) VALUES(?,?)')->execute([$name,$active]); set_flash('ok','Resource saved'); redirect('resources.php');
