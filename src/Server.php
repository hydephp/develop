<?php

namespace Hyde\RealtimeCompiler;

use Hyde\RealtimeCompiler\Actions\FormatsAnsiString;

class Server
{
    public static function log(string $message, bool $isDebug = false, bool $ansi = true)
    {
        if ($ansi) {
            $message = FormatsAnsiString::get($message);
        }

        if ($isDebug && !LOG_DEBUG_MESSAGES) {
            return;
        }

        file_put_contents('php://stdout', $message . PHP_EOL);
    }
}