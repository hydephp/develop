<?php

namespace Hyde\Framework\Modules\Router;

use Illuminate\Support\Collection;

class Router implements Concerns\RouterContract
{
    public function getRoutes(): Collection
    {
        // TODO: Implement getRoutes() method.
    }

    public function getArray(): array
    {
        // TODO: Implement getArray() method.
    }

    public function getJson(): string
    {
        // TODO: Implement getJson() method.
    }


    protected function discoverRoutes(): void {}

    protected function discoverBladePages(): void {}

    protected function discoverMarkdownPages(): void {}

    protected function discoverMarkdownPosts(): void {}

    protected function discoverDocumentationPages(): void {}

    protected function discoverAbstract(string $className): void {}
}
