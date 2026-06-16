<?php
$DB_HOST = 'localhost';
$DB_NAME = 'cst5l_db';
$DB_USER = 'root';
$DB_PASS = '';
$dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";
$options = [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

$use_sqlite = false;
try {
  $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
  // Fallback to SQLite
  $sqlite_path = __DIR__ . '/../database.db';
  $is_new = !file_exists($sqlite_path);
  try {
    $pdo = new PDO("sqlite:" . $sqlite_path, null, null, $options);
    $use_sqlite = true;
    $pdo->exec('PRAGMA foreign_keys = ON;');
  } catch (PDOException $se) {
    die('DB Connection failed: ' . $e->getMessage() . ' | SQLite: ' . $se->getMessage());
  }
}

if ($use_sqlite) {
  // Register custom functions for MySQL compatibility in SQLite
  $pdo->sqliteCreateFunction('NOW', function() {
    return date('Y-m-d H:i:s');
  });
  $pdo->sqliteCreateFunction('CURDATE', function() {
    return date('Y-m-d');
  });
  $pdo->sqliteCreateFunction('DATE_FORMAT', function($val, $format) {
    if (!$val) return null;
    $timestamp = strtotime($val);
    if (!$timestamp) return $val;
    $map = [
      '%Y' => 'Y',
      '%m' => 'm',
      '%d' => 'd',
      '%H' => 'H',
      '%i' => 'i',
      '%s' => 's',
    ];
    $php_format = strtr($format, $map);
    return date($php_format, $timestamp);
  });
  $pdo->sqliteCreateFunction('TIME_FORMAT', function($val, $format) {
    if (!$val) return null;
    $timestamp = strtotime("1970-01-01 " . $val);
    if (!$timestamp) return $val;
    $map = [
      '%H' => 'H',
      '%i' => 'i',
      '%s' => 's',
    ];
    $php_format = strtr($format, $map);
    return date($php_format, $timestamp);
  });

  if ($is_new) {
    $schema = file_get_contents(__DIR__ . '/../sql/schema.sql');
    
    // Convert MySQL-specific definitions to SQLite compatible ones:
    // Remove ENGINE=InnoDB
    $schema = preg_replace('/ENGINE\s*=\s*\w+/i', '', $schema);
    // Convert id INT AUTO_INCREMENT PRIMARY KEY to id INTEGER PRIMARY KEY AUTOINCREMENT
    $schema = preg_replace('/\bid\s+INT\s+AUTO_INCREMENT\s+PRIMARY\s+KEY/i', 'id INTEGER PRIMARY KEY AUTOINCREMENT', $schema);
    // Convert ENUM(...) type declarations to TEXT
    $schema = preg_replace('/ENUM\([^)]+\)/i', 'TEXT', $schema);
    // Remove inline INDEX declarations
    $schema = preg_replace('/,\s*INDEX\s+\w+\s*\([^)]+\)/i', '', $schema);
    
    // Execute translated schema
    $pdo->exec($schema);
    
    // Load and execute seed data
    if (file_exists(__DIR__ . '/../sql/seed.sql')) {
      $seed = file_get_contents(__DIR__ . '/../sql/seed.sql');
      $pdo->exec($seed);
    }
  }
}

