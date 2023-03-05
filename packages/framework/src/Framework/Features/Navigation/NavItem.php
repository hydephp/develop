<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Hyde\Hyde;
use Hyde\Support\Models\Route;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\Deprecated;
use Stringable;

/**
 * Abstraction for a navigation menu item. Used by the NavigationMenu and DocumentationSidebar classes.
 *
 * @todo Refactor to reduce code overlapping with the Route class
 *
 * You have a few options to construct a navigation menu item:
 *   1. You can supply a Route directly and explicit properties to the constructor
 *   2. You can use NavItem::fromRoute() to use data from the route
 *   3. You can use NavItem::toLink() for an external or un-routed link
 */
class NavItem implements Stringable
{
    /** @deprecated Use $destination instead */
    public Route $route;

    /** @deprecated Use $destination instead */
    public string $href;

    public string $destination;

    public string $label;
    public int $priority;
    public ?string $group;

    /**
     * Create a new navigation menu item.
     */
    public function __construct(Route|string $destination, string $label, int $priority = 500, ?string $group = null)
    {
        $this->destination = $destination instanceof Route ? $destination->getLink() : $destination;

        // @deprecated: Temporary during refactor
        if ($destination instanceof Route) {
            $this->route = $destination;
        } else {
            $this->href = $destination;
        }

        $this->label = $label;
        $this->priority = $priority;
        $this->group = $group;
    }

    /**
     * Create a new navigation menu item from a route.
     */
    public static function fromRoute(Route $route): static
    {
        return new static(
            // $route->getLink(),
            $route, // needed by NavigationMenu::shouldItemBeHidden()
            $route->getPage()->navigationMenuLabel(),
            $route->getPage()->navigationMenuPriority(),
            static::resolveRouteGroup($route),
        );
    }

    /**
     * Create a new navigation menu item leading to an external URI.
     *
     * @deprecated Rename to forLink instead as toLink makes it sound like it returns a Link instance
     */
    public static function toLink(string $href, string $label, int $priority = 500): static
    {
        return new static($href, $label, $priority);
    }

    /**
     * Create a new navigation menu item leading to a Route model.
     *
     * @deprecated Rename to forRoute instead as toRoute makes it sound like it returns a Route instance
     *
     * @todo Support route keys as well
     */
    public static function toRoute(Route $route, string $label, int $priority = 500): static
    {
        return new static($route->getLink(), $label, $priority, static::resolveRouteGroup($route));
    }

    /**
     * Resolve a link to the navigation item.
     */
    public function __toString(): string
    {
        return $this->destination;
    }

    /**
     * Check if the NavItem instance is the current page.
     */
    public function isCurrent(): bool
    {
        return Hyde::currentRoute()->getLink() === $this->destination;
    }

    public function getGroup(): ?string
    {
        return $this->group;
    }

    protected static function resolveRouteGroup(Route $route): ?string
    {
        return static::normalizeGroupKey(($route)->getPage()->data('navigation.group'));
    }

    protected static function normalizeGroupKey(?string $group): ?string
    {
        return $group ? Str::slug($group) : null;
    }
}
