<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\XmlGenerators;

use function htmlspecialchars;

abstract class BaseXmlGenerator implements XmlGeneratorContract
{
    protected static function escape(string $string): string
    {
        return htmlspecialchars($string, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }
}
