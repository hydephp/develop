<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models\PublicationFieldValues;

trait CanonicableTrait
{
    public function __toString(): string
    {
        return $this->getCanonicalValue();
    }

    public function getCanonicalValue(): string
    {
        return $this->value;
    }
}
