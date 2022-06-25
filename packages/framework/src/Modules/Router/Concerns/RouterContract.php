<?php

namespace Hyde\Framework\Modules\Router\Concerns;

use Illuminate\Support\Collection;

/**
 * protected @method  discoverRoutes(): void;
 * protected @method  discoverBladePages(): void;
 * protected @method  discoverMarkdownPages(): void;
 * protected @method  discoverMarkdownPosts(): void;
 * protected @method  discoverDocumentationPages(): void;
 * protected @method  discoverAbstract(string $className): void;
 */
interface RouterContract
{
    public function getRoutes(): Collection;
    public function getArray():  array;
    public function getJson(): string;
}
