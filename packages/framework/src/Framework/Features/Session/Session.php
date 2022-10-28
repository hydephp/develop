<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Session;

class Session
{
    protected array $warnings = [];

    public function addWarning(string $warning): void
    {
        $this->warnings[] = $warning;
    }

    public function getWarnings(): array
    {
        return $this->warnings;
    }
}
