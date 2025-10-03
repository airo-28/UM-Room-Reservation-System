<?php
require_once __DIR__.'/../../config/db.php';
header('Content-Type: application/json');

$room_id = (int)($_GET['room_id'] ?? 0);
$date = $_GET['date'] ?? '';

if (!$room_id || !$date) {
  echo json_encode(['ok'=>false,'error'=>'missing params']); exit;
}

$stmt = $pdo->prepare('SELECT start_time, end_time FROM reservations WHERE room_id=? AND date=? AND status="approved"');
$stmt->execute([$room_id, $date]);
$rows = $stmt->fetchAll();

$taken = [];
foreach ($rows as $r) {
  $s = (int)date('G', strtotime($r['start_time']));
  $e = (int)date('G', strtotime($r['end_time']));
  for ($h=$s; $h<$e; $h++) { $taken[] = $h; } // mark each hour within the approved block
}

echo json_encode(['ok'=>true,'taken'=>$taken]);
