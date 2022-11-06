<?php

declare(strict_types=1);

namespace Hyde\Support\Helpers;

use function htmlspecialchars;

class XML
{
    public static function escape(string $string): string
    {
        return htmlspecialchars($string, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }
}
