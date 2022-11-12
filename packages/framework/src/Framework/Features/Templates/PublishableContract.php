<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Templates;

interface PublishableContract
{
    public static function publish(): bool;
}
