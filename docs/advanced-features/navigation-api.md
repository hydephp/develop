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
We'll also be mixing in some practical examples of Blade and PHP code to illustrate how you can use the API in your own projects.

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

HydePHP comes with two built-in menus: the main navigation menu and the documentation sidebar, both represented as child classes of the `NavigationMenu` class.
They are bound into the service container as singletons and can be accessed through dependency injection.

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

## Creating Custom Menus

### Introduction Overview

When creating a custom menu, there are two ways to go about it:

1. You can construct a NavigationMenu instance directly and add items to it. This works great for smaller menus that don't need any special logic, for example a footer menu or social media links.
2. You can create a custom class that extends the NavigationMenu class. This is useful for larger menus that require more complex logic, or that you want to reuse in multiple places in your application or in a package.

In both cases, the underlying API is the same, and you can use the helper methods and features provided by the APIs.

In this section, you will first see some high level overviews of how the API can be used, then we'll dive in deeper and take a look at each class and method in detail.

### High Level Example

To illustrate how you can create a custom menu, let's make something useful: A footer menu with social media links.

#### Step 1: Create the Menu

To create our menu, we start by constructing a new NavigationMenu instance.
We can then add our social media links as NavigationItem instances to the menu.

```php
$menu = new NavigationMenu();

$menu->add([
    // The first parameter is the URL, and the second is the label.
    NavigationItem::create('https://twitter.com/hydephp', 'Twitter'),
    NavigationItem::create('https://github.com/hydephp', 'GitHub'),
    NavigationItem::create('https://hydephp.com', 'Website'),
]);
```

#### Step 2: Display the Menu

We can now iterate over the menu items to render them in our footer.

```blade
<footer>
    <ul>
        @foreach ($menu->getItems() as $item)
            <li><a href="{{ $item->getLink() }}">{{ $item->getLabel() }}</a></li>
        @endforeach
    </ul>
</footer>
```

This will result in the following HTML:

```html
<footer>
    <ul>
        <li><a href="https://twitter.com/hydephp">Twitter</a></li>
        <li><a href="https://github.com/hydephp">GitHub</a></li>
        <li><a href="https://hydephp.com">Website</a></li>
    </ul>
</footer>
```

#### Next Steps & Tips

Of course, this is an incredibly simplistic example to illustrate the core concepts.
Where the Navigation API really shines is in more complex scenarios where you want to utilize things like
HydePHP Routes to resolve dynamic relative urls, and to use features like groups, priorities, and active states helpers to check if a menu item is the current page being viewed.

The object-oriented nature of the API also makes this perfect for package developers wanting to create dynamic and reusable navigation menus that even can be further extended and customized by the end user.

Here are some general tips to keep in mind when working with the Navigation API:
- You can use the `add` method to add single items or arrays of items. You can also pass an array of items directly to the menu constructor.
- The navigation menu items is stored in a Laravel Collection, and is type safe to support both `NavigationItem` and `NavigationGroup` instances. 
- You can also construct NavigationItem instances directly, but the `create` method is a convenient shorthand, and can fill in data from routes, if you use them.
- It's also possible to set an item's priority as the third parameter, but here we don't need it, as they default to the order they are added.

## Class Reference

Below is a reference of the classes and methods available in the Navigation API.

### NavigationMenu

The `NavigationMenu` class represents a navigation menu. It contains a collection of items, which can be either `NavigationItem` or `NavigationGroup` instances.

### NavigationItem

The `NavigationItem` class represents a single item in a navigation menu. It contains information such as the destination link or route, a label, and priority for ordering in the menu.

### NavigationGroup

The `NavigationGroup` class represents a group of items in a navigation menu. It contains a label, priority, and a collection of navigation items.
This class is often used to create submenus or dropdowns in a navigation menu.
