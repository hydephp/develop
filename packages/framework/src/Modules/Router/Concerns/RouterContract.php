<?php

namespace Hyde\Framework\Modules\Router\Concerns;

use Illuminate\Support\Collection;

interface RouterContract
{
    public function getRoutes(): Collection;
    public function getArray():  array;
    public function getJson(): string;
}
