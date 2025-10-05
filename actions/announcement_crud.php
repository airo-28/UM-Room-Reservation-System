<?php
require_once __DIR__.'/../lib/auth.php';
require_role(['admin']);
require_once __DIR__.'/../lib/csrf.php';
require_once __DIR__.'/../lib/helpers.php';
require_once __DIR__.'/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  set_flash('err','Invalid request','danger');
  redirect('announcements.php');
}

verify_csrf();

/* severity: update|info|notice|important */
$allowedSev = ['update','info','notice','important'];

/* Delete */
if (!empty($_POST['delete_id'])) {
  $id = (int)$_POST['delete_id'];
  $st = $pdo->prepare("DELETE FROM announcements WHERE id=? LIMIT 1");
  $st->execute([$id]);
  set_flash('ok','Announcement deleted');
  redirect('announcements.php');
}

/* Create / Update */
$id        = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$title     = trim($_POST['title'] ?? '');
$body      = trim($_POST['body'] ?? '');              // NOTE: using "body" to match your existing form/DB
$severity  = trim($_POST['severity'] ?? 'info');
$starts_at = trim($_POST['starts_at'] ?? '');
$ends_at   = trim($_POST['ends_at'] ?? '');
$is_active = isset($_POST['is_active']) ? 1 : 0;

/* Basic validation */
if ($title === '' || $body === '' || !in_array($severity, $allowedSev, true)) {
  set_flash('err','Please complete the form correctly','danger');
  redirect('announcements.php' . ($id ? '?edit='.$id : ''));
}

/* Normalize datetime (nullable) */
$sa = $starts_at !== '' ? date('Y-m-d H:i:s', strtotime($starts_at)) : null;
$ea = $ends_at   !== '' ? date('Y-m-d H:i:s', strtotime($ends_at))   : null;

/* Persist */
if ($id > 0) {
  $st = $pdo->prepare("
    UPDATE announcements
       SET title=?, body=?, severity=?, starts_at=?, ends_at=?, is_active=?
     WHERE id=?
  ");
  $st->execute([$title,$body,$severity,$sa,$ea,$is_active,$id]);
  set_flash('ok','Announcement updated');
} else {
  $st = $pdo->prepare("
    INSERT INTO announcements (title, body, severity, starts_at, ends_at, is_active)
    VALUES (?,?,?,?,?,?)
  ");
  $st->execute([$title,$body,$severity,$sa,$ea,$is_active]);
  set_flash('ok','Announcement created');
}

redirect('announcements.php');
