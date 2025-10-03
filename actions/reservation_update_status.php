<?php
require_once __DIR__.'/../lib/auth.php';
require_once __DIR__.'/../lib/csrf.php';
require_once __DIR__.'/../config/db.php';
require_once __DIR__.'/../lib/helpers.php';

start_session();
require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verify_csrf();
} else {
  redirect('calendar.php');
  exit;
}

$id     = (int)($_POST['id'] ?? 0);
$status = $_POST['status'] ?? '';

$allowed = ['approved','rejected'];
if ($id <= 0 || !in_array($status, $allowed, true)) {
  set_flash('err','Invalid request','danger');
  redirect('calendar.php');
  exit;
}

$st = $pdo->prepare("UPDATE reservations SET status=? WHERE id=?");
$st->execute([$status, $id]);

set_flash('ok','Reservation updated');
redirect('calendar.php');
