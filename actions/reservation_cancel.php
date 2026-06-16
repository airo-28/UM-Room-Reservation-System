<?php
require_once __DIR__.'/../lib/auth.php';
require_once __DIR__.'/../lib/csrf.php';
require_once __DIR__.'/../config/db.php';
require_once __DIR__.'/../lib/helpers.php';

start_session();
require_login();
verify_csrf();

$u   = user();
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

// Only allow cancel if future and currently pending/approved
$now     = new DateTime('now');
$startDT = DateTime::createFromFormat('Y-m-d H:i:s', $r['date'].' '.$r['start_time']);
$from    = strtolower(trim((string)$r['status']));
$can     = in_array($from, ['pending','approved'], true) && $startDT && $startDT > $now;

if (!$can) {
  set_flash('err','This reservation cannot be canceled','danger');
  redirect('my_reservations.php');
}

$to = 'canceled';

// update reservation
$up = $pdo->prepare("UPDATE reservations SET status=? WHERE id=?");
$up->execute([$to, $id]);

// transaction log with auto note
$log = $pdo->prepare("
  INSERT INTO reservation_logs (reservation_id, action, from_status, to_status, actor_user_id, actor_role, note)
  VALUES (?,?,?,?,?,?,?)
");
$log->execute([$id, $to, $from, $to, $uid, 'user', 'Canceled by user.']);

set_flash('ok','Reservation canceled');
redirect('my_reservations.php');
