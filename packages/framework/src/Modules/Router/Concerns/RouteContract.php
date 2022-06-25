<?php

namespace Hyde\Framework\Modules\Router\Concerns;

/**
 * protected @var string  $sourceModel (abstract page model)
 * protected @var string  $sourceFile (relative Hyde path)
 * protected @var string  $name (basename/slug)
 */
interface RouteContract
{
    public function __construct(string $sourceModel, string $sourceFile);

    public function getPath(): string;
    public function getName(): string;

    public function __toString(): string;
}
