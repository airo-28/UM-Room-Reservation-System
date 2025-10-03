<?php
require_once __DIR__.'/../config/db.php';
require_once __DIR__.'/../lib/auth.php';
require_role(['admin']);
require_once __DIR__.'/../lib/csrf.php';
require_once __DIR__.'/../lib/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('users.php');
    exit;
}
verify_csrf();

$deleteId = isset($_POST['delete_id']) ? (int)$_POST['delete_id'] : 0;
$id       = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$fullName = trim($_POST['full_name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$role     = trim($_POST['role'] ?? 'user');

/* ========== DELETE USER ========== */
if ($deleteId > 0) {
    $me = user();
    if ($me && (int)$me['id'] === $deleteId) {
        set_flash('err','You cannot delete your own account.','danger');
        redirect('users.php');
        exit;
    }

    $st = $pdo->prepare('DELETE FROM users WHERE id=? LIMIT 1');
    $st->execute([$deleteId]);
    set_flash('ok','User deleted.');
    redirect('users.php');
    exit;
}

/* ========== VALIDATE INPUTS ========== */
if ($fullName === '' || $email === '') {
    set_flash('err','Full name and email are required.','danger');
    redirect('users.php'.($id?('?edit='.$id):''));
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    set_flash('err','Invalid email format.','danger');
    redirect('users.php'.($id?('?edit='.$id):''));
    exit;
}

if (!in_array($role, ['user','admin'], true)) {
    $role = 'user';
}

/* ========== CHECK EMAIL DUPLICATES ========== */
if ($id > 0) {
    $st = $pdo->prepare('SELECT id FROM users WHERE email=? AND id<>? LIMIT 1');
    $st->execute([$email, $id]);
} else {
    $st = $pdo->prepare('SELECT id FROM users WHERE email=? LIMIT 1');
    $st->execute([$email]);
}
if ($st->fetchColumn()) {
    set_flash('err','Email is already in use.','danger');
    redirect('users.php'.($id?('?edit='.$id):''));
    exit;
}

/* ========== UPDATE OR CREATE USER ========== */
if ($id > 0) {
    $st = $pdo->prepare('UPDATE users SET full_name=?, email=?, role=? WHERE id=?');
    $st->execute([$fullName, $email, $role, $id]);
    set_flash('ok','User updated.');
    redirect('users.php');
    exit;
} else {
    // If you don’t want random passwords, add a password field in the form instead
    $tempPassword = bin2hex(random_bytes(4)); // generates 8-char hex password
    $hash = password_hash($tempPassword, PASSWORD_BCRYPT);

    $st = $pdo->prepare('INSERT INTO users (full_name, email, role, password_hash, created_at) VALUES (?,?,?,?,NOW())');
    $st->execute([$fullName, $email, $role, $hash]);

    set_flash('ok','User created. Temporary password: '.$tempPassword);
    redirect('users.php');
    exit;
}
