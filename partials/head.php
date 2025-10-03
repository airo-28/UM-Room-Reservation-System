<?php
require_once __DIR__.'/../lib/auth.php';
start_session();
?>
<!doctype html>
<html lang="en"><head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo h(app_config()['APP_NAME']); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link rel="icon" href="<?php echo h(base_url('../assets/img/ui/logo.png')); ?>">
<link rel="stylesheet" href="<?php echo h(base_url('../assets/css/style.css')); ?>">
</head><body>
