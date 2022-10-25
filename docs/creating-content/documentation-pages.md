---
label: Documentation Pages
priority: 12
---

# Creating Documentation Pages

## Introduction to Hyde Documentation Pages

Hyde makes it easier than ever to create documentation sites.
By the way, this documentation site is of course made with the Hyde Documentation module!

In short, all you need to do is put standard Markdown files in the `_docs/` directory and Hyde will do the rest.

What is "the rest", you may ask? Well, for starters:

- Hyde compiles your Markdown content into a beautiful static HTML page based on [the Lagrafo frontend](https://github.com/caendesilva/lagrafo)
- A sidebar (which is responsive) is automatically created based on your Markdown files
  - If you have an `index.md` in the `_docs/` directory, it will be used as the sidebar header
  - You can even [customize the order and labels](#sidebar-page-order) of sidebar items
- If you have an `index.md` in the `_docs/` directory,
  a link to it will be added to the site navigation menu named "Docs".
- If you have a Torchlight API token in your .env file, Hyde will even automatically enable Syntax Highlighting for you.
  See more about this in the [extensions page](extensions#torchlight).

### Best Practices and Hyde Expectations

Since Hyde does a lot of things automatically, there are some things you may need
to keep in mind when creating blog posts so that you don't get unexpected results.

#### Filenames

- Hyde Documentation pages are files are stored in the `_docs` directory
- The filename is used as the filename for the compiled HTML
- Filenames should use `kebab-case-slug` format, followed by the appropriate extension
- Files prefixed with `_underscores` are ignored by Hyde
- You should always have an `index.md` file in the `_docs/` directory
- Your page will be stored in `_site/docs/<slug>.html` unless you [change it in the config](#output-directory)

### Advanced usage and customization

Like most of HydePHP, the Hyde Documentation module is highly customizable. Much of the frontend is composed using Blade templates and components, which you can customize to your heart's content.
Since there are so many components, it's hard to list them all here in the documentation, so I encourage you to check out the [source code](https://github.com/hydephp/framework/tree/master/resources/views/components/docs) to see how it's all put together and find the customizations you are looking for.


## Creating Documentation Pages
You can create a Documentation page by adding a file to the `_docs` directory where the filename ends in `.md`.

You can also scaffold one quickly by using the [HydeCLI](console-commands).

```bash
php hyde make:page "Page Title" --type="docs"
```

This will create the following file saved as `_docs/page-title.md`

```markdown
# Page Title
```

### Front Matter is optional

You don't need to use [front matter](blog-posts#supported-front-matter-properties) to create a documentation page.

However, Hyde still supports front matter here as it allows you to quickly override the default values.

Here is a quick reference, however, you should take a look at the [dynamic content](#dynamic-content-generation) section to learn more.

```yaml
---
title: "Page Title"
navigation:
  label: "Sidebar Label"
  hidden: true
  priority: 5
---
```


## Dynamic content generation

Hyde makes documentation pages easy to create by automatically generating dynamic content such as the sidebar and page title.
If you are not happy with the results you can customize them in the config or with front matter.

Before we look at how to override things, here is an overview of the relevant content Hyde generates,
and where the data is from as well as where it can be overridden.


| Property                        | Description                                            | Source                              | Override in          |
|---------------------------------|--------------------------------------------------------|-------------------------------------|----------------------|
| `title` (string)                | The title of the page used in the HTML `<title>` tag   | The first H1 heading (`# Foo`)      | Front matter         |
| `navigation.label` (string)     | The label for the page shown in the sidebar            | The page identifier/basename (slug) | Front matter, config |
| `navigation.hidden` (boolean)   | Hides the page from the sidebar                        | _null_                              | Front matter, config |
| `navigation.priority` (integer) | The priority of the page used for ordering the sidebar | Defaults to 999                     | Front matter, config |


## Sidebar

The sidebar is automatically generated from the files in the `_docs` directory. You will probably want to change the order
of these items. You can do this in two ways, either in the config or with front matter using the navigation array.

### Table of contents

Hyde automatically generates a table of contents for the page and adds it to the sidebar.

The behaviour of this can be changed in the configuration file.
See [the customization page](customization#navigation-menu--sidebar) for more details.


### Sidebar ordering

The sidebar is sorted/ordered by the `priority` property. The higher the priority the further down in the sidebar it will be.
The default priority is 999. You can override the priority using the following front matter:

```yaml
navigation:
  priority: 5
```

You can also change the order in the Docs configuration file.
See [the chapter in the customization page](customization#navigation-menu--sidebar) for more details. <br>
 _I personally think the config route is easier as it gives an instant overview, however the first way is nice as well._

### Sidebar labels

The sidebar items are labeled with the `label` property. The default label is the filename of the file.
You can change it with the following front matter:

```yaml
navigation:
  label: "My Custom Sidebar Label"
```

### Sidebar grouping

Sidebar grouping allows you to group items in the sidebar into categories. This is useful for creating a sidebar with a lot of items.
The Hyde docs for instance use this.

The feature is enabled automatically when one or more of your documentation pages have the navigation.group property set
in the front matter. This will then switch to a slightly more compact sidebar layout with pages sorted into categories.
Any pages without the group front matter will get put in the "Other" group.

#### Using Front Matter

To enable sidebar grouping, you can add the following front matter to your documentation pages:

```yaml
navigation:
  group: "Getting Started"
```

#### Using subdirectories

Since [v0.52.0-beta](https://github.com/hydephp/develop/releases/tag/v0.52.0-beta), you can also automatically group your documentation pages by placing source files in sub-directories.

For example, putting a Markdown file in `_docs/getting-started/`, is equivalent to adding the same front matter in the step above.

> Note that the file will still be compiled to the `_site/docs/` directory like it would be if you didn't use the sub-directories.


### Hiding items

You can hide items from the sidebar by adding the `hidden` property to the front matter:

```yaml
navigation:
  hidden: true
```

This can be useful to create redirects or other items that should not be shown in the sidebar.

> The index page is by default not shown as a sidebar item, but instead is linked in the sidebar header. <br>

## Customization

Please see the [customization page](customization) for in-depth information on how to customize Hyde,
including the documentation pages. Here is a high level overview for quick reference though.

### Output directory

If you want to store the compiled documentation pages in a different directory than the default 'docs' directory,
for example to specify a version like the Hyde docs does, you can specify the output directory in the Docs configuration file.

```php
'output_directory' => 'docs' // default
'output_directory' => 'docs/master' // What the Hyde docs use
```

### Automatic navigation menu

By default, a link to the documentation page is added to the navigation menu when an index.md file is found in the `_docs` directory. Please see the [the customization page](customization#navigation-menu--sidebar) for more information.

### Sidebar header name

By default, the site title shown in the sidebar header is generated from the configured site name suffixed with "docs".
You can change this in the Docs configuration file.

```php
'title' => 'API Documentation',
```

> Tip: The header will link to the docs/index page, if it exists.

### Sidebar page order

To quickly arrange the order of items in the sidebar, you can reorder the page slugs in the list and the links will be sorted in that order.
Link items without an entry here will have fall back to the default priority of 999, putting them last.

```php
'sidebar_order' => [
    'readme',
    'installation',
    'getting-started',
]
```

See [the chapter in the customization page](customization#navigation-menu--sidebar) for more details. <br>


### Table of contents settings

In the `config/docs.php` file you can configure the behavior, content,
and the look and feel of the sidebar table of contents.
You can also disable the feature completely.

```php
'table_of_contents' => [
	'enabled' => true,
	'min_heading_level' => 2,
	'max_heading_level' => 4,
	'smooth_page_scrolling' => true,
],
```

### Search feature

#### Introduction

The HydeSearch plugin adds a search feature to documentation pages.

The search feature is enabled by default.
You can disable it by removing the `documentationSearch` from the Hyde `Features` config array.

#### Using the search

The search works by generating a JSON search index which the JavaScript plugin loads asynchronously.

Two types of search methods are added, one is a full page search screen that will saved to `docs/search.html`.
<small><blockquote>The full page can be disabled by setting `create_search_page` to `false` in the `docs` config.</blockquote></small>
> Note that the fullpage search is used as a fallback for browsers that don't support the `<dialogue>` element, and may lead to 404 errors if disabled.

The second method is a button added to the documentation pages, similar to how Algolia DocSearch works.
Opening it will open a dialog modal with an integrated search screen.
You can also open the dialog using the keyboard shortcut `/`.

#### Hiding pages from indexing

If you have a large page, like a changelog, on your documentation sites you may want to hide it from the search index. You can do this by adding the page slug to the `exclude_from_search` array in the `docs` config, similar to how navigation menu items are hidden.

```php
// filepath: config/docs.php

'exclude_from_search' => [
  'changelog',
]
```

The page will still be accessible via the URL, but will be added to the search index JSON file. Don't include the file extension.


### Automatic "Edit Page" button

#### Introduction

Hyde can automatically add links to documentation pages that takes the user
to a GitHub page (or similar) to edit the page. This makes it great for open-source projects
looking to allow others to contribute to the documentation in a quick and easy manner.

The feature is automatically enabled when you specify a base URL in the Docs configuration file.
Hyde expects this to be a GitHub path, but it will probably work with other methods as well,
if not, please send a PR and/or create an issue on the [GitHub repository](https://github.com/hydephp/framework)!

#### Example configuration

Let's take a practical example for how HydePHP.com uses this feature.

```php
// Filepath: config/docs.php

'source_file_location_base' => 'https://github.com/hydephp/docs/blob/master/',
```

#### Changing the button text

Changing the label is easy, just change the following config setting:

```php
// Filepath: config/docs.php
'edit_source_link_text' => 'Edit Source on GitHub',
```

#### Changing the position

By default the button will be shown in both the documentation page footer.
You can change this by setting the following config setting to `'header'`, `'footer'`, or `'both'`

```php
// Filepath: config/docs.php
'edit_source_link_position' => 'header',
```

#### Adding a button icon

This is not included out of the box, but is easy to add with some CSS!
Just target the `.edit-page-link` class.

```css
// filepath e.g. app.css
.edit-page-link::before {content: "✏ "}
```

#### Changing the Blade view

You can also publish the `edit-source-button.blade.php` view and change it to your liking.