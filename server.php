<?php

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @package  Laravel
 * @author   Taylor Otwell <taylorotwell@gmail.com>
 */

// @todo Handle routes ending in .html
// @todo Proxy media assets
// @todo Cache compiled files

define('PROXY_START', microtime(true));
define('HYDE_PATH', realpath('../../../'));

$uri = urldecode(
  parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

// This file allows us to emulate Apache's "mod_rewrite" functionality from the
// built-in PHP web server. This provides a convenient way to test a Laravel
// application without having installed a "real" web server software here.
if ($uri !== '/' && file_exists(getcwd().'/public'.$uri)) {
  return false;
}

// Bootstrap any application services.
require 'vendor/autoload.php';

// Serve the application.

\Hyde\RealtimeCompiler\HydeRC::boot($uri);
