<?php

namespace Hyde\RealtimeCompiler;

class HydeRC
{
    public static function boot(string $uri)
    {
        Server::log('HydeRC: Booting Realtime Compiler...');
        Server::log('HydeRC: Hyde Installation Path: ' . static::getHydePath());

        $proxy = new Proxy($uri);
        $proxy->serve();
    }

    public static function getHydePath()
    {
        return realpath(__DIR__ . '/../../../');
    }
}