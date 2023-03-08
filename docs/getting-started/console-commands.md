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


| Command                                 | Description                                                                                  |
|-----------------------------------------|----------------------------------------------------------------------------------------------|
| [`build`](#build)                       | Build the static site                                                                        |
| [`serve`](#serve)                       | Start the realtime compiler server.                                                          |
| [`rebuild`](#rebuild)                   | Run the static site builder for a single file                                                |
| [`build:rss`](#build-rss)               | Generate the RSS feed                                                                        |
| [`build:search`](#build-search)         | Generate the docs/search.json                                                                |
| [`build:sitemap`](#build-sitemap)       | Generate the sitemap.xml                                                                     |
| [`make:page`](#make-page)               | Scaffold a new Markdown, Blade, or documentation page file                                   |
| [`make:post`](#make-post)               | Scaffold a new Markdown blog post file                                                       |
| [`publish:configs`](#publish-configs)   | Publish the default configuration files                                                      |
| [`publish:homepage`](#publish-homepage) | Publish one of the default homepages to index.blade.php.                                     |
| [`publish:views`](#publish-views)       | Publish the hyde components for customization. Note that existing files will be overwritten. |
| [`vendor:publish`](#vendor-publish)     | Publish any publishable assets from vendor packages                                          |
| [`route:list`](#route-list)             | Display all registered routes.                                                               |
| [`validate`](#validate)                 | Run a series of tests to validate your setup and help you optimize your site.                |
| [`list`](#list)                         | List all available commands.                                                                 |


---

[Blade]: {!! class_exists(\App\CommandDocumentationController::class) ? (new \App\CommandDocumentationController())() : 'Command list not available'; !!}
