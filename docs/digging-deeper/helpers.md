---
navigation:
    priority: 35
    label: "Helpers and Utilities"
---

# Helpers and Utilities

## Introduction

HydePHP comes with a few helper classes and utilities to make your life easier. This page will cover some of the most important ones.

### File-based Collections

Hyde provides `DataCollections`, a subset of [Laravel Collections](https://laravel.com/docs/10.x/collections) giving you a similar developer experience to working with Eloquent Collections. However, instead of accessing a database,
it's all entirely file-based using static data files such as Markdown, Yaml, and JSON files which get parsed into objects that you can easily work with.

See the [File-based Collections](collections) documentation for more information.

### File Includes

The Includes facade provides a simple way to access partials in the includes directory.

If the file does not exist, the method will return `null`.
You can also supply a default value as the second argument.
Both Markdown and Blade includes will be rendered to HTML.

#### Using Includes

Includes are stored in the `resources/includes` directory. You can access them using the `Includes` facade.

```php
use Hyde\Support\Includes;

Includes::get('example.md');
Includes::get('example.md', 'Default content');
```

#### Markdown Includes

Gets the rendered Markdown of a partial file in the includes directory.
When using this method, supplying the file extension is optional.

```php
use Hyde\Support\Includes;

Includes::markdown('footer');
Includes::markdown('footer.md');

// With default value if the file does not exist
Includes::markdown('footer', 'Default content');
```

#### Blade Includes

Gets the rendered Blade of a partial file in the includes directory.
When using this method, supplying the file extension is optional.

```php
use Hyde\Support\Includes;

Includes::blade('banner');
Includes::blade('banner.blade.php');

// With default value if the file does not exist
Includes::blade('banner', 'Default content');
```

#### Directory Structure Example

Here is an example of the directory structure for includes:

```tree
resources/
|-- includes/
|   |-- example.md
|   |-- footer.md
|   |-- banner.blade.php
```

