<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Templates;

abstract class PublishableView implements PublishableContract
{
    protected static string $title;
    protected static string $description;

    abstract public static function publish(bool $force = false): bool;

    public static function getTitle(): string
    {
        return static::$title;
    }

    public static function getDescription(): string
    {
        return static::$description;
    }
}
