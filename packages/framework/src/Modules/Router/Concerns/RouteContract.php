<?php

namespace Hyde\Framework\Modules\Router\Concerns;

interface RouteContract
{
    public function __construct(string $sourceModel, string $sourceFile);

    public function getPath(): string;

    public function getName(): string;

    public function baseName(): string;

    public function __toString(): string;
}
