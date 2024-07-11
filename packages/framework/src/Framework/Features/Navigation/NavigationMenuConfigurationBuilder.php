<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use ArrayObject;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Configuration helper method to define the navigation menu configuration with better IDE support.
 *
 * The configured object will be cast to an array that will be used by the framework to set the config data.
 *
 * @experimental This method is experimental and may change in the future.
 */
class NavigationMenuConfigurationBuilder extends ArrayObject implements Arrayable
{
    protected array $config = [];
}
