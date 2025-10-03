<?php
function csrf_start_session() {
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }
}

/**
 * Add this inside your <form>. It prints a hidden input with the CSRF token.
 */
function csrf_field() {
  csrf_start_session();
  if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }
  $token = htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8');
  echo '<input type="hidden" name="csrf_token" value="'.$token.'">';
}

/**
 * Call this ONLY in action scripts that handle POST (e.g., auth_login.php, reservation_create.php).
 */
function verify_csrf() {
  csrf_start_session();
  $posted = $_POST['csrf_token'] ?? '';
  if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $posted)) {
    die('Invalid CSRF token');
  }
}
