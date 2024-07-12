
    /*
    |--------------------------------------------------------------------------
    | Navigation Menu Configuration
    |--------------------------------------------------------------------------
    |
    | If you are looking to customize the main navigation menu, this is the place!
    |
    | All these settings uses Route Keys to identify the page you want to configure.
    | A route key is simply the URL path to the page, without the file extension.
    | So `_site/posts/hello-world.html` has the route key 'posts/hello-world'.
    |
    */

    // OLD
    
    'navigation' => [
        // This configuration sets the priorities used to determine the order of the menu.
        // The default values have been added below for reference and easy editing.
        // The array key is the page's route key, the value is the priority.
        // Lower values show up first in the menu. The default is 999.
        'order' => [
            'index' => 0,
            'posts' => 10,
            'docs/index' => 100,
        ],

        // In case you want to customize the labels for the menu items, you can do so here.
        // Simply add the route key as the array key, and the label as the value.
        'labels' => [
            'index' => 'Home',
            'docs/index' => 'Docs',
        ],

        // These are the route keys of pages that should not show up in the navigation menu.
        'exclude' => [
            '404',
        ],

        // Any extra links you want to add to the navigation menu can be added here.
        // To get started quickly, you can uncomment the defaults here.
        // See the documentation link above for more information.
        'custom' => [
            // NavigationItem::create('https://github.com/hydephp/hyde', 'GitHub', 200),
        ],

        // How should pages in subdirectories be displayed in the menu?
        // You can choose between 'dropdown', 'flat', and 'hidden'.
        'subdirectories' => 'hidden',
    ],


    // NEW (creates an array exactly like the one above, but with a more fluent API that supports better autocomplete)

    'navigation' => Navigation::configure()
        ->setPagePriorities([
            'index' => 0,
            'posts' => 10,
            'docs/index' => 100,
        ])
        ->setPageLabels([
            'index' => 'Home',
            'docs/index' => 'Docs',
        ])
        ->excludePages([
            '404',
        ])
        ->addNavigationItems([
            // Navigation::item('https://github.com/hydephp/hyde', 'GitHub', 200),
        ])
        ->setSubdirectoryDisplayMode('hidden'),

    // All of these options work in the yaml config (this is just an example, please use the same example data as above in the docs)
    hyde:
        navigation:
            custom:
                - destination: 'https://example.com'
                label: 'Example'
                priority: 100
                attributes:
                    class: 'example'
                - destination: 'about'
                label: 'About Us'
                priority: 200
                attributes:
                    class: 'about'
                    id: 'about'
                - destination: 'contact'
                label: 'Contact'
                priority: 300
                attributes:
                    target: '_blank'
                    rel: 'noopener noreferrer'
                    foo: 'bar'


// relevant code snippets

<?php

/**
 * General facade for navigation features.
 */
class Navigation
{
    /**
     * Configuration helper method to define a new navigation item, with better IDE support.
     *
     * The returned array will then be used by the framework to create a new NavigationItem instance. {@see \Hyde\Framework\Features\Navigation\NavigationItem}
     *
     * @see https://hydephp.com/docs/2.x/navigation-api
     *
     * @param  string<\Hyde\Support\Models\RouteKey>|string  $destination  Route key, or an external URI.
     * @param  string|null  $label  If not provided, Hyde will try to get it from the route's connected page, or from the URL.
     * @param  int|null  $priority  If not provided, Hyde will try to get it from the route or the default priority of 500.
     * @param  array<string, scalar>  $attributes  Additional attributes for the navigation item.
     * @return array{destination: string, label: ?string, priority: ?int, attributes: array<string, scalar>}
     */
    public static function item(string $destination, ?string $label = null, ?int $priority = null, array $attributes = []): array
    {
        return compact('destination', 'label', 'priority', 'attributes');
    }

    /**
     * Configuration helper method to define the navigation menu configuration with better IDE support.
     *
     * The builder is an array object that will be used by the framework to set the navigation menu configuration.
     *
     * @experimental This method is experimental and may change or be removed before the final release.
     */
    public static function configure(): NavigationMenuConfigurationBuilder
    {
        return new NavigationMenuConfigurationBuilder();
    }
}

<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use TypeError;
use ArrayObject;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Configuration helper class to define the navigation menu configuration with better IDE support.
 *
 * The configured data will then be used by the framework to set the navigation menu configuration.
 *
 * @see \Hyde\Facades\Navigation::configure()
 *
 * @experimental This class is experimental and may change or be removed before the final release.
 */
class NavigationMenuConfigurationBuilder extends ArrayObject implements Arrayable
{
    /**
     * Set the order of the navigation items.
     *
     * Either define a map of route keys to priorities, or just a list of route keys and we'll try to match that order.
     *
     * @param  array<string, int>|array<string>  $order
     * @return $this
     */
    public function setPagePriorities(array $order): static
    {
        $this['order'] = $order;

        return $this;
    }

    /**
     * Set the labels for the navigation items.
     *
     * Each key should be a route key, and the value should be the label to display.
     *
     * @param  array<string, string>  $labels
     * @return $this
     */
    public function setPageLabels(array $labels): static
    {
        $this['labels'] = $labels;

        return $this;
    }

    /**
     * Exclude certain items from the navigation.
     *
     * Each item should be a route key for the page to exclude.
     *
     * @param  array<string>  $exclude
     * @return $this
     */
    public function excludePages(array $exclude): static
    {
        $this['exclude'] = $exclude;

        return $this;
    }

    /**
     * Add custom items to the navigation.
     *
     * @example `[Navigation::item('https://github.com/hydephp/hyde', 'GitHub', 200, ['target' => '_blank'])]`
     *
     * @param  array<array{destination: string, label: ?string, priority: ?int, attributes: array<string, scalar>}>  $custom
     * @return $this
     */
    public function addNavigationItems(array $custom): static
    {
        $this['custom'] = $custom;

        return $this;
    }

    /**
     * Set the display mode for pages in subdirectories.
     *
     * You can choose between 'dropdown', 'flat', and 'hidden'. The default is 'hidden'.
     *
     * @param  'dropdown'|'flat'|'hidden'  $displayMode
     * @return $this
     */
    public function setSubdirectoryDisplayMode(string $displayMode): static
    {
        self::assertType(['dropdown', 'flat', 'hidden'], $displayMode);

        $this['subdirectory_display'] = $displayMode;

        return $this;
    }

    /**
     * Hide pages in subdirectories from the navigation.
     *
     * @experimental This method is experimental and may change or be removed before the final release.
     *
     * @return $this
     */
    public function hideSubdirectoriesFromNavigation(): static
    {
        return $this->setSubdirectoryDisplayMode('hidden');
    }

    /**
     * Get the instance as an array.
     *
     * @return array{order: array<string, int>, labels: array<string, string>, exclude: array<string>, custom: array<array{destination: string, label: ?string, priority: ?int, attributes: array<string, scalar>}>, subdirectory_display: string}
     */
    public function toArray(): array
    {
        return $this->getArrayCopy();
    }

    /** @experimental May be moved to a separate helper class in the future. */
    protected static function assertType(array $types, string $value): void
    {
        if (! in_array($value, $types)) {
            throw new TypeError('Value must be one of: '.implode(', ', $types));
        }
    }
}


// The docs config has not been updated to use the builder api

    /*
    |--------------------------------------------------------------------------
    | Sidebar Settings
    |--------------------------------------------------------------------------
    |
    | The Hyde Documentation Module comes with a fancy Sidebar that is
    | automatically populated with links to your documentation pages.
    | Here, you can configure its behavior, content, look and feel.
    |
    */

    'sidebar' => [
        // The title in the sidebar header
        'header' => env('SITE_NAME', 'HydePHP').' Docs',

        // When using a grouped sidebar, should the groups be collapsible?
        'collapsible' => true,

        // A string of Markdown to show in the footer. Set to `false` to disable.
        'footer' => '[Back to home page](../)',

        /*
        |--------------------------------------------------------------------------
        | Sidebar Page Order
        |--------------------------------------------------------------------------
        |
        | In the generated Documentation pages the navigation links in the sidebar
        | default to sort alphabetically. You can reorder the page identifiers
        | in the list below, and the links will get sorted in that order.
        |
        | The items will get a priority of 500 plus the order its found in the list.
        | Pages without a priority will fall back to the default priority of 999.
        |
        | You can also set explicit priorities in front matter or by specifying
        | a value to the array key in the list to override the inferred value.
        |
        */

        'order' => [
            'readme',
            'installation',
            'getting-started',
        ],

        /*
        |--------------------------------------------------------------------------
        | Table of Contents Settings
        |--------------------------------------------------------------------------
        |
        | The Hyde Documentation Module comes with a fancy Sidebar that, by default,
        | has a Table of Contents included. Here, you can configure its behavior,
        | content, look and feel. You can also disable the feature completely.
        |
        */

        'table_of_contents' => [
            'enabled' => true,
            'min_heading_level' => 2,
            'max_heading_level' => 4,
        ],

    ],