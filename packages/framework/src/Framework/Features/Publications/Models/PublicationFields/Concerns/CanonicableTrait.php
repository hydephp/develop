<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models\PublicationFields\Concerns;

use RuntimeException;
use function substr;

trait CanonicableTrait
{
    public function __toString(): string
    {
        return $this->getCanonicalValue();
    }

    public function getCanonicalValue(): string
    {
        return substr($this->value, 0, 64) ?: throw new RuntimeException('Canonical value cannot be empty');
    }
}
