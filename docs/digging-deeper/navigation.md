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

If you're not happy with these, it's easy to override navigation link labels by mapping the route key to the desired title in the Hyde config:

```php
// filepath: config/hyde.php
'navigation' => [
    'labels' => [
        'index' => 'Start',
        'docs/index' => 'Documentation',
    ]
]
```

**Note:** This feature is not yet supported for the sidebar.

### Excluding Items (Blacklist)

When you have a lot of pages, it may be useful to prevent links from being added to the main navigation menu.

To exclude items from being added, simply add the page's route key to the navigation blacklist in the Hyde config:

```php
// filepath: config/hyde.php
'navigation' => [
    'exclude' => [
        '404'
    ]
]
```

### Adding Custom Navigation Menu Links

You can easily add custom navigation menu links in the Hyde config:

```php
// filepath: config/hyde.php
use Hyde\Facades\Navigation;

'navigation' => [
    'custom' => [
        Navigation::item(
            destination: 'https://github.com/hydephp/hyde', // Required
            label: 'GitHub', // Optional, defaults to the destination value
            priority: 200 // Optional, defaults to 500
        ),
    ]
]
```

**Tip:** While named arguments are used in the example for clarity, they are not required.


### Configure Subdirectory Display

You can configure how subdirectories should be displayed in the menu:

```php
// filepath: config/hyde.php
'navigation' => [
    'subdirectory_display' => 'dropdown'
]
```

**Supported Options:**
- `dropdown`: Pages in subdirectories are displayed in a dropdown menu
- `hidden`: Pages in subdirectories are not displayed at all the menus
- `flat`: Pages in subdirectories are displayed as individual items

### Automatic Menu Groups

A handy feature HydePHP has is that it can automatically place pages in dropdowns based on subdirectory structures.

#### Automatic Navigation Menu Dropdowns

Enable this feature in the `hyde.php` config file by setting the `subdirectory_display` key to `dropdown`.

```php
// filepath: config/hyde.php
'navigation' => [
    'subdirectory_display' => 'dropdown',
],
```

Now if you create a page called `_pages/about/contact.md` it will automatically be placed in a dropdown called "About".

#### Automatic Documentation Sidebar Grouping

This feature is always enabled for documentation pages. Simply place your pages in subdirectories to have them grouped in the sidebar.

For example: `_docs/getting-started/installation.md` will be placed in a group called "Getting Started".

>info Tip: When using subdirectory-based dropdowns, you can set their priority using the directory name as the array key.

#### Dropdown Menu Notes

- Dropdowns take priority over standard items. If you have a dropdown with the key `about` and a page with the key `about`, the dropdown will be created, and the page won't be in the menu.
- Example: With this file structure: `_pages/foo.md`, `_pages/foo/bar.md`, `_pages/foo/baz.md`, the link to `foo` will be lost, so please keep this in mind when using this feature.

## Numerical Prefix Navigation Ordering

HydePHP v2 introduces navigation item ordering based on numerical prefixes in filenames. This feature works for both the primary navigation menu and the documentation sidebar.

This has the great benefit of matching the navigation menu layout with the file structure view, and works especially great with subdirectory-based navigation grouping.

```shell
_pages/
  01-home.md     # Priority: 1 (saved to _site/index.html)
  02-about.md    # Priority: 2 (saved to _site/about.html)
  03-contact.md  # Priority: 3 (saved to _site/contact.html)
```

As you can seem Hyde parses the number from the filename and uses it as the priority for the page in navigation menus, while stripping the prefix from the route key.

### Important Notes

1. The numerical prefix remains part of the page identifier but is stripped from the route key.
   For example: `_pages/01-home.md` has route key `home` and page identifier `01-home`.
2. You can delimit the numerical prefix with either a dash or an underscore.
   For example: Both `_pages/01-home.md` and `_pages/01_home.md` are valid.
3. Leading zeros are optional. `_pages/1-home.md` is equally valid.

### Using Numerical Prefix Ordering in Subdirectories

This feature integrates well with automatic subdirectory-based navigation grouping. Here are two useful tips:

1. You can use numerical prefixes in subdirectories to control the dropdown/sidebar order.
2. The numbering within a subdirectory works independently of its siblings, so you can start from one in each subdirectory.

Here is an example structure of how could organize a documentation site:

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

If you're not interested in using numerical prefix ordering, you can disable it in the Hyde config file. Hyde will then no longer extract the priority and will no longer strip the prefix from the route key.

```php
// filepath: config/hyde.php
'numerical_page_ordering' => false,
```


## Digging Deeper into the Internals

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

For advanced users and package developers, Hyde offers a Navigation API for programmatic interaction with site navigation. This API consists of a set of PHP classes allowing fluent interaction with navigation menus.

For more detailed information about the API, refer to the [Navigation API](navigation-api) documentation.
