<?php

namespace Hyde\RealtimeCompiler;

class Server
{
    public static function log(string $message)
    {
        file_put_contents('php://stdout', $message . PHP_EOL);
    }
}