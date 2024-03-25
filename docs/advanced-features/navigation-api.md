---
navigation:
    label: "Navigation API"
---

# Navigation API

>warning This article covers advanced information that is only relevant if you want to create custom navigation menus. Instead, you may want to read the [Navigation](navigation) article for a general overview.

## Abstract

This article describes the Navigation API introduced in HydePHP v2. Both the main navigation menu and the documentation sidebar bundled with HydePHP are built with this API.
However, if you are interested in creating your own custom navigation menus, you can also utilize this API - and this article will show you how.

This article is intended for advanced users, as most users will not need to create custom navigation menus.
For this reason, the documentation is very code-driven due to the technical nature of the API.

## Overview

The Navigation API consists of a set of classes within the `Hyde\Framework\Features\Navigation` namespace.

Together, these form an object-oriented way to create and interact with navigation menus and their items.

In short, a navigation menu is an instance of the `NavigationMenu` class. Each menu contains a collection of `NavigationItem` or `NavigationGroup` classes.
The former represents a single item in the menu, while the latter represents a group of items.

### Visualisation

Here is a visual representation of the structure of a navigation menu:

```php
namespace Hyde\Framework\Features\Navigation;

class NavigationMenu {
    protected Collection $items = [
        new NavigationItem(destination: 'index.html', label: 'Home'),
        new NavigationItem(destination: 'posts.html', label: 'Blog'),
        new NavigationGroup(label: 'About', items: [
            new NavigationItem(destination: 'about.html', label: 'About Us'),
            new NavigationItem(destination: 'team.html', label: 'Our Team'),
        ]),
    ];
}
```

### Built-in Menus

HydePHP comes with two built-in menus: the main navigation menu and the documentation sidebar.

These are bound into the service container as singletons and can be accessed through dependency injection.

```php
use Hyde\Framework\Features\Navigation\MainNavigationMenu;
use Hyde\Framework\Features\Navigation\DocumentationSidebar;

/** @var \Hyde\Framework\Features\Navigation\MainNavigationMenu $menu */
$menu = app('navigation.main')

/** @var DocumentationSidebar $sidebar */
$sidebar= app('navigation.sidebar')
```

You can also get them through the static `get` helpers on the menu classes themselves.

```php
use Hyde\Framework\Features\Navigation\MainNavigationMenu;
use Hyde\Framework\Features\Navigation\DocumentationSidebar;

$menu = MainNavigationMenu::get();
$sidebar = DocumentationSidebar::get();
```

>info Developer tip: The menus are only generated *after* the Hyde Kernel is booted. If you are getting BindingResolutionExceptions, ensure that you are not trying to access the menus too early in the application lifecycle. (Consider using the `booted` event.) 
