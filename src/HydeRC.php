<?php

namespace Hyde\RealtimeCompiler;

class HydeRC
{
    public static function boot(string $uri)
    {
        $proxy = new Proxy($uri);
        $proxy->serve();
    }
}