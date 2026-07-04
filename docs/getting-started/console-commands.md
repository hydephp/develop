---
navigation:
    priority: 5
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

```bash
php hyde list
```

### Got stuck? The CLI can help.

Every command also includes a "help" screen which displays and describes the command's available arguments and options.
To view a help screen, precede the name of the command with `help`:

```bash
php hyde help <command>
```

You can also always add `--help` to a command to show detailed usage information.

```bash
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
| [`vendor:publish`](#vendor-publish)     | Publish any publishable assets from vendor packages (including the Hyde config files)       |
| [`route:list`](#route-list)             | Display all registered routes                                                               |
| [`validate`](#validate)                 | Run a series of tests to validate your setup and help you optimize your site                |
| [`list`](#available-commands)           | List all available commands                                                                 |

## Build the Static Site

<a name="build" style="display: inline-block; position: absolute; margin-top: -5rem;"></a>

```bash
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

```bash
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

```bash
php hyde validate
```

Run a series of tests to validate your setup and help you optimize your site.

## Generate the RSS Feed

<a name="build-rss" style="display: inline-block; position: absolute; margin-top: -5rem;"></a>

```bash
php hyde build:rss
```

Generate the RSS feed

## Generate the `docs/search.json` file

<a name="build-search" style="display: inline-block; position: absolute; margin-top: -5rem;"></a>

```bash
php hyde build:search
```

Generate the `docs/search.json` file

## Generate the `sitemap.xml` file

<a name="build-sitemap" style="display: inline-block; position: absolute; margin-top: -5rem;"></a>

```bash
php hyde build:sitemap
```

Generate the `sitemap.xml` file

## Scaffold a new Markdown, Blade, or documentation page file

<a name="make-page" style="display: inline-block; position: absolute; margin-top: -5rem;"></a>

```bash
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

```bash
php hyde make:post [--force] [--] [<title>]
```

Scaffold a new Markdown blog post file

#### Arguments & Options

|           |                                                                            |
|-----------|----------------------------------------------------------------------------|
| `title`   | The title for the Post. Will also be used to generate the filename         |
| `--force` | Should the generated file overwrite existing posts with the same filename? |

## Publish Hyde views and starter pages for customization

<a name="publish" style="display: inline-block; position: absolute; margin-top: -5rem;"></a>

```bash
php hyde publish [--layouts] [--components] [--all] [--page[=NAME]] [--to=PATH] [--force]
```

Publish Hyde views and starter pages for customization.

With no flags, `publish` runs an interactive wizard that lets you choose between publishing
**views** (Hyde's Blade layouts and components) or **a starter page** (such as a homepage or 404 page).
Each flag simply skips a step of the wizard. Existing files that you have modified are never overwritten
without your confirmation or the `--force` flag.

#### Options

| Option         | Description                                                  |
|----------------|--------------------------------------------------------------|
| `--layouts`    | Scope publishing to the Hyde layout views                    |
| `--components` | Scope publishing to the Hyde component views                 |
| `--all`        | Publish all Hyde views without the picker                    |
| `--page[=NAME]`| Publish a starter page, optionally by name (e.g. `--page=welcome`) |
| `--to=PATH`    | Destination path for a published page (pages only)           |
| `--force`      | Overwrite files that you have modified                       |

Published views land in `resources/views/vendor/hyde`, and published pages land in `_pages`.

>info **Tip:** To publish the Hyde configuration files, use `php hyde vendor:publish --tag=hyde-config`. See the [`vendor:publish`](#vendor-publish) command below.

## Display All Registered Routes.

<a name="route-list" style="display: inline-block; position: absolute; margin-top: -5rem;"></a>

```bash
php hyde route:list
```

Display all registered routes.

## Publish any publishable assets from vendor packages

<a name="vendor-publish" style="display: inline-block; position: absolute; margin-top: -5rem;"></a>

```bash
php hyde vendor:publish [--existing] [--force] [--all] [--provider [PROVIDER]] [--tag [TAG]]
```

Publish any publishable assets from vendor packages. This is the advanced Laravel publishing path,
and is also where the Hyde configuration files are published from, using the `hyde-config` tag:

```bash
php hyde vendor:publish --tag=hyde-config
```

This publishes the Hyde-owned config files (`hyde.php`, `docs.php`, `markdown.php`, `view.php`, `cache.php`, and `commands.php`) to your project's `config` directory.

#### Options

|               |                                                                            |
|---------------|----------------------------------------------------------------------------|
| `--existing`  | Publish and overwrite only the files that have already been published      |
| `--force`     | Overwrite any existing files                                               |
| `--all`       | Publish assets for all service providers without prompt                    |
| `--provider=` | The service provider that has assets you want to publish                   |
| `--tag=`      | One or many tags that have assets you want to publish \n- Is multiple: yes |

## Deprecated publishing commands

The following commands from earlier versions of Hyde still work but are deprecated, and print a notice
pointing to their replacement. They will be removed in a future major version, so prefer the new commands.

| Deprecated command            | Use instead                                        |
|-------------------------------|----------------------------------------------------|
| `publish:views [group]`       | `publish --layouts` / `publish --components`       |
| `publish:configs`             | `vendor:publish --tag=hyde-config`                 |
| `publish:homepage [template]` | `publish --page=[template]`                        |
