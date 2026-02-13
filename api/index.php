<?php

require __DIR__ . '/../vendor/autoload.php';

// -------------------------------------------------------------------------
// Vercel Serverless Fix: Use /tmp for everything (Read-Only Filesystem)
// -------------------------------------------------------------------------

// 1. Override Cache Paths (bootstrap/cache)
$cachePath = '/tmp/cache';
if (!is_dir($cachePath)) {
    mkdir($cachePath, 0777, true);
}

$_ENV['APP_SERVICES_CACHE'] = $cachePath . '/services.php';
$_ENV['APP_PACKAGES_CACHE'] = $cachePath . '/packages.php';
$_ENV['APP_ROUTES_CACHE'] = $cachePath . '/routes.php';
$_ENV['APP_EVENTS_CACHE'] = $cachePath . '/events.php';
$_ENV['APP_CONFIG_CACHE'] = $cachePath . '/config.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

// 2. Override Storage Path
$storagePath = '/tmp/storage';
if (!is_dir($storagePath)) {
    mkdir($storagePath, 0777, true);
    mkdir($storagePath . '/framework/views', 0777, true);
    mkdir($storagePath . '/framework/cache', 0777, true);
    mkdir($storagePath . '/framework/sessions', 0777, true);
    mkdir($storagePath . '/logs', 0777, true);
}

$app->useStoragePath($storagePath);

// -------------------------------------------------------------------------

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
