<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Templates;

interface PublishableContract
{
    public static function publish(bool $force = false): bool;

    public static function getTitle(): string;

    public static function getDescription(): string;

    public static function getOutputPath(): string;

    public static function toArray(): array;
}
