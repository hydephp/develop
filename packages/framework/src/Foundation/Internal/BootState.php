<?php

declare(strict_types=1);

namespace Hyde\Foundation\Internal;

/**
 * @internal
 */
class BootState
{
    protected bool $booting = false;
    protected bool $booted = false;
    protected bool $ready = false;

    public function isBooting(): bool
    {
        return $this->booting;
    }

    public function isBooted(): bool
    {
        return $this->booted;
    }

    public function isReady(): bool
    {
        return $this->ready;
    }

    public function canBoot(): bool
    {
        return $this->ready && ! $this->booting;
    }

    public function ready(): void
    {
        $this->ready = true;
    }

    public function booting(): void
    {
        $this->booting = true;
    }

    public function booted(): void
    {
        $this->booting = false;
        $this->booted = true;
    }
}
