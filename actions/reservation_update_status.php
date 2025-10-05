<?php
require_once __DIR__.'/../lib/auth.php';
require_role(['admin']);
require_once __DIR__.'/../lib/csrf.php';
require_once __DIR__.'/../lib/helpers.php';
require_once __DIR__.'/../config/db.php';

verify_csrf();

$id     = (int)($_POST['id'] ?? 0);
$status = strtolower(trim((string)($_POST['status'] ?? ''))); // approved|rejected
$note   = trim($_POST['note'] ?? '');
$admin  = user();

if ($id <= 0 || !in_array($status, ['approved','rejected'], true)) {
  set_flash('err','Invalid request','danger');
  redirect('calendar.php');
}

// fetch current status
$st = $pdo->prepare("SELECT id, status FROM reservations WHERE id=? LIMIT 1");
$st->execute([$id]);
$cur = $st->fetch();
if (!$cur) {
  set_flash('err','Reservation not found','danger');
  redirect('calendar.php');
}
$from = strtolower(trim((string)$cur['status']));
$to   = $status;

// update reservation status
$up = $pdo->prepare("UPDATE reservations SET status=? WHERE id=? LIMIT 1");
$up->execute([$to, $id]);

// write transaction log
$log = $pdo->prepare("
  INSERT INTO reservation_logs (reservation_id, action, from_status, to_status, actor_user_id, actor_role, note)
  VALUES (?,?,?,?,?,?,?)
");
$log->execute([$id, $to, $from, $to, (int)$admin['id'], 'admin', $note !== '' ? $note : null]);

set_flash('ok', 'Reservation '.$to);
redirect('calendar.php');
