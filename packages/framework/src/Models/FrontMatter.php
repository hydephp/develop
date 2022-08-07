<?php

namespace Hyde\Framework\Models;

use Hyde\Framework\Actions\ConvertsArrayToFrontMatter;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Object representing the YAML front matter of a Markdown file.
 *
 * The data here is equal to the YAML. Unless you are using the data to construct dynamic data,
 * you probably want to call the `matter()` method on the Page object, as that will let you
 * access dynamic computed data if it exists, or it will fall back to this class's data.
 *
 * For package developers:
 * Use $page->matter('foo') to access computed data,
 * Use $page->matter->get('foo') to access raw data.
 *
 * @see \Hyde\Framework\Testing\Unit\FrontMatterModelTest
 */
class FrontMatter implements Arrayable, \Stringable
{
    public array $data;

    public function __construct(array $matter = [])
    {
        $this->data = $matter;
    }

    public function __toString(): string
    {
        return (new ConvertsArrayToFrontMatter())->execute($this->data);
    }

    public function __get(string $key): mixed
    {
        return $this->get($key);
    }

    public function get(string $key = null, mixed $default = null): mixed
    {
        if ($key) {
            return Arr::get($this->data, $key, $default);
        }

        return $this->data;
    }

    public function set(string $key, mixed $value): static
    {
        $this->data[$key] = $value;

        return $this;
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public static function fromArray(array $matter): static
    {
        return new static($matter);
    }
}
