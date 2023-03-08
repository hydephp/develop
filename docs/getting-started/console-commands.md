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


### Got stuck? The CLI can help.

You can always run the base command `php hyde`, or `php hyde list`, to show the list of commands.

```bash
php hyde # or `php hyde list`
```

You can also always add `--help` to a command to show detailed usage information.
```bash
php hyde <command> --help
```

## Available Commands

Here is a quick reference of all the available commands. You can also run `php hyde list` to see this list.


| Command                                | Description                                                                                  |
|----------------------------------------|----------------------------------------------------------------------------------------------|
| [`build`](#build)                      | Build the static site                                                                        |
| [`serve`](#serve)                      | Start the realtime compiler server.                                                          |
| [`rebuild`](#rebuild)                  | Run the static site builder for a single file                                                |
| [`build:rss`](#buildrss)               | Generate the RSS feed                                                                        |
| [`build:search`](#buildsearch)         | Generate the docs/search.json                                                                |
| [`build:sitemap`](#buildsitemap)       | Generate the sitemap.xml                                                                     |
| [`make:page`](#makepage)               | Scaffold a new Markdown, Blade, or documentation page file                                   |
| [`make:post`](#makepost)               | Scaffold a new Markdown blog post file                                                       |
| [`publish:configs`](#publishconfigs)   | Publish the default configuration files                                                      |
| [`publish:homepage`](#publishhomepage) | Publish one of the default homepages to index.blade.php.                                     |
| [`publish:views`](#publishviews)       | Publish the hyde components for customization. Note that existing files will be overwritten. |
| [`vendor:publish`](#vendorpublish)     | Publish any publishable assets from vendor packages                                          |
| [`route:list`](#routelist)             | Display all registered routes.                                                               |
| [`validate`](#validate)                | Run a series of tests to validate your setup and help you optimize your site.                |
| [`list`](#list)                        | List all available commands.                                                                 |



`build`
-------

Build the static site

### Usage

* `build [--run-dev] [--run-prod] [--run-prettier] [--pretty-urls] [--no-api]`

Build the static site

### Options

#### `--run-dev`

Run the NPM dev script after build

* Accept value: no
* Is value required: no
* Default: `false`

#### `--run-prod`

Run the NPM prod script after build

* Accept value: no
* Is value required: no
* Default: `false`

#### `--run-prettier`

Format the output using NPM Prettier

* Accept value: no
* Is value required: no
* Default: `false`

#### `--pretty-urls`

Should links in output use pretty URLs?

* Accept value: no
* Is value required: no
* Default: `false`

#### `--no-api`

Disable API calls, for example, Torchlight

* Accept value: no
* Is value required: no
* Default: `false`

`rebuild`
---------

Run the static site builder for a single file

### Usage

* `rebuild <path>`

Run the static site builder for a single file

### Arguments

#### `path`

The relative file path (example: _posts/hello-world.md)

* Is required: yes
* Is array: no
* Default: `NULL`

### Options

`serve`
-------

Start the realtime compiler server.

### Usage

* `serve [--host [HOST]] [--port [PORT]]`

Start the realtime compiler server.

### Options

#### `--host`

[default: "localhost"]

* Accept value: yes
* Is value required: no
* Default: `NULL`

#### `--port`

[default: 8080]

* Accept value: yes
* Is value required: no
* Default: `NULL`

`validate`
----------

Run a series of tests to validate your setup and help you optimize your site.

### Usage

* `validate`

Run a series of tests to validate your setup and help you optimize your site.

### Options

`build:rss`
-----------

Generate the RSS feed

### Usage

* `build:rss`

Generate the RSS feed

### Options

`build:search`
--------------

Generate the docs/search.json

### Usage

* `build:search`

Generate the docs/search.json

### Options

`build:sitemap`
---------------

Generate the sitemap.xml

### Usage

* `build:sitemap`

Generate the sitemap.xml

### Options

`make:page`
-----------

Scaffold a new Markdown, Blade, or documentation page file

### Usage

* `make:page [--type [TYPE]] [--blade] [--docs] [--force] [--] [<title>]`

Scaffold a new Markdown, Blade, or documentation page file

### Arguments

#### `title`

The name of the page file to create. Will be used to generate the slug

* Is required: no
* Is array: no
* Default: `NULL`

### Options

#### `--type`

The type of page to create (markdown, blade, or docs)

* Accept value: yes
* Is value required: no
* Default: `'markdown'`

#### `--blade`

Create a Blade page

* Accept value: no
* Is value required: no
* Default: `false`

#### `--docs`

Create a Documentation page

* Accept value: no
* Is value required: no
* Default: `false`

#### `--force`

Overwrite any existing files

* Accept value: no
* Is value required: no
* Default: `false`

`make:post`
-----------

Scaffold a new Markdown blog post file

### Usage

* `make:post [--force] [--] [<title>]`

Scaffold a new Markdown blog post file

### Arguments

#### `title`

The title for the Post. Will also be used to generate the filename

* Is required: no
* Is array: no
* Default: `NULL`

### Options

#### `--force`

Should the generated file overwrite existing posts with the same filename?

* Accept value: no
* Is value required: no
* Default: `false`

`publish:configs`
-----------------

Publish the default configuration files

### Usage

* `publish:configs`

Publish the default configuration files

### Options

`publish:homepage`
------------------

Publish one of the default homepages to index.blade.php.

### Usage

* `publish:homepage [--force] [--] [<homepage>]`

Publish one of the default homepages to index.blade.php.

### Arguments

#### `homepage`

The name of the page to publish

* Is required: no
* Is array: no
* Default: `NULL`

### Options

#### `--force`

Overwrite any existing files

* Accept value: no
* Is value required: no
* Default: `false`

`publish:views`
---------------

Publish the hyde components for customization. Note that existing files will be overwritten.

### Usage

* `publish:views [<category>]`

Publish the hyde components for customization. Note that existing files will be overwritten.

### Arguments

#### `category`

The category to publish

* Is required: no
* Is array: no
* Default: `NULL`

### Options

`route:list`
------------

Display all registered routes.

### Usage

* `route:list`

Display all registered routes.

### Options

`torchlight:install`
--------------------

Install the Torchlight config file into your app.

### Usage

* `torchlight:install`

Install the Torchlight config file into your app.

### Options

`vendor:publish`
----------------

Publish any publishable assets from vendor packages

### Usage

* `vendor:publish [--existing] [--force] [--all] [--provider [PROVIDER]] [--tag [TAG]]`

Publish any publishable assets from vendor packages

### Options

#### `--existing`

Publish and overwrite only the files that have already been published

* Accept value: no
* Is value required: no
* Default: `false`

#### `--force`

Overwrite any existing files

* Accept value: no
* Is value required: no
* Default: `false`

#### `--all`

Publish assets for all service providers without prompt

* Accept value: no
* Is value required: no
* Default: `false`

#### `--provider`

The service provider that has assets you want to publish

* Accept value: yes
* Is value required: no
* Default: `NULL`

#### `--tag`

One or many tags that have assets you want to publish

* Accept value: yes
* Is value required: no
* Is multiple: yes
* Default: `array ()`
