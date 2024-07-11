---
navigation:
    label: "Navigation Menus"
    priority: 26
---

# Navigation Menus

## Introduction

HydePHP offers an automatic navigation menu and documentation sidebar generation feature, designed to take the pain out of creating navigation menus.
While Hyde does its best to configure these menus automatically based on understanding your project files, you may want to customize them further.

There are two types of navigation menus in Hyde:

1. **Primary Navigation Menu**: The main navigation menu appearing on most pages of your site. Unique features include dropdowns for subdirectories, depending on configuration.
2. **Documentation Sidebar**: The sidebar on documentation pages with links to other documentation pages. Unique features include automatic grouping based on subdirectories.

This documentation will guide you through the customization process. To learn even more about sidebars, visit the [Documentation Pages](documentation-pages) documentation.

### Internal Structure Overview

Internally, both navigation menu types extend the same base class, and thus share core functionality. This means the configuration process is similar for both types, making the documentation applicable to both.

For a deeper understanding of the internal workings, refer to the [Digging Deeper](#digging-deeper-into-the-internals) section.

### Understanding Priorities

All navigation menu items have an internal priority value determining their order. Lower values place items higher in the menu. The default priority for pages is `999`, placing them last unless you specify a value. Some pages, like the `index` page, are by default configured with the lowest priority of `0`.

### Customization Options

Here's an overview of what you can customize in your navigation menus:

- Item labels: The text displayed in menu links
- Item priorities: Control the order of link appearance
- Item visibility: Choose to hide or show pages in the menu
- Item grouping: Group pages together in dropdowns or sidebar categories

### Customization Methods

Hyde provides multiple ways to customize navigation menus to suit your needs:

1. Front matter data in Markdown and Blade page files, applicable to all menu types
2. Configuration in the `hyde` config file for main navigation items
3. Configuration in the `docs` config file for documentation sidebar items

Keep in mind that front matter data overrides dynamically inferred or config-defined priorities. While useful for quick one-off changes on small sites, it can make reordering items later on more challenging as you can't see the entire structure at once.

Additionally, general options for the entire navigation menus are also available in the `hyde` and `docs` config files.

## Front Matter Configuration

Front matter options allow per-page customization of navigation menus. Here's a quick reference of available options:

```yaml
navigation:
    label: string  # The displayed text in the navigation item link
    priority: int  # The page's priority for ordering (lower means higher up/first)
    hidden: bool   # Whether the page should be hidden from the navigation menu
    group: string  # Set main menu dropdown or sidebar group key
```

You only need to specify the keys you want to customize.

### `label`

Customizes the text appearing in the navigation menu link for the page. If not set anywhere else, Hyde will search for a title in the page content or generate one from the filename.

```yaml
navigation:
    label: "My Custom Label"
```

### `priority`

Controls the order in which the page appears in the navigation menu.

```yaml
navigation:
    priority: 10
```

### `hidden`

Determines if the page appears in the navigation menu.

```yaml
navigation:
    hidden: true
```

**Tip:** You can also use `visible: false` to achieve the same effect.

### `group`

For the primary navigation menu, this groups pages together in dropdowns. For the sidebar, it groups pages under a common heading.

```yaml
navigation:
    group: "My Group"
```

**Note:** Sidebar group keys are normalized, so `My Group` and `my-group` are equivalent.

## Config File Configuration

Let's explore how to customize navigation menus using configuration files:

- For the main navigation menu, use `navigation` setting in the `hyde.php` config file.
- For the sidebar, use `sidebar` setting in the `docs.php` config file.

When customizing the main navigation menu, use the [route key](core-concepts#route-keys) of the page. For the sidebar, you can use either the route key or the [page identifier](core-concepts#page-identifiers).

### Changing Priorities

The `navigation.order` and `sidebar.order` settings allow you to customize the order of pages in the navigation menus.

#### Basic Priority Syntax

A nice and simple way to define the order of pages, is to add their route keys as a simple list array. We'll then match that array order.

It may be useful to know that we internally will assign a priority calculated according to its position in the list, plus an offset of `500`. The offset is added to make it easier to place pages earlier in the list using front matter or with explicit priority settings.

```php
// filepath: config/hyde.php
'navigation' => [
    'order' => [
        'home', // Priority: 500
        'about', // Priority: 501
        'contact', // Priority: 502
    ]
]
```

```php
// filepath: config/docs.php
'sidebar' => [
    'order' => [
        'readme', // Priority: 500
        'installation', // Priority: 501
        'getting-started', // Priority: 502
    ]
]
```

#### Explicit Priority Syntax

You can also specify explicit priorities by adding a value to the array keys. We'll then use these exact values as the priorities.

```php
// filepath: config/hyde.php
'navigation' => [
    'order' => [
        'home' => 10,
        'about' => 15,
        'contact' => 20,
    ]
]
```

```php
// filepath: config/docs.php
'sidebar' => [
    'order' => [
        'readme' => 10,
        'installation' => 15,
        'getting-started' => 20,
    ]
]
```

You could also combine these methods if desired:

```php
// filepath: Applicable to both
[
    'readme' => 10, // Priority: 10
    'installation', // Priority: 500
    'getting-started', // Priority: 501
]
```

### Changing Menu Item Labels

Hyde makes a few attempts to find a suitable label for the navigation menu items to automatically create helpful titles.

From the Hyde config you can override the label of navigation links using the by mapping the route key to the desired title.
This is not yet supported for the sidebar, but will be in the future.

```php
// filepath: config/hyde.php
'navigation' => [
    'labels' => [
        'index' => 'Start',
        'docs/index' => 'Documentation',
    ]
]
```

### Excluding Items (Blacklist)

To prevent specific pages from showing up in the main navigation menu, simply add their route keys to the blacklist:

```php
// filepath: config/hyde.php
'navigation' => [
    'exclude' => [
        '404'
    ]
]
```

### Adding Custom Navigation Menu Links

You can easily add custom navigation menu links similar to how we add Authors. Simply add a `NavigationItem` model to the `navigation.custom` array.

When linking to an external site, you should use the `NavigationItem::create()` method facade. The first two arguments are the
destination and label, both required. The third argument is the priority, which is optional, and defaults to `500`.

```php
// filepath: config/hyde.php
use Hyde\Framework\Features\Navigation\NavigationItem;

'navigation' => [
    'custom' => [
        NavigationItem::create('https://github.com/hydephp/hyde', 'GitHub', 200),
    ]
]
```

Simplified, this will then be rendered as follows:

```html
<a href="https://github.com/hydephp/hyde">GitHub</a>
```

### Configure subdirectory handling

Within the Hyde config you can configure how subdirectories should be displayed in the menu.

```php
// filepath: config/hyde.php
'navigation' => [
    'subdirectory_display' => 'dropdown'
]
```

Dropdown means that pages in subdirectories will be displayed in a dropdown menu,
while `flat` means that pages in subdirectories will be displayed as individual items in the menu.
Hidden means that pages in subdirectories will not be displayed in the menu at all.

### Automatic menu groups

HydePHP has a neat feature to automatically place pages in dropdowns based on subdirectories.

#### Automatic navigation menu dropdowns

For pages that can be in the main site menu, this feature needs to be enabled in the `hyde.php` config file.

```php
// filepath config/hyde.php

'navigation' => [
    'subdirectory_display' => 'dropdown',
],
```

Now if you create a page called `_pages/about/contact.md` it will automatically be placed in a dropdown called "About".

#### Automatic documentation sidebar grouping

This feature works similarly to the automatic navigation menu dropdowns, but instead places the sidebar items in named groups.
This feature is enabled by default, so you only need to place your pages in subdirectories to have them grouped.

For example: `_docs/getting-started/installation.md` will be placed in a group called "Getting Started".

>info Tip: When using subdirectory-based dropdowns, you can set their priority using the directory name as the array key.

#### Dropdown menu notes

Here are some things to keep in mind when using dropdown menus, regardless of the configuration:
- Dropdowns take priority over standard items. So if you have a dropdown with the key `about` and a page with the key `about`, the dropdown will be created, and the page won't be in the menu.
    - For example: With this file structure: `_pages/foo.md`, `_pages/foo/bar.md`, `_pages/foo/baz.md`, the link to `foo` will be lost.

## Numerical Prefix Navigation Ordering

HydePHP v2 introduces a new feature that allows navigation items to be ordered based on a numerical prefix in the filename.
This is a great way to control the ordering of pages in both the primary navigation menu and the documentation sidebar,
as your file structure will match the order of the pages in the navigation menus.

For example, the following will have the same order in the navigation menu as in a file explorer:

```shell
_pages/
  01-home.md # Gets priority 1, putting it first (will be saved to _site/index.html)
  02-about.md # Gets priority 2, putting it second (will be saved to _site/about.html)
  03-contact.md # Gets priority 3, putting it third (will be saved to _site/contact.html)
```

Hyde will then parse the number from the filename and use it as the priority for the page in the navigation menus.

### Keep in mind

Here are some things to keep in mind, especially if you mix numerical prefix ordering with other ordering methods:

1. The numerical prefix will still be part of the page identifier, but it will be stripped from the route key.
    - For example: `_pages/01-home.md` will have the route key `home` and the page identifier `01-home`.
2. You can delimit the numerical prefix with either a dash or an underscore.
    - For example: `_pages/01-home.md` and `_pages/01_home.md` are both valid.
3. The leading zeroes are optional, so `_pages/1-home.md` is also valid.

### Using numerical prefix ordering in subdirectories

The numerical prefix ordering feature works great when using the automatic subdirectory-based grouping for navigation menu dropdowns and documentation sidebar categories.

This integration has two main features to consider:
1. You can use numerical prefixes in subdirectories to control the order of dropdowns.
2. The ordering within a subdirectory works independently of its siblings, so you can start from one in each subdirectory.

Here is an example structure of how you may want to organize a documentation site:

```shell
_docs/
  01-getting-started/
    01-installation.md
    02-requirements.md
    03-configuration.md
  02-usage/
    01-quick-start.md
    02-advanced-usage.md
  03-features/
    01-feature-1.md
    02-feature-2.md
```

### Customization

You can disable this feature by setting the `numerical_page_ordering` setting to `false` in the `hyde.php` config file. Hyde will then no longer extract the priority and will no longer strip the prefix from the route key.

```php
// filepath config/hyde.php

'numerical_page_ordering' => false,
```

While it's not recommended, as you lose out on the convenience of the automatic ordering, any front matter priority settings will override the numerical prefix ordering if you for some reason need to.

## Digging Deeper into the internals

While not required to know, you may find it interesting to learn more about how the navigation is handled internally. Here is a high level overview,
but you can find more detailed information in the [Navigation API](navigation-api) documentation.

The main navigation menu is the `MainNavigationMenu` class, and the documentation sidebar is the `DocumentationSidebar` class. Both extend the same base `NavigationMenu` class.

```php
use Hyde\Framework\Features\Navigation\MainNavigationMenu;
use Hyde\Framework\Features\Navigation\DocumentationSidebar;
use Hyde\Framework\Features\Navigation\NavigationMenu;
```

Within the base `NavigationMenu` class, you will find the main logic for how the menus are generated,
while the child implementations contain the extra logic tailored for their specific use cases.

All the navigation menus store the menu items in their `$items` collection containing instances of the `NavigationItem` class.

The `NavigationItem` class is a simple class that contains the label and URL of the menu item and is used to represent each item in the menu.
Dropdowns are represented by `NavigationGroup` instances, which extend the `NavigationMenu` class and contain a collection of additional `NavigationItem` instances.

```php
use Hyde\Framework\Features\Navigation\NavigationItem;
use Hyde\Framework\Features\Navigation\NavigationGroup;
```

## The Navigation API

If you want to interact with the site navigation programmatically, or if you want to create complex custom menus, you can do so through the new Navigation API.
For most cases you don't need this, as Hyde creates the navigation for you. But it can be useful for advanced users and package developers.

The Navigation API consists of a set of PHP classes, allowing you to fluently interact with the navigation menus. You can learn more about the API in the [Navigation API](navigation-api) documentation.
