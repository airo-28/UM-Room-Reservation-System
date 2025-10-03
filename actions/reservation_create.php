<?php
require_once __DIR__.'/../lib/auth.php'; start_session(); require_login();
require_once __DIR__.'/../lib/csrf.php'; verify_csrf();
require_once __DIR__.'/../config/db.php';
require_once __DIR__.'/../lib/helpers.php';

$uid       = user()['id'];
$room_id   = (int)($_POST['room_id'] ?? 0);
$date      = $_POST['date'] ?? '';
$start     = $_POST['start_time'] ?? '';
$end       = $_POST['end_time'] ?? '';
$purpose   = trim($_POST['purpose'] ?? '');
$res_ids   = $_POST['resource_ids'] ?? [];

if (!$room_id || !$date || !$start || !$end || !$purpose) {
  set_flash('err','All fields required','danger'); redirect('reservations.php');
}

function tsec($t) { // "HH:MM" or "HH:MM:SS" -> seconds
  if (strlen($t) === 5) $t .= ':00';
  [$H,$M,$S] = array_map('intval', explode(':', $t));
  return $H*3600 + $M*60 + $S;
}
function norm($t) { return strlen($t)===5 ? $t.':00' : $t; }

$start = norm($start);
$end   = norm($end);

$startMin = (int)date('i', strtotime($start));
$endMin   = (int)date('i', strtotime($end));
if ($startMin !== 0 || $endMin !== 0) {
  set_flash('err','Start/End must be on the hour (e.g., 13:00)','danger');
  redirect('reservations.php');
}

$rc = $pdo->prepare('SELECT open_time, close_time FROM rooms WHERE id=?');
$rc->execute([$room_id]);
$r = $rc->fetch();
if (!$r) {
  set_flash('err','Room not found','danger'); redirect('reservations.php');
}

$start_s = tsec($start);
$end_s   = tsec($end);
$open_s  = tsec($r['open_time']);
$close_s = tsec($r['close_time']);

if ($end_s <= $start_s) {
  set_flash('err','End time must be after start time','danger'); redirect('reservations.php');
}
if ($start_s < $open_s || $end_s > $close_s) {
  set_flash('err','Booking must be within room hours ('.$r['open_time'].'–'.$r['close_time'].')','danger');
  redirect('reservations.php');
}

$durH = ($end_s - $start_s) / 3600;
if ($durH < 1) {
  set_flash('err','Minimum 1 hour per booking','danger'); redirect('reservations.php');
}

// Conflict check: only against APPROVED reservations, same date/room, overlapping window
$conf = $pdo->prepare('
  SELECT COUNT(*) FROM reservations
  WHERE room_id=? AND date=? AND status="approved"
    AND NOT (end_time <= ? OR start_time >= ?)
');
$conf->execute([$room_id, $date, $start, $end]);
if ((int)$conf->fetchColumn() > 0) {
  set_flash('err','Slot overlaps an approved reservation','danger'); redirect('reservations.php');
}

try {
  $pdo->beginTransaction();

  $ins = $pdo->prepare('
    INSERT INTO reservations(user_id,room_id,date,start_time,end_time,purpose,status)
    VALUES(?,?,?,?,?,?,"pending")
  ');
  $ins->execute([$uid,$room_id,$date,$start,$end,$purpose]);
  $rid = (int)$pdo->lastInsertId();

  if (!empty($res_ids)) {
    $link = $pdo->prepare('INSERT INTO reservation_resources(reservation_id,resource_id) VALUES(?,?)');
    foreach ($res_ids as $rId) {
      $link->execute([$rid, (int)$rId]);
    }
  }

  $pdo->commit();
  set_flash('ok','Reservation submitted for approval'); redirect('my_reservations.php');
} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  set_flash('err','Failed to create reservation','danger'); redirect('reservations.php');
}
