<?php
require_once __DIR__ . '/vendor/autoload.php';

echo '<pre>';
echo 'autoload exists: ' . (file_exists(__DIR__ . '/vendor/autoload.php') ? 'SI' : 'NO') . PHP_EOL;
echo 'PHPMailer class exists: ' . (class_exists(\PHPMailer\PHPMailer\PHPMailer::class) ? 'SI' : 'NO') . PHP_EOL;
echo '</pre>';