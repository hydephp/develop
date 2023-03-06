<?php

declare(strict_types=1);

namespace Hyde\Pages\Concerns;

use Stringable;
use Hyde\Facades\Filesystem;
use Illuminate\Contracts\Support\Arrayable;

class PageContents implements Arrayable, Stringable
{
    public string $body;

    public function __construct(string $body = '')
    {
        $this->body = str_replace("\r\n", "\n", rtrim($body));
    }

    public static function fromFile(string $path): static
    {
        return new static(Filesystem::getContents($path));
    }

    public function body(): string
    {
        return $this->body;
    }

    public function __toString(): string
    {
        return $this->body;
    }

    /** @return string[] */
    public function toArray(): array
    {
        return explode("\n", $this->body);
    }
}
