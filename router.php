<?php
/**
 * Router for PHP built-in development server.
 *
 * Usage:  php -S 127.0.0.1:8000 router.php
 *
 * This serves the WHOLE project tree (just like Apache/XAMPP would)
 * so that relative paths like ../assets/ and ../actions/ from public/
 * resolve correctly.
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Map bare "/" to public/index.php
if ($uri === '/') {
    require __DIR__ . '/public/index.php';
    return true;
}

$file = __DIR__ . $uri;

// If the path maps to an actual file, serve it
if (is_file($file)) {
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

    // Execute PHP files
    if ($ext === 'php') {
        require $file;
        return true;
    }

    // Set proper MIME types for common static files
    $mime = [
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'json' => 'application/json',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'svg'  => 'image/svg+xml',
        'ico'  => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2'=> 'font/woff2',
        'ttf'  => 'font/ttf',
        'mp4'  => 'video/mp4',
    ];

    if (isset($mime[$ext])) {
        header('Content-Type: ' . $mime[$ext]);
    }

    readfile($file);
    return true;
}

// If it's a directory that contains index.php, serve it
if (is_dir($file) && is_file($file . '/index.php')) {
    require $file . '/index.php';
    return true;
}

// 404
http_response_code(404);
echo "<h1>Not Found</h1><p>The requested resource <code>" . htmlspecialchars($uri) . "</code> was not found.</p>";
return true;
