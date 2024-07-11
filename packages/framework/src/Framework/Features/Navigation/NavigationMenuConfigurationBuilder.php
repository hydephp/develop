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

    public function toArray(): array
    {
        return $this->config;
    }

    /**
     * Set the order of the navigation items.
     *
     * @param array $order
     * @return $this
     */
    public function order(array $order): static
    {
        // TODO: Implement order() method.
    }

    /**
     * Set the labels for the navigation items.
     *
     * @param array $labels
     * @return $this
     */
    public function labels(array $labels): static
    {
        // TODO: Implement labels() method.
    }

    /**
     * Exclude certain items from the navigation.
     *
     * @param array $exclude
     * @return $this
     */
    public function exclude(array $exclude): static
    {
        // TODO: Implement exclude() method.
    }

    /**
     * Add custom items to the navigation.
     *
     * @param array $custom
     * @return $this
     */
    public function custom(array $custom): static
    {
        // TODO: Implement custom() method.
    }

    /**
     * Set the display mode for subdirectories.
     *
     * @param string $displayMode
     * @return $this
     */
    public function subdirectoryDisplay(string $displayMode): static
    {
        // TODO: Implement subdirectoryDisplay() method.
    }
}
