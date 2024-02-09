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

## Quick primer on the internals

It may be beneficial to understand the internal workings of the navigation menus to take full advantage of the options.

In short, both navigation menu types extend the same class (meaning they share the same base code), this means that the way
they are configured are very similar, making the documentation here applicable to both types of menus.

See the [Digging Deeper](#digging-deeper-into-the-internals) section of this page if you want the full scoop on the internals!

## What to customize?

Here is a quick overview of what you might want to customize in your navigation menus:

- Navigation menu item labels - the text that appears in the menu links
- Navigation menu item priority - control the order in which the links appear
- Navigation menu item visibility - control if pages may show up in the menus
- Navigation menu item grouping - group pages together in dropdowns

## How and where to customize?

Hyde provides a few different ways to customize the navigation menus, depending on what you prefer.

To customize how a page is represented in navigation, you can either set the `navigation` front matter data in the page's markdown file,
or configure it in the config file. Main navigation items are in the `hyde` config file, while documentation sidebar items are in the `docs` config file.
General options for the entire navigation menus are also available in the `hyde` and `docs` config files. 

Now that you know the basics, let's dive into the details of how to customize the navigation menus!

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
