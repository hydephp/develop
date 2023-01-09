<?php

declare(strict_types=1);

namespace Hyde\Foundation;

/**
 * @deprecated Temporary class for easier design overview
 * @internal Temporary class for easier design overview
 */
enum BootStates
{
    case NotBooted;
    case Ready;
    case Booting;
    case Booted;

    public function isBooted()
    {
        return $this === self::Booted;
    }

    public function canBoot()
    {
        return $this === self::NotBooted && $this === self::Ready;
    }

    public function shouldBoot()
    {
        return $this === self::NotBooted;
    }
}
