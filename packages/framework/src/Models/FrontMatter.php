<?php

namespace Hyde\Framework\Models;

use Hyde\Framework\Actions\ConvertsArrayToFrontMatter;
use Illuminate\Support\Arr;

/**
 * @see \Hyde\Framework\Testing\Unit\FrontMatterModelTest
 */
class FrontMatter
{
    public array $matter;

    public function __construct(array $matter = [])
    {
        $this->matter = $matter;
    }

    public function __toString(): string
    {
        return (new ConvertsArrayToFrontMatter())->execute($this->matter);
    }

    public function __get(string $key): mixed
    {
        return $this->get($key);
    }

    public function get(string $key = null, mixed $default = null): mixed
    {
        if ($key) {
            return Arr::get($this->matter, $key, $default);
        }

        return $this->matter;
    }

    public static function fromArray(array $matter): static
    {
        return new static($matter);
    }
}
