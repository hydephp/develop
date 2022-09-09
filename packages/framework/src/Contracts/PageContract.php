<?php

namespace Hyde\Framework\Contracts;

/**
 * @deprecated Will be merged into AbstractPage
 */
interface PageContract
{
    public function get(string $key = null, mixed $default = null): mixed;

    public function matter(string $key = null, mixed $default = null): mixed;

    public function has(string $key, bool $strict = false): bool;

    public function getIdentifier(): string;

    public function getSourcePath(): string;

    public function getOutputPath(): string;

    public function getRouteKey(): string;

    public function getRoute(): RouteContract;

    public function compile(): string;

    public function htmlTitle(): string;
}
