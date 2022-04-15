<?php

namespace Hyde\RealtimeCompiler;

/**
 * Main entry point for the Hyde Realtime Compiler.
 * 
 * Boots the compiler and proxies static assets.
 */
class HydeRC
{
    public static function boot(string $uri)
    {
        Server::log('Bootloader: Start time ' . PROXY_START);
        if (\Phar::running()) {
            Server::log('Phar: Running through phar');
        }

        Server::log('HydeRC: Booting Realtime Compiler...');
        Server::log('HydeRC: Hyde Installation Path: ' . HYDE_PATH);
        Server::log('HydeRC: Requested URI: ' . $uri);

        $proxy = new Proxy($uri);
        $proxy->serve();

        Server::log('HydeRC: Finished handling request. Execution time: ' . static::getExecutionTime() . 'ms.');
        Server::log('Bootloader: Stop time: ' . microtime(true));
    }

    public static function getExecutionTime()
    {
        return round((microtime(true) - PROXY_START) * 1000, 2);
    }

    public static function serveMedia(string $basename)
    {
        // First check if file exists in the _site/media directory
        $media_path = HYDE_PATH . '/_site/media/' . $basename;
        if (file_exists($media_path)) {
            return static::serveStatic($media_path);
        }
        // If not, check if file exists in the _media directory
        $media_path = HYDE_PATH . '/_media/' . $basename;
        if (file_exists($media_path)) {
            return static::serveStatic($media_path);
        }
        // Send 404 header
        header('HTTP/1.0 404 Not Found');
        exit();
    }

    /** @internal */
    private static function serveStatic(string $path)
    {
        header('Content-Type: ' . static::getStaticContentType($path));
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    /** @internal */
    private static function getStaticContentType(string $path)
    {
        if (str_ends_with($path, '.css')) {
            return 'text/css';
        }

        if (str_ends_with($path, '.js')) {
            return 'text/javascript';
        }

        return mime_content_type($path);
    }
}