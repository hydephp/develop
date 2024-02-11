---
navigation:
    priority: 35
    label: "Helpers and Utilities"
---

# Helpers and Utilities

## Introduction

HydePHP comes with a few helper classes and utilities to make your life easier. This page will cover some of the most important ones.
Note that these helpers targets those who write custom code and Blade templates, and that you are expected to have a basic understanding of programming and PHP.


## File-based Collections

Hyde provides `DataCollections`, a subset of [Laravel Collections](https://laravel.com/docs/10.x/collections) giving you a similar developer experience to working with Eloquent Collections. However, instead of accessing a database,
it's all entirely file-based using static data files such as Markdown, Yaml, and JSON files which get parsed into objects that you can easily work with.

```php
use Hyde\Support\DataCollections;

// Gets all Markdown files in resources/collections/$name directory
DataCollections::markdown(string $name);

// Gets all YAML files in resources/collections/$name directory
DataCollections::yaml(string $name);

// Gets all JSON files in resources/collections/$name directory
DataCollections::json(string $name, bool $asArray = false);
```

See the [File-based Collections](collections) documentation for the full details.


## File Includes

The Includes facade provides a simple way to access partials in the includes directory.

If the file does not exist, the method will return `null`.
You can also supply a default value as the second argument.
Both Markdown and Blade includes will be rendered to HTML.

### Using Includes

Includes are stored in the `resources/includes` directory. You can access them using the `Includes` facade.

```php
use Hyde\Support\Includes;

Includes::get('example.md');
Includes::get('example.md', 'Default content');
```

#### Markdown Includes

Gets the rendered Markdown of a partial file in the includes directory. Supplying the file extension is optional.

```php
use Hyde\Support\Includes;

Includes::markdown('footer');
Includes::markdown('footer.md');

// With default value if the file does not exist
Includes::markdown('footer', 'Default content');
```

#### Blade Includes

Gets the rendered Blade of a partial file in the includes directory. Supplying the file extension is optional.

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


## Reading time helper

The `ReadingTime` helper provides a simple way to calculate the reading time of a given string, for example a blog post.

### Create a new `ReadingTime` instance

There are a few ways to create a new `ReadingTime` instance. Either create a new instance directly, or use the static `fromString` or `fromFile`  helpers.

In all cases, you will end up with a `ReadingTime` object that you can use to get the reading time.

```php
// Via constructor
$time = new ReadingTime('Input text string');

// Via static method
$time = ReadingTime::fromString('Input text string');

// Via static method (from file)
$time = ReadingTime::fromFile('path/to/file.txt');
```

### Get the reading time string

To make things really easy, the `ReadingTime` instance can be automatically cast to a human-readable string with the default formatting.

```php
(string) ReadingTime::fromString('Input text string'); // 1min, 0sec
```

```blade
{{ ReadingTime::fromString('Input text string') }} // 1min, 0sec
```

You can also call the `getFormatted` method directly.

```php
ReadingTime::fromString('Input text string')->getFormatted(); // 1min, 0sec
```

### Get the reading time data

We also provide a few methods to get the reading time data directly.

```php
// Get the reading time in seconds
$time->getSeconds();

// Get the reading time in minutes (rounded down)
$time->getMinutes();

// Get the remaining seconds after the rounded down minutes
// (Perfect for showing after the `getMinutes()` value)
$time->getSecondsOver();

// Get the word count of the input string
$time->getWordCount();
```

### Custom formatting

Additionally, there are several ways to customize the output format.

#### Specify sprintf format

The `getFormatted` method accepts a `sprintf` format string as the first argument.

```php
// The default format
$time->getFormatted('%dmin, %dsec');

// Custom format
$time->getFormatted('%d minutes and %d seconds');
```

The first `%d` will be replaced with the minutes, and the second `%d` will be replaced with the seconds.

#### Format using a custom callback

You can also use a custom callback to format the reading time string. This is perfect if you want to create custom formatting logic.

The closure will receive the minutes and seconds as integers and should return a string.

```php
$time->formatUsingClosure(function (int $minutes, int $seconds): string {
    return "$minutes minutes, $seconds seconds";
}); // 1 minutes, 30 seconds
```

## Pagination utility

The `Pagination` class provides utilities to help you create custom pagination components.

Hyde comes with a simple pagination view that you can use, but you can also use the utility to create your own custom pagination components.
You can of course also publish and modify the default pagination view to fit your needs.

The paginator is designed to paginate Hyde pages and their routes, but can also be used with other data sources.

### Usage

To use the pagination component which is generic by design, you need to create the `Pagination` instance yourself, with the data you want to paginate.

To get started, simply create a paginator instance with a collection or array of items (like pages), and render the component.
You also need to pass the current page being rendered (if you're on pagination page 3, pass that to the constructor).
