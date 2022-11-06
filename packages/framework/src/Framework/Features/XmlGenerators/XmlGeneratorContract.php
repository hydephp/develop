<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\XmlGenerators;

interface XmlGeneratorContract
{
    public static function generate(): string;
}
