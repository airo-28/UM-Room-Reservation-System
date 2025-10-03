<?php
require_once __DIR__.'/helpers.php';

// Pull in DB so functions here can use $pdo when called directly
if (!isset($pdo)) {
  $dbFile = __DIR__ . '/../config/db.php';
  if (file_exists($dbFile)) {
    require_once $dbFile;
  }
}

function start_session() {
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }
}

function user() {
  return $_SESSION['user'] ?? null;
}

function is_logged_in() {
  return isset($_SESSION['user']);
}

function require_login() {
  start_session();
  if (!is_logged_in()) {
    header("Location: " . base_url("login.php"));
    exit();
  }
}

function require_role(array $roles) {
  start_session();
  if (!is_logged_in()) {
    header("Location: " . base_url("login.php"));
    exit();
  }
  $u = user();
  if (!in_array($u['role'], $roles, true)) {
    header("Location: " . base_url("login.php"));
    exit();
  }
}

function login_user(array $u) {
  start_session();
  $_SESSION['user'] = [
    'id'    => (int)$u['id'],
    'name'  => $u['full_name'] ?? ($u['name'] ?? ''),
    'email' => $u['email'] ?? '',
    'role'  => $u['role'] ?? 'user'
  ];
}

/**
 * Compatibility helper:
 * Allows old code that calls login($email, $password).
 * Returns true on success, false on failure.
 */
function login($email, $password) {
  start_session();
  global $pdo;
  if (!isset($pdo) || !$pdo) {
    // Try loading DB again if somehow not loaded
    $dbFile = __DIR__ . '/../config/db.php';
    if (file_exists($dbFile)) {
      require_once $dbFile;
    }
  }
  if (!isset($pdo) || !$pdo) {
    return false;
  }

  $stmt = $pdo->prepare("SELECT id, full_name, email, password_hash, role FROM users WHERE email=? LIMIT 1");
  $stmt->execute([$email]);
  $u = $stmt->fetch();

  if ($u && password_verify($password, $u['password_hash'])) {
    login_user($u);
    return true;
  }
  return false;
}

function logout_user() {
  start_session();
  $_SESSION = [];
  if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
  }
  session_destroy();
}
