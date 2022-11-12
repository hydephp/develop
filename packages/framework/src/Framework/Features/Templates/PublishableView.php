<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Templates;

abstract class PublishableView implements PublishableContract
{
    abstract public static function publish(): bool;

    public static function getTitle(): string
    {
        // TODO: Implement getTitle() method.
    }

    public static function getDescription(): string
    {
        // TODO: Implement getDescription() method.
    }
}
