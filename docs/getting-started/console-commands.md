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
// torchlight! {"lineNumbers": false}
php hyde list
```

### Got stuck? The CLI can help.

Every command also includes a "help" screen which displays and describes the command's available arguments and options.
To view a help screen, precede the name of the command with `help`:

```bash
// torchlight! {"lineNumbers": false}
php hyde help <command>
```

You can also always add `--help` to a command to show detailed usage information.

```bash
// torchlight! {"lineNumbers": false}
php hyde <command> --help
```


## Available Commands

Here is a quick reference of all the available commands. You can also run `php hyde list` to see this list.

| Command                                 | Description                                                                                 |
|-----------------------------------------|---------------------------------------------------------------------------------------------|
| [`build`](#build)                       | Build the static site                                                                       |
| [`serve`](#serve)                       | Start the realtime compiler server                                                          |
| [`rebuild`](#rebuild)                   | Run the static site builder for a single file                                               |
| [`build:rss`](#build-rss)               | Generate the RSS feed                                                                       |
| [`build:search`](#build-search)         | Generate the `docs/search.json` file                                                        |
| [`build:sitemap`](#build-sitemap)       | Generate the `sitemap.xml` file                                                             |
| [`make:page`](#make-page)               | Scaffold a new Markdown, Blade, or documentation page file                                  |
| [`make:post`](#make-post)               | Scaffold a new Markdown blog post file                                                      |
| [`publish:configs`](#publish-configs)   | Publish the default configuration files                                                     |
| [`publish:homepage`](#publish-homepage) | Publish one of the default homepages as `index.blade.php`                                   |
| [`publish:views`](#publish-views)       | Publish the hyde components for customization. Note that existing files will be overwritten |
| [`vendor:publish`](#vendor-publish)     | Publish any publishable assets from vendor packages                                         |
| [`route:list`](#route-list)             | Display all registered routes                                                               |
| [`validate`](#validate)                 | Run a series of tests to validate your setup and help you optimize your site                |
| [`list`](#available-commands)           | List all available commands                                                                 |


## Build the static site

<a name="build" style="display: inline-block; position: absolute; margin-top: -5rem;"></a>

```bash
// torchlight! {"lineNumbers": false}
php hyde build [--run-dev] [--run-prod] [--run-prettier] [--pretty-urls] [--no-api]
```

Build the static site

#### Options

|                  |                                            |
|------------------|--------------------------------------------|
| `--run-dev`      | Run the NPM dev script after build         |
| `--run-prod`     | Run the NPM prod script after build        |
| `--run-prettier` | Format the output using NPM Prettier       |
| `--pretty-urls`  | Should links in output use pretty URLs?    |
| `--no-api`       | Disable API calls, for example, Torchlight |


## Run the static site builder for a single file

<a name="rebuild" style="display: inline-block; position: absolute; margin-top: -5rem;"></a>

```bash
// torchlight! {"lineNumbers": false}
php hyde rebuild <path>
```

Run the static site builder for a single file

#### Arguments

|        |                                                                                |
|--------|--------------------------------------------------------------------------------|
| `path` | The relative file path (example: \_posts/hello-world.md) \n - Is required: yes |


## Start the realtime compiler server

<a name="serve" style="display: inline-block; position: absolute; margin-top: -5rem;"></a>

```bash
// torchlight! {"lineNumbers": false}
php hyde serve [--host [HOST]] [--port [PORT]]
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
// torchlight! {"lineNumbers": false}
php hyde validate
```

Run a series of tests to validate your setup and help you optimize your site.


## Generate the RSS feed

<a name="build-rss" style="display: inline-block; position: absolute; margin-top: -5rem;"></a>

```bash
// torchlight! {"lineNumbers": false}
php hyde build:rss
```

Generate the RSS feed


## Generate the docs/search.json

<a name="build-search" style="display: inline-block; position: absolute; margin-top: -5rem;"></a>

```bash
// torchlight! {"lineNumbers": false}
php hyde build:search
```

Generate the docs/search.json


## Generate the sitemap.xml

<a name="build-sitemap" style="display: inline-block; position: absolute; margin-top: -5rem;"></a>

```bash
// torchlight! {"lineNumbers": false}
php hyde build:sitemap
```

Generate the sitemap.xml


## Scaffold a new Markdown, Blade, or documentation page file

<a name="make-page" style="display: inline-block; position: absolute; margin-top: -5rem;"></a>

```bash
// torchlight! {"lineNumbers": false}
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
// torchlight! {"lineNumbers": false}
php hyde make:post [--force] [--] [<title>]
```

Scaffold a new Markdown blog post file

#### Arguments & Options

|           |                                                                            |
|-----------|----------------------------------------------------------------------------|
| `title`   | The title for the Post. Will also be used to generate the filename         |
| `--force` | Should the generated file overwrite existing posts with the same filename? |


## Publish the default configuration files

<a name="publish-configs" style="display: inline-block; position: absolute; margin-top: -5rem;"></a>

```bash
// torchlight! {"lineNumbers": false}
php hyde publish:configs
```

Publish the default configuration files


## Publish one of the default homepages to index.blade.php.

<a name="publish-homepage" style="display: inline-block; position: absolute; margin-top: -5rem;"></a>

```bash
// torchlight! {"lineNumbers": false}
php hyde publish:homepage [--force] [--] [<homepage>]
```

Publish one of the default homepages to index.blade.php.

#### Arguments & Options

|            |                                 |
|------------|---------------------------------|
| `homepage` | The name of the page to publish |
| `--force`  | Overwrite any existing files    |


## Publish the hyde components for customization

<a name="publish-views" style="display: inline-block; position: absolute; margin-top: -5rem;"></a>

```bash
// torchlight! {"lineNumbers": false}
php hyde publish:views [<category>]
```

Publish the hyde components for customization. Note that existing files will be overwritten.

#### Arguments

|            |                         |
|------------|-------------------------|
| `category` | The category to publish |


## Display all registered routes.

<a name="route-list" style="display: inline-block; position: absolute; margin-top: -5rem;"></a>

```bash
// torchlight! {"lineNumbers": false}
php hyde route:list
```

Display all registered routes.


## Publish any publishable assets from vendor packages

<a name="vendor-publish" style="display: inline-block; position: absolute; margin-top: -5rem;"></a>

```bash
// torchlight! {"lineNumbers": false}
php hyde vendor:publish [--existing] [--force] [--all] [--provider [PROVIDER]] [--tag [TAG]]
```

Publish any publishable assets from vendor packages

#### Options

|               |                                                                            |
|---------------|----------------------------------------------------------------------------|
| `--existing`  | Publish and overwrite only the files that have already been published      |
| `--force`     | Overwrite any existing files                                               |
| `--all`       | Publish assets for all service providers without prompt                    |
| `--provider=` | The service provider that has assets you want to publish                   |
| `--tag=`      | One or many tags that have assets you want to publish \n- Is multiple: yes |
