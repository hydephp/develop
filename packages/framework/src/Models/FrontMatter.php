<?php

namespace Hyde\Framework\Models;

use Hyde\Framework\Actions\ConvertsArrayToFrontMatter;

class FrontMatter
{
    public array $matter;

    public function __construct(array $matter)
    {
        $this->matter = $matter;
    }

    public function __toString(): string
    {
        return (new ConvertsArrayToFrontMatter())->execute($this->matter);
    }

    public static function fromArray(array $matter): static
    {
        return new static($matter);
    }
}
