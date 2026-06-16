<?php
$baseUrl = '/pl_final_project/public';
if (isset($_SERVER['HTTP_HOST']) && ($_SERVER['HTTP_HOST'] === 'localhost:8000' || $_SERVER['HTTP_HOST'] === '127.0.0.1:8000')) {
  $baseUrl = '/public';
}
return [
  'APP_NAME' => 'UM Room Reservation',
  'BASE_URL' => $baseUrl,
  'SESSION_NAME' => 'cst5l_session',
  'TIMEZONE' => 'Asia/Manila',
];

