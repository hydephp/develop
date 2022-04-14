<?php

namespace Hyde\RealtimeCompiler;

class HydeRC
{
    public static function boot(string $uri)
    {
        Server::log('Bootloader: Start time ' . PROXY_START);
        Server::log('HydeRC: Booting Realtime Compiler...');
        Server::log('HydeRC: Hyde Installation Path: ' . HYDE_PATH);

        $proxy = new Proxy($uri);
        $proxy->serve();

        Server::log('HydeRC: Finished handling request. Execution time: ' . static::getExecutionTime() . 'ms.');
        Server::log('Bootloader: Stop time: ' . microtime(true));
    }

    public static function getExecutionTime()
    {
        return round((microtime(true) - PROXY_START) * 1000, 2);
    }
}