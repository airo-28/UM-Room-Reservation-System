<?php
require_once __DIR__.'/../lib/auth.php';
require_once __DIR__.'/../lib/csrf.php';
require_once __DIR__.'/../config/db.php';
require_once __DIR__.'/../lib/helpers.php';

start_session();
require_login();
verify_csrf();

$u = user();
$uid = (int)$u['id'];
$id  = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
  set_flash('err','Invalid reservation id','danger');
  redirect('my_reservations.php');
}

$stmt = $pdo->prepare("
  SELECT id, user_id, date, start_time, end_time, status
  FROM reservations
  WHERE id = ? LIMIT 1
");
$stmt->execute([$id]);
$r = $stmt->fetch();

if (!$r || (int)$r['user_id'] !== $uid) {
  set_flash('err','Reservation not found','danger');
  redirect('my_reservations.php');
}

// Only allow cancel if it’s in the future and not already rejected/canceled
$now      = new DateTime('now');
$startDT  = DateTime::createFromFormat('Y-m-d H:i:s', $r['date'].' '.$r['start_time']);
$allowed  = in_array($r['status'], ['pending','approved'], true) && $startDT && $startDT > $now;

if (!$allowed) {
  set_flash('err','This reservation cannot be canceled','danger');
  redirect('my_reservations.php');
}

$up = $pdo->prepare("UPDATE reservations SET status='canceled' WHERE id=? LIMIT 1");
$up->execute([$id]);

set_flash('ok','Reservation canceled');
redirect('my_reservations.php');
