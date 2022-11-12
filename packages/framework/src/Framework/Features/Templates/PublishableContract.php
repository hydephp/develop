<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Templates;

use Illuminate\Contracts\Support\Arrayable;

interface PublishableContract extends Arrayable
{
    public static function publish(bool $force = false): bool;

    public static function getTitle(): string;

    public static function getDescription(): string;

    public static function getOutputPath(): string;
}
