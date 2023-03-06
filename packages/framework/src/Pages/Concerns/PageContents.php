<?php

declare(strict_types=1);

namespace Hyde\Pages\Concerns;

use Stringable;
use Illuminate\Contracts\Support\Arrayable;

class PageContents implements Arrayable, Stringable
{
    public string $body;
}
