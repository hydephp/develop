---
navigation:
    label: "Navigation Menus"
    priority: 26
---

# Navigation Menus

## Introduction

A great time-saving feature of HydePHP is the automatic navigation menu and documentation sidebar generation.
Hyde is designed to automatically configure these menus for you based on the content you have in your project.

There are two types of navigation menus in Hyde:

- **Primary Navigation Menu**: This is the main navigation menu that appears on most pages of your site.
- **Documentation Sidebar**: This is a sidebar that appears on documentation pages and contains links to other documentation pages.

HydePHP automatically generates all of these menus for you based on the content of your project,
and does its best to automatically configure them in the way that you most likely want them to be.

Of course, this won't always be perfect, so thankfully Hyde makes it a breeze to customize these menus to your liking.
Keep on reading to learn how! To learn even more about the sidebars, visit the [Documentation Pages](documentation-pages) documentation.

### Quick primer on the internals

It may be beneficial to understand the internal workings of the navigation menus to take full advantage of the options.

In short, both navigation menu types extend the same class (meaning they share the same base code), this means that the way
they are configured are very similar, making the documentation here applicable to both types of menus.

See the [Digging Deeper](#digging-deeper-into-the-internals) section of this page if you want the full scoop on the internals!

### Primer on priorities

All navigation menu items have an internal priority value that determines its order in the navigation.
Lower values means that the item will be higher up in the menu. The default for pages is `999` which puts them last.
However, some pages are autoconfigured to have a lower priority, for example, the `index` page defaults to a priority of `0`,

### What to customize?

Here is a quick overview of what you might want to customize in your navigation menus:

- Navigation menu item labels - the text that appears in the menu links
- Navigation menu item priority - control the order in which the links appear
- Navigation menu item visibility - control if pages may show up in the menus
- Navigation menu item grouping - group pages together in dropdowns

### How and where to customize?

Hyde provides a few different ways to customize the navigation menus, depending on what you prefer.

Specifying the data in the front matter will override any dynamically inferred or config defined priority.
While this is useful for one-offs, it can make it harder to reorder items later on as you can't see the whole picture at once.
It's up to you which method you prefer to use.

To customize how a page is represented in navigation, you can either set the `navigation` front matter data in the page's markdown file,
or configure it in the config file. Main navigation items are in the `hyde` config file, while documentation sidebar items are in the `docs` config file.
General options for the entire navigation menus are also available in the `hyde` and `docs` config files. 

Now that you know the basics, let's dive into the details of how to customize the navigation menus!

## Front matter configuration

The front matter options allow you to customize the navigation menus on a per-page basis. 
Here is a quick reference of the available options. The full documentation of each option is below.
You don't need to specify all the keys, only the ones you want to customize.

```yaml
navigation:
    label: string  # The text to display
    priority: int  # Order is also supported
    hidden: bool   # Visible is also supported (but obviously invert the value)
    group: string  # Category is also supported
```

### `label`

The `label` option allows you to customize the text that appears in the navigation menu for the page.

```yaml
navigation:
    label: "My Custom Label"
```

### `priority`

The `priority` option allows you to control the order in which the page appears in the navigation menu. You can also use `order` instead of `priority`.

```yaml
navigation:
    priority: 10
```

### `hidden`

The `hidden` option allows you to control if the page appears in the navigation menu. You can also use `visible` instead of `hidden`, but obviously invert the value.

```yaml
navigation:
    hidden: true
```

### `group`

The `group` option has a slightly different meaning depending on the type of navigation menu.
For the primary navigation menu, it allows you to group pages together in dropdowns.
For the sidebar, it allows you to group pages together in the sidebar under a common heading.
You can also use `category` instead of `group`.

```yaml
navigation:
    group: "My Group"
```

## Config file configuration

Next up, let's look at how to customize the navigation menus using the config files.

- To customize the navigation menu, use the setting `navigation.order` in the `hyde.php` config.
- When customizing the navigation menu, you should use the [route key](core-concepts#route-keys) of the page.

- To customize the sidebar, use the setting `sidebar_order` in the `docs.php` config.
- When customizing the sidebar, can use the route key, or just the [page identifier](core-concepts#page-identifiers) of the page.

### `navigation.order` and `sidebar_order`

The `navigation.order` and `sidebar_order` settings allow you to customize the order of the pages in the navigation menus.

#### Basic syntax for changing the priorities

The cleanest way is to use the list-style syntax where each item will get the priority calculated according to its position in the list, plus an offset of `500`.
The offset is added to make it easier to place pages earlier in the list using front matter or with explicit priority settings.

```php
// filepath: config/hyde.php

'navigation' => [
    'order' => [
        'home', // Gets priority 500
        'about', // Gets priority 501
        'contact', // Gets priority 502
    ]
]
```

```php
// filepath: config/docs.php

'sidebar_order' => [
    'readme', // Gets priority 500
    'installation', // Gets priority 501
    'getting-started', // Gets priority 502
]
```

#### Explicit syntax for changing the priorities

You can also specify explicit priorities by adding a value to the array key:

```php
// filepath: config/hyde.php

'navigation' => [
    'order' => [
        'home' => 10, // Gets priority 10
        'about' => 15, // Gets priority 15
        'contact' => 20, // Gets priority 20
    ]
]
```

```php
// filepath: config/docs.php

'sidebar_order' => [
    'readme' => 10, // Gets priority 10
    'installation' => 15, // Gets priority 15
    'getting-started' => 20, // Gets priority 20
]
```

You can of course also combine these methods if you want:

```php
// filepath: Applicable to both
[
    'readme' => 10, // Gets priority 10
    'installation', // Gets priority 500
    'getting-started', // Gets priority 501
]
```

## Digging deeper into the internals

While not required to know, you may find it interesting to learn more about how the navigation is handled internally.
The best way to learn about this is to look at the source code, so here is a high-level overview with details on where to look in the source code.

The main navigation menu is the `NavigationMenu` class, and the documentation sidebar is the `DocumentationSidebar` class.
Both extend the same `BaseNavigationMenu` class:

```php
use Hyde\Framework\Features\Navigation\NavigationMenu;
use Hyde\Framework\Features\Navigation\DocumentationSidebar;
use \Hyde\Framework\Features\Navigation\BaseNavigationMenu;
```

Within the `BaseNavigationMenu` class, you will find the main logic for how the menus are generated,
while the child implementations contain the extra logic tailored for their specific use cases.

All the navigation menus store the menu items in their `$items` array containing instances of the `NavItem` class.

The `NavItem` class is a simple class that contains the label and URL of the menu item and is used to represent each item in the menu.
Dropdowns are represented by `DropdownNavItem` instances, which extend the `NavItem` class and contain an array of additional `NavItem` instances.

```php
use Hyde\Framework\Features\Navigation\NavItem;
use Hyde\Framework\Features\Navigation\DropdownNavItem;
```
