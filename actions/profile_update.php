<?php require_once __DIR__.'/../lib/auth.php'; start_session(); require_login(); require_once __DIR__.'/../lib/csrf.php'; verify_csrf(); require_once __DIR__.'/../config/db.php'; require_once __DIR__.'/../lib/helpers.php';
$full_name=trim($_POST['full_name']??''); if(!$full_name){ set_flash('err','Name required','danger'); redirect('profile.php'); }
$pdo->prepare('UPDATE users SET full_name=? WHERE id=?')->execute([$full_name, user()['id']]); $_SESSION['user']['name']=$full_name; set_flash('ok','Profile updated'); redirect('profile.php');
