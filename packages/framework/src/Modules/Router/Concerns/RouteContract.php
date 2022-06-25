<?php

namespace Hyde\Framework\Modules\Router\Concerns;

/**
 * protected @var string  $sourceModel (abstract page model)
 * protected @var string  $sourceFile (relative Hyde path)
 * protected @var string  $name (generated route key)
 * protected @var string  $path (generated HTML path)
 *
 * protected @method  generateRouteName(): void;
 * protected @method  generateOutputPath(): void;
 */
interface RouteContract
{
    public function __construct(string $sourceModel, string $sourceFile);

    public function getPath(): string;
    public function getName(): string;

    public function __toString(): string;
}
