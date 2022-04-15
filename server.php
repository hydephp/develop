<?php

/**
 * Hyde Realtime Server
 * 
 * Important! This feature is experimental at best and is in no way intended to be used in production.
 * Though, why you would want a dynamic web server to serve static HTML I don't know anyway.
 * But again, this is just a local testing tool.
 * 
 * Please report any bugs -- and there will be bugs -- on GitHub!
 * 
 * @author Caen De Silva <caen@desilva.se>
 * @package Hyde/RealtimeCompiler
 * @license MIT
 *
 * This file serves as an initial router and is based on the one from Laravel (license MIT) 
 */

// @todo Handle routes ending in .html
// @todo Proxy media assets
// @todo Cache compiled files

define('PROXY_START', microtime(true));

// Define the configuration constants
define('HYDE_PATH', realpath('../../'));
define('LOG_DEBUG_MESSAGES', false);

// Handle the request

$uri = urldecode(
  parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

// If uri ends in .html, strip it
if (str_ends_with($uri, '.html')) {
  $uri = substr($uri, 0, -5);
}

// This file allows us to emulate Apache's "mod_rewrite" functionality from the
// built-in PHP web server. This provides a convenient way to test a Laravel
// application without having installed a "real" web server software here.

// Bootstrap any application services.
require 'vendor/autoload.php';

// If it is a media asset, proxy it directly without booting the entire RC
if (str_starts_with($uri, '/media/')) {
    \Hyde\RealtimeCompiler\HydeRC::serveMedia(basename($uri));
}

// If the uri is empty, serve the index file
if (empty($uri) || $uri == '/') {
    $uri = '/index';
}

// Serve the application.
\Hyde\RealtimeCompiler\HydeRC::boot($uri);
