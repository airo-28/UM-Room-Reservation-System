<?php require_once __DIR__.'/../lib/auth.php'; require_once __DIR__.'/../lib/csrf.php'; start_session(); verify_csrf(); require_once __DIR__.'/../lib/helpers.php'; require_once __DIR__.'/../config/db.php'; require_once __DIR__.'/../lib/validator.php';
$full_name=trim($_POST['full_name']??''); $email=trim($_POST['email']??''); $password=$_POST['password']??'';
if(!required($full_name)||!email($email)||!minlen($password,6)){ set_flash('err','Please provide valid details','danger'); redirect('register.php'); }
$stmt=$pdo->prepare('SELECT id FROM users WHERE email=?'); $stmt->execute([$email]); if($stmt->fetch()){ set_flash('err','Email already registered','danger'); redirect('register.php'); }
$hash=password_hash($password,PASSWORD_DEFAULT);
$stmt=$pdo->prepare('INSERT INTO users(full_name,email,password_hash,role) VALUES(?,?,?,"student")'); $stmt->execute([$full_name,$email,$hash]);
set_flash('ok','Account created. Please login.'); redirect('login.php');
