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

## Configuration Methods

Hyde provides multiple ways to customize navigation menus to suit your needs:

1. Front matter data in Markdown and Blade page files, applicable to all menu types
2. Configuration in the `hyde.php` or `hyde.yml` config file for main navigation items
3. Configuration in the `docs.php` config file for documentation sidebar items

The new builder pattern in the configuration allows for a more fluent and IDE-friendly way to customize navigation. However, the old array-based method still works, as the navigation builder is an `ArrayObject` and can be used as an array.

### Using the Navigation Builder

To use the new builder pattern, you can configure your navigation in the `hyde.php` file like this:

```php
use Hyde\Facades\Navigation;

return [
    // ...

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
            Navigation::item('https://github.com/hydephp/hyde', 'GitHub', 200),
        ])
        ->setSubdirectoryDisplayMode('hidden'),
];
```

### Using YAML Configuration

You can also set the navigation configuration in the `hyde.yml` file. The structure remains the same as the array-based method:

```yaml
hyde:
  navigation:
    order:
      index: 0
      posts: 10
      docs/index: 100
    labels:
      index: Home
      docs/index: Docs
    exclude:
      - 404
    custom:
      - destination: 'https://github.com/hydephp/hyde'
        label: 'GitHub'
        priority: 200
    subdirectory_display: hidden
```

## Customization Options

Here's an overview of what you can customize in your navigation menus:

- Item labels: The text displayed in menu links
- Item priorities: Control the order of link appearance
- Item visibility: Choose to hide or show pages in the menu
- Item grouping: Group pages together in dropdowns or sidebar categories

### Setting Page Priorities

Use the `setPagePriorities` method to define the order of pages in the navigation menu:

```php
->setPagePriorities([
    'index' => 0,
    'posts' => 10,
    'docs/index' => 100,
])
```

### Changing Menu Item Labels

Override navigation link labels using the `setPageLabels` method:

```php
->setPageLabels([
    'index' => 'Home',
    'docs/index' => 'Documentation',
])
```

### Excluding Items

Prevent links from being added to the main navigation menu using the `excludePages` method:

```php
->excludePages([
    '404',
])
```

### Adding Custom Navigation Menu Links

Add custom navigation menu links using the `addNavigationItems` method:

```php
->addNavigationItems([
    Navigation::item('https://github.com/hydephp/hyde', 'GitHub', 200),
])
```

### Configure Subdirectory Display

Set how subdirectories should be displayed in the menu using the `setSubdirectoryDisplayMode` method:

```php
->setSubdirectoryDisplayMode('dropdown')
```

Supported options are 'dropdown', 'hidden', and 'flat'.

## Front Matter Configuration

Front matter options allow per-page customization of navigation menus. These options remain unchanged:

```yaml
navigation:
  label: string  # The displayed text in the navigation item link
  priority: int  # The page's priority for ordering (lower means higher up/first)
  hidden: bool   # Whether the page should be hidden from the navigation menu
  group: string  # Set main menu dropdown or sidebar group key
```

## Numerical Prefix Navigation Ordering

The numerical prefix navigation ordering feature remains unchanged. You can still use numerical prefixes in filenames to control the order of navigation items.

To disable this feature, you can use the following configuration:

```php
return [
    // ...
    'numerical_page_ordering' => false,
];
```

## Conclusion

The new builder pattern provides a more fluent and IDE-friendly way to configure navigation in HydePHP. However, the old array-based method and YAML configuration are still supported, giving you flexibility in how you choose to customize your site's navigation.