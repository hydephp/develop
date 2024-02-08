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

- **Primary Navigation Menu**: This is the main navigation menu that appears on most pages your site.
- **Documentation Sidebar**: This is a sidebar that appears on documentation pages and contains links to other documentation pages.

HydePHP automatically generates all of these menus for you based on the content in your project,
and does its best to automatically configure them in the way that you most likely want them to be.

Of course, this won't always be perfect, so thankfully Hyde makes it a breeze to customize these menus to your liking.
Keep on reading to learn how! To learn even more about the sidebars, visit the [Documentation Pages](documentation-pages) documentation.

## Quick primer on the internals

It may be beneficial to understand the internal workings of the navigation menus in order to take full advantage of the options.

In short, both navigation menu types extend the same class (meaning they share the same base code), this means that the way
they are configured are very similar, making the documentation here applicable to both types of menus.

See the [Digging Deeper](#digging-deeper-into-the-internals) section of this page if you want the full scoop on the internals!
