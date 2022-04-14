<?php

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @package  Laravel
 * @author   Taylor Otwell <taylorotwell@gmail.com>
 */

// @todo Handle routes ending in .html

define('PROXY_START', microtime(true));

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
require_once getcwd().'/public/index.php';