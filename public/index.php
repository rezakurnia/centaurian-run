<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Hostinger: aplikasi ada di folder "centaurian" sejajar dengan public_html; lokal tetap satu level di atas public
$base = is_dir(__DIR__.'/../centaurian') ? __DIR__.'/../centaurian' : __DIR__.'/..';

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $base.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $base.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $base.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
