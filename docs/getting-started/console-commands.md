---
navigation:
    priority: 5
abstract: "HydePHP's command line is built on Laravel Zero, so everything here will feel like Artisan. This page covers the core commands you'll reach for daily to scaffold, build, and publish your site"
---

# Console Commands

The primary way of interacting with Hyde is through the command line using the HydeCLI.

If you have ever used the Artisan Console in Laravel you will feel right at home,
the HydeCLI is based on Artisan after all!

## Introduction

To use the HydeCLI, run `php hyde` from your project directory followed by a command.

All HydeCLI commands start with `php hyde`. Anything in `[brackets]` is optional.
If an argument or option value has a space in it, it needs to be wrapped in quotes.

The HydeCLI exists at the root of your application as the `hyde` script and provides a number of helpful commands that can
assist you while you build your site. To view a list of all available Hyde commands, you may use the list command:

```terminal
php hyde list
```

### Got stuck? The CLI can help.

Every command also includes a "help" screen which displays and describes the command's available arguments and options.
To view a help screen, precede the name of the command with `help`:

```terminal
php hyde help <command>
```

You can also always add `--help` to a command to show detailed usage information.

```terminal
php hyde <command> --help
```

## Available Commands

Here is a quick reference of all the available commands. You can also run `php hyde list` to see this list.

| Command                                 | Description                                                                                 |
|-----------------------------------------|---------------------------------------------------------------------------------------------|
| [`build`](#build)                       | Build the static site                                                                       |
| [`serve`](#serve)                       | Start the realtime compiler server                                                          |
| [`build:rss`](#build-rss)               | Generate the RSS feed                                                                       |
| [`build:search`](#build-search)         | Generate the `docs/search.json` file                                                        |
| [`build:sitemap`](#build-sitemap)       | Generate the `sitemap.xml` file                                                             |
| [`make:page`](#make-page)               | Scaffold a new Markdown, Blade, or documentation page file                                  |
| [`make:post`](#make-post)               | Scaffold a new Markdown blog post file                                                      |
| [`publish`](#publish)                   | Publish Hyde views and starter pages for customization                                      |
| [`vendor:publish`](#vendor-publish)     | Publish any publishable assets from vendor packages, including the Hyde config files        |
| [`route:list`](#route-list)             | Display all registered routes                                                               |
| [`validate`](#validate)                 | Run a series of tests to validate your setup and help you optimize your site                |
| [`list`](#available-commands)           | List all available commands                                                                 |

## Build the Static Site

<a name="build" style="display: inline-block; position: absolute; margin-top: -5rem;"></a>

```terminal
php hyde build [--vite] [--pretty-urls] [--no-api]
```

Build the static site

#### Options

|                 |                                            |
|-----------------|--------------------------------------------|
| `--vite`        | Build frontend assets using Vite           |
| `--pretty-urls` | Should links in output use pretty URLs?    |
| `--no-api`      | Disable API calls, for example, Torchlight |

## Start the Realtime Compiler Server

<a name="serve" style="display: inline-block; position: absolute; margin-top: -5rem;"></a>

```terminal
php hyde serve [--host [HOST]] [--port [PORT]] [--vite]
```

Start the realtime compiler server.

#### Options

|           |                        |
|-----------|------------------------|
| `--host=` | [default: "localhost"] |
| `--port=` | [default: 8080]        |

## Test and validate your project to optimize your site

<a name="validate" style="display: inline-block; position: absolute; margin-top: -5rem;"></a>

```terminal
php hyde validate
```

Run a series of tests to validate your setup and help you optimize your site.

## Generate the RSS Feed

<a name="build-rss" style="display: inline-block; position: absolute; margin-top: -5rem;"></a>

```terminal
php hyde build:rss
```

Generate the RSS feed

Compiles the registered RSS feed page. The command fails if no feed page is registered.

## Generate the `docs/search.json` file

<a name="build-search" style="display: inline-block; position: absolute; margin-top: -5rem;"></a>

```terminal
php hyde build:search
```

Generate the `docs/search.json` file

## Generate the `sitemap.xml` file

<a name="build-sitemap" style="display: inline-block; position: absolute; margin-top: -5rem;"></a>

```terminal
php hyde build:sitemap
```

Generate the `sitemap.xml` file

Compiles the registered `sitemap.xml` page. The command fails if no sitemap page is registered.

## Scaffold a new Markdown, Blade, or documentation page file

<a name="make-page" style="display: inline-block; position: absolute; margin-top: -5rem;"></a>

```terminal
php hyde make:page [--type [TYPE]] [--blade] [--docs] [--force] [--] [<title>]
```

Scaffold a new Markdown, Blade, or documentation page file

#### Arguments & Options

|                   |                                                                            |
|-------------------|----------------------------------------------------------------------------|
| `title`           | The name of the page file to create. Will be used to generate the filename |
| `--type=markdown` | The type of page to create (markdown, blade, or docs)                      |
| `--blade`         | Create a Blade page                                                        |
| `--docs`          | Create a Documentation page                                                |
| `--force`         | Overwrite any existing files                                               |

## Scaffold a new Markdown blog post file

<a name="make-post" style="display: inline-block; position: absolute; margin-top: -5rem;"></a>

```terminal
php hyde make:post [--force] [--] [<title>]
```

Scaffold a new Markdown blog post file

#### Arguments & Options

|           |                                                                            |
|-----------|----------------------------------------------------------------------------|
| `title`   | The title for the Post. Will also be used to generate the filename         |
| `--force` | Should the generated file overwrite existing posts with the same filename? |

## Publish Hyde Views and Starter Pages

<a name="publish" style="display: inline-block; position: absolute; margin-top: -5rem;"></a>

```terminal
php hyde publish [--layouts] [--components] [--all] [--page[=NAME]] [--to=PATH] [--force]
```

Run without any options to pick between publishing views and a starter page interactively. Each option skips a step:
`--layouts` and `--components` scope the view picker, `--all` publishes every view without asking, and `--page=NAME`
publishes a starter page such as `welcome`, `posts`, `blank`, or `404`.

Views are published to `resources/views/vendor/hyde`, and starter pages to `_pages`. Files you have modified are never
overwritten without your confirmation or `--force`.

#### Options

|                 |                                                                    |
|-----------------|--------------------------------------------------------------------|
| `--layouts`     | Scope publishing to the Hyde layout views                          |
| `--components`  | Scope publishing to the Hyde component views                       |
| `--all`         | Publish all Hyde views without the picker                          |
| `--page[=NAME]` | Publish a starter page, optionally by name (e.g. `--page=welcome`) |
| `--to=PATH`     | Destination path for a published page (pages only)                 |
| `--force`       | Overwrite files that you have modified                             |

>info The Hyde configuration files are published with [`vendor:publish`](#vendor-publish) instead, using the `hyde-config` tag.

## Display All Registered Routes.

<a name="route-list" style="display: inline-block; position: absolute; margin-top: -5rem;"></a>

```terminal
php hyde route:list
```

Display all registered routes.

## Publish any publishable assets from vendor packages

<a name="vendor-publish" style="display: inline-block; position: absolute; margin-top: -5rem;"></a>

```terminal
php hyde vendor:publish [--existing] [--force] [--all] [--provider [PROVIDER]] [--tag [TAG]]
```

Publish any publishable assets from vendor packages. This is also where the Hyde configuration files
(`hyde.php`, `docs.php`, `markdown.php`, `view.php`, `cache.php`, and `commands.php`) are published from:

```terminal
php hyde vendor:publish --tag=hyde-config
```

Existing files are skipped unless you add `--force`.

#### Options

|               |                                                                            |
|---------------|----------------------------------------------------------------------------|
| `--existing`  | Publish and overwrite only the files that have already been published      |
| `--force`     | Overwrite any existing files                                               |
| `--all`       | Publish assets for all service providers without prompt                    |
| `--provider=` | The service provider that has assets you want to publish                   |
| `--tag=`      | One or many tags that have assets you want to publish \n- Is multiple: yes |
