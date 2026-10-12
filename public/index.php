<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Localise la racine applicative, en local comme sur InfinityFree où le
// contenu de public/ est versé à la racine de htdocs et le code vit dans
// un sous-dossier (ex. htdocs/<dossier-app>/bootstrap/app.php).
// En local : __DIR__.'/..' (public/.. = racine). En prod : on cherche le
// premier sous-dossier contenant bootstrap/app.php + vendor/autoload.php.
$laravelBase = dirname(__DIR__);
if (! is_file($laravelBase.'/vendor/autoload.php') || ! is_file($laravelBase.'/bootstrap/app.php')) {
    foreach (glob(__DIR__.'/*', GLOB_ONLYDIR) ?: [] as $dir) {
        if (is_file($dir.'/vendor/autoload.php') && is_file($dir.'/bootstrap/app.php')) {
            $laravelBase = $dir;

            break;
        }
    }
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $laravelBase.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $laravelBase.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $laravelBase.'/bootstrap/app.php';

// Sur les hébergements type InfinityFree où index.php est servi depuis la
// racine htdocs (contenu de public/ déversé à la racine), le chemin public
// configuré par défaut (<base>/public) ne correspond plus au docroot réel.
// On aligne alors le chemin public sur le dossier d'index.php afin que
// public_path() (manifest, sw.js, offline.html) et asset() restent cohérents.
if (method_exists($app, 'usePublicPath')) {
    try {
        $configuredPublic = $app->publicPath();
    } catch (Throwable $e) {
        $configuredPublic = null;
    }

    if ($configuredPublic !== __DIR__ && is_file(__DIR__.'/manifest.webmanifest')) {
        $app->usePublicPath(__DIR__);
    }
}

$app->handleRequest(Request::capture());

